<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    /**
     * Validate coupon and calculate exact discount amount for a cart.
     *
     * @param string $code
     * @param array $items Array of items with ['product_id', 'quantity', 'unit_price']
     * @param string $channel 'online_only', 'pos_only', or 'all'
     * @param Customer|null $customer
     * @param string|null $customerPhone
     * @param float $shippingCost
     * @return array
     */
    public function validateAndCalculate(
        string $code,
        array $items,
        string $channel = 'all',
        ?Customer $customer = null,
        ?string $customerPhone = null,
        float $shippingCost = 0.0
    ): array {
        $code = strtoupper(trim($code));

        if (empty($code)) {
            return [
                'valid' => false,
                'message' => 'Please provide a coupon code.',
            ];
        }

        if (empty($items)) {
            return [
                'valid' => false,
                'message' => 'Your cart is empty.',
            ];
        }

        // 1. Fetch Coupon with restrictions
        $coupon = Coupon::with('restrictions')
            ->where('code', $code)
            ->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => "Coupon code '{$code}' does not exist.",
            ];
        }

        // 2. Active status check
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'message' => "Coupon '{$code}' is currently inactive or disabled.",
            ];
        }

        // 3. Exact Start and Expiry Date-Time Validation
        if ($coupon->start_date && now()->lt($coupon->start_date)) {
            return [
                'valid' => false,
                'message' => "Coupon '{$code}' will be active from " . $coupon->start_date->format('d M Y, h:i A') . ".",
            ];
        }

        if ($coupon->end_date && now()->gt($coupon->end_date)) {
            return [
                'valid' => false,
                'message' => "Coupon '{$code}' expired on " . $coupon->end_date->format('d M Y, h:i A') . ".",
            ];
        }

        // 4. Channel match check
        if ($coupon->channel !== 'all' && $channel !== 'all' && $coupon->channel !== $channel) {
            $allowedChannel = $coupon->channel === 'pos_only' ? 'in-store POS counter' : 'online website orders';
            return [
                'valid' => false,
                'message' => "Coupon '{$code}' is only valid for {$allowedChannel}.",
            ];
        }

        // 5. Global usage limit check
        if ($coupon->usage_limit_total !== null && $coupon->times_used >= $coupon->usage_limit_total) {
            return [
                'valid' => false,
                'message' => "Coupon '{$code}' has reached its maximum global redemption limit.",
            ];
        }

        // 6. Per-customer & per-phone limit check (supports registered customer & guest phone)
        $phone = $customerPhone ?: $customer?->phone;
        if ($customer || $phone) {
            $usageQuery = CouponUsage::where('coupon_id', $coupon->id);

            $usageQuery->where(function ($q) use ($customer, $phone) {
                if ($customer && $phone) {
                    $q->where('customer_id', $customer->id)
                      ->orWhere('customer_phone', $phone);
                } elseif ($customer) {
                    $q->where('customer_id', $customer->id);
                } elseif ($phone) {
                    $q->where('customer_phone', $phone);
                }
            });

            $usageCount = $usageQuery->count();

            if ($coupon->usage_limit_per_customer !== null && $usageCount >= $coupon->usage_limit_per_customer) {
                return [
                    'valid' => false,
                    'message' => "You have already used coupon '{$code}' the maximum allowed number of times ({$coupon->usage_limit_per_customer}).",
                ];
            }
        }

        // 7. First order only check
        if ($coupon->is_first_order_only) {
            $pastSalesQuery = Sale::query();
            if ($customer) {
                $pastSalesQuery->where('customer_id', $customer->id);
            } elseif ($phone) {
                $pastSalesQuery->whereHas('customer', function ($q) use ($phone) {
                    $q->where('phone', $phone);
                });
            } else {
                $pastSalesQuery->whereRaw('1 = 0');
            }

            if ($pastSalesQuery->where('status', 'completed')->exists()) {
                return [
                    'valid' => false,
                    'message' => "Coupon '{$code}' is reserved exclusively for first-time customers.",
                ];
            }
        }

        // 8. Compute total subtotal and scope-eligible subtotal & items
        $productIds = collect($items)->pluck('product_id')->unique()->filter()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $cartSubtotal = 0.0;
        $eligibleSubtotal = 0.0;
        $eligibleUnitPrices = []; // For Buy X Get Y price calculations

        $categoryRestrictions = $coupon->restrictions
            ->where('restrictable_type', 'category')
            ->pluck('restrictable_id')
            ->all();

        $productRestrictions = $coupon->restrictions
            ->where('restrictable_type', 'product')
            ->pluck('restrictable_id')
            ->all();

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $lineSubtotal = round($qty * $price, 2);
            $cartSubtotal += $lineSubtotal;

            $product = $products->get($item['product_id']);
            $isEligible = true;

            if ($coupon->applies_to === 'specific_categories') {
                $isEligible = $product && in_array($product->category_id, $categoryRestrictions);
            } elseif ($coupon->applies_to === 'specific_products') {
                $isEligible = $product && in_array($product->id, $productRestrictions);
            }

            if ($isEligible) {
                $eligibleSubtotal += $lineSubtotal;
                // Add unit prices for BXGY evaluation
                for ($i = 0; $i < (int) floor($qty); $i++) {
                    $eligibleUnitPrices[] = $price;
                }
            }
        }

        // 9. Scope qualification check
        if ($eligibleSubtotal <= 0) {
            $scopeName = $coupon->applies_to === 'specific_categories' ? 'qualifying categories' : 'qualifying products';
            return [
                'valid' => false,
                'message' => "None of the items in your cart belong to the {$scopeName} for coupon '{$code}'.",
            ];
        }

        // 10. Minimum cart subtotal threshold check
        if ($cartSubtotal < (float) $coupon->min_order_amount) {
            return [
                'valid' => false,
                'message' => "Minimum order subtotal of ₹" . number_format($coupon->min_order_amount, 2) . " required for coupon '{$code}' (current subtotal: ₹" . number_format($cartSubtotal, 2) . ").",
            ];
        }

        // 11. Calculate final discount amount based on Discount Type
        $discountAmount = 0.0;
        $successMessage = "Coupon '{$coupon->code}' applied successfully!";

        if ($coupon->type === 'percentage') {
            // Percentage discount
            $discountAmount = round($eligibleSubtotal * ((float) $coupon->value / 100), 2);

            // Maximum discount cap applicable ONLY to percentage discounts
            if ($coupon->max_discount_amount !== null && (float) $coupon->max_discount_amount > 0) {
                $discountAmount = min($discountAmount, (float) $coupon->max_discount_amount);
            }
            $successMessage = "Coupon '{$coupon->code}' ({$coupon->value}% off) applied! Savings: ₹" . number_format($discountAmount, 2);

        } elseif ($coupon->type === 'fixed_cart') {
            // Fixed Cart discount (max discount cap not applicable)
            $discountAmount = min((float) $coupon->value, $eligibleSubtotal);
            $successMessage = "Coupon '{$coupon->code}' (Flat ₹" . number_format($coupon->value, 2) . " off) applied!";

        } elseif ($coupon->type === 'free_shipping') {
            // Free Shipping waiver
            $discountAmount = $shippingCost > 0 ? $shippingCost : 50.0;
            $successMessage = "Coupon '{$coupon->code}' applied: 100% Free Shipping waiver!";

        } elseif ($coupon->type === 'buy_x_get_y') {
            // Buy X Get Y Discount
            $buyQty = max(1, (int) ($coupon->buy_qty ?: 1));
            $getQty = max(1, (int) ($coupon->get_qty ?: 1));
            $bundleSize = $buyQty + $getQty;
            $discountPct = (float) ($coupon->get_discount_percent ?: 100.0);

            $totalEligibleQty = count($eligibleUnitPrices);

            if ($totalEligibleQty < $bundleSize) {
                $needed = $bundleSize - $totalEligibleQty;
                return [
                    'valid' => false,
                    'message' => "Buy {$buyQty} Get {$getQty} Offer: Please add {$needed} more qualifying item(s) to activate this discount.",
                ];
            }

            // Sort unit prices ascending so discount applies to lowest priced items (retail standard)
            sort($eligibleUnitPrices);

            $numBundles = (int) floor($totalEligibleQty / $bundleSize);
            $totalFreeUnits = $numBundles * $getQty;

            // Take the cheapest $totalFreeUnits items
            $freeUnits = array_slice($eligibleUnitPrices, 0, $totalFreeUnits);
            foreach ($freeUnits as $unitPrice) {
                $discountAmount += round($unitPrice * ($discountPct / 100), 2);
            }

            $discountAmount = min($discountAmount, $eligibleSubtotal);
            $successMessage = "Buy {$buyQty} Get {$getQty} offer applied! Saved ₹" . number_format($discountAmount, 2);

        } elseif ($coupon->type === 'tiered') {
            // Tiered Discount based on eligible subtotal
            $tiers = is_array($coupon->tier_rules) ? $coupon->tier_rules : json_decode($coupon->tier_rules ?: '[]', true);

            if (empty($tiers)) {
                return [
                    'valid' => false,
                    'message' => "Coupon '{$code}' has no tier configuration defined.",
                ];
            }

            // Sort tiers by min_amount descending
            usort($tiers, function ($a, $b) {
                return (float) ($b['min_amount'] ?? 0) <=> (float) ($a['min_amount'] ?? 0);
            });

            $matchedTier = null;
            foreach ($tiers as $tier) {
                if ($eligibleSubtotal >= (float) ($tier['min_amount'] ?? 0)) {
                    $matchedTier = $tier;
                    break;
                }
            }

            if (!$matchedTier) {
                // Find minimum required tier
                $lowestTier = end($tiers);
                $minRequired = (float) ($lowestTier['min_amount'] ?? 0);
                $needed = $minRequired - $eligibleSubtotal;
                return [
                    'valid' => false,
                    'message' => "Add items worth ₹" . number_format($needed, 2) . " more to qualify for tiered discount '{$code}'.",
                ];
            }

            $tierType = $matchedTier['type'] ?? 'percentage';
            $tierVal = (float) ($matchedTier['discount'] ?? 0);

            if ($tierType === 'percentage') {
                $discountAmount = round($eligibleSubtotal * ($tierVal / 100), 2);
                if ($coupon->max_discount_amount !== null && (float) $coupon->max_discount_amount > 0) {
                    $discountAmount = min($discountAmount, (float) $coupon->max_discount_amount);
                }
                $successMessage = "Tier unlocked! {$tierVal}% discount applied for purchase over ₹" . number_format($matchedTier['min_amount'], 2);
            } else {
                $discountAmount = min($tierVal, $eligibleSubtotal);
                $successMessage = "Tier unlocked! Flat ₹" . number_format($tierVal, 2) . " off applied for purchase over ₹" . number_format($matchedTier['min_amount'], 2);
            }
        }

        $discountAmount = max(0.0, round($discountAmount, 2));

        return [
            'valid' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount_amount' => $discountAmount,
            'free_shipping' => ($coupon->type === 'free_shipping'),
            'eligible_subtotal' => $eligibleSubtotal,
            'cart_subtotal' => $cartSubtotal,
            'message' => $successMessage,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
                'min_order_amount' => (float) $coupon->min_order_amount,
                'buy_qty' => $coupon->buy_qty,
                'get_qty' => $coupon->get_qty,
                'get_discount_percent' => $coupon->get_discount_percent,
                'tier_rules' => $coupon->tier_rules,
            ],
        ];
    }

    /**
     * Commit coupon redemption to database ledger and increment times_used with secure concurrent locking.
     *
     * @param Coupon $coupon
     * @param Sale $sale
     * @param Customer|null $customer
     * @param string|null $customerPhone
     * @param float $discountAmount
     * @return CouponUsage
     * @throws \Exception
     */
    public function recordUsage(
        Coupon $coupon,
        Sale $sale,
        ?Customer $customer = null,
        ?string $customerPhone = null,
        float $discountAmount = 0.0
    ): CouponUsage {
        // Secure concurrent redemption locking: lock the coupon row inside the transaction
        $lockedCoupon = Coupon::where('id', $coupon->id)->lockForUpdate()->first();

        if (!$lockedCoupon || !$lockedCoupon->is_active) {
            abort(422, "Coupon '{$coupon->code}' is no longer active.");
        }

        if ($lockedCoupon->usage_limit_total !== null && $lockedCoupon->times_used >= $lockedCoupon->usage_limit_total) {
            abort(422, "Coupon '{$coupon->code}' has reached its maximum global redemption limit.");
        }

        $phone = $customerPhone ?: $customer?->phone;
        if ($customer || $phone) {
            $usageQuery = CouponUsage::where('coupon_id', $lockedCoupon->id);
            $usageQuery->where(function ($q) use ($customer, $phone) {
                if ($customer && $phone) {
                    $q->where('customer_id', $customer->id)
                      ->orWhere('customer_phone', $phone);
                } elseif ($customer) {
                    $q->where('customer_id', $customer->id);
                } elseif ($phone) {
                    $q->where('customer_phone', $phone);
                }
            });

            $currentUses = $usageQuery->lockForUpdate()->count();
            if ($lockedCoupon->usage_limit_per_customer !== null && $currentUses >= $lockedCoupon->usage_limit_per_customer) {
                abort(422, "You have already reached the maximum redemption limit ({$lockedCoupon->usage_limit_per_customer}) for coupon '{$lockedCoupon->code}'.");
            }
        }

        $usage = CouponUsage::create([
            'coupon_id' => $lockedCoupon->id,
            'sale_id' => $sale->id,
            'customer_id' => $customer?->id,
            'customer_phone' => $phone,
            'discount_amount' => $discountAmount,
            'created_at' => now(),
        ]);

        $lockedCoupon->increment('times_used');

        return $usage;
    }
}
