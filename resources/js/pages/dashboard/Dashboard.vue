<template>
    <div class="container-fluid py-4">

        <!-- Welcome Banner -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('header.portal_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('dashboard.subtitle') }}
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="d-flex gap-2">
                <router-link to="/admin/pos" class="btn btn-primary fw-semibold">
                    🛒 {{ $t('nav.pos_counter') }}
                </router-link>
                <router-link to="/admin/orders" class="btn btn-outline-primary">
                    📦 {{ $t('nav.online_orders') }}
                </router-link>
                <router-link to="/admin/reports" class="btn btn-outline-secondary">
                    📊 {{ $t('dashboard.view_reports') }}
                </router-link>
            </div>
        </div>

        <!-- Real-time Dashboard KPI Cards -->
        <div class="row g-3 mb-4">
            <!-- Today's Sales -->
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l1.25 5h8.22l1.25-5H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('dashboard.today_sales') }}</div>
                            <h4 class="fw-bold text-primary mb-0">₹{{ Number(stats.today_sales).toLocaleString() }}</h4>
                            <div class="small text-muted mt-1">{{ stats.today_orders }} {{ $t('dashboard.today_orders') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gross Profit -->
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success-subtle text-success rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('dashboard.gross_profit') }}</div>
                            <h4 class="fw-bold text-success mb-0">₹{{ Number(stats.month_profit).toLocaleString() }}</h4>
                            <div class="small text-success fw-semibold mt-1">{{ stats.month_margin }}% {{ $t('dashboard.profit_margin') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Products -->
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info-subtle text-info rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('dashboard.total_products') }}</div>
                            <h4 class="fw-bold mb-0">{{ stats.total_products }}</h4>
                            <div class="small text-muted mt-1">{{ stats.stock_units }} {{ $t('inventory.total_stock_units') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Health -->
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('inventory.valuation_cost') }}</div>
                            <h4 class="fw-bold text-dark mb-0">₹{{ Number(stats.cost_value).toLocaleString() }}</h4>
                            <div class="small text-muted mt-1">{{ $t('inventory.title') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Navigation Modules Grid -->
        <h5 class="fw-bold mb-3 text-secondary">{{ $t('dashboard.quick_actions') }}</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <router-link to="/admin/pos" class="text-decoration-none">
                    <div class="card dashboard-card h-100 hover-card p-3 border-start border-primary border-4">
                        <h6 class="fw-bold text-primary mb-1">🛒 {{ $t('nav.pos_counter') }}</h6>
                        <p class="text-muted small mb-0">{{ $t('pos.cart_empty_sub') }}</p>
                    </div>
                </router-link>
            </div>

            <div class="col-md-4">
                <router-link to="/admin/orders" class="text-decoration-none">
                    <div class="card dashboard-card h-100 hover-card p-3 border-start border-info border-4">
                        <h6 class="fw-bold text-info mb-1">📦 {{ $t('nav.online_orders') }}</h6>
                        <p class="text-muted small mb-0">{{ $t('orders.subtitle') }}</p>
                    </div>
                </router-link>
            </div>

            <div class="col-md-4">
                <router-link to="/admin/inventory" class="text-decoration-none">
                    <div class="card dashboard-card h-100 hover-card p-3 border-start border-warning border-4">
                        <h6 class="fw-bold text-warning mb-1">⚖️ {{ $t('nav.stock_management') }}</h6>
                        <p class="text-muted small mb-0">{{ $t('inventory.subtitle') }}</p>
                    </div>
                </router-link>
            </div>

            <div class="col-md-4">
                <router-link to="/admin/purchases" class="text-decoration-none">
                    <div class="card dashboard-card h-100 hover-card p-3 border-start border-secondary border-4">
                        <h6 class="fw-bold text-secondary mb-1">📥 {{ $t('nav.purchases') }}</h6>
                        <p class="text-muted small mb-0">{{ $t('purchases.subtitle') }}</p>
                    </div>
                </router-link>
            </div>

            <div class="col-md-4">
                <router-link to="/admin/products" class="text-decoration-none">
                    <div class="card dashboard-card h-100 hover-card p-3 border-start border-dark border-4">
                        <h6 class="fw-bold text-dark mb-1">🏷️ {{ $t('nav.products') }}</h6>
                        <p class="text-muted small mb-0">{{ $t('products.subtitle') }}</p>
                    </div>
                </router-link>
            </div>

            <div class="col-md-4">
                <router-link to="/admin/reports" class="text-decoration-none">
                    <div class="card dashboard-card h-100 hover-card p-3 border-start border-success border-4">
                        <h6 class="fw-bold text-success mb-1">📊 {{ $t('nav.reports') }}</h6>
                        <p class="text-muted small mb-0">{{ $t('reports.subtitle') }}</p>
                    </div>
                </router-link>
            </div>
        </div>

    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import api from '../../services/api';

const stats = reactive({
    today_sales: 0,
    today_orders: 0,
    month_profit: 0,
    month_margin: 0,
    total_products: 0,
    stock_units: 0,
    cost_value: 0,
});

async function loadDashboardMetrics() {
    try {
        const todayStr = new Date().toISOString().substring(0, 10);
        const [todayRes, monthRes, valRes] = await Promise.all([
            api.get('/reports/dashboard', { params: { from_date: todayStr, to_date: todayStr } }),
            api.get('/reports/dashboard'),
            api.get('/reports/inventory-valuation')
        ]);

        stats.today_sales = todayRes.data?.kpis?.total_sales || 0;
        stats.today_orders = todayRes.data?.kpis?.orders_count || 0;

        stats.month_profit = monthRes.data?.kpis?.gross_profit || 0;
        stats.month_margin = monthRes.data?.kpis?.profit_margin || 0;

        stats.total_products = valRes.data?.valuation?.total_products || 0;
        stats.stock_units = valRes.data?.valuation?.total_stock_units || 0;
        stats.cost_value = valRes.data?.valuation?.cost_value || 0;
    } catch (err) {
        console.error('Failed to load dashboard metrics:', err);
    }
}

onMounted(() => {
    loadDashboardMetrics();
});
</script>

<style scoped>
.dashboard-card {
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border-radius: 10px;
    background: #fff;
}

.hover-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.12) !important;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
