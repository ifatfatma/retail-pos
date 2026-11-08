<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponAuditLog;
use App\Models\CouponRestriction;
use App\Models\CouponUsage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    // List coupons with filters and usage metrics
    public function index(Request $request)
    {
        $query = Coupon::with(['restrictions', 'creator:id,name'])
            ->withCount('usages');

        // Search by code or name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        // Filter by channel
        if ($request->filled('channel') && in_array($request->channel, ['online_only', 'pos_only', 'all'])) {
            $query->where('channel', $request->channel);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by Expiry Status
        if ($request->filled('expiry_status')) {
            $now = now();
            if ($request->expiry_status === 'active') {
                $query->where('is_active', true)
                      ->where(function ($q) use ($now) {
                          $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                      })
                      ->where(function ($q) use ($now) {
                          $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                      });
            } elseif ($request->expiry_status === 'expired') {
                $query->where('end_date', '<', $now);
            } elseif ($request->expiry_status === 'upcoming') {
                $query->where('start_date', '>', $now);
            } elseif ($request->expiry_status === 'expiring_soon') {
                $query->where('is_active', true)
                      ->whereBetween('end_date', [$now, $now->copy()->addDays(7)]);
            }
        }

        // Filter by Date Range (creation date)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $coupons = $query->latest('id')->paginate($request->get('per_page', 15));

        return response()->json($coupons);
    }

    // Create a new coupon
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_cart,free_shipping,buy_x_get_y,tiered',
            'value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'buy_qty' => 'nullable|integer|min:1',
            'get_qty' => 'nullable|integer|min:1',
            'get_discount_percent' => 'nullable|numeric|min:0|max:100',
            'tier_rules' => 'nullable|array',
            'channel' => 'required|in:all,online_only,pos_only',
            'applies_to' => 'required|in:all,specific_categories,specific_products',
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'is_first_order_only' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_first_order_only'] = $request->boolean('is_first_order_only', false);
        $validated['usage_limit_per_customer'] = $validated['usage_limit_per_customer'] ?? 1;
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['value'] = $validated['value'] ?? 0;
        $validated['created_by'] = auth()->id();

        // Max discount cap is applicable only to percentage discounts
        if ($validated['type'] !== 'percentage') {
            $validated['max_discount_amount'] = null;
        }

        $coupon = DB::transaction(function () use ($validated, $request) {
            $coupon = Coupon::create($validated);

            // Sync category restrictions
            if ($validated['applies_to'] === 'specific_categories' && $request->filled('category_ids')) {
                foreach ($request->category_ids as $catId) {
                    CouponRestriction::create([
                        'coupon_id' => $coupon->id,
                        'restrictable_type' => 'category',
                        'restrictable_id' => $catId,
                    ]);
                }
            }

            // Sync product restrictions
            if ($validated['applies_to'] === 'specific_products' && $request->filled('product_ids')) {
                foreach ($request->product_ids as $prodId) {
                    CouponRestriction::create([
                        'coupon_id' => $coupon->id,
                        'restrictable_type' => 'product',
                        'restrictable_id' => $prodId,
                    ]);
                }
            }

            $this->logAudit($coupon, 'created', [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'name' => $coupon->name,
                'channel' => $coupon->channel,
            ]);

            return $coupon->load('restrictions');
        });

        return response()->json([
            'message' => "Coupon '{$coupon->code}' created successfully.",
            'data' => $coupon,
        ], 201);
    }

    // Show coupon details
    public function show(Coupon $coupon)
    {
        $coupon->load(['restrictions', 'creator:id,name'])
            ->loadCount('usages');

        $recentUsages = CouponUsage::with(['customer:id,name,phone', 'sale:id,invoice_number,total_amount'])
            ->where('coupon_id', $coupon->id)
            ->latest('id')
            ->take(10)
            ->get();

        return response()->json([
            'data' => $coupon,
            'recent_usages' => $recentUsages,
        ]);
    }

    // Update coupon
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons')->ignore($coupon->id)],
            'name' => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_cart,free_shipping,buy_x_get_y,tiered',
            'value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'buy_qty' => 'nullable|integer|min:1',
            'get_qty' => 'nullable|integer|min:1',
            'get_discount_percent' => 'nullable|numeric|min:0|max:100',
            'tier_rules' => 'nullable|array',
            'channel' => 'required|in:all,online_only,pos_only',
            'applies_to' => 'required|in:all,specific_categories,specific_products',
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'is_first_order_only' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_first_order_only'] = $request->boolean('is_first_order_only', false);

        // Max discount cap is applicable only to percentage discounts
        if ($validated['type'] !== 'percentage') {
            $validated['max_discount_amount'] = null;
        }

        DB::transaction(function () use ($coupon, $validated, $request) {
            $oldValues = $coupon->only(['code', 'name', 'type', 'value', 'is_active']);
            $coupon->update($validated);

            // Re-sync restrictions
            CouponRestriction::where('coupon_id', $coupon->id)->delete();

            if ($validated['applies_to'] === 'specific_categories' && $request->filled('category_ids')) {
                foreach ($request->category_ids as $catId) {
                    CouponRestriction::create([
                        'coupon_id' => $coupon->id,
                        'restrictable_type' => 'category',
                        'restrictable_id' => $catId,
                    ]);
                }
            } elseif ($validated['applies_to'] === 'specific_products' && $request->filled('product_ids')) {
                foreach ($request->product_ids as $prodId) {
                    CouponRestriction::create([
                        'coupon_id' => $coupon->id,
                        'restrictable_type' => 'product',
                        'restrictable_id' => $prodId,
                    ]);
                }
            }

            $this->logAudit($coupon, 'updated', [
                'old' => $oldValues,
                'new' => $coupon->only(['code', 'name', 'type', 'value', 'is_active']),
            ]);
        });

        return response()->json([
            'message' => "Coupon '{$coupon->code}' updated successfully.",
            'data' => $coupon->load('restrictions'),
        ]);
    }

    // Duplicate coupon
    public function duplicate(Coupon $coupon)
    {
        $cloned = DB::transaction(function () use ($coupon) {
            // Generate non-colliding uppercase code
            $baseCode = strtoupper($coupon->code) . '_COPY';
            $newCode = $baseCode;
            $counter = 2;
            while (Coupon::where('code', $newCode)->exists()) {
                $newCode = $baseCode . $counter;
                $counter++;
            }

            $couponData = $coupon->replicate([
                'times_used',
                'created_at',
                'updated_at',
                'deleted_at',
            ])->toArray();

            $couponData['code'] = $newCode;
            $couponData['name'] = 'Copy of ' . $coupon->name;
            $couponData['times_used'] = 0;
            $couponData['created_by'] = auth()->id();
            $couponData['is_active'] = true;

            $newCoupon = Coupon::create($couponData);

            // Clone restrictions
            foreach ($coupon->restrictions as $restriction) {
                CouponRestriction::create([
                    'coupon_id' => $newCoupon->id,
                    'restrictable_type' => $restriction->restrictable_type,
                    'restrictable_id' => $restriction->restrictable_id,
                ]);
            }

            $this->logAudit($newCoupon, 'duplicated', [
                'cloned_from_id' => $coupon->id,
                'cloned_from_code' => $coupon->code,
                'new_code' => $newCoupon->code,
            ]);

            return $newCoupon->load('restrictions');
        });

        return response()->json([
            'message' => "Coupon duplicated successfully as '{$cloned->code}'.",
            'data' => $cloned,
        ], 201);
    }

    // Soft delete coupon
    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $this->logAudit($coupon, 'deleted', [
            'code' => $code,
            'name' => $coupon->name,
            'times_used' => $coupon->times_used,
        ]);

        $coupon->delete();

        return response()->json([
            'message' => "Coupon '{$code}' deleted successfully.",
        ]);
    }

    // Quick toggle active status
    public function toggleStatus(Coupon $coupon)
    {
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        $this->logAudit($coupon, 'status_toggled', [
            'is_active' => $coupon->is_active,
        ]);

        return response()->json([
            'message' => "Coupon '{$coupon->code}' is now " . ($coupon->is_active ? 'active' : 'inactive') . '.',
            'is_active' => $coupon->is_active,
        ]);
    }

    // List paginated usage audit log for coupon
    public function usages(Request $request, Coupon $coupon)
    {
        $usages = CouponUsage::with([
            'customer:id,name,phone,email',
            'sale:id,invoice_number,total_amount,sale_date,order_type'
        ])
        ->where('coupon_id', $coupon->id)
        ->latest('id')
        ->paginate($request->get('per_page', 20));

        return response()->json($usages);
    }

    // Customer-wise aggregated usage report
    public function customerReport(Coupon $coupon)
    {
        $customerStats = CouponUsage::where('coupon_id', $coupon->id)
            ->select(
                'customer_id',
                'customer_phone',
                DB::raw('COUNT(*) as total_redemptions'),
                DB::raw('SUM(discount_amount) as total_savings'),
                DB::raw('MAX(created_at) as last_redeemed_at')
            )
            ->groupBy('customer_id', 'customer_phone')
            ->with('customer:id,name,phone,email')
            ->orderByDesc('total_redemptions')
            ->paginate(15);

        return response()->json($customerStats);
    }

    // Audit logs for coupon
    public function auditLogs(Coupon $coupon)
    {
        $logs = CouponAuditLog::with('user:id,name')
            ->where('coupon_id', $coupon->id)
            ->latest('id')
            ->paginate(20);

        return response()->json($logs);
    }

    // Helper to write audit log entries
    protected function logAudit(Coupon $coupon, string $action, ?array $details = null): void
    {
        try {
            CouponAuditLog::create([
                'coupon_id' => $coupon->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'details' => $details,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail gracefully without breaking transaction
        }
    }
}
