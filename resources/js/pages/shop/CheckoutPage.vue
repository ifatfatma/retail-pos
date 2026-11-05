<template>
    <div class="checkout-page py-4">
        <div class="container">
            <h3 class="fw-bold mb-4">💳 Checkout & Delivery</h3>

            <!-- If Cart is Empty -->
            <div v-if="cartItems.length === 0" class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <div class="fs-1 text-muted mb-3">🛒</div>
                <h4 class="fw-bold mb-2">Your Cart is Empty</h4>
                <p class="text-muted small mb-4">Please add items to your cart before proceeding to checkout.</p>
                <div>
                    <router-link to="/shop" class="btn btn-primary rounded-pill px-4">
                        Browse Catalog
                    </router-link>
                </div>
            </div>

            <!-- Checkout Form & Order Summary -->
            <div v-else class="row g-4">
                <!-- Left Column: Form Details -->
                <div class="col-lg-7">
                    <form @submit.prevent="submitOrder">
                        <!-- Customer Contact Details Card -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                    <span>1.</span>
                                    <span>Customer Information</span>
                                </h5>

                                <span v-if="isCustomerAuthenticated" class="badge bg-success-subtle text-success small">
                                    ✓ Logged in as {{ customerName }}
                                </span>
                                <router-link v-else :to="{ path: '/login', query: { redirect: '/checkout' } }" class="small text-primary text-decoration-none">
                                    Have an account? Sign In →
                                </router-link>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.customer_name"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. Rahul Sharma"
                                        required
                                    />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.customer_phone"
                                        type="tel"
                                        class="form-control"
                                        placeholder="e.g. 9876543210"
                                        required
                                    />
                                    <div class="extra-small text-muted mt-1">We will send SMS / WhatsApp updates on this number.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Email Address <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input
                                        v-model="form.customer_email"
                                        type="email"
                                        class="form-control"
                                        placeholder="e.g. rahul@example.com"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address Card -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <span>2.</span>
                                <span>Delivery Address</span>
                            </h5>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">House / Flat No., Street, Landmark <span class="text-danger">*</span></label>
                                    <textarea
                                        v-model="form.street_address"
                                        class="form-control"
                                        rows="2"
                                        placeholder="e.g. Flat 402, Royal Palms, Link Road, Near Metro Pillar 12"
                                        required
                                    ></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">City <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. Mumbai"
                                        required
                                    />
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">State <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.state"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. Maharashtra"
                                        required
                                    />
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Pincode <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.pincode"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. 400001"
                                        required
                                    />
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Delivery Instructions / Notes <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input
                                        v-model="form.notes"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. Call before delivery, leave with security guard"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Card -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <span>3.</span>
                                <span>Payment Method</span>
                            </h5>

                            <div class="d-flex flex-column gap-3">
                                <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer" :class="{ 'border-primary bg-primary-subtle': form.payment_method === 'cod' }">
                                    <input type="radio" v-model="form.payment_method" value="cod" class="form-check-input mt-0" />
                                    <div>
                                        <div class="fw-bold text-dark">💵 Cash on Delivery (COD)</div>
                                        <div class="extra-small text-muted">Pay by cash or scan QR when your parcel arrives.</div>
                                    </div>
                                </label>

                                <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer" :class="{ 'border-primary bg-primary-subtle': form.payment_method === 'upi' }">
                                    <input type="radio" v-model="form.payment_method" value="upi" class="form-check-input mt-0" />
                                    <div>
                                        <div class="fw-bold text-dark">📱 Instant UPI / QR Payment</div>
                                        <div class="extra-small text-muted">Google Pay, PhonePe, Paytm, BHIM.</div>
                                    </div>
                                </label>

                                <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer" :class="{ 'border-primary bg-primary-subtle': form.payment_method === 'card' }">
                                    <input type="radio" v-model="form.payment_method" value="card" class="form-check-input mt-0" />
                                    <div>
                                        <div class="fw-bold text-dark">💳 Credit / Debit Card</div>
                                        <div class="extra-small text-muted">Visa, MasterCard, RuPay, Maestro.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Server Error Display -->
                        <div v-if="serverError" class="alert alert-danger rounded-3 mb-4" role="alert">
                            <strong>Order Placement Error:</strong> {{ serverError }}
                        </div>

                        <!-- Submit Button on mobile/tablet -->
                        <div class="d-lg-none mb-4">
                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100 fw-bold rounded-pill"
                                :disabled="submitting"
                            >
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                {{ submitting ? 'Placing Order...' : `Place Order (₹${formatMoney(cartGrandTotal)})` }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Order Review & Placement -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 80px;">
                        <h5 class="fw-bold mb-3">Order Items ({{ cartCount }})</h5>

                        <!-- Items list -->
                        <div class="order-items-scroll mb-3" style="max-height: 240px; overflow-y: auto;">
                            <div
                                v-for="item in cartItems"
                                :key="item.product_variant_id"
                                class="d-flex align-items-center justify-content-between py-2 border-bottom"
                            >
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <img
                                        v-if="item.image_url"
                                        :src="item.image_url"
                                        class="rounded border"
                                        style="width: 40px; height: 40px; object-fit: cover;"
                                    />
                                    <div v-else class="rounded border bg-light text-center" style="width: 40px; height: 40px; line-height: 40px;">
                                        📦
                                    </div>
                                    <div class="min-w-0">
                                        <div class="small fw-semibold text-truncate" style="max-width: 170px;">
                                            {{ item.name }}
                                        </div>
                                        <div class="extra-small text-muted">
                                            Qty: {{ item.quantity }} {{ item.unit_name }} × ₹{{ formatMoney(item.selling_price) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end fw-bold small text-dark">
                                    ₹{{ formatMoney(item.selling_price * item.quantity) }}
                                </div>
                            </div>
                        </div>

                        <!-- Coupon Code Section -->
                        <div class="mb-3 pb-3 border-bottom">
                            <label class="form-label extra-small fw-semibold text-uppercase text-muted mb-1">Coupon Code</label>

                            <div v-if="appliedCoupon" class="p-2 rounded bg-success-subtle border border-success-subtle d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">🏷️</span>
                                    <div>
                                        <div class="fw-bold text-success small font-monospace">{{ appliedCoupon.code }}</div>
                                        <div class="extra-small text-secondary">{{ appliedCoupon.name || 'Coupon Applied' }}</div>
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

                        <!-- Calculation breakdown -->
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Subtotal</span>
                            <span class="fw-semibold text-dark">₹{{ formatMoney(cartSubtotal) }}</span>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>GST Tax</span>
                            <span class="fw-semibold text-dark">₹{{ formatMoney(cartTaxTotal) }}</span>
                        </div>

                        <div v-if="appliedCoupon && couponDiscount > 0" class="d-flex justify-content-between small text-success mb-2">
                            <span class="fw-medium">Coupon Savings ({{ appliedCoupon.code }})</span>
                            <span class="fw-bold">-₹{{ formatMoney(couponDiscount) }}</span>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-3 pb-3 border-bottom">
                            <span>Delivery</span>
                            <span v-if="cartShipping === 0" class="badge bg-success-subtle text-success">
                                FREE
                            </span>
                            <span v-else class="fw-semibold text-dark">
                                ₹{{ formatMoney(cartShipping) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark mb-4">
                            <span>Total Amount</span>
                            <span class="text-primary">₹{{ formatMoney(cartGrandTotal) }}</span>
                        </div>

                        <!-- Desktop Submit Button -->
                        <button
                            type="button"
                            class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-none d-lg-block"
                            :disabled="submitting"
                            @click="submitOrder"
                        >
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                            {{ submitting ? 'Processing Order...' : `Confirm & Place Order` }}
                        </button>

                        <div class="extra-small text-muted text-center">
                            By placing this order, you agree to our Store Terms and live inventory reservation policy.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useCart } from '../../composables/useCart';
import { useNotification } from '../../composables/useNotification';
import { useCustomerAuth } from '../../composables/useCustomerAuth';

const router = useRouter();
const { toast } = useNotification();
const { customer, customerName, isCustomerAuthenticated, fetchCustomerProfile } = useCustomerAuth();
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
    clearCart,
} = useCart();

const form = ref({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    street_address: '',
    city: '',
    state: '',
    pincode: '',
    notes: '',
    payment_method: 'cod',
});

const couponInput = ref('');
const validatingCoupon = ref(false);
const couponError = ref('');
const submitting = ref(false);
const serverError = ref(null);

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

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
            customer_phone: form.value.customer_phone || null,
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

async function submitOrder() {
    // Form validation
    if (!form.value.customer_name.trim()) {
        toast.warning('Please enter your full name.', 'Missing Name');
        return;
    }
    if (!form.value.customer_phone.trim()) {
        toast.warning('Please enter your contact phone number.', 'Missing Phone');
        return;
    }
    if (!form.value.street_address.trim() || !form.value.city.trim() || !form.value.pincode.trim()) {
        toast.warning('Please complete the delivery address fields (Street, City, Pincode).', 'Address Incomplete');
        return;
    }

    submitting.value = true;
    serverError.value = null;

    try {
        const fullShippingAddress = `${form.value.street_address.trim()}, ${form.value.city.trim()}, ${form.value.state.trim()} - ${form.value.pincode.trim()}`;

        const payload = {
            customer_name: form.value.customer_name.trim(),
            customer_phone: form.value.customer_phone.trim(),
            customer_email: form.value.customer_email.trim() || null,
            shipping_address: fullShippingAddress,
            payment_method: form.value.payment_method,
            coupon_code: appliedCoupon.value ? appliedCoupon.value.code : null,
            notes: form.value.notes.trim() || null,
            items: cartItems.value.map(item => ({
                product_id: item.product_id,
                product_variant_id: item.product_variant_id,
                quantity: item.quantity,
            })),
        };

        const res = await axios.post('/api/shop/checkout', payload);
        const orderData = res.data.data;

        // Clear cart and coupon
        clearCart();

        // Redirect to success page
        router.push({
            name: 'OrderSuccess',
            params: { orderNumber: orderData.order_number },
            state: { order: orderData }
        });
    } catch (e) {
        console.error('Checkout error:', e);
        serverError.value = e.response?.data?.message || 'Failed to place order. Please review your details and try again.';
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    if (isCustomerAuthenticated.value) {
        await fetchCustomerProfile();
        if (customer.value) {
            form.value.customer_name = customer.value.name || '';
            form.value.customer_phone = customer.value.phone || '';
            form.value.customer_email = customer.value.email || '';
            form.value.street_address = customer.value.address || '';
            form.value.city = customer.value.city || '';
            form.value.state = customer.value.state || '';
            form.value.pincode = customer.value.pincode || '';
        }
    }
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.cursor-pointer {
    cursor: pointer;
}
</style>
