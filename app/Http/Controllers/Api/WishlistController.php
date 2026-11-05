<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class WishlistController extends Controller
{
    /**
     * Resolve authenticated customer or guest identity token.
     */
    protected function resolveIdentity(Request $request): array
    {
        $customer = null;

        $user = $request->user();
        if ($user instanceof Customer) {
            $customer = $user;
        }

        if (!$customer) {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser instanceof Customer) {
                $customer = $sanctumUser;
            }
        }

        if (!$customer) {
            $bearer = $request->bearerToken();
            if ($bearer) {
                $token = PersonalAccessToken::findToken($bearer);
                if ($token && $token->tokenable instanceof Customer) {
                    $customer = $token->tokenable;
                }
            }
        }

        $guestToken = $request->header('X-Guest-Token')
            ?: $request->input('guest_token')
            ?: $request->query('guest_token');

        if ($guestToken) {
            $guestToken = trim(substr($guestToken, 0, 64));
        }

        return [$customer, $guestToken];
    }

    /**
     * Get wishlist items for customer or guest.
     */
    public function index(Request $request)
    {
        [$customer, $guestToken] = $this->resolveIdentity($request);

        if (!$customer && !$guestToken) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'in_stock_count' => 0,
                    'out_of_stock_count' => 0,
                    'price_drop_count' => 0,
                ],
            ]);
        }

        $query = Wishlist::with([
            'product' => function ($q) {
                $q->with([
                    'category:id,name,slug',
                    'unit:id,name,short_name,allow_decimal',
                    'images',
                    'variants' => function ($v) {
                        $v->with('stock')->where('status', true);
                    },
                ]);
            },
            'variant.stock',
        ]);

        if ($customer) {
            $query->where('customer_id', $customer->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $wishlistItems = $query->latest('id')->get();

        $formatted = [];
        $inStockCount = 0;
        $outOfStockCount = 0;
        $priceDropCount = 0;

        foreach ($wishlistItems as $item) {
            $product = $item->product;
            if (!$product || !$product->status) {
                continue;
            }

            // Default or selected variant
            $variant = $item->variant
                ?: ($product->variants->firstWhere('is_default', true) ?: $product->variants->first());

            $stockQty = (float) ($variant?->stock?->quantity ?? 0);
            $inStock = !$product->track_inventory || $stockQty > 0;

            if ($inStock) {
                $inStockCount++;
            } else {
                $outOfStockCount++;
            }

            $currentPrice = (float) ($variant?->selling_price ?? 0);
            $initialPrice = (float) $item->initial_price;
            $hasPriceDrop = $initialPrice > 0 && $currentPrice < $initialPrice;

            if ($hasPriceDrop) {
                $priceDropCount++;
            }

            $primaryImg = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
            $imageUrl = $primaryImg ? asset('storage/' . $primaryImg->image_path) : null;

            $formatted[] = [
                'id' => $item->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_name' => $product->category?->name ?? 'General',
                'category_slug' => $product->category?->slug ?? '',
                'image_url' => $imageUrl,
                'unit_name' => $product->unit?->short_name ?? 'pcs',
                'selling_price' => $currentPrice,
                'mrp' => (float) ($variant?->mrp ?? 0),
                'initial_price' => $initialPrice,
                'price_dropped' => $hasPriceDrop,
                'price_diff' => $hasPriceDrop ? round($initialPrice - $currentPrice, 2) : 0,
                'stock' => $stockQty,
                'in_stock' => $inStock,
                'track_inventory' => (bool) $product->track_inventory,
                'added_at' => $item->created_at->format('d M Y'),
                'variant' => $variant ? [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'selling_price' => $currentPrice,
                    'mrp' => (float) ($variant->mrp ?? 0),
                    'tax_rate' => (float) ($variant->tax_rate ?? 0),
                ] : null,
            ];
        }

        return response()->json([
            'data' => $formatted,
            'meta' => [
                'total' => count($formatted),
                'in_stock_count' => $inStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'price_drop_count' => $priceDropCount,
            ],
        ]);
    }

    /**
     * Toggle product in wishlist (add if absent, remove if present).
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'guest_token' => 'nullable|string|max:64',
        ]);

        [$customer, $guestToken] = $this->resolveIdentity($request);

        if (!$customer && !$guestToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to identify customer or guest session token.',
            ], 422);
        }

        $productId = $request->product_id;
        $variantId = $request->product_variant_id;

        $product = Product::with(['variants' => function ($v) {
            $v->where('status', true);
        }])->findOrFail($productId);

        if (!$variantId) {
            $defaultVariant = $product->variants->firstWhere('is_default', true) ?: $product->variants->first();
            $variantId = $defaultVariant?->id;
        }

        $query = Wishlist::where('product_id', $productId);
        if ($customer) {
            $query->where('customer_id', $customer->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'in_wishlist' => false,
                'message' => "'{$product->name}' removed from your Wishlist.",
            ]);
        }

        // Add new wishlist entry with current price
        $variant = ProductVariant::find($variantId);
        $currentPrice = (float) ($variant?->selling_price ?? 0);

        Wishlist::create([
            'customer_id' => $customer?->id,
            'guest_token' => $customer ? null : $guestToken,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'initial_price' => $currentPrice,
            'notify_price_drop' => true,
            'notify_back_in_stock' => true,
        ]);

        return response()->json([
            'success' => true,
            'in_wishlist' => true,
            'message' => "'{$product->name}' added to your Wishlist!",
        ]);
    }

    /**
     * Remove a single item from the wishlist.
     */
    public function destroy(Request $request, $productId)
    {
        [$customer, $guestToken] = $this->resolveIdentity($request);

        if (!$customer && !$guestToken) {
            return response()->json(['success' => false, 'message' => 'Missing session identity.'], 422);
        }

        $query = Wishlist::where('product_id', $productId);
        if ($customer) {
            $query->where('customer_id', $customer->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from your Wishlist.',
        ]);
    }

    /**
     * Synchronize guest wishlist items into registered customer account upon login/register.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'guest_token' => 'required|string|max:64',
        ]);

        [$customer] = $this->resolveIdentity($request);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer must be authenticated to synchronize wishlist.',
            ], 401);
        }

        $guestToken = trim($request->guest_token);
        $guestItems = Wishlist::where('guest_token', $guestToken)->get();

        if ($guestItems->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No guest wishlist items to synchronize.',
                'synced_count' => 0,
            ]);
        }

        $syncedCount = 0;

        DB::transaction(function () use ($guestItems, $customer, &$syncedCount) {
            foreach ($guestItems as $item) {
                // Check if customer already has this product in wishlist
                $alreadyExists = Wishlist::where('customer_id', $customer->id)
                    ->where('product_id', $item->product_id)
                    ->exists();

                if ($alreadyExists) {
                    // Drop duplicate guest record
                    $item->delete();
                } else {
                    // Transfer ownership to registered customer
                    $item->customer_id = $customer->id;
                    $item->guest_token = null;
                    $item->save();
                    $syncedCount++;
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Synchronized {$syncedCount} item(s) from your guest session into your account.",
            'synced_count' => $syncedCount,
        ]);
    }

    /**
     * Clear entire wishlist for customer or guest.
     */
    public function clear(Request $request)
    {
        [$customer, $guestToken] = $this->resolveIdentity($request);

        if (!$customer && !$guestToken) {
            return response()->json(['success' => false, 'message' => 'Missing session identity.'], 422);
        }

        $query = Wishlist::query();
        if ($customer) {
            $query->where('customer_id', $customer->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your Wishlist has been cleared.',
        ]);
    }
}

