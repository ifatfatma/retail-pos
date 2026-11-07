<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('inventory.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('inventory.subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary"
                    @click="refreshData"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    {{ $t('common.refresh') }}
                </button>

                <button
                    class="btn btn-primary"
                    @click="openQuickAdjustModal"
                >
                    + {{ $t('inventory.adjust_stock') }}
                </button>
            </div>
        </div>

        <!-- Summary Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('inventory.total_stock_units') }}</div>
                            <h4 class="fw-bold mb-0">{{ summary.total_items }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.qty') }}: {{ summary.total_quantity }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100 stat-card cursor-pointer"
                    :class="{ 'border-2 border-success': stockStatusFilter === 'in_stock' }"
                    @click="setStockFilter('in_stock')"
                >
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success-subtle text-success rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('common.in_stock') }}</div>
                            <h4 class="fw-bold text-success mb-0">{{ summary.in_stock }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.active') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100 stat-card cursor-pointer"
                    :class="{ 'border-2 border-warning': stockStatusFilter === 'low_stock' }"
                    @click="setStockFilter('low_stock')"
                >
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('common.low_stock') }}</div>
                            <h4 class="fw-bold text-warning mb-0">{{ summary.low_stock }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.warning') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100 stat-card cursor-pointer"
                    :class="{ 'border-2 border-danger': stockStatusFilter === 'out_of_stock' }"
                    @click="setStockFilter('out_of_stock')"
                >
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-danger-subtle text-danger rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('common.out_of_stock') }}</div>
                            <h4 class="fw-bold text-danger mb-0">{{ summary.out_of_stock }}</h4>
                            <div class="small text-muted mt-1">0 {{ $t('products.current_stock') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-3 gap-2">
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ 'active': activeTab === 'inventory' }"
                    @click="activeTab = 'inventory'"
                >
                    {{ $t('inventory.title') }}
                </button>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ 'active': activeTab === 'transactions' }"
                    @click="switchTab('transactions')"
                >
                    {{ $t('inventory.history') }}
                </button>
            </li>
        </ul>

        <!-- TAB 1: INVENTORY TABLE -->
        <div v-show="activeTab === 'inventory'">
            <!-- Search & Filters -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                :placeholder="$t('products.search_placeholder')"
                                @input="debouncedLoad"
                            />
                        </div>

                        <div class="col-md-3">
                            <select
                                v-model="categoryFilter"
                                class="form-select"
                                @change="loadInventory(1)"
                            >
                                <option value="">{{ $t('products.all_categories') }}</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select
                                v-model="stockStatusFilter"
                                class="form-select"
                                @change="loadInventory(1)"
                            >
                                <option value="all">-- {{ $t('common.all') }} --</option>
                                <option value="in_stock">{{ $t('common.in_stock') }}</option>
                                <option value="low_stock">{{ $t('common.low_stock') }}</option>
                                <option value="out_of_stock">{{ $t('common.out_of_stock') }}</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <button
                                class="btn btn-light w-100"
                                @click="resetFilters"
                                :title="$t('common.clear')"
                            >
                                {{ $t('common.clear') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Items Table -->
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>{{ $t('products.product_name') }}</th>
                                <th>{{ $t('products.category') }}</th>
                                <th>{{ $t('products.sku') }} / {{ $t('common.barcode') }}</th>
                                <th class="text-end">{{ $t('products.current_stock') }}</th>
                                <th class="text-end">{{ $t('products.low_stock_threshold') }}</th>
                                <th class="text-center">{{ $t('common.status') }}</th>
                                <th class="text-center" width="220">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-if="loading">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    {{ $t('common.loading') }}
                                </td>
                            </tr>

                            <tr v-else-if="items.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    {{ $t('common.no_data') }}
                                </td>
                            </tr>

                            <tr v-for="(item, index) in items" :key="item.id">
                                <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>

                                <td>
                                    <div class="fw-semibold text-dark">{{ item.name }}</div>
                                    <small class="text-muted" v-if="item.brand && item.brand !== '-'">
                                        {{ $t('products.brand') }}: {{ item.brand }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        {{ item.category }}
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-mono text-dark small">
                                        {{ item.default_variant?.sku || '-' }}
                                    </div>
                                    <small class="text-muted" v-if="item.default_variant?.barcode">
                                        {{ item.default_variant.barcode }}
                                    </small>
                                </td>

                                <td class="text-end">
                                    <span class="fs-6 fw-bold" :class="stockNumberClass(item)">
                                        {{ formatQty(item.total_stock, item.unit?.allow_decimal) }}
                                    </span>
                                    <small class="text-muted ms-1">
                                        {{ item.unit?.short_name || '' }}
                                    </small>
                                </td>

                                <td class="text-end text-muted">
                                    {{ formatQty(item.low_stock_threshold, item.unit?.allow_decimal) }}
                                    <small class="text-muted">{{ item.unit?.short_name || '' }}</small>
                                </td>

                                <td class="text-center">
                                    <span
                                        class="badge"
                                        :class="statusBadgeClass(item.stock_status)"
                                    >
                                        {{ statusLabel(item.stock_status) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            class="btn btn-outline-primary"
                                            @click="openAdjustModal(item)"
                                        >
                                            {{ $t('inventory.adjust_stock') }}
                                        </button>

                                        <button
                                            class="btn btn-outline-secondary"
                                            @click="openHistoryModal(item)"
                                        >
                                            {{ $t('inventory.history') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">
                        {{ $t('common.showing') }} {{ items.length }} {{ $t('common.of') }} {{ totalItems }}
                    </span>

                    <div v-if="lastPage > 1">
                        <button
                            class="btn btn-sm btn-outline-secondary me-1"
                            :disabled="currentPage <= 1 || loading"
                            @click="loadInventory(currentPage - 1)"
                        >
                            &laquo; {{ $t('common.previous') }}
                        </button>

                        <span class="px-2 small text-muted">
                            {{ $t('common.page') }} {{ currentPage }} {{ $t('common.of') }} {{ lastPage }}
                        </span>

                        <button
                            class="btn btn-sm btn-outline-secondary ms-1"
                            :disabled="currentPage >= lastPage || loading"
                            @click="loadInventory(currentPage + 1)"
                        >
                            {{ $t('common.next') }} &raquo;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: AUDIT TRAIL / TRANSACTIONS TABLE -->
        <div v-show="activeTab === 'transactions'">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <input
                                v-model="txSearch"
                                type="text"
                                class="form-control"
                                :placeholder="$t('products.search_placeholder')"
                                @input="debouncedLoadTx"
                            />
                        </div>

                        <div class="col-md-4">
                            <select
                                v-model="txTypeFilter"
                                class="form-select"
                                @change="loadTransactions(1)"
                            >
                                <option value="">-- {{ $t('common.all') }} --</option>
                                <option value="in">{{ $t('inventory.add_stock') }}</option>
                                <option value="out">{{ $t('inventory.subtract_stock') }}</option>
                                <option value="adjustment">{{ $t('inventory.audit_correction') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button
                                class="btn btn-light w-100"
                                @click="resetTxFilters"
                            >
                                {{ $t('common.reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $t('common.date') }} & {{ $t('common.time') }}</th>
                                <th>{{ $t('products.product_name') }}</th>
                                <th>{{ $t('products.sku') }}</th>
                                <th>{{ $t('inventory.adjustment_type') }}</th>
                                <th class="text-end">{{ $t('inventory.quantity_adjusted') }}</th>
                                <th class="text-end">{{ $t('inventory.stock_after') }}</th>
                                <th>{{ $t('inventory.adjustment_reason') }}</th>
                                <th>{{ $t('common.notes') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-if="txLoading">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div>
                                    {{ $t('common.loading') }}
                                </td>
                            </tr>

                            <tr v-else-if="transactions.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    {{ $t('common.no_data') }}
                                </td>
                            </tr>

                            <tr v-for="tx in transactions" :key="tx.id">
                                <td class="small text-muted">
                                    {{ formatDate(tx.created_at) }}
                                </td>

                                <td class="fw-semibold">
                                    {{ tx.product?.name || '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ tx.variant?.sku || '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge" :class="txBadgeClass(tx.type)">
                                        {{ txTypeLabel(tx.type) }}
                                    </span>
                                </td>

                                <td class="text-end fw-bold" :class="txQtyClass(tx.type)">
                                    {{ txQtyPrefix(tx.type) }}{{ tx.quantity }}
                                    <small class="text-muted fw-normal">
                                        {{ tx.product?.unit?.short_name || '' }}
                                    </small>
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ tx.balance_after }}
                                    <small class="text-muted fw-normal">
                                        {{ tx.product?.unit?.short_name || '' }}
                                    </small>
                                </td>

                                <td>
                                    <span class="fw-medium">{{ tx.reason }}</span>
                                </td>

                                <td class="small text-muted">
                                    {{ tx.notes || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for transactions -->
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">
                        {{ $t('common.showing') }} {{ transactions.length }} {{ $t('common.of') }} {{ txTotal }}
                    </span>

                    <div v-if="txLastPage > 1">
                        <button
                            class="btn btn-sm btn-outline-secondary me-1"
                            :disabled="txCurrentPage <= 1 || txLoading"
                            @click="loadTransactions(txCurrentPage - 1)"
                        >
                            &laquo; {{ $t('common.previous') }}
                        </button>

                        <span class="px-2 small text-muted">
                            {{ $t('common.page') }} {{ txCurrentPage }} {{ $t('common.of') }} {{ txLastPage }}
                        </span>

                        <button
                            class="btn btn-sm btn-outline-secondary ms-1"
                            :disabled="txCurrentPage >= txLastPage || txLoading"
                            @click="loadTransactions(txCurrentPage + 1)"
                        >
                            {{ $t('common.next') }} &raquo;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: ADJUST STOCK -->
        <!-- ============================================================ -->
        <div class="modal fade" id="adjustModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form @submit.prevent="submitAdjustment">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold">{{ $t('inventory.adjust_stock') }}</h5>
                                <div class="text-muted small" v-if="selectedItem">
                                    {{ selectedItem.name }} ({{ selectedItem.default_variant?.sku || '-' }})
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Product Selector if opened via Quick Adjust without item preselected -->
                            <div class="mb-3" v-if="!selectedItem">
                                <label class="form-label fw-semibold">{{ $t('products.product_name') }} *</label>
                                <select
                                    v-model="adjustForm.product_id"
                                    class="form-select"
                                    required
                                    @change="onProductSelectChange"
                                >
                                    <option value="">-- {{ $t('products.add_product') }} --</option>
                                    <option
                                        v-for="prod in items"
                                        :key="prod.id"
                                        :value="prod.id"
                                    >
                                        {{ prod.name }} ({{ $t('products.current_stock') }}: {{ prod.total_stock }} {{ prod.unit?.short_name || '' }})
                                    </option>
                                </select>
                            </div>

                            <!-- Current Stock Info Badge -->
                            <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3 py-2">
                                <span class="text-muted">{{ $t('products.current_stock') }}:</span>
                                <span class="fw-bold fs-6">
                                    {{ currentAdjustStock }} {{ activeUnitShortName }}
                                </span>
                            </div>

                            <!-- Adjustment Type Selector -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ $t('inventory.adjustment_type') }} *</label>
                                <div class="btn-group w-100" role="group">
                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="adjustType"
                                        id="typeIn"
                                        value="in"
                                        v-model="adjustForm.type"
                                    />
                                    <label class="btn btn-outline-success" for="typeIn">
                                        {{ $t('inventory.add_stock') }}
                                    </label>

                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="adjustType"
                                        id="typeOut"
                                        value="out"
                                        v-model="adjustForm.type"
                                    />
                                    <label class="btn btn-outline-danger" for="typeOut">
                                        {{ $t('inventory.subtract_stock') }}
                                    </label>

                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="adjustType"
                                        id="typeSet"
                                        value="adjustment"
                                        v-model="adjustForm.type"
                                    />
                                    <label class="btn btn-outline-primary" for="typeSet">
                                        {{ $t('inventory.audit_correction') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Quantity Input -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    {{ $t('common.quantity') }} *
                                </label>

                                <div class="input-group">
                                    <input
                                        v-model.number="adjustForm.quantity"
                                        type="number"
                                        :step="allowDecimal ? '0.001' : '1'"
                                        min="0"
                                        class="form-control"
                                        :placeholder="$t('common.quantity')"
                                        required
                                    />
                                    <span class="input-group-text">{{ activeUnitShortName || 'Units' }}</span>
                                </div>
                            </div>

                            <!-- Live Calculation Preview -->
                            <div
                                class="card mb-3 p-2 bg-light border-0 small"
                                v-if="adjustForm.quantity !== null && adjustForm.quantity !== ''"
                            >
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">{{ $t('inventory.stock_after') }}:</span>
                                    <span
                                        class="fw-bold"
                                        :class="calculatedNewStock < 0 ? 'text-danger' : 'text-primary'"
                                    >
                                        {{ calculatedNewStock }} {{ activeUnitShortName }}
                                    </span>
                                </div>
                                <div v-if="calculatedNewStock < 0" class="text-danger small mt-1">
                                    {{ $t('common.warning') }}: {{ $t('pos.insufficient_stock') }}
                                </div>
                            </div>

                            <!-- Reason Selector -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ $t('inventory.adjustment_reason') }} *</label>
                                <select
                                    v-model="selectedReasonPreset"
                                    class="form-select mb-2"
                                    @change="onReasonPresetChange"
                                >
                                    <option value="Received Purchase / Restock">{{ $t('purchases.title') }} / {{ $t('inventory.add_stock') }}</option>
                                    <option value="Physical Count Reconciliation">{{ $t('inventory.audit_correction') }}</option>
                                    <option value="Damaged / Broken Goods">{{ $t('inventory.damage') }}</option>
                                    <option value="Expired Product">{{ $t('inventory.expiry') }}</option>
                                    <option value="Lost / Missing">{{ $t('inventory.theft') }}</option>
                                    <option value="custom">{{ $t('common.actions') }}...</option>
                                </select>

                                <input
                                    v-if="selectedReasonPreset === 'custom'"
                                    v-model="adjustForm.reason"
                                    type="text"
                                    class="form-control"
                                    :placeholder="$t('inventory.adjustment_reason')"
                                    required
                                />
                            </div>

                            <!-- Notes Field -->
                            <div class="mb-3">
                                <label class="form-label">{{ $t('common.notes') }}</label>
                                <textarea
                                    v-model="adjustForm.notes"
                                    class="form-control"
                                    rows="2"
                                    :placeholder="$t('common.notes')"
                                ></textarea>
                            </div>

                            <!-- Error Message Alert -->
                            <div v-if="adjustError" class="alert alert-danger py-2 small">
                                {{ adjustError }}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                {{ $t('common.cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="adjusting || (adjustForm.type === 'out' && calculatedNewStock < 0)"
                            >
                                <span v-if="adjusting" class="spinner-border spinner-border-sm me-1"></span>
                                {{ adjusting ? $t('common.processing') : $t('common.confirm') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: PRODUCT STOCK HISTORY -->
        <!-- ============================================================ -->
        <div class="modal fade" id="historyModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold">{{ $t('inventory.history') }}</h5>
                            <div class="text-muted small" v-if="historyItem">
                                {{ historyItem.name }} ({{ historyItem.default_variant?.sku || '-' }})
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div v-if="historyLoading" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            {{ $t('common.loading') }}
                        </div>

                        <div v-else-if="historyList.length === 0" class="text-center py-5 text-muted">
                            {{ $t('common.no_data') }}
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>{{ $t('common.date') }} & {{ $t('common.time') }}</th>
                                        <th>{{ $t('inventory.adjustment_type') }}</th>
                                        <th class="text-end">{{ $t('inventory.quantity_adjusted') }}</th>
                                        <th class="text-end">{{ $t('inventory.stock_after') }}</th>
                                        <th>{{ $t('inventory.adjustment_reason') }} & {{ $t('common.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr v-for="log in historyList" :key="log.id">
                                        <td class="text-muted">{{ formatDate(log.created_at) }}</td>
                                        <td>
                                            <span class="badge" :class="txBadgeClass(log.type)">
                                                {{ txTypeLabel(log.type) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold" :class="txQtyClass(log.type)">
                                            {{ txQtyPrefix(log.type) }}{{ log.quantity }}
                                            <small class="text-muted fw-normal">
                                                {{ historyItem?.unit?.short_name || '' }}
                                            </small>
                                        </td>
                                        <td class="text-end fw-semibold">
                                            {{ log.balance_after }}
                                            <small class="text-muted fw-normal">
                                                {{ historyItem?.unit?.short_name || '' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ log.reason }}</div>
                                            <div class="text-muted small" v-if="log.notes">{{ log.notes }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ $t('common.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { Modal } from 'bootstrap';
import api from '../../services/api';

// Tab state
const activeTab = ref('inventory');

// Inventory list state
const items = ref([]);
const categories = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);

// Filters
const search = ref('');
const categoryFilter = ref('');
const stockStatusFilter = ref('all');

// Summary state
const summary = reactive({
    total_items: 0,
    in_stock: 0,
    low_stock: 0,
    out_of_stock: 0,
    total_quantity: 0,
});

// Transactions tab state
const transactions = ref([]);
const txLoading = ref(false);
const txSearch = ref('');
const txTypeFilter = ref('');
const txCurrentPage = ref(1);
const txLastPage = ref(1);
const txTotal = ref(0);

// Modal instances
let adjustModal = null;
let historyModal = null;

// Adjust Form State
const selectedItem = ref(null);
const adjusting = ref(false);
const adjustError = ref('');
const selectedReasonPreset = ref('Received Purchase / Restock');

const adjustForm = reactive({
    product_id: '',
    product_variant_id: null,
    type: 'in', // 'in', 'out', 'adjustment'
    quantity: '',
    reason: 'Received Purchase / Restock',
    notes: '',
});

// History Modal State
const historyItem = ref(null);
const historyList = ref([]);
const historyLoading = ref(false);

// Debounce timer
let debounceTimer = null;
let txDebounceTimer = null;

// -------------------------------------------------------------------------
// Computed Helpers
// -------------------------------------------------------------------------
const currentAdjustStock = computed(() => {
    if (!selectedItem.value) return 0;
    return Number(selectedItem.value.total_stock || 0);
});

const activeUnitShortName = computed(() => {
    return selectedItem.value?.unit?.short_name || '';
});

const allowDecimal = computed(() => {
    return Boolean(selectedItem.value?.unit?.allow_decimal);
});

const calculatedNewStock = computed(() => {
    const current = Number(currentAdjustStock.value || 0);
    const qty = Number(adjustForm.quantity || 0);

    if (adjustForm.type === 'in') {
        return Number((current + qty).toFixed(3));
    } else if (adjustForm.type === 'out') {
        return Number((current - qty).toFixed(3));
    } else if (adjustForm.type === 'adjustment') {
        return Number(qty.toFixed(3));
    }
    return current;
});

// -------------------------------------------------------------------------
// Data Fetching
// -------------------------------------------------------------------------
async function loadSummary() {
    try {
        const res = await api.get('/inventory/summary');
        Object.assign(summary, res.data);
    } catch (err) {
        console.error('Failed to load summary:', err);
    }
}

async function loadCategories() {
    try {
        const res = await api.get('/categories');
        categories.value = res.data.data || res.data || [];
    } catch (err) {
        console.error('Failed to load categories:', err);
    }
}

async function loadInventory(page = 1) {
    loading.value = true;
    try {
        const res = await api.get('/inventory', {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
                category_id: categoryFilter.value,
                stock_status: stockStatusFilter.value,
            }
        });

        items.value = res.data.data || [];
        currentPage.value = res.data.current_page || 1;
        lastPage.value = res.data.last_page || 1;
        totalItems.value = res.data.total || 0;

    } catch (err) {
        console.error('Failed to load inventory:', err);
    } finally {
        loading.value = false;
    }
}

async function loadTransactions(page = 1) {
    txLoading.value = true;
    try {
        const res = await api.get('/inventory/transactions', {
            params: {
                page,
                search: txSearch.value,
                type: txTypeFilter.value,
            }
        });

        transactions.value = res.data.data || [];
        txCurrentPage.value = res.data.current_page || 1;
        txLastPage.value = res.data.last_page || 1;
        txTotal.value = res.data.total || 0;

    } catch (err) {
        console.error('Failed to load transactions:', err);
    } finally {
        txLoading.value = false;
    }
}

function debouncedLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        loadInventory(1);
    }, 350);
}

function debouncedLoadTx() {
    clearTimeout(txDebounceTimer);
    txDebounceTimer = setTimeout(() => {
        loadTransactions(1);
    }, 350);
}

function refreshData() {
    loadSummary();
    if (activeTab.value === 'inventory') {
        loadInventory(currentPage.value);
    } else {
        loadTransactions(txCurrentPage.value);
    }
}

function switchTab(tab) {
    activeTab.value = tab;
    if (tab === 'transactions' && transactions.value.length === 0) {
        loadTransactions(1);
    }
}

function setStockFilter(status) {
    if (stockStatusFilter.value === status) {
        stockStatusFilter.value = 'all';
    } else {
        stockStatusFilter.value = status;
    }
    loadInventory(1);
}

function resetFilters() {
    search.value = '';
    categoryFilter.value = '';
    stockStatusFilter.value = 'all';
    loadInventory(1);
}

function resetTxFilters() {
    txSearch.value = '';
    txTypeFilter.value = '';
    loadTransactions(1);
}

// -------------------------------------------------------------------------
// Stock Adjustment Modal
// -------------------------------------------------------------------------
function openQuickAdjustModal() {
    selectedItem.value = null;
    resetAdjustForm();
    adjustModal?.show();
}

function openAdjustModal(item) {
    selectedItem.value = item;
    resetAdjustForm();
    adjustForm.product_id = item.id;
    adjustForm.product_variant_id = item.default_variant?.id || null;
    adjustModal?.show();
}

function onProductSelectChange() {
    const item = items.value.find(p => p.id === Number(adjustForm.product_id));
    selectedItem.value = item || null;
    if (item) {
        adjustForm.product_variant_id = item.default_variant?.id || null;
    }
}

function onReasonPresetChange() {
    if (selectedReasonPreset.value !== 'custom') {
        adjustForm.reason = selectedReasonPreset.value;
    } else {
        adjustForm.reason = '';
    }
}

function resetAdjustForm() {
    adjustError.value = '';
    selectedReasonPreset.value = 'Received Purchase / Restock';
    Object.assign(adjustForm, {
        product_id: selectedItem.value ? selectedItem.value.id : '',
        product_variant_id: selectedItem.value?.default_variant?.id || null,
        type: 'in',
        quantity: '',
        reason: 'Received Purchase / Restock',
        notes: '',
    });
}

async function submitAdjustment() {
    if (!adjustForm.product_id) {
        adjustError.value = 'Please select a product.';
        return;
    }

    if (adjustForm.quantity === '' || adjustForm.quantity === null || Number(adjustForm.quantity) < 0) {
        adjustError.value = 'Please enter a valid positive quantity.';
        return;
    }

    if (adjustForm.type === 'out' && calculatedNewStock.value < 0) {
        adjustError.value = 'Cannot deduct more units than current available stock.';
        return;
    }

    adjusting.value = true;
    adjustError.value = '';

    try {
        const payload = {
            product_id: adjustForm.product_id,
            product_variant_id: adjustForm.product_variant_id,
            type: adjustForm.type,
            quantity: Number(adjustForm.quantity),
            reason: adjustForm.reason.trim(),
            notes: adjustForm.notes ? adjustForm.notes.trim() : null,
        };

        const res = await api.post('/inventory/adjust', payload);

        adjustModal?.hide();
        alert(res.data.message || 'Stock adjusted successfully.');

        // Refresh tables
        loadSummary();
        loadInventory(currentPage.value);
        if (activeTab.value === 'transactions') {
            loadTransactions(1);
        }

    } catch (err) {
        adjustError.value = err.response?.data?.message
            || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : 'Unable to adjust stock.');
    } finally {
        adjusting.value = false;
    }
}

// -------------------------------------------------------------------------
// Movement History Modal
// -------------------------------------------------------------------------
async function openHistoryModal(item) {
    historyItem.value = item;
    historyList.value = [];
    historyLoading.value = true;
    historyModal?.show();

    try {
        const res = await api.get(`/inventory/${item.id}/history`);
        historyList.value = res.data.data || [];
    } catch (err) {
        console.error('Failed to load item history:', err);
    } finally {
        historyLoading.value = false;
    }
}

// -------------------------------------------------------------------------
// UI Helpers
// -------------------------------------------------------------------------
function formatQty(val, allowDec) {
    if (val === undefined || val === null) return '0';
    const num = Number(val);
    return allowDec ? num.toFixed(3) : Math.round(num).toString();
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function stockNumberClass(item) {
    if (item.stock_status === 'out_of_stock') return 'text-danger';
    if (item.stock_status === 'low_stock') return 'text-warning';
    return 'text-success';
}

function statusBadgeClass(status) {
    if (status === 'out_of_stock') return 'bg-danger';
    if (status === 'low_stock') return 'bg-warning text-dark';
    return 'bg-success';
}

function statusLabel(status) {
    if (status === 'out_of_stock') return 'Out of Stock';
    if (status === 'low_stock') return 'Low Stock';
    return 'In Stock';
}

function txBadgeClass(type) {
    if (type === 'in') return 'bg-success-subtle text-success border border-success-subtle';
    if (type === 'out') return 'bg-danger-subtle text-danger border border-danger-subtle';
    return 'bg-primary-subtle text-primary border border-primary-subtle';
}

function txTypeLabel(type) {
    if (type === 'in') return 'Stock In (+)';
    if (type === 'out') return 'Stock Out (-)';
    return 'Audit Set (=)';
}

function txQtyPrefix(type) {
    if (type === 'in') return '+';
    if (type === 'out') return '-';
    return '=';
}

function txQtyClass(type) {
    if (type === 'in') return 'text-success';
    if (type === 'out') return 'text-danger';
    return 'text-primary';
}

// -------------------------------------------------------------------------
// Lifecycle
// -------------------------------------------------------------------------
onMounted(() => {
    const adjEl = document.getElementById('adjustModal');
    if (adjEl) adjustModal = new Modal(adjEl);

    const histEl = document.getElementById('historyModal');
    if (histEl) historyModal = new Modal(histEl);

    loadSummary();
    loadCategories();
    loadInventory();
});

onBeforeUnmount(() => {
    adjustModal?.dispose();
    historyModal?.dispose();
    clearTimeout(debounceTimer);
    clearTimeout(txDebounceTimer);
});
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.stat-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}
</style>

