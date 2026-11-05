<template>
    <div class="cart-page py-4">
        <div class="container">
            <h3 class="fw-bold mb-4">🛒 Shopping Cart</h3>

            <!-- Empty Cart State -->
            <div v-if="cartItems.length === 0" class="mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
                    <div class="fs-1 text-muted mb-3">🛍️</div>
                    <h4 class="fw-bold mb-2">Your Cart is Empty</h4>
                    <p class="text-muted small mb-4" style="max-width: 400px; margin: 0 auto;">
                        Looks like you haven't added anything to your cart yet. Explore our wide range of products with live inventory!
                    </p>
                    <div>
                        <router-link to="/shop" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                            Start Shopping Now →
                        </router-link>
                    </div>
                </div>

                <!-- Saved for later even if cart is empty -->
                <div v-if="savedForLaterItems.length > 0" class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <span>📌</span>
                            <span>Saved for Later ({{ savedForLaterItems.length }} {{ savedForLaterItems.length === 1 ? 'item' : 'items' }})</span>
                        </h5>
                        <button class="btn btn-link text-danger p-0 extra-small text-decoration-none" @click="clearSaved">
                            Clear All Saved
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                <tr v-for="item in savedForLaterItems" :key="item.product_variant_id">
                                    <td style="width: 60px;">
                                        <div class="cart-img-wrap rounded border p-1 bg-light">
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                :alt="item.name"
                                                class="cart-thumb"
                                            />
                                            <div v-else class="text-muted small text-center">📦</div>
                                        </div>
                                    </td>
                                    <td>
                                        <router-link :to="`/product/${item.product_id}`" class="text-dark fw-bold text-decoration-none d-block text-truncate" style="max-width: 260px;">
                                            {{ item.name }}
                                        </router-link>
                                        <div class="extra-small text-muted">
                                            Qty: {{ item.quantity }} | Unit: {{ item.unit_name }}
                                        </div>
                                        <div class="extra-small mt-1">
                                            <span v-if="!item.track_inventory || item.available_stock > 0" class="text-success fw-semibold">
                                                ● In Stock
                                            </span>
                                            <span v-else class="text-danger fw-semibold">
                                                ● Out of Stock
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        ₹{{ formatMoney(item.selling_price) }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm rounded-pill px-3"
                                                :disabled="item.track_inventory && item.available_stock <= 0"
                                                @click="moveToCartFromSaved(item.product_variant_id)"
                                            >
                                                Move to Cart
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-link text-danger p-0"
                                                title="Remove from saved"
                                                @click="removeFromSaved(item.product_variant_id)"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Cart with Items -->
            <div v-else class="row g-4">
                <!-- Items Column -->
                <div class="col-lg-8">
                    <!-- Free Delivery Progress Bar -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white">
                        <div v-if="cartSubtotal < 500 && (!appliedCoupon || (appliedCoupon.type !== 'free_shipping' && !appliedCoupon.free_shipping))">
                            <div class="d-flex justify-content-between small fw-medium mb-1">
                                <span>🚚 Add <strong>₹{{ formatMoney(500 - cartSubtotal) }}</strong> more for <strong>FREE Delivery</strong></span>
                                <span class="text-muted">{{ Math.min(100, Math.round((cartSubtotal / 500) * 100)) }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div
                                    class="progress-bar bg-warning progress-bar-striped progress-bar-animated"
                                    role="progressbar"
                                    :style="{ width: `${Math.min(100, (cartSubtotal / 500) * 100)}%` }"
                                ></div>
                            </div>
                        </div>
                        <div v-else class="d-flex align-items-center gap-2 text-success small fw-bold">
                            <span>🎉 Congratulations! Your order qualifies for FREE Delivery!</span>
                        </div>
                    </div>

                    <!-- Cart Table Card -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-3">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted">
                                    <tr>
                                        <th scope="col" style="min-width: 220px;">Product</th>
                                        <th scope="col" class="text-center">Price</th>
                                        <th scope="col" class="text-center" style="min-width: 130px;">Quantity</th>
                                        <th scope="col" class="text-end">Subtotal</th>
                                        <th scope="col" class="text-center" style="width: 110px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in cartItems" :key="item.product_variant_id">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="cart-img-wrap rounded border p-1 bg-light flex-shrink-0">
                                                    <img
                                                        v-if="item.image_url"
                                                        :src="item.image_url"
                                                        :alt="item.name"
                                                        class="cart-thumb"
                                                    />
                                                    <div v-else class="text-muted small text-center">📦</div>
                                                </div>
                                                <div class="min-w-0">
                                                    <router-link :to="`/product/${item.product_id}`" class="text-dark fw-bold text-decoration-none d-block text-truncate" style="max-width: 200px;">
                                                        {{ item.name }}
                                                    </router-link>
                                                    <div class="extra-small text-muted">
                                                        SKU: {{ item.sku }} | Unit: {{ item.unit_name }}
                                                    </div>
                                                    <div v-if="item.track_inventory" class="extra-small text-success">
                                                        In Stock: {{ item.available_stock }} available
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center fw-semibold text-secondary">
                                            ₹{{ formatMoney(item.selling_price) }}
                                        </td>

                                        <td class="text-center">
                                            <div class="input-group input-group-sm mx-auto" style="width: 110px;">
                                                <button
                                                    class="btn btn-outline-secondary"
                                                    type="button"
                                                    @click="updateQuantity(item.product_variant_id, item.quantity - 1)"
                                                >
                                                    -
                                                </button>
                                                <input
                                                    :value="item.quantity"
                                                    type="number"
                                                    class="form-control text-center fw-bold px-1"
                                                    min="1"
                                                    :max="item.track_inventory ? item.available_stock : 999"
                                                    @change="onQtyInputChange(item, $event)"
                                                />
                                                <button
                                                    class="btn btn-outline-secondary"
                                                    type="button"
                                                    :disabled="item.track_inventory && item.quantity >= item.available_stock"
                                                    @click="updateQuantity(item.product_variant_id, item.quantity + 1)"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        </td>

                                        <td class="text-end fw-bold text-primary">
                                            ₹{{ formatMoney(item.selling_price * item.quantity) }}
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <button
                                                    class="btn btn-link text-danger p-0"
                                                    title="Remove item"
                                                    @click="removeFromCart(item.product_variant_id)"
                                                >
                                                    🗑️
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-link text-primary p-0 extra-small text-decoration-none"
                                                    title="Save for Later"
                                                    @click="saveForLater(item.product_variant_id)"
                                                >
                                                    Save for later
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bottom Cart Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
                            <button class="btn btn-outline-danger btn-sm rounded-pill" @click="confirmClearCart">
                                Clear Cart
                            </button>
                            <router-link to="/shop" class="btn btn-link text-decoration-none small text-primary">
                                ← Continue Shopping
                            </router-link>
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- SAVED FOR LATER SECTION (INSIDE ACTIVE CART)             -->
                    <!-- ======================================================== -->
                    <div v-if="savedForLaterItems.length > 0" class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <span>📌</span>
                                <span>Saved for Later ({{ savedForLaterItems.length }} {{ savedForLaterItems.length === 1 ? 'item' : 'items' }})</span>
                            </h6>
                            <button class="btn btn-link text-danger p-0 extra-small text-decoration-none" @click="clearSaved">
                                Clear Saved
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <tbody>
                                    <tr v-for="item in savedForLaterItems" :key="item.product_variant_id">
                                        <td style="width: 50px;">
                                            <div class="cart-img-wrap rounded border p-1 bg-light">
                                                <img
                                                    v-if="item.image_url"
                                                    :src="item.image_url"
                                                    :alt="item.name"
                                                    class="cart-thumb"
                                                />
                                                <div v-else class="text-muted small text-center">📦</div>
                                            </div>
                                        </td>
                                        <td>
                                            <router-link :to="`/product/${item.product_id}`" class="text-dark fw-bold text-decoration-none d-block text-truncate" style="max-width: 200px;">
                                                {{ item.name }}
                                            </router-link>
                                            <div class="extra-small text-muted">
                                                Qty: {{ item.quantity }} | Unit: {{ item.unit_name }}
                                            </div>
                                            <div class="extra-small mt-1">
                                                <span v-if="!item.track_inventory || item.available_stock > 0" class="text-success fw-semibold">
                                                    ● In Stock
                                                </span>
                                                <span v-else class="text-danger fw-semibold">
                                                    ● Out of Stock
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold text-primary">
                                            ₹{{ formatMoney(item.selling_price) }}
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm rounded-pill px-3"
                                                    :disabled="item.track_inventory && item.available_stock <= 0"
                                                    @click="moveToCartFromSaved(item.product_variant_id)"
                                                >
                                                    Move to Cart
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-link text-danger p-0"
                                                    title="Remove from saved"
                                                    @click="removeFromSaved(item.product_variant_id)"
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Column -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 80px;">
                        <h5 class="fw-bold mb-3">Order Summary</h5>

                        <!-- Coupon Code Section -->
                        <div class="mb-3 pb-3 border-bottom">
                            <label class="form-label extra-small fw-semibold text-uppercase text-muted mb-1">Promo / Coupon Code</label>

                            <div v-if="appliedCoupon" class="p-2 rounded bg-success-subtle border border-success-subtle d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">🏷️</span>
                                    <div>
                                        <div class="fw-bold text-success small font-monospace">{{ appliedCoupon.code }}</div>
                                        <div class="extra-small text-secondary">{{ appliedCoupon.name || 'Discount applied' }}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" title="Remove Coupon" @click="removeCoupon">
                                    ✕ Remove
                                </button>
                            </div>

                            <div v-else>
                                <div class="input-group input-group-sm">
                                    <input
                                        v-model="couponInput"
                                        type="text"
                                        class="form-control text-uppercase font-monospace"
                                        placeholder="e.g. WELCOME100"
                                        :disabled="validatingCoupon"
                                        @input="couponInput = couponInput.toUpperCase()"
                                        @keydown.enter.prevent="handleApplyCoupon"
                                    />
                                    <button
                                        class="btn btn-outline-primary fw-semibold px-3"
                                        type="button"
                                        :disabled="validatingCoupon || !couponInput.trim()"
                                        @click="handleApplyCoupon"
                                    >
                                        <span v-if="validatingCoupon" class="spinner-border spinner-border-sm me-1"></span>
                                        {{ validatingCoupon ? 'Checking...' : 'Apply' }}
                                    </button>
                                </div>
                                <div v-if="couponError" class="extra-small text-danger mt-1">
                                    ⚠️ {{ couponError }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Subtotal ({{ cartCount }} items)</span>
                            <span class="fw-semibold text-dark">₹{{ formatMoney(cartSubtotal) }}</span>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Estimated Tax (GST)</span>
                            <span class="fw-semibold text-dark">₹{{ formatMoney(cartTaxTotal) }}</span>
                        </div>

                        <div v-if="appliedCoupon && couponDiscount > 0" class="d-flex justify-content-between small text-success mb-2">
                            <span class="fw-medium">Coupon Savings ({{ appliedCoupon.code }})</span>
                            <span class="fw-bold">-₹{{ formatMoney(couponDiscount) }}</span>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-3 pb-3 border-bottom">
                            <span>Delivery Charges</span>
                            <span v-if="cartShipping === 0" class="badge bg-success-subtle text-success">
                                FREE
                            </span>
                            <span v-else class="fw-semibold text-dark">
                                ₹{{ formatMoney(cartShipping) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark mb-4">
                            <span>Total Payable</span>
                            <span class="text-primary">₹{{ formatMoney(cartGrandTotal) }}</span>
                        </div>

                        <router-link
                            to="/checkout"
                            class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        >
                            <span>Proceed to Checkout</span>
                            <span>→</span>
                        </router-link>

                        <!-- Trust Highlights -->
                        <div class="bg-light rounded-3 p-3 extra-small text-muted">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span>🔒</span>
                                <span>SSL 256-bit Secure Checkout</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span>⚡</span>
                                <span>Real-time counter stock reservation</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span>💵</span>
                                <span>Cash on Delivery & UPI accepted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useCart } from '../../composables/useCart';
import { useNotification } from '../../composables/useNotification';

const { toast, confirmDialog } = useNotification();

const {
    cartItems,
    cartCount,
    cartSubtotal,
    cartTaxTotal,
    cartShipping,
    couponDiscount,
    cartGrandTotal,
    appliedCoupon,
    applyCoupon,
    removeCoupon,
    updateQuantity,
    removeFromCart,
    clearCart,
    savedForLaterItems,
    saveForLater,
    moveToCartFromSaved,
    removeFromSaved,
    clearSaved,
} = useCart();

const couponInput = ref('');
const validatingCoupon = ref(false);
const couponError = ref('');

async function handleApplyCoupon() {
    couponError.value = '';
    const code = couponInput.value.trim().toUpperCase();
    if (!code) return;

    validatingCoupon.value = true;
    try {
        const payload = {
            code: code,
            items: cartItems.value.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.selling_price,
            })),
            shipping_cost: cartShipping.value,
        };

        const res = await axios.post('/api/shop/coupons/validate', payload);
        const data = res.data.data;
        applyCoupon({
            ...data.coupon,
            discount_amount: data.discount_amount,
            free_shipping: data.free_shipping,
        });
        couponInput.value = '';
        toast.success(data.message || `Coupon '${code}' applied successfully!`, 'Coupon Applied');
    } catch (err) {
        const msg = err.response?.data?.message || 'Invalid or ineligible coupon code.';
        couponError.value = msg;
        toast.error(msg, 'Coupon Error');
    } finally {
        validatingCoupon.value = false;
    }
}

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function onQtyInputChange(item, event) {
    const val = parseFloat(event.target.value);
    if (isNaN(val) || val <= 0) {
        updateQuantity(item.product_variant_id, 1);
    } else {
        updateQuantity(item.product_variant_id, val);
    }
}

async function confirmClearCart() {
    const ok = await confirmDialog({
        title: 'Clear Shopping Cart?',
        message: 'Are you sure you want to remove all items from your cart?',
        confirmText: 'Yes, Clear Cart',
        type: 'danger',
    });
    if (ok) {
        clearCart();
        toast.info('Your shopping cart has been cleared.', 'Cart Cleared');
    }
}
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.cart-img-wrap {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-thumb {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}
</style>
