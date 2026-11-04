<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // List purchases with filters & pagination
    public function index(Request $request)
    {
        $query = Purchase::with([
            'supplier:id,name,company_name,phone',
            'creator:id,name',
            'items'
        ]);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->latest('purchase_date')->latest('id')
            ->paginate($request->get('per_page', 10));

        return response()->json($purchases);
    }

    // High level metrics
    public function summary()
    {
        $totalPurchases = (float) Purchase::sum('total_amount');
        $totalPaid = (float) Purchase::sum('paid_amount');
        $totalDue = (float) Purchase::sum('due_amount');
        $invoiceCount = Purchase::count();

        return response()->json([
            'total_amount' => round($totalPurchases, 2),
            'total_paid' => round($totalPaid, 2),
            'total_due' => round($totalDue, 2),
            'invoice_count' => $invoiceCount,
        ]);
    }

    // Create a new purchase
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'status' => 'required|in:received,pending,ordered,cancelled',
            'payment_method' => 'required|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $purchase = DB::transaction(function () use ($validated) {
            // Generate unique invoice number
            $today = date('Ymd');
            $latest = Purchase::whereDate('created_at', today())->count() + 1;
            $purchaseNumber = 'PUR-' . $today . '-' . str_pad($latest, 4, '0', STR_PAD_LEFT);

            // Calculate item values
            $subtotal = 0;
            $taxAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $rowSubtotal = round($qty * $cost, 2);

                $taxRate = isset($item['tax_rate']) ? (float) $item['tax_rate'] : 0;
                $rowTax = round($rowSubtotal * ($taxRate / 100), 2);
                $rowTotal = round($rowSubtotal + $rowTax, 2);

                $subtotal += $rowSubtotal;
                $taxAmount += $rowTax;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $rowSubtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $rowTax,
                    'total' => $rowTotal,
                ];
            }

            $discount = isset($validated['discount_amount']) ? (float) $validated['discount_amount'] : 0;
            $shipping = isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : 0;
            $totalAmount = max(0, round($subtotal + $taxAmount + $shipping - $discount, 2));

            $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0;
            $paidAmount = min($paidAmount, $totalAmount);
            $dueAmount = max(0, round($totalAmount - $paidAmount, 2));

            if ($paidAmount >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'unpaid';
            }

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'reference_no' => $validated['reference_no'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'status' => $validated['status'],
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'shipping_cost' => $shipping,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id() ?? null,
            ]);

            // Save items
            foreach ($itemsData as $row) {
                $row['purchase_id'] = $purchase->id;
                PurchaseItem::create($row);

                // If status is received, immediately increase inventory stock
                if ($purchase->status === 'received') {
                    $stock = Stock::firstOrCreate(
                        [
                            'product_id' => $row['product_id'],
                            'product_variant_id' => $row['product_variant_id']
                        ],
                        ['quantity' => 0]
                    );

                    $stock->quantity += $row['quantity'];
                    $stock->save();

                    // Record transaction in audit log
                    StockTransaction::create([
                        'product_id' => $row['product_id'],
                        'product_variant_id' => $row['product_variant_id'],
                        'type' => 'in',
                        'quantity' => $row['quantity'],
                        'balance_after' => $stock->quantity,
                        'reason' => 'Purchase Bill #' . $purchase->purchase_number,
                        'notes' => 'Received from supplier' . ($purchase->supplier ? ' ' . $purchase->supplier->name : ''),
                        'user_id' => auth()->id() ?? null,
                    ]);
                }
            }

            return $purchase;
        });

        return response()->json([
            'message' => 'Purchase created successfully.',
            'data' => $purchase->load([
                'supplier',
                'items.product.unit',
                'items.variant'
            ]),
        ], 201);
    }

    // View specific purchase
    public function show(Purchase $purchase)
    {
        return response()->json([
            'data' => $purchase->load([
                'supplier',
                'creator:id,name',
                'items.product.unit',
                'items.variant:id,sku,variant_name,barcode'
            ]),
        ]);
    }

    // Delete purchase with stock rollback
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // If it was already received, rollback stock
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
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
                            'reason' => 'Purchase Deleted/Cancelled #' . $purchase->purchase_number,
                            'notes' => 'Stock reverted back',
                            'user_id' => auth()->id() ?? null,
                        ]);
                    }
                }
            }

            $purchase->items()->delete();
            $purchase->delete();
        });

        return response()->json([
            'message' => 'Purchase deleted successfully.',
        ]);
    }
}

