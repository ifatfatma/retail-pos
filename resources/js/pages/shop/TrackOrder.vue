<template>
    <div class="track-order-page py-4">
        <div class="container" style="max-width: 800px;">
            <div class="text-center mb-4 d-print-none">
                <span class="fs-1">📍</span>
                <h3 class="fw-bold mb-1">{{ $t('nav.track_order') }}</h3>
                <p class="text-muted small">{{ $t('storefront.track_sub') }}</p>
            </div>

            <!-- Search Card -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4 d-print-none">
                <form @submit.prevent="lookupOrder" class="d-flex gap-2">
                    <input
                        v-model="orderInput"
                        type="text"
                        class="form-control"
                        :placeholder="$t('storefront.track_placeholder')"
                        required
                    />
                    <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill flex-shrink-0" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ $t('common.track') }}
                    </button>
                </form>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">{{ $t('storefront.locating_order') }}</p>
            </div>

            <!-- Error State -->
            <div v-else-if="errorMessage" class="alert alert-warning rounded-3 text-center" role="alert">
                {{ errorMessage }}
            </div>

            <!-- Tracking Result -->
            <div v-else-if="order" class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <!-- Header with Order Number & Date -->
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <div>
                        <div class="extra-small text-muted text-uppercase fw-semibold">{{ $t('orders.order_number') }}</div>
                        <h4 class="fw-bold text-primary mb-0">{{ order.order_number }}</h4>
                    </div>
                    <div class="text-end">
                        <div class="extra-small text-muted">{{ $t('storefront.placed_on') }}</div>
                        <div class="fw-semibold text-dark">{{ order.sale_date }}</div>
                    </div>
                </div>

                <!-- Cancelled Alert -->
                <div v-if="order.status === 'cancelled'" class="alert alert-danger rounded-3 mb-4 text-center">
                    <strong>{{ $t('storefront.order_cancelled') }}</strong><br>
                    <small>{{ $t('storefront.order_cancelled_sub') }}</small>
                </div>

                <!-- Stepper Progress Bar (for non-cancelled orders) -->
                <div v-else class="status-stepper mb-5 py-2">
                    <div class="d-flex justify-content-between position-relative">
                        <!-- Progress connecting line -->
                        <div class="stepper-line" :style="{ width: progressLineWidth }"></div>

                        <!-- Step 1: Placed -->
                        <div class="step-item text-center">
                            <div class="step-circle" :class="{ 'completed': isStepCompleted(1), 'active': currentStepIndex === 1 }">
                                ✓
                            </div>
                            <div class="step-label small fw-semibold mt-2">{{ $t('storefront.order_placed') }}</div>
                            <div class="extra-small text-muted">Pending</div>
                        </div>

                        <!-- Step 2: Confirmed -->
                        <div class="step-item text-center">
                            <div class="step-circle" :class="{ 'completed': isStepCompleted(2), 'active': currentStepIndex === 2 }">
                                2
                            </div>
                            <div class="step-label small fw-semibold mt-2">{{ $t('storefront.confirmed') }}</div>
                            <div class="extra-small text-muted">Accepted</div>
                        </div>

                        <!-- Step 3: Processing -->
                        <div class="step-item text-center">
                            <div class="step-circle" :class="{ 'completed': isStepCompleted(3), 'active': currentStepIndex === 3 }">
                                3
                            </div>
                            <div class="step-label small fw-semibold mt-2">{{ $t('storefront.processing') }}</div>
                            <div class="extra-small text-muted">Packed</div>
                        </div>

                        <!-- Step 4: Shipped -->
                        <div class="step-item text-center">
                            <div class="step-circle" :class="{ 'completed': isStepCompleted(4), 'active': currentStepIndex === 4 }">
                                4
                            </div>
                            <div class="step-label small fw-semibold mt-2">{{ $t('common.shipped') }}</div>
                            <div class="extra-small text-muted">In Transit</div>
                        </div>

                        <!-- Step 5: Delivered -->
                        <div class="step-item text-center">
                            <div class="step-circle" :class="{ 'completed': isStepCompleted(5), 'active': currentStepIndex === 5 }">
                                5
                            </div>
                            <div class="step-label small fw-semibold mt-2">{{ $t('common.delivered') }}</div>
                            <div class="extra-small text-muted">Fulfilled</div>
                        </div>
                    </div>
                </div>

                <!-- Delivery & Shipping Details -->
                <div class="row g-3 bg-light-subtle rounded-3 p-3 mb-4 small border">
                    <div class="col-md-6">
                        <div class="text-muted extra-small text-uppercase">{{ $t('storefront.recipient_info') }}</div>
                        <div class="fw-bold text-dark">{{ order.customer?.name }}</div>
                        <div class="text-muted">{{ order.customer?.phone }}</div>
                        <div class="text-dark mt-1">{{ order.shipping_address }}</div>
                    </div>
                    <div class="col-md-6 border-start-md ps-md-3">
                        <div class="text-muted extra-small text-uppercase">{{ $t('storefront.logistics_tracking') }}</div>
                        <div>
                            {{ $t('storefront.delivery_partner') }}:
                            <strong class="text-dark">{{ order.delivery_partner || 'Store Express Dispatch' }}</strong>
                        </div>
                        <div v-if="order.tracking_number">
                            {{ $t('storefront.courier_tracking_num') }}:
                            <strong class="text-primary">{{ order.tracking_number }}</strong>
                        </div>
                        <div v-if="order.estimated_delivery_date">
                            {{ $t('storefront.estimated_delivery') }}:
                            <strong class="text-success">{{ order.estimated_delivery_date }}</strong>
                        </div>
                        <div class="mt-2">
                            {{ $t('account.payment') }}:
                            <span class="badge bg-secondary-subtle text-secondary text-uppercase me-1">{{ order.payment_method }}</span>
                            <span class="badge" :class="order.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'">
                                {{ order.payment_status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ordered Items Table -->
                <h6 class="fw-bold mb-3">{{ $t('storefront.items_in_order') }}</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th>{{ $t('orders.item') }}</th>
                                <th class="text-center">{{ $t('common.sku') }}</th>
                                <th class="text-center">{{ $t('common.qty') }}</th>
                                <th class="text-end">{{ $t('common.price') }}</th>
                                <th class="text-end">{{ $t('common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr v-for="(it, idx) in order.items" :key="idx">
                                <td class="fw-medium text-dark">{{ it.product_name }}</td>
                                <td class="text-center text-muted">{{ it.sku }}</td>
                                <td class="text-center">{{ it.quantity }}</td>
                                <td class="text-end">₹{{ formatMoney(it.unit_price) }}</td>
                                <td class="text-end fw-semibold">₹{{ formatMoney(it.total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-top">
                            <tr class="fw-bold text-dark">
                                <td colspan="4" class="text-end">{{ $t('common.grand_total') }}:</td>
                                <td class="text-end text-primary fs-6">₹{{ formatMoney(order.total_amount) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Back to Shop & Print Invoice -->
                <div class="d-flex justify-content-center gap-2 pt-2 d-print-none">
                    <button
                        type="button"
                        class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-semibold"
                        @click="printOrderInvoice"
                    >
                        🖨️ {{ $t('receipt.print_invoice') }}
                    </button>
                    <router-link to="/shop" class="btn btn-outline-primary rounded-pill px-4 btn-sm">
                        ← {{ $t('storefront.back_to_shopping') }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const orderInput = ref(route.query.order_number || '');
const order = ref(null);
const loading = ref(false);
const errorMessage = ref(null);

const statusStepMap = {
    pending: 1,
    confirmed: 2,
    processing: 3,
    shipped: 4,
    delivered: 5,
};

const currentStepIndex = computed(() => {
    if (!order.value) return 1;
    return statusStepMap[order.value.status] || 1;
});

const progressLineWidth = computed(() => {
    const idx = currentStepIndex.value;
    if (idx <= 1) return '0%';
    if (idx === 2) return '25%';
    if (idx === 3) return '50%';
    if (idx === 4) return '75%';
    return '100%';
});

function isStepCompleted(stepNum) {
    return currentStepIndex.value >= stepNum;
}

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

async function lookupOrder() {
    if (!orderInput.value.trim()) return;
    loading.value = true;
    errorMessage.value = null;
    order.value = null;

    try {
        const res = await axios.get(`/api/shop/track/${encodeURIComponent(orderInput.value.trim())}`);
        order.value = res.data.data;
    } catch (e) {
        errorMessage.value = e.response?.data?.message || 'Could not find order. Please double check the order number.';
    } finally {
        loading.value = false;
    }
}

function printOrderInvoice() {
    window.print();
}

onMounted(() => {
    if (orderInput.value.trim()) {
        lookupOrder();
    }
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

@media print {
    .track-order-page {
        padding: 0 !important;
    }
    .status-stepper {
        display: none !important;
    }
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }
    html[dir="rtl"] table th,
    html[dir="rtl"] table td {
        text-align: right !important;
    }
    html[dir="rtl"] table th.text-end,
    html[dir="rtl"] table td.text-end {
        text-align: left !important;
    }
    html[dir="rtl"] table th.text-center,
    html[dir="rtl"] table td.text-center {
        text-align: center !important;
    }
}

.status-stepper {
    position: relative;
    padding: 0 10px;
}

.stepper-line {
    position: absolute;
    top: 18px;
    left: 25px;
    height: 4px;
    background: #0d6efd;
    transition: width 0.4s ease;
    z-index: 1;
}

.status-stepper::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 25px;
    right: 25px;
    height: 4px;
    background: #e9ecef;
    z-index: 0;
}

.step-item {
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-circle {
    width: 38px;
    height: 38px;
    margin: 0 auto;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.step-circle.completed {
    background: #0d6efd;
    color: #fff;
}

.step-circle.active {
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
}
</style>

