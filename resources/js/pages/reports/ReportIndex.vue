<template>
    <div class="container-fluid py-4">

        <!-- Dedicated Print Header (Hidden on screen, visible ONLY when printing) -->
        <div class="d-none d-print-block mb-4 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">The Smart Stationery</h2>
                    <div class="text-secondary small">{{ $t('reports.official_report_sub') }}</div>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-primary mb-1">{{ currentTabHeading }}</h5>
                    <div class="small text-muted">
                        <span v-if="activeTab !== 'valuation'"><strong>{{ $t('reports.reporting_period') }}:</strong> {{ formatDate(fromDate) }} — {{ formatDate(toDate) }}</span>
                        <span v-else><strong>{{ $t('reports.inventory_as_of') }}:</strong> {{ formatCurrentDate() }}</span>
                    </div>
                    <div class="extra-small text-muted mt-1">{{ $t('reports.generated_on') }}: {{ formatPrintTimestamp() }}</div>
                </div>
            </div>
        </div>

        <!-- Page Header & Global Date Filter (Hidden on Print) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 d-print-none">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('reports.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('reports.subtitle') }}
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button
                    class="btn btn-outline-secondary"
                    @click="printReport"
                    :title="$t('common.print')"
                >
                    🖨️ {{ $t('common.print') }}
                </button>

                <button
                    class="btn btn-outline-secondary"
                    @click="refreshCurrentTab"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    {{ $t('common.refresh') }}
                </button>
            </div>
        </div>

        <!-- Date Range Filter Bar (Applies to Sales, Products, and P&L) (Hidden on Print) -->
        <div class="card border-0 shadow-sm mb-4 d-print-none" v-if="activeTab !== 'valuation'">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <!-- Quick Presets -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button
                            type="button"
                            class="btn"
                            :class="datePreset === 'today' ? 'btn-primary' : 'btn-outline-secondary'"
                            @click="applyPreset('today')"
                        >
                            {{ $t('reports.today') }}
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="datePreset === '7days' ? 'btn-primary' : 'btn-outline-secondary'"
                            @click="applyPreset('7days')"
                        >
                            {{ $t('reports.last_7_days') }}
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="datePreset === 'this_month' ? 'btn-primary' : 'btn-outline-secondary'"
                            @click="applyPreset('this_month')"
                        >
                            {{ $t('reports.this_month') }}
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="datePreset === 'last_month' ? 'btn-primary' : 'btn-outline-secondary'"
                            @click="applyPreset('last_month')"
                        >
                            {{ $t('reports.last_month') }}
                        </button>
                    </div>

                    <!-- Custom Date Range Picker -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ $t('common.from') }}:</span>
                        <input
                            v-model="fromDate"
                            type="date"
                            class="form-control form-control-sm"
                            style="width: 140px;"
                        />
                        <span class="small text-muted">{{ $t('common.to') }}:</span>
                        <input
                            v-model="toDate"
                            type="date"
                            class="form-control form-control-sm"
                            style="width: 140px;"
                        />
                        <button
                            class="btn btn-sm btn-dark px-3"
                            @click="applyCustomDateRange"
                            :disabled="loading"
                        >
                            {{ $t('common.filter') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Tabs Navigation (Hidden on Print) -->
        <ul class="nav nav-pills mb-4 gap-2 d-print-none">
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ 'active': activeTab === 'overview' }"
                    @click="switchTab('overview')"
                >
                    📊 {{ $t('reports.tab_overview') }}
                </button>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ 'active': activeTab === 'products' }"
                    @click="switchTab('products')"
                >
                    🏆 {{ $t('reports.tab_products') }}
                </button>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ 'active': activeTab === 'valuation' }"
                    @click="switchTab('valuation')"
                >
                    📦 {{ $t('reports.tab_valuation') }}
                </button>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ 'active': activeTab === 'pnl' }"
                    @click="switchTab('pnl')"
                >
                    📑 {{ $t('reports.tab_pnl') }}
                </button>
            </li>
        </ul>

        <!-- ============================================================ -->
        <!-- TAB 1: EXECUTIVE OVERVIEW & SALES                             -->
        <!-- ============================================================ -->
        <div v-show="activeTab === 'overview'" id="printableOverviewSection">
            <!-- 4 Top KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l1.25 5h8.22l1.25-5H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Total Sales Revenue</div>
                                <h4 class="fw-bold mb-0">₹{{ Number(dashboardData.kpis.total_sales).toLocaleString() }}</h4>
                                <div class="small text-muted mt-1">{{ dashboardData.kpis.orders_count }} total orders</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-success-subtle text-success rounded-3 p-3 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Gross Profit & Margin</div>
                                <h4 class="fw-bold text-success mb-0">₹{{ Number(dashboardData.kpis.gross_profit).toLocaleString() }}</h4>
                                <div class="small text-success fw-semibold mt-1">{{ dashboardData.kpis.profit_margin }}% Margin</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Stock Purchases</div>
                                <h4 class="fw-bold text-warning mb-0">₹{{ Number(dashboardData.kpis.total_purchases).toLocaleString() }}</h4>
                                <div class="small text-muted mt-1">{{ dashboardData.kpis.purchases_count }} purchase orders</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-info-subtle text-info rounded-3 p-3 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Average Order Value</div>
                                <h4 class="fw-bold mb-0">₹{{ Number(dashboardData.kpis.average_order_value).toLocaleString() }}</h4>
                                <div class="small text-muted mt-1">Per transaction</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Statistics Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-sm-4 col-md-2 border-end">
                            <small class="text-muted d-block">Cost of Sold Items</small>
                            <span class="fw-bold fs-6">₹{{ Number(dashboardData.kpis.total_cogs).toFixed(2) }}</span>
                        </div>
                        <div class="col-sm-4 col-md-2 border-end">
                            <small class="text-muted d-block">Tax Collected</small>
                            <span class="fw-bold fs-6">₹{{ Number(dashboardData.kpis.total_tax).toFixed(2) }}</span>
                        </div>
                        <div class="col-sm-4 col-md-2 border-end">
                            <small class="text-muted d-block">Discounts Given</small>
                            <span class="fw-bold fs-6 text-danger">-₹{{ Number(dashboardData.kpis.total_discount).toFixed(2) }}</span>
                        </div>
                        <div class="col-sm-4 col-md-2 border-end">
                            <small class="text-muted d-block">Shipping Fees</small>
                            <span class="fw-bold fs-6">+₹{{ Number(dashboardData.kpis.total_shipping).toFixed(2) }}</span>
                        </div>
                        <div class="col-sm-4 col-md-2 border-end">
                            <small class="text-muted d-block">Customer Dues</small>
                            <span class="fw-bold fs-6" :class="Number(dashboardData.kpis.customer_receivables) > 0 ? 'text-danger' : 'text-success'">
                                ₹{{ Number(dashboardData.kpis.customer_receivables).toFixed(2) }}
                            </span>
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <small class="text-muted d-block">Supplier Payables</small>
                            <span class="fw-bold fs-6" :class="Number(dashboardData.kpis.supplier_payables) > 0 ? 'text-danger' : 'text-success'">
                                ₹{{ Number(dashboardData.kpis.supplier_payables).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Channels & Payment Methods Breakdown -->
            <div class="row g-4 mb-4">
                <!-- Sales by Channel -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            Sales by Sales Channel
                        </div>
                        <div class="card-body">
                            <div v-if="dashboardData.channels.length === 0" class="text-muted text-center py-4">
                                No sales recorded for selected period.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Channel</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="ch in dashboardData.channels" :key="ch.channel">
                                            <td>
                                                <span class="badge" :class="channelBadge(ch.channel)">
                                                    {{ formatChannel(ch.channel) }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ ch.count }}</td>
                                            <td class="text-end fw-bold">₹{{ Number(ch.total).toFixed(2) }}</td>
                                            <td class="text-end text-muted small">
                                                {{ dashboardData.kpis.total_sales > 0 ? ((ch.total / dashboardData.kpis.total_sales) * 100).toFixed(1) : 0 }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales by Payment Method -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            Sales by Payment Method
                        </div>
                        <div class="card-body">
                            <div v-if="dashboardData.payment_methods.length === 0" class="text-muted text-center py-4">
                                No payments recorded for selected period.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Method</th>
                                            <th class="text-center">Transactions</th>
                                            <th class="text-end">Collected</th>
                                            <th class="text-end">Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="pm in dashboardData.payment_methods" :key="pm.method">
                                            <td class="fw-semibold text-uppercase">{{ pm.method }}</td>
                                            <td class="text-center">{{ pm.count }}</td>
                                            <td class="text-end fw-bold text-success">₹{{ Number(pm.total).toFixed(2) }}</td>
                                            <td class="text-end text-muted small">
                                                {{ dashboardData.kpis.total_sales > 0 ? ((pm.total / dashboardData.kpis.total_sales) * 100).toFixed(1) : 0 }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Sales Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span>Daily Sales Velocity</span>
                    <small class="text-muted">Orders & Revenue timeline</small>
                </div>
                <div class="card-body p-0">
                    <div v-if="dashboardData.daily_trends.length === 0" class="text-center py-4 text-muted">
                        No transactions recorded in this period.
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Orders Count</th>
                                    <th class="text-end">Total Tax</th>
                                    <th class="text-end">Sales Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in dashboardData.daily_trends" :key="d.date">
                                    <td class="fw-semibold">{{ formatDate(d.date) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ d.orders }}</span>
                                    </td>
                                    <td class="text-end text-muted">₹{{ Number(d.tax).toFixed(2) }}</td>
                                    <td class="text-end fw-bold text-primary">₹{{ Number(d.sales).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 2: PRODUCT PERFORMANCE & BEST SELLERS                    -->
        <!-- ============================================================ -->
        <div v-show="activeTab === 'products'" id="printableProductsSection">
            <!-- Product Performance Summary Bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3">
                        <small class="text-muted">Total Units Sold</small>
                        <h4 class="fw-bold mb-0">{{ productsSummary.total_items_sold }} units</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3">
                        <small class="text-muted">Total Sales Revenue</small>
                        <h4 class="fw-bold text-primary mb-0">₹{{ Number(productsSummary.total_revenue).toFixed(2) }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3">
                        <small class="text-muted">Total Gross Profit</small>
                        <h4 class="fw-bold text-success mb-0">₹{{ Number(productsSummary.total_profit).toFixed(2) }}</h4>
                    </div>
                </div>
            </div>

            <!-- Product Performance Table -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span>Product Margin & Velocity Ranking</span>
                    <button class="btn btn-sm btn-outline-secondary d-print-none" @click="downloadProductsCsv">
                        📥 Download CSV
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="productsList.length === 0" class="text-center py-5 text-muted">
                        No product sales recorded in the selected date range.
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>SKU</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-end">Avg Price</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">COGS</th>
                                    <th class="text-end">Gross Profit</th>
                                    <th class="text-center">Margin %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(prod, idx) in productsList" :key="prod.variant_id">
                                    <td>{{ idx + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ prod.name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ prod.category }}</span></td>
                                    <td class="small text-muted fw-mono">{{ prod.sku }}</td>
                                    <td class="text-center fw-bold">
                                        {{ prod.quantity_sold }} <small class="text-muted fw-normal">{{ prod.unit }}</small>
                                    </td>
                                    <td class="text-end">₹{{ Number(prod.avg_selling_price).toFixed(2) }}</td>
                                    <td class="text-end fw-bold text-primary">₹{{ Number(prod.total_revenue).toFixed(2) }}</td>
                                    <td class="text-end text-muted">₹{{ Number(prod.total_cogs).toFixed(2) }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ Number(prod.gross_profit).toFixed(2) }}</td>
                                    <td class="text-center">
                                        <span class="badge" :class="marginBadge(prod.margin_percent)">
                                            {{ prod.margin_percent }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 3: INVENTORY VALUATION & HEALTH                          -->
        <!-- ============================================================ -->
        <div v-show="activeTab === 'valuation'" id="printableValuationSection">
            <!-- Valuation Metrics -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-medium">Inventory Value at Cost</div>
                            <h3 class="fw-bold text-dark mb-0">₹{{ Number(valuationData.valuation.cost_value).toLocaleString() }}</h3>
                            <div class="small text-muted mt-1">{{ valuationData.valuation.total_stock_units }} total on-hand units</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-medium">Inventory Value at Retail</div>
                            <h3 class="fw-bold text-primary mb-0">₹{{ Number(valuationData.valuation.retail_value).toLocaleString() }}</h3>
                            <div class="small text-muted mt-1">Projected sales realization</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-medium">Projected Gross Profit</div>
                            <h3 class="fw-bold text-success mb-0">₹{{ Number(valuationData.valuation.potential_profit).toLocaleString() }}</h3>
                            <div class="small text-success fw-semibold mt-1">{{ valuationData.valuation.projected_margin }}% Projected Margin</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Valuation Table -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    Category Inventory Valuation Breakdown
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Products Count</th>
                                    <th class="text-center">Total Stock Units</th>
                                    <th class="text-end">Cost Value</th>
                                    <th class="text-end">Retail Value</th>
                                    <th class="text-end">Potential Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cat in valuationData.categories_breakdown" :key="cat.category">
                                    <td class="fw-bold text-dark">{{ cat.category }}</td>
                                    <td class="text-center">{{ cat.products_count }}</td>
                                    <td class="text-center fw-semibold">{{ cat.stock_units }}</td>
                                    <td class="text-end">₹{{ Number(cat.cost_value).toFixed(2) }}</td>
                                    <td class="text-end fw-bold text-primary">₹{{ Number(cat.retail_value).toFixed(2) }}</td>
                                    <td class="text-end fw-bold text-success">
                                        ₹{{ (Number(cat.retail_value) - Number(cat.cost_value)).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stock Alerts Lists -->
            <div class="row g-4 mb-4">
                <!-- Low Stock Alert List -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3 text-warning">
                            ⚠️ Low Stock Reorder List ({{ valuationData.low_stock_items.length }})
                        </div>
                        <div class="card-body p-0">
                            <div v-if="valuationData.low_stock_items.length === 0" class="text-muted text-center py-4">
                                No products currently below reorder threshold.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Alert Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in valuationData.low_stock_items" :key="item.id">
                                            <td class="fw-semibold">{{ item.name }}</td>
                                            <td><small class="text-muted">{{ item.category }}</small></td>
                                            <td class="text-center fw-bold text-warning">{{ item.stock }} {{ item.unit }}</td>
                                            <td class="text-center text-muted">{{ item.threshold }} {{ item.unit }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Alert List -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3 text-danger">
                            ❌ Out of Stock List ({{ valuationData.out_of_stock_items.length }})
                        </div>
                        <div class="card-body p-0">
                            <div v-if="valuationData.out_of_stock_items.length === 0" class="text-muted text-center py-4">
                                No products completely out of stock.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th class="text-center">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in valuationData.out_of_stock_items" :key="item.id">
                                            <td class="fw-semibold">{{ item.name }}</td>
                                            <td><small class="text-muted">{{ item.category }}</small></td>
                                            <td class="text-center fw-bold text-danger">0 {{ item.unit }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 4: PROFIT & LOSS (P&L) STATEMENT                         -->
        <!-- ============================================================ -->
        <div v-show="activeTab === 'pnl'" id="printablePnlSection">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom text-center">
                            <h4 class="fw-bold mb-1">PROFIT & LOSS STATEMENT</h4>
                            <div class="text-muted small">
                                Statement for period: <strong>{{ pnlData.date_range.from }}</strong> to <strong>{{ pnlData.date_range.to }}</strong>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Revenue / Income -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary text-uppercase border-bottom pb-2">1. Operating Income / Revenue</h6>
                                <div class="d-flex justify-content-between py-1">
                                    <span>Gross Sales Revenue (POS + Online)</span>
                                    <span>₹{{ Number(pnlData.income.gross_sales).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1" v-if="Number(pnlData.income.shipping_revenue) > 0">
                                    <span>Delivery & Shipping Fees Collected</span>
                                    <span>+ ₹{{ Number(pnlData.income.shipping_revenue).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1" v-if="Number(pnlData.income.tax_collected) > 0">
                                    <span>Taxes Collected</span>
                                    <span>+ ₹{{ Number(pnlData.income.tax_collected).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 text-danger" v-if="Number(pnlData.income.discounts) > 0">
                                    <span>Less: Discounts Allowed</span>
                                    <span>- ₹{{ Number(pnlData.income.discounts).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-top fw-bold fs-6">
                                    <span>Net Sales Revenue</span>
                                    <span class="text-primary">₹{{ Number(pnlData.income.net_sales).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Cost of Goods Sold -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-danger text-uppercase border-bottom pb-2">2. Cost of Goods Sold (COGS)</h6>
                                <div class="d-flex justify-content-between py-1">
                                    <span>Cost of Items Sold (Purchase Cost Basis)</span>
                                    <span>₹{{ Number(pnlData.cogs.cost_of_goods_sold).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-top fw-bold fs-6">
                                    <span>Total Cost of Sales</span>
                                    <span class="text-danger">₹{{ Number(pnlData.cogs.cost_of_goods_sold).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Gross Profit Summary Box -->
                            <div class="card bg-success-subtle border-success-subtle p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold fs-5 text-success">GROSS PROFIT</span>
                                    <span class="fw-bold fs-4 text-success">₹{{ Number(pnlData.profit.gross_profit).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-success">
                                    <span>Gross Margin Percentage</span>
                                    <span class="fw-bold">{{ pnlData.profit.gross_margin_percent }}%</span>
                                </div>
                            </div>

                            <!-- Procurement Activity in Period -->
                            <div>
                                <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2">3. Procurement & Stock Additions</h6>
                                <div class="d-flex justify-content-between py-1">
                                    <span>Total Inventory Purchases Billed</span>
                                    <span class="fw-semibold">₹{{ Number(pnlData.procurement.total_purchases).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white text-center py-3 d-print-none">
                            <button class="btn btn-outline-secondary btn-sm" @click="printReport">
                                🖨️ Print Statement
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../services/api';

const activeTab = ref('overview');
const loading = ref(false);

const currentTabHeading = computed(() => {
    switch (activeTab.value) {
        case 'overview':
            return 'Executive Overview & Sales Report';
        case 'products':
            return 'Product Performance & Margins Report';
        case 'valuation':
            return 'Inventory Valuation & Health Report';
        case 'pnl':
            return 'Profit & Loss (P&L) Statement';
        default:
            return 'Business Analytics Report';
    }
});

function formatCurrentDate() {
    return new Date().toLocaleDateString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatPrintTimestamp() {
    return new Date().toLocaleString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Date filters
const datePreset = ref('this_month');
const fromDate = ref('');
const toDate = ref('');

// Tab 1: Dashboard Data
const dashboardData = reactive({
    kpis: {
        total_sales: 0,
        orders_count: 0,
        total_cogs: 0,
        gross_profit: 0,
        profit_margin: 0,
        total_purchases: 0,
        purchases_count: 0,
        average_order_value: 0,
        total_tax: 0,
        total_discount: 0,
        total_shipping: 0,
        customer_receivables: 0,
        supplier_payables: 0,
    },
    channels: [],
    payment_methods: [],
    daily_trends: [],
});

// Tab 2: Products Performance Data
const productsList = ref([]);
const productsSummary = reactive({
    total_items_sold: 0,
    total_revenue: 0,
    total_profit: 0,
});

// Tab 3: Inventory Valuation Data
const valuationData = reactive({
    valuation: {
        total_products: 0,
        total_stock_units: 0,
        cost_value: 0,
        retail_value: 0,
        potential_profit: 0,
        projected_margin: 0,
        low_stock_count: 0,
        out_of_stock_count: 0,
    },
    categories_breakdown: [],
    low_stock_items: [],
    out_of_stock_items: [],
});

// Tab 4: Profit & Loss Data
const pnlData = reactive({
    date_range: { from: '', to: '' },
    income: {
        gross_sales: 0,
        discounts: 0,
        shipping_revenue: 0,
        tax_collected: 0,
        net_sales: 0,
    },
    cogs: {
        cost_of_goods_sold: 0,
    },
    profit: {
        gross_profit: 0,
        gross_margin_percent: 0,
    },
    procurement: {
        total_purchases: 0,
    }
});

// -------------------------------------------------------------------------
// Date Presets
// -------------------------------------------------------------------------
function initDates() {
    applyPreset('this_month');
}

function applyPreset(preset) {
    datePreset.value = preset;
    const now = new Date();

    if (preset === 'today') {
        const d = now.toISOString().substring(0, 10);
        fromDate.value = d;
        toDate.value = d;
    } else if (preset === '7days') {
        const past = new Date();
        past.setDate(now.getDate() - 7);
        fromDate.value = past.toISOString().substring(0, 10);
        toDate.value = now.toISOString().substring(0, 10);
    } else if (preset === 'this_month') {
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        fromDate.value = start.toISOString().substring(0, 10);
        toDate.value = now.toISOString().substring(0, 10);
    } else if (preset === 'last_month') {
        const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const end = new Date(now.getFullYear(), now.getMonth(), 0);
        fromDate.value = start.toISOString().substring(0, 10);
        toDate.value = end.toISOString().substring(0, 10);
    }

    fetchActiveData();
}

function applyCustomDateRange() {
    datePreset.value = 'custom';
    fetchActiveData();
}

// -------------------------------------------------------------------------
// Data Fetching
// -------------------------------------------------------------------------
async function fetchOverview() {
    loading.value = true;
    try {
        const res = await api.get('/reports/dashboard', {
            params: { from_date: fromDate.value, to_date: toDate.value }
        });
        Object.assign(dashboardData.kpis, res.data.kpis);
        dashboardData.channels = res.data.channels || [];
        dashboardData.payment_methods = res.data.payment_methods || [];
        dashboardData.daily_trends = res.data.daily_trends || [];
    } catch (err) {
        console.error('Failed to load overview report:', err);
    } finally {
        loading.value = false;
    }
}

async function fetchProducts() {
    loading.value = true;
    try {
        const res = await api.get('/reports/products', {
            params: { from_date: fromDate.value, to_date: toDate.value }
        });
        productsList.value = res.data.data || [];
        Object.assign(productsSummary, res.data.summary);
    } catch (err) {
        console.error('Failed to load product performance report:', err);
    } finally {
        loading.value = false;
    }
}

async function fetchValuation() {
    loading.value = true;
    try {
        const res = await api.get('/reports/inventory-valuation');
        Object.assign(valuationData.valuation, res.data.valuation);
        valuationData.categories_breakdown = res.data.categories_breakdown || [];
        valuationData.low_stock_items = res.data.low_stock_items || [];
        valuationData.out_of_stock_items = res.data.out_of_stock_items || [];
    } catch (err) {
        console.error('Failed to load valuation report:', err);
    } finally {
        loading.value = false;
    }
}

async function fetchPnL() {
    loading.value = true;
    try {
        const res = await api.get('/reports/profit-loss', {
            params: { from_date: fromDate.value, to_date: toDate.value }
        });
        Object.assign(pnlData, res.data);
    } catch (err) {
        console.error('Failed to load P&L report:', err);
    } finally {
        loading.value = false;
    }
}

function fetchActiveData() {
    if (activeTab.value === 'overview') fetchOverview();
    else if (activeTab.value === 'products') fetchProducts();
    else if (activeTab.value === 'valuation') fetchValuation();
    else if (activeTab.value === 'pnl') fetchPnL();
}

function switchTab(tab) {
    activeTab.value = tab;
    fetchActiveData();
}

function refreshCurrentTab() {
    fetchActiveData();
}

// -------------------------------------------------------------------------
// CSV Export
// -------------------------------------------------------------------------
function downloadProductsCsv() {
    if (productsList.value.length === 0) {
        alert('No product data to export.');
        return;
    }

    const headers = ['#', 'Product Name', 'Category', 'SKU', 'Units Sold', 'Avg Price', 'Revenue', 'COGS', 'Gross Profit', 'Margin %'];
    const rows = productsList.value.map((p, idx) => [
        idx + 1,
        `"${p.name.replace(/"/g, '""')}"`,
        `"${p.category}"`,
        `"${p.sku}"`,
        p.quantity_sold,
        p.avg_selling_price,
        p.total_revenue,
        p.total_cogs,
        p.gross_profit,
        `${p.margin_percent}%`
    ]);

    const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `product_performance_${fromDate.value}_to_${toDate.value}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printReport() {
    const originalTitle = document.title;
    const periodStr = activeTab.value !== 'valuation' ? ` (${fromDate.value} to ${toDate.value})` : '';
    document.title = `The Smart Stationery - ${currentTabHeading.value}${periodStr}`;
    window.print();
    setTimeout(() => {
        document.title = originalTitle;
    }, 1000);
}

// -------------------------------------------------------------------------
// Helpers & Badges
// -------------------------------------------------------------------------
function formatDate(str) {
    if (!str) return '-';
    return new Date(str).toLocaleDateString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatChannel(ch) {
    const map = {
        web: 'Web Store',
        whatsapp: 'WhatsApp',
        phone: 'Phone Order',
        pos: 'POS Counter',
        app: 'Mobile App'
    };
    return map[ch] || ch.toUpperCase();
}

function channelBadge(ch) {
    if (ch === 'whatsapp') return 'bg-success text-white';
    if (ch === 'web') return 'bg-primary text-white';
    if (ch === 'pos') return 'bg-dark text-white';
    return 'bg-info text-dark';
}

function marginBadge(margin) {
    if (margin >= 25) return 'bg-success';
    if (margin >= 10) return 'bg-primary';
    return 'bg-warning text-dark';
}

onMounted(() => {
    initDates();
});
</script>

<style scoped>
.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

@media print {
    /* Hide all screen control elements */
    .d-print-none,
    button,
    .btn,
    .btn-group,
    input,
    select,
    .nav,
    .nav-pills {
        display: none !important;
    }

    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Print cards with neat borders and no shadows */
    .card {
        border: 1px solid #d0d7de !important;
        box-shadow: none !important;
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1.25rem !important;
        background: #ffffff !important;
    }

    .card-header {
        background-color: #f6f8fa !important;
        border-bottom: 1px solid #d0d7de !important;
        color: #000000 !important;
        font-weight: bold;
    }

    /* Professional table layout */
    .table-responsive {
        overflow: visible !important;
    }

    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table th,
    .table td {
        padding: 6px 8px !important;
        border-bottom: 1px solid #dee2e6 !important;
        color: #000000 !important;
        font-size: 11px !important;
    }

    .table thead th {
        background-color: #f1f3f5 !important;
        border-bottom: 2px solid #adb5bd !important;
        color: #000000 !important;
    }

    tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .stat-icon {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        color: #333333 !important;
    }

    /* Color adjustments for print contrast */
    .text-muted {
        color: #495057 !important;
    }

    html[dir="rtl"] .table th,
    html[dir="rtl"] .table td {
        text-align: right !important;
    }

    html[dir="rtl"] .table th.text-end,
    html[dir="rtl"] .table td.text-end {
        text-align: left !important;
    }

    html[dir="rtl"] .table th.text-center,
    html[dir="rtl"] .table td.text-center {
        text-align: center !important;
    }
}
</style>

