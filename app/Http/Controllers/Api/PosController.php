<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    // Fast product catalog search for POS terminal
    public function products(Request $request)
    {
        $query = Product::with([
            'category:id,name',
            'unit:id,name,short_name,allow_decimal',
            'variants' => function ($v) {
                $v->with('stock')->where('status', true);
            },
        ])->where('status', true);

        // Search by product name, SKU or barcode (ideal for barcode scanners)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($v) use ($search) {
                        $v->where('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', '=', $search);
                    });
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->take($request->get('limit', 40))->get();

        $items = $products->map(function ($product) {
            $defaultVariant = $product->variants->firstWhere('is_default', true)
                ?: $product->variants->first();

            $stock = (float) ($defaultVariant?->stock?->quantity ?? 0);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'category_name' => $product->category ? $product->category->name : 'General',
                'track_inventory' => (bool) $product->track_inventory,
                'low_stock_threshold' => (float) $product->low_stock_threshold,
                'unit' => [
                    'id' => $product->unit?->id,
                    'name' => $product->unit?->name,
                    'short_name' => $product->unit?->short_name ?: 'pcs',
                    'allow_decimal' => (bool) ($product->unit?->allow_decimal ?? false),
                ],
                'variant' => $defaultVariant ? [
                    'id' => $defaultVariant->id,
                    'sku' => $defaultVariant->sku,
                    'barcode' => $defaultVariant->barcode,
                    'selling_price' => (float) $defaultVariant->selling_price,
                    'mrp' => (float) $defaultVariant->mrp,
                    'tax_rate' => (float) $defaultVariant->tax_rate,
                ] : null,
                'stock' => $stock,
                'in_stock' => $stock > 0,
            ];
        })->filter(function ($item) {
            return $item['variant'] !== null;
        })->values();

        return response()->json([
            'data' => $items,
        ]);
    }

    // Process POS checkout
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string|in:cash,upi,card,other',
            'paid_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $sale = DB::transaction(function () use ($validated) {
            // Generate invoice number: POS-YYYYMMDD-0001
            $today = date('Ymd');
            $countToday = Sale::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'POS-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $taxAmount = 0;
            $itemsData = [];

            // Verify stock and compute amounts
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = ProductVariant::findOrFail($item['product_variant_id']);

                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $rowSubtotal = round($qty * $price, 2);

                $taxRate = isset($item['tax_rate']) ? (float) $item['tax_rate'] : 0;
                $rowTax = round($rowSubtotal * ($taxRate / 100), 2);
                $rowTotal = round($rowSubtotal + $rowTax, 2);

                // Stock check
                if ($product->track_inventory) {
                    $stock = Stock::where('product_id', $product->id)
                        ->where('product_variant_id', $variant->id)
                        ->lockForUpdate()
                        ->first();

                    $available = (float) ($stock?->quantity ?? 0);
                    if ($available < $qty) {
                        abort(422, "Insufficient stock for '{$product->name}'. Available: {$available}, Requested: {$qty}");
                    }
                }

                $subtotal += $rowSubtotal;
                $taxAmount += $rowTax;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $rowTax,
                    'subtotal' => $rowSubtotal,
                    'total' => $rowTotal,
                ];
            }

            $discount = isset($validated['discount_amount']) ? (float) $validated['discount_amount'] : 0;
            $totalAmount = max(0, round($subtotal + $taxAmount - $discount, 2));

            $paidAmount = (float) $validated['paid_amount'];
            $changeAmount = max(0, round($paidAmount - $totalAmount, 2));
            $actualPaid = min($paidAmount, $totalAmount);

            $paymentStatus = $actualPaid >= $totalAmount ? 'paid' : ($actualPaid > 0 ? 'partial' : 'unpaid');

            // Create Sale record
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'sale_date' => now(),
                'order_type' => 'pos',
                'status' => 'completed',
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => $actualPaid,
                'change_amount' => $changeAmount,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id() ?? null,
            ]);

            // Save line items & deduct stock
            foreach ($itemsData as $row) {
                $row['sale_id'] = $sale->id;
                SaleItem::create($row);

                $product = Product::find($row['product_id']);
                if ($product && $product->track_inventory) {
                    $stock = Stock::firstOrCreate(
                        [
                            'product_id' => $row['product_id'],
                            'product_variant_id' => $row['product_variant_id'],
                        ],
                        ['quantity' => 0]
                    );

                    $stock->quantity = max(0, $stock->quantity - $row['quantity']);
                    $stock->save();

                    StockTransaction::create([
                        'product_id' => $row['product_id'],
                        'product_variant_id' => $row['product_variant_id'],
                        'type' => 'out',
                        'quantity' => $row['quantity'],
                        'balance_after' => $stock->quantity,
                        'reason' => 'POS Sale #' . $sale->invoice_number,
                        'notes' => 'Sold via POS Counter',
                        'user_id' => auth()->id() ?? null,
                    ]);
                }
            }

            // Update customer totals if registered customer
            if (!empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
                if ($customer) {
                    $customer->increment('total_orders');
                    $customer->increment('total_spent', $totalAmount);
                }
            }

            return $sale;
        });

        return response()->json([
            'message' => 'Sale completed successfully.',
            'data' => $sale->load([
                'customer',
                'items.product.unit',
                'items.variant:id,sku,variant_name,barcode'
            ]),
        ], 201);
    }

    // View specific invoice
    public function invoice(Sale $sale)
    {
        return response()->json([
            'data' => $sale->load([
                'customer',
                'cashier:id,name',
                'items.product.unit',
                'items.variant:id,sku,variant_name,barcode'
            ]),
        ]);
    }

    // Today's recent POS sales history
    public function history(Request $request)
    {
        $sales = Sale::with(['customer', 'items'])
            ->where('order_type', 'pos')
            ->whereDate('sale_date', today())
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'data' => $sales
        ]);
    }
}

