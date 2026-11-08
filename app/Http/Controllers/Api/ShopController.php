<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    // Public store product catalog with live stock
    public function products(Request $request)
    {
        $query = Product::with([
            'category:id,name,name_ar,slug',
            'unit:id,name,name_ar,short_name,short_name_ar,allow_decimal',
            'images',
            'variants' => function ($v) {
                $v->with('stock')->where('status', true);
            },
        ])->where('status', true);

        // Search query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($v) use ($search) {
                        $v->where('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', '=', $search);
                    });
            });
        }

        // Category filter (id or slug)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('category_slug')) {
            $query->whereHas('category', function ($c) use ($request) {
                $c->where('slug', $request->category_slug);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_low') {
            $query->orderBy(
                ProductVariant::select('selling_price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_default', true)
                    ->take(1),
                'asc'
            );
        } elseif ($sort === 'price_high') {
            $query->orderBy(
                ProductVariant::select('selling_price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_default', true)
                    ->take(1),
                'desc'
            );
        } else {
            $query->latest();
        }

        $perPage = $request->get('per_page', 20);
        $paginated = $query->paginate($perPage);

        $transformed = $paginated->getCollection()->map(function ($product) {
            $defaultVariant = $product->variants->firstWhere('is_default', true)
                ?: $product->variants->first();

            $stock = (float) ($defaultVariant?->stock?->quantity ?? 0);
            $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'name_ar' => $product->name_ar,
                'slug' => $product->slug,
                'description' => $product->description,
                'description_ar' => $product->description_ar,
                'category_id' => $product->category_id,
                'category_name' => $product->category->name ?? 'General',
                'category_name_ar' => $product->category->name_ar ?? null,
                'category_slug' => $product->category->slug ?? '',
                'unit' => [
                    'name' => $product->unit->name ?? 'Piece',
                    'name_ar' => $product->unit->name_ar ?? null,
                    'short_name' => $product->unit->short_name ?? 'pcs',
                    'short_name_ar' => $product->unit->short_name_ar ?? null,
                    'allow_decimal' => (bool) ($product->unit->allow_decimal ?? false),
                ],
                'image_url' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                'images' => $product->images->map(function ($img) {
                    return asset('storage/' . $img->image_path);
                }),
                'variant' => $defaultVariant ? [
                    'id' => $defaultVariant->id,
                    'sku' => $defaultVariant->sku,
                    'selling_price' => (float) $defaultVariant->selling_price,
                    'mrp' => (float) $defaultVariant->mrp,
                    'tax_rate' => (float) $defaultVariant->tax_rate,
                ] : null,
                'track_inventory' => (bool) $product->track_inventory,
                'stock' => $stock,
                'in_stock' => !$product->track_inventory || $stock > 0,
            ];
        })->filter(function ($item) {
            return $item['variant'] !== null;
        })->values();

        $paginated->setCollection($transformed);

        return response()->json($paginated);
    }

    // Single product details
    public function productDetails($idOrSlug)
    {
        $product = Product::with([
            'category:id,name,name_ar,slug',
            'brand:id,name,name_ar',
            'unit:id,name,name_ar,short_name,short_name_ar,allow_decimal',
            'images',
            'variants' => function ($v) {
                $v->with('stock')->where('status', true);
            },
        ])
        ->where(function ($q) use ($idOrSlug) {
            $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
        })
        ->where('status', true)
        ->firstOrFail();

        $defaultVariant = $product->variants->firstWhere('is_default', true)
            ?: $product->variants->first();

        $stock = (float) ($defaultVariant?->stock?->quantity ?? 0);
        $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'name_ar' => $product->name_ar,
                'slug' => $product->slug,
                'description' => $product->description,
                'description_ar' => $product->description_ar,
                'category_id' => $product->category_id,
                'category_name' => $product->category ? $product->category->name : 'General',
                'category_name_ar' => $product->category ? $product->category->name_ar : null,
                'category' => $product->category ? $product->category->name : 'General',
                'brand_name' => $product->brand ? $product->brand->name : null,
                'brand_name_ar' => $product->brand ? $product->brand->name_ar : null,
                'brand' => $product->brand ? $product->brand->name : null,
                'unit' => [
                    'name' => $product->unit->name ?? 'Piece',
                    'name_ar' => $product->unit->name_ar ?? null,
                    'short_name' => $product->unit->short_name ?? 'pcs',
                    'short_name_ar' => $product->unit->short_name_ar ?? null,
                    'allow_decimal' => (bool) ($product->unit->allow_decimal ?? false),
                ],
                'image_url' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                'images' => $product->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->image_path),
                        'is_primary' => (bool) $img->is_primary,
                    ];
                }),
                'variant' => $defaultVariant ? [
                    'id' => $defaultVariant->id,
                    'sku' => $defaultVariant->sku,
                    'barcode' => $defaultVariant->barcode,
                    'selling_price' => (float) $defaultVariant->selling_price,
                    'mrp' => (float) $defaultVariant->mrp,
                    'tax_rate' => (float) $defaultVariant->tax_rate,
                ] : null,
                'track_inventory' => (bool) $product->track_inventory,
                'stock' => $stock,
                'in_stock' => !$product->track_inventory || $stock > 0,
            ]
        ]);
    }

    // Store categories with active product count
    public function categories()
    {
        $categories = Category::where('status', true)
            ->withCount(['products' => function ($q) {
                $q->where('status', true);
            }])
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'name_ar' => $cat->name_ar,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'description_ar' => $cat->description_ar,
                    'products_count' => $cat->products_count,
                ];
            });

        return response()->json([
            'data' => $categories
        ]);
    }

    // Validate coupon code for storefront cart / checkout
    public function validateCoupon(Request $request, DiscountService $discountService)
    {
        $request->validate([
            'code' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        $authCustomer = auth('sanctum')->user();
        $customer = ($authCustomer instanceof Customer) ? $authCustomer : null;
        $shippingCost = (float) $request->get('shipping_cost', 0);

        $result = $discountService->validateAndCalculate(
            $request->code,
            $request->items,
            'online_only',
            $customer,
            $request->customer_phone,
            $shippingCost
        );

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    // Process customer storefront checkout (decrements inventory real-time)
    public function checkout(Request $request, DiscountService $discountService)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:150',
            'shipping_address' => 'required|string|max:500',
            'payment_method' => 'required|string|in:cod,upi,card,bank_transfer',
            'coupon_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $order = DB::transaction(function () use ($validated, $discountService) {
            // Check if customer is authenticated or find/create by phone
            $authCustomer = auth('sanctum')->user();
            if ($authCustomer && ($authCustomer instanceof Customer)) {
                $customer = $authCustomer;
                if ($customer->name !== $validated['customer_name']) {
                    $customer->name = $validated['customer_name'];
                }
                if ($validated['customer_email'] && !$customer->email) {
                    $customer->email = $validated['customer_email'];
                }
                $customer->address = $validated['shipping_address'];
                $customer->save();
            } else {
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['customer_phone']],
                    [
                        'name' => $validated['customer_name'],
                        'email' => $validated['customer_email'] ?? null,
                        'address' => $validated['shipping_address'],
                        'status' => true,
                    ]
                );
            }

            // Generate order invoice number: ORD-YYYYMMDD-XXXX
            $today = date('Ymd');
            $todayCount = Sale::where('order_type', '!=', 'pos')
                ->whereDate('created_at', today())
                ->count() + 1;
            $orderNumber = 'ORD-' . $today . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $taxAmount = 0;
            $itemsData = [];

            // Verify live stock and compute prices
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = ProductVariant::where('product_id', $product->id)
                    ->where('id', $item['product_variant_id'])
                    ->firstOrFail();

                $qty = (float) $item['quantity'];
                $price = (float) $variant->selling_price;
                $rowSubtotal = round($qty * $price, 2);

                $taxRate = (float) ($variant->tax_rate ?? 0);
                $rowTax = round($rowSubtotal * ($taxRate / 100), 2);
                $rowTotal = round($rowSubtotal + $rowTax, 2);

                // REAL-TIME INVENTORY VERIFICATION
                if ($product->track_inventory) {
                    $stock = Stock::where('product_id', $product->id)
                        ->where('product_variant_id', $variant->id)
                        ->lockForUpdate()
                        ->first();

                    $available = (float) ($stock?->quantity ?? 0);
                    if ($available < $qty) {
                        abort(422, "Sorry, '{$product->name}' has only {$available} units available in stock. Please adjust your cart quantity.");
                    }
                }

                $subtotal += $rowSubtotal;
                $taxAmount += $rowTax;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $rowTax,
                    'subtotal' => $rowSubtotal,
                    'total' => $rowTotal,
                ];
            }

            // Flat shipping: Free over ₹500, else ₹50
            $shippingCost = $subtotal >= 500 ? 0 : 50;

            // Coupon validation & discount calculation
            $couponCode = !empty($validated['coupon_code']) ? trim($validated['coupon_code']) : null;
            $discountAmount = 0.0;
            $appliedCoupon = null;

            if ($couponCode) {
                $couponValidation = $discountService->validateAndCalculate(
                    $couponCode,
                    $itemsData,
                    'online_only',
                    $customer,
                    $validated['customer_phone'],
                    (float) $shippingCost
                );

                if (!$couponValidation['valid']) {
                    abort(422, $couponValidation['message']);
                }

                $discountAmount = (float) $couponValidation['discount_amount'];
                $appliedCoupon = $couponValidation['coupon'];

                if (!empty($couponValidation['free_shipping'])) {
                    $shippingCost = 0;
                }
            }

            $totalAmount = max(0, round($subtotal + $taxAmount + $shippingCost - $discountAmount, 2));

            // Payment status
            $paymentMethod = $validated['payment_method'];
            $isPaidOnline = in_array($paymentMethod, ['upi', 'card', 'bank_transfer']);
            $paymentStatus = $isPaidOnline ? 'paid' : 'unpaid';
            $paidAmount = $isPaidOnline ? $totalAmount : 0;

            // Create Order in sales table
            $sale = Sale::create([
                'invoice_number' => $orderNumber,
                'customer_id' => $customer->id,
                'sale_date' => now(),
                'order_type' => 'online',
                'channel' => 'web',
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'coupon_id' => $appliedCoupon ? $appliedCoupon['id'] : null,
                'coupon_code' => $appliedCoupon ? $appliedCoupon['code'] : null,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => 0,
                'shipping_address' => $validated['shipping_address'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Commit coupon usage
            if ($appliedCoupon) {
                $couponModel = Coupon::find($appliedCoupon['id']);
                if ($couponModel) {
                    $discountService->recordUsage(
                        $couponModel,
                        $sale,
                        $customer,
                        $validated['customer_phone'],
                        $discountAmount
                    );
                }
            }

            // Save line items & DECREMENT INVENTORY STOCK
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

                    // Log audit trail in stock_transactions
                    StockTransaction::create([
                        'product_id' => $row['product_id'],
                        'product_variant_id' => $row['product_variant_id'],
                        'type' => 'out',
                        'quantity' => $row['quantity'],
                        'balance_after' => $stock->quantity,
                        'reason' => 'Online Store Order #' . $sale->invoice_number,
                        'notes' => 'Customer: ' . $customer->name . ' (' . $customer->phone . ')',
                        'user_id' => null,
                    ]);
                }
            }

            // Update customer metrics
            $customer->increment('total_orders');
            $customer->increment('total_spent', $totalAmount);

            return $sale;
        });

        return response()->json([
            'message' => 'Your order has been placed successfully!',
            'data' => [
                'order_number' => $order->invoice_number,
                'total_amount' => (float) $order->total_amount,
                'customer_name' => $validated['customer_name'],
                'shipping_address' => $validated['shipping_address'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $order->payment_status,
                'sale_date' => $order->sale_date,
            ]
        ], 201);
    }

    // Customer order tracking lookup
    public function trackOrder($orderNumber)
    {
        $order = Sale::with([
            'customer:id,name,phone',
            'items.product:id,name',
            'items.variant:id,sku'
        ])
        ->where('invoice_number', trim($orderNumber))
        ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found. Please verify the order number (e.g. ORD-20261104-0001).'
            ], 404);
        }

        return response()->json([
            'data' => [
                'order_number' => $order->invoice_number,
                'sale_date' => $order->sale_date,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'shipping_address' => $order->shipping_address,
                'delivery_partner' => $order->delivery_partner,
                'tracking_number' => $order->tracking_number,
                'estimated_delivery_date' => $order->estimated_delivery_date,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'customer' => [
                    'name' => $order->customer->name ?? '',
                    'phone' => $order->customer->phone ?? '',
                ],
                'items' => $order->items->map(function ($it) {
                    return [
                        'name' => $it->product->name ?? 'Product',
                        'product_name' => $it->product->name ?? 'Product',
                        'sku' => $it->variant->sku ?? '',
                        'quantity' => (float) $it->quantity,
                        'unit_price' => (float) $it->unit_price,
                        'total' => (float) $it->total,
                    ];
                })
            ]
        ]);
    }
}
