<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Executive Dashboard & Sales Summary
    public function dashboard(Request $request)
    {
        $fromDate = $request->filled('from_date')
            ? Carbon::parse($request->from_date)->startOfDay()
            : now()->startOfMonth();

        $toDate = $request->filled('to_date')
            ? Carbon::parse($request->to_date)->endOfDay()
            : now()->endOfDay();

        // Base Sales Query for date range
        $salesQuery = Sale::where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$fromDate, $toDate]);

        $totalSales = (float) (clone $salesQuery)->sum('total_amount');
        $totalTax = (float) (clone $salesQuery)->sum('tax_amount');
        $totalDiscount = (float) (clone $salesQuery)->sum('discount_amount');
        $totalShipping = (float) (clone $salesQuery)->sum('shipping_cost');
        $ordersCount = (clone $salesQuery)->count();
        $averageOrderValue = $ordersCount > 0 ? round($totalSales / $ordersCount, 2) : 0;

        // Base Purchases Query
        $purchasesQuery = Purchase::whereBetween('purchase_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        $totalPurchases = (float) (clone $purchasesQuery)->sum('total_amount');
        $purchasesCount = (clone $purchasesQuery)->count();
        $supplierDue = (float) Purchase::sum('due_amount');

        // Customer Outstanding Receivables
        $customerDue = (float) Sale::where('status', '!=', 'cancelled')->sum(DB::raw('total_amount - paid_amount'));
        $customerDue = max(0, $customerDue);

        // Calculate COGS and Gross Profit for sold items in date range
        $saleIds = (clone $salesQuery)->pluck('id');
        $saleItems = SaleItem::with('variant')
            ->whereIn('sale_id', $saleIds)
            ->get();

        $totalCogs = 0;
        foreach ($saleItems as $item) {
            $cost = (float) ($item->variant->purchase_price ?? 0);
            $totalCogs += ((float) $item->quantity) * $cost;
        }

        $grossProfit = round($totalSales - $totalCogs, 2);
        $profitMargin = $totalSales > 0 ? round(($grossProfit / $totalSales) * 100, 2) : 0;

        // Sales by Channel
        $channels = (clone $salesQuery)
            ->select('channel', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('channel')
            ->get()
            ->map(function ($row) {
                return [
                    'channel' => $row->channel ?: 'pos',
                    'count' => $row->count,
                    'total' => round((float) $row->total, 2),
                ];
            });

        // Sales by Payment Method
        $paymentMethods = (clone $salesQuery)
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(function ($row) {
                return [
                    'method' => $row->payment_method,
                    'count' => $row->count,
                    'total' => round((float) $row->total, 2),
                ];
            });

        // Daily Trends (last 14 days or within date range)
        $dailyTrends = (clone $salesQuery)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('count(*) as orders'),
                DB::raw('sum(total_amount) as sales'),
                DB::raw('sum(tax_amount) as tax')
            )
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($d) {
                return [
                    'date' => $d->date,
                    'orders' => $d->orders,
                    'sales' => round((float) $d->sales, 2),
                    'tax' => round((float) $d->tax, 2),
                ];
            });

        return response()->json([
            'kpis' => [
                'total_sales' => round($totalSales, 2),
                'orders_count' => $ordersCount,
                'total_cogs' => round($totalCogs, 2),
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMargin,
                'total_purchases' => round($totalPurchases, 2),
                'purchases_count' => $purchasesCount,
                'average_order_value' => $averageOrderValue,
                'total_tax' => round($totalTax, 2),
                'total_discount' => round($totalDiscount, 2),
                'total_shipping' => round($totalShipping, 2),
                'customer_receivables' => round($customerDue, 2),
                'supplier_payables' => round($supplierDue, 2),
            ],
            'channels' => $channels,
            'payment_methods' => $paymentMethods,
            'daily_trends' => $dailyTrends,
            'date_range' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ]
        ]);
    }

    // Top Selling & Product Performance Report
    public function productPerformance(Request $request)
    {
        $fromDate = $request->filled('from_date')
            ? Carbon::parse($request->from_date)->startOfDay()
            : now()->startOfMonth();

        $toDate = $request->filled('to_date')
            ? Carbon::parse($request->to_date)->endOfDay()
            : now()->endOfDay();

        $saleIds = Sale::where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->pluck('id');

        $items = SaleItem::with(['product.category', 'product.unit', 'variant'])
            ->whereIn('sale_id', $saleIds)
            ->get();

        // Group by product_variant_id
        $grouped = $items->groupBy('product_variant_id');

        $products = $grouped->map(function ($rows) {
            $first = $rows->first();
            $qtySold = $rows->sum('quantity');
            $revenue = $rows->sum('total');
            $costPerUnit = (float) ($first->variant->purchase_price ?? 0);
            $totalCogs = $qtySold * $costPerUnit;
            $profit = round($revenue - $totalCogs, 2);
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

            return [
                'product_id' => $first->product_id,
                'variant_id' => $first->product_variant_id,
                'name' => $first->product->name ?? 'Unknown',
                'sku' => $first->variant->sku ?? '-',
                'category' => $first->product->category->name ?? 'General',
                'unit' => $first->product->unit->short_name ?? 'pcs',
                'quantity_sold' => round($qtySold, 2),
                'total_revenue' => round($revenue, 2),
                'total_cogs' => round($totalCogs, 2),
                'gross_profit' => $profit,
                'margin_percent' => $margin,
                'avg_selling_price' => $qtySold > 0 ? round($revenue / $qtySold, 2) : 0,
            ];
        })->values();

        // Sort by revenue descending
        $sorted = $products->sortByDesc('total_revenue')->values();

        return response()->json([
            'data' => $sorted,
            'summary' => [
                'total_items_sold' => round($products->sum('quantity_sold'), 2),
                'total_revenue' => round($products->sum('total_revenue'), 2),
                'total_profit' => round($products->sum('gross_profit'), 2),
            ]
        ]);
    }

    // Inventory Valuation & Health Report
    public function inventoryValuation()
    {
        $products = Product::with([
            'category:id,name',
            'unit:id,name,short_name,allow_decimal',
            'variants.stock'
        ])->where('status', true)->get();

        $totalStockUnits = 0;
        $totalCostValue = 0;
        $totalRetailValue = 0;

        $categoryStats = [];
        $lowStockItems = [];
        $outOfStockItems = [];

        foreach ($products as $prod) {
            $catName = $prod->category->name ?? 'Uncategorized';
            if (!isset($categoryStats[$catName])) {
                $categoryStats[$catName] = [
                    'category' => $catName,
                    'products_count' => 0,
                    'stock_units' => 0,
                    'cost_value' => 0,
                    'retail_value' => 0,
                ];
            }
            $categoryStats[$catName]['products_count']++;

            $prodStock = 0;
            foreach ($prod->variants as $variant) {
                $qty = (float) ($variant->stock->quantity ?? 0);
                $cost = (float) ($variant->purchase_price ?? 0);
                $retail = (float) ($variant->selling_price ?? 0);

                $prodStock += $qty;
                $lineCost = $qty * $cost;
                $lineRetail = $qty * $retail;

                $totalStockUnits += $qty;
                $totalCostValue += $lineCost;
                $totalRetailValue += $lineRetail;

                $categoryStats[$catName]['stock_units'] += $qty;
                $categoryStats[$catName]['cost_value'] += $lineCost;
                $categoryStats[$catName]['retail_value'] += $lineRetail;
            }

            // Check stock alerts
            $threshold = (float) $prod->low_stock_threshold;
            if ($prod->track_inventory) {
                if ($prodStock <= 0) {
                    $outOfStockItems[] = [
                        'id' => $prod->id,
                        'name' => $prod->name,
                        'category' => $catName,
                        'stock' => $prodStock,
                        'unit' => $prod->unit->short_name ?? 'pcs',
                        'threshold' => $threshold,
                    ];
                } elseif ($prodStock <= $threshold) {
                    $lowStockItems[] = [
                        'id' => $prod->id,
                        'name' => $prod->name,
                        'category' => $catName,
                        'stock' => $prodStock,
                        'unit' => $prod->unit->short_name ?? 'pcs',
                        'threshold' => $threshold,
                    ];
                }
            }
        }

        $potentialProfit = round($totalRetailValue - $totalCostValue, 2);
        $projectedMargin = $totalRetailValue > 0 ? round(($potentialProfit / $totalRetailValue) * 100, 2) : 0;

        return response()->json([
            'valuation' => [
                'total_products' => $products->count(),
                'total_stock_units' => round($totalStockUnits, 2),
                'cost_value' => round($totalCostValue, 2),
                'retail_value' => round($totalRetailValue, 2),
                'potential_profit' => $potentialProfit,
                'projected_margin' => $projectedMargin,
                'low_stock_count' => count($lowStockItems),
                'out_of_stock_count' => count($outOfStockItems),
            ],
            'categories_breakdown' => array_values($categoryStats),
            'low_stock_items' => array_slice($lowStockItems, 0, 15),
            'out_of_stock_items' => array_slice($outOfStockItems, 0, 15),
        ]);
    }

    // Profit & Loss (P&L) Statement
    public function profitLoss(Request $request)
    {
        $fromDate = $request->filled('from_date')
            ? Carbon::parse($request->from_date)->startOfDay()
            : now()->startOfMonth();

        $toDate = $request->filled('to_date')
            ? Carbon::parse($request->to_date)->endOfDay()
            : now()->endOfDay();

        $sales = Sale::where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$fromDate, $toDate]);

        $grossSales = (float) (clone $sales)->sum('subtotal');
        $discounts = (float) (clone $sales)->sum('discount_amount');
        $shippingRevenue = (float) (clone $sales)->sum('shipping_cost');
        $taxes = (float) (clone $sales)->sum('tax_amount');
        $netSales = (float) (clone $sales)->sum('total_amount');

        // COGS
        $saleIds = (clone $sales)->pluck('id');
        $saleItems = SaleItem::with('variant')->whereIn('sale_id', $saleIds)->get();

        $cogs = 0;
        foreach ($saleItems as $item) {
            $unitCost = (float) ($item->variant->purchase_price ?? 0);
            $cogs += ((float) $item->quantity) * $unitCost;
        }

        $grossProfit = round($netSales - $cogs, 2);
        $grossMarginPercent = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0;

        // Purchases in period
        $purchases = Purchase::whereBetween('purchase_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        $totalPurchased = (float) (clone $purchases)->sum('total_amount');

        return response()->json([
            'date_range' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'income' => [
                'gross_sales' => round($grossSales, 2),
                'discounts' => round($discounts, 2),
                'shipping_revenue' => round($shippingRevenue, 2),
                'tax_collected' => round($taxes, 2),
                'net_sales' => round($netSales, 2),
            ],
            'cogs' => [
                'cost_of_goods_sold' => round($cogs, 2),
            ],
            'profit' => [
                'gross_profit' => $grossProfit,
                'gross_margin_percent' => $grossMarginPercent,
            ],
            'procurement' => [
                'total_purchases' => round($totalPurchased, 2),
            ]
        ]);
    }
}

