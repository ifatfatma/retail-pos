<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // List inventory items with stock information
    public function index(Request $request)
    {
        $query = Product::with([
            'category:id,name',
            'brand:id,name',
            'unit:id,name,short_name,allow_decimal',
            'variants' => function ($v) {
                $v->with('stock');
            },
        ])->where('track_inventory', true);

        // Search by product name, SKU or barcode
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($v) use ($search) {
                        $v->where('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Stock status filter (in_stock, low_stock, out_of_stock)
        $stockStatus = $request->get('stock_status', 'all');

        $products = $query->latest()->paginate($request->get('per_page', 10));

        // Transform results with calculated stock levels
        $transformed = $products->getCollection()->map(function ($product) {
            $defaultVariant = $product->variants->firstWhere('is_default', true) 
                ?: $product->variants->first();

            $totalStock = $product->variants->sum(function ($variant) {
                return (float) ($variant->stock->quantity ?? 0);
            });

            $threshold = (float) $product->low_stock_threshold;

            if ($totalStock <= 0) {
                $status = 'out_of_stock';
            } elseif ($totalStock <= $threshold) {
                $status = 'low_stock';
            } else {
                $status = 'in_stock';
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category ? $product->category->name : '-',
                'brand' => $product->brand ? $product->brand->name : '-',
                'unit' => [
                    'id' => $product->unit->id ?? null,
                    'name' => $product->unit->name ?? '',
                    'short_name' => $product->unit->short_name ?? '',
                    'allow_decimal' => (bool) ($product->unit->allow_decimal ?? false),
                ],
                'default_variant' => $defaultVariant ? [
                    'id' => $defaultVariant->id,
                    'sku' => $defaultVariant->sku,
                    'barcode' => $defaultVariant->barcode,
                    'purchase_price' => $defaultVariant->purchase_price,
                    'selling_price' => $defaultVariant->selling_price,
                    'stock_id' => $defaultVariant->stock->id ?? null,
                    'quantity' => (float) ($defaultVariant->stock->quantity ?? 0),
                ] : null,
                'total_stock' => $totalStock,
                'low_stock_threshold' => $threshold,
                'stock_status' => $status,
                'variants_count' => $product->variants->count(),
                'status' => $product->status,
            ];
        });

        // If client requested stock status filtering in PHP (for computed aggregation)
        if ($stockStatus && $stockStatus !== 'all') {
            $filtered = $transformed->filter(function ($item) use ($stockStatus) {
                return $item['stock_status'] === $stockStatus;
            })->values();

            $products->setCollection($filtered);
        } else {
            $products->setCollection($transformed);
        }

        return response()->json($products);
    }

    // Inventory metrics summary
    public function summary()
    {
        $products = Product::with(['variants.stock'])->where('track_inventory', true)->get();

        $totalItems = $products->count();
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;
        $totalQuantity = 0;

        foreach ($products as $product) {
            $stock = $product->variants->sum(function ($variant) {
                return (float) ($variant->stock->quantity ?? 0);
            });

            $totalQuantity += $stock;
            $threshold = (float) $product->low_stock_threshold;

            if ($stock <= 0) {
                $outOfStock++;
            } elseif ($stock <= $threshold) {
                $lowStock++;
            } else {
                $inStock++;
            }
        }

        return response()->json([
            'total_items' => $totalItems,
            'in_stock' => $inStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_quantity' => round($totalQuantity, 3),
        ]);
    }

    // Perform stock adjustment
    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $productId = $validated['product_id'];
        $type = $validated['type'];
        $inputQty = (float) $validated['quantity'];

        $result = DB::transaction(function () use ($productId, $validated, $type, $inputQty) {
            // Find variant
            if (!empty($validated['product_variant_id'])) {
                $variant = ProductVariant::where('product_id', $productId)
                    ->where('id', $validated['product_variant_id'])
                    ->firstOrFail();
            } else {
                $variant = ProductVariant::where('product_id', $productId)
                    ->where('is_default', true)
                    ->first()
                    ?: ProductVariant::where('product_id', $productId)->firstOrFail();
            }

            // Lock or create stock record
            $stock = Stock::where('product_id', $productId)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = Stock::create([
                    'product_id' => $productId,
                    'product_variant_id' => $variant->id,
                    'quantity' => 0,
                ]);
            }

            $currentQty = (float) $stock->quantity;
            $newQty = $currentQty;
            $loggedQty = $inputQty;

            if ($type === 'in') {
                $newQty = $currentQty + $inputQty;
            } elseif ($type === 'out') {
                if ($inputQty > $currentQty) {
                    abort(422, "Cannot deduct {$inputQty} units. Current stock is only {$currentQty}.");
                }
                $newQty = $currentQty - $inputQty;
            } elseif ($type === 'adjustment') {
                $newQty = $inputQty;
                $loggedQty = abs($newQty - $currentQty);
            }

            $stock->quantity = $newQty;
            $stock->save();

            // Record transaction
            $transaction = StockTransaction::create([
                'product_id' => $productId,
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $loggedQty,
                'balance_after' => $newQty,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id() ?? null,
            ]);

            return [
                'stock' => $stock,
                'transaction' => $transaction,
                'current_quantity' => $newQty,
            ];
        });

        return response()->json([
            'message' => 'Stock adjusted successfully.',
            'data' => $result,
        ]);
    }

    // List recent stock transactions across all products
    public function transactions(Request $request)
    {
        $query = StockTransaction::with([
            'product:id,name,unit_id',
            'product.unit:id,name,short_name',
            'variant:id,sku,variant_name',
            'user:id,name'
        ]);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type') && in_array($request->type, ['in', 'out', 'adjustment'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('product', function ($p) use ($search) {
                $p->where('name', 'like', "%{$search}%");
            })->orWhereHas('variant', function ($v) use ($search) {
                $v->where('sku', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($transactions);
    }

    // History for a specific variant or product
    public function history($productId)
    {
        $history = StockTransaction::with([
            'variant:id,sku,variant_name',
            'user:id,name'
        ])
            ->where('product_id', $productId)
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'data' => $history
        ]);
    }
}

