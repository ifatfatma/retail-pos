<template>
    <div class="order-success-page py-5">
        <div class="container" style="max-width: 650px;">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center bg-white">
                <!-- Checkmark Icon -->
                <div class="success-icon-wrap mx-auto mb-3 bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                    <span class="fs-1">✓</span>
                </div>

                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill mb-2 fw-semibold">
                    {{ $t('storefront.order_confirmed') }}
                </span>

                <h2 class="fw-extrabold text-dark mb-2">{{ $t('storefront.thank_you_order') }}</h2>
                <p class="text-muted small mb-4">
                    {{ $t('storefront.thank_you_sub') }}
                </p>

                <!-- Order ID Callout -->
                <div class="bg-light rounded-3 p-3 mb-4 text-center border">
                    <div class="text-muted extra-small text-uppercase fw-bold">{{ $t('storefront.tracking_number_label') }}</div>
                    <div class="fs-4 fw-bold text-primary">{{ orderNumber }}</div>
                </div>

                <!-- Order Details Snapshot -->
                <div v-if="order" class="text-start bg-light-subtle rounded-3 p-3 mb-4 small border">
                    <div class="row g-2">
                        <div class="col-6 text-muted">{{ $t('orders.customer') }}:</div>
                        <div class="col-6 fw-bold text-dark text-end">{{ order.customer_name }}</div>

                        <div class="col-6 text-muted">{{ $t('orders.payment_method') }}:</div>
                        <div class="col-6 fw-bold text-dark text-end text-uppercase">{{ order.payment_method }}</div>

                        <div class="col-6 text-muted">{{ $t('checkout.delivery_address') }}:</div>
                        <div class="col-6 text-dark text-end">{{ order.shipping_address }}</div>

                        <div class="col-6 text-muted pt-2 border-top">{{ $t('common.grand_total') }}:</div>
                        <div class="col-6 fw-bold text-primary pt-2 border-top text-end fs-6">
                            ₹{{ formatMoney(order.total_amount) }}
                        </div>
                    </div>
                </div>

                <!-- Live Inventory Sync Badge -->
                <div class="alert alert-success d-flex align-items-center gap-2 small text-start mb-4" role="alert">
                    <span class="fs-4">⚡</span>
                    <div>
                        <strong>{{ $t('storefront.live_stock_updated') }}:</strong> {{ $t('storefront.live_stock_sub') }}
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center d-print-none">
                    <button
                        type="button"
                        class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-flex align-items-center justify-content-center gap-1"
                        @click="printOrder"
                    >
                        <span>🖨️</span>
                        <span>{{ $t('receipt.print_receipt') }}</span>
                    </button>
                    <router-link
                        :to="{ path: '/track-order', query: { order_number: orderNumber } }"
                        class="btn btn-primary rounded-pill px-4 fw-semibold"
                    >
                        📍 {{ $t('storefront.track_order_status') }}
                    </router-link>
                    <router-link to="/shop" class="btn btn-outline-secondary rounded-pill px-4">
                        🛍️ {{ $t('storefront.continue_shopping') }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const orderNumber = ref(route.params.orderNumber);
const order = ref(history.state?.order || null);

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

async function fetchOrderInfo() {
    if (order.value) return;
    try {
        const res = await axios.get(`/api/shop/track/${orderNumber.value}`);
        order.value = res.data.data;
    } catch (e) {
        console.error('Failed to fetch order summary:', e);
    }
}

function printOrder() {
    window.print();
}

onMounted(() => {
    fetchOrderInfo();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.success-icon-wrap {
    width: 72px;
    height: 72px;
}

@media print {
    .order-success-page {
        padding: 0 !important;
    }
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }
}
</style>

