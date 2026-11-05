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

class OrderController extends Controller
{
    // List online orders with filters & pagination
    public function index(Request $request)
    {
        $query = Sale::with([
            'customer:id,name,phone,email,address,city,state,pincode',
            'items.product:id,name,unit_id',
            'items.product.unit:id,name,short_name',
            'items.variant:id,sku,variant_name,barcode'
        ])->where('order_type', '!=', 'pos');

        // Search by order #, tracking #, customer name, phone
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('delivery_partner', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by order lifecycle status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by sales channel (web, whatsapp, phone, app)
        if ($request->filled('channel') && $request->channel !== 'all') {
            $query->where('channel', $request->channel);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $orders = $query->latest('sale_date')->latest('id')
            ->paginate($request->get('per_page', 10));

        return response()->json($orders);
    }

    // High level metrics for online orders
    public function summary()
    {
        $base = Sale::where('order_type', '!=', 'pos');

        $totalOrders = (clone $base)->count();
        $totalRevenue = (float) (clone $base)->where('status', '!=', 'cancelled')->sum('total_amount');
        $pendingCount = (clone $base)->where('status', 'pending')->count();
        $processingCount = (clone $base)->whereIn('status', ['confirmed', 'processing'])->count();
        $shippedCount = (clone $base)->where('status', 'shipped')->count();
        $deliveredCount = (clone $base)->where('status', 'delivered')->count();
        $cancelledCount = (clone $base)->where('status', 'cancelled')->count();

        return response()->json([
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'pending_count' => $pendingCount,
            'processing_count' => $processingCount,
            'shipped_count' => $shippedCount,
            'delivered_count' => $deliveredCount,
            'cancelled_count' => $cancelledCount,
        ]);
    }

    // Create an online / manual delivery order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|email|max:150',
            'shipping_address' => 'required|string|max:500',
            'channel' => 'nullable|string|in:web,whatsapp,phone,app',
            'payment_method' => 'required|string|max:50', // cod, upi, card, bank_transfer
            'payment_status' => 'nullable|string|in:paid,partial,unpaid',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'delivery_partner' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'estimated_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            // Find or create customer if name/phone provided
            $customerId = $validated['customer_id'] ?? null;
            if (!$customerId && !empty($validated['customer_name'])) {
                $customer = Customer::create([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'] ?? null,
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['shipping_address'] ?? null,
                    'status' => true,
                ]);
                $customerId = $customer->id;
            }

            // Generate order number: ORD-YYYYMMDD-0001
            $today = date('Ymd');
            $countToday = Sale::where('order_type', '!=', 'pos')
                ->whereDate('created_at', today())
                ->count() + 1;
            $orderNumber = 'ORD-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $taxAmount = 0;
            $itemsData = [];

            // Calculate items & check stock
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = ProductVariant::findOrFail($item['product_variant_id']);

                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $rowSubtotal = round($qty * $price, 2);

                $taxRate = isset($item['tax_rate']) ? (float) $item['tax_rate'] : 0;
                $rowTax = round($rowSubtotal * ($taxRate / 100), 2);
                $rowTotal = round($rowSubtotal + $rowTax, 2);

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
            $shipping = isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : 0;
            $totalAmount = max(0, round($subtotal + $taxAmount + $shipping - $discount, 2));

            $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0;
            if ($validated['payment_method'] !== 'cod' && empty($validated['paid_amount'])) {
                // If not COD and paid amount not explicitly set, default to total if payment_status is 'paid'
                if (($validated['payment_status'] ?? '') === 'paid') {
                    $paidAmount = $totalAmount;
                }
            }
            $paidAmount = min($paidAmount, $totalAmount);

            $paymentStatus = $validated['payment_status'] ?? ($paidAmount >= $totalAmount && $totalAmount > 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'));

            $order = Sale::create([
                'invoice_number' => $orderNumber,
                'customer_id' => $customerId,
                'sale_date' => now(),
                'order_type' => 'online',
                'channel' => $validated['channel'] ?? 'web',
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'shipping_cost' => $shipping,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => 0,
                'shipping_address' => $validated['shipping_address'],
                'delivery_partner' => $validated['delivery_partner'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id() ?? null,
            ]);

            // Save line items & deduct stock
            foreach ($itemsData as $row) {
                $row['sale_id'] = $order->id;
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
                        'reason' => 'Online Order #' . $order->invoice_number,
                        'notes' => 'Channel: ' . ucfirst($order->channel),
                        'user_id' => auth()->id() ?? null,
                    ]);
                }
            }

            if ($customerId) {
                $cust = Customer::find($customerId);
                if ($cust) {
                    $cust->increment('total_orders');
                    $cust->increment('total_spent', $totalAmount);
                }
            }

            return $order;
        });

        return response()->json([
            'message' => 'Online order created successfully.',
            'data' => $order->load([
                'customer',
                'items.product.unit',
                'items.variant'
            ]),
        ], 201);
    }

    // View specific order details
    public function show(Sale $order)
    {
        return response()->json([
            'data' => $order->load([
                'customer',
                'cashier:id,name',
                'items.product.unit',
                'items.variant:id,sku,variant_name,barcode'
            ]),
        ]);
    }

    // Update order status (with auto stock rollback on cancellation)
    public function updateStatus(Request $request, Sale $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $order->status;

        if ($newStatus === $oldStatus) {
            return response()->json([
                'message' => 'Order status already ' . $newStatus,
                'data' => $order
            ]);
        }

        DB::transaction(function () use ($order, $newStatus, $oldStatus) {
            // If cancelling an order that had previously deducted stock, return stock back!
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('product_variant_id', $item->product_variant_id)
                        ->first();

                    if ($stock) {
                        $stock->quantity += $item->quantity;
                        $stock->save();

                        StockTransaction::create([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'balance_after' => $stock->quantity,
                            'reason' => 'Online Order Cancelled #' . $order->invoice_number,
                            'notes' => 'Stock restored to inventory',
                            'user_id' => auth()->id() ?? null,
                        ]);
                    }
                }
            }

            // If reactivating a cancelled order, re-deduct stock
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('product_variant_id', $item->product_variant_id)
                        ->first();

                    if ($stock) {
                        $stock->quantity = max(0, $stock->quantity - $item->quantity);
                        $stock->save();

                        StockTransaction::create([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'type' => 'out',
                            'quantity' => $item->quantity,
                            'balance_after' => $stock->quantity,
                            'reason' => 'Online Order Reactivated #' . $order->invoice_number,
                            'notes' => 'Stock re-allocated',
                            'user_id' => auth()->id() ?? null,
                        ]);
                    }
                }
            }

            // If marked as delivered and payment was COD unpaid, mark as paid
            if ($newStatus === 'delivered' && $order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                $order->payment_status = 'paid';
                $order->paid_amount = $order->total_amount;
            }

            $order->status = $newStatus;
            $order->save();
        });

        return response()->json([
            'message' => 'Order status updated to ' . ucfirst($newStatus) . '.',
            'data' => $order->fresh(['customer', 'items.product.unit', 'items.variant']),
        ]);
    }

    // Update tracking and shipping details
    public function updateTracking(Request $request, Sale $order)
    {
        $validated = $request->validate([
            'delivery_partner' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'estimated_delivery_date' => 'nullable|date',
            'auto_ship' => 'nullable|boolean',
        ]);

        $order->delivery_partner = $validated['delivery_partner'];
        $order->tracking_number = $validated['tracking_number'];
        $order->estimated_delivery_date = $validated['estimated_delivery_date'] ?? null;

        if (!empty($validated['auto_ship']) && in_array($order->status, ['pending', 'confirmed', 'processing'])) {
            $order->status = 'shipped';
        }

        $order->save();

        return response()->json([
            'message' => 'Tracking information updated successfully.',
            'data' => $order->fresh(['customer', 'items.product.unit', 'items.variant']),
        ]);
    }

    // Update payment details
    public function updatePayment(Request $request, Sale $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|string|in:paid,partial,unpaid',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $order->payment_status = $validated['payment_status'];
        $order->paid_amount = $validated['paid_amount'];
        if (!empty($validated['payment_method'])) {
            $order->payment_method = $validated['payment_method'];
        }
        $order->save();

        return response()->json([
            'message' => 'Payment status updated successfully.',
            'data' => $order->fresh(),
        ]);
    }

    // Delete order with stock reversal
    public function destroy(Sale $order)
    {
        DB::transaction(function () use ($order) {
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('product_variant_id', $item->product_variant_id)
                        ->first();

                    if ($stock) {
                        $stock->quantity += $item->quantity;
                        $stock->save();

                        StockTransaction::create([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'balance_after' => $stock->quantity,
                            'reason' => 'Online Order Deleted #' . $order->invoice_number,
                            'notes' => 'Stock reverted back',
                            'user_id' => auth()->id() ?? null,
                        ]);
                    }
                }
            }

            $order->items()->delete();
            $order->delete();
        });

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }
}

