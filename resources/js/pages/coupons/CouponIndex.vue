<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">🏷️ {{ $t('coupons.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('coupons.subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary"
                    @click="loadCoupons(currentPage)"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    {{ $t('common.refresh') }}
                </button>

                <button
                    class="btn btn-primary"
                    @click="openCreateModal"
                >
                    + {{ $t('coupons.add_coupon') }}
                </button>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                            <span class="fs-4">🏷️</span>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('coupons.title') }}</div>
                            <h4 class="fw-bold mb-0">{{ totalCouponsCount }}</h4>
                            <div class="small text-muted mt-1">{{ activeCouponsCount }} {{ $t('common.active') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success-subtle text-success rounded-3 p-3 me-3">
                            <span class="fs-4">✅</span>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('common.active') }}</div>
                            <h4 class="fw-bold mb-0 text-success">{{ activeCouponsCount }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.status') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info-subtle text-info rounded-3 p-3 me-3">
                            <span class="fs-4">🎯</span>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('coupons.usage_history') }}</div>
                            <h4 class="fw-bold mb-0">{{ totalRedemptionsCount }}</h4>
                            <div class="small text-muted mt-1">Web & POS</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3 me-3">
                            <span class="fs-4">⚡</span>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('coupons.discount_type') }}</div>
                            <h5 class="fw-bold mb-0">{{ $t('coupons.percentage') }} / {{ $t('coupons.fixed') }}</h5>
                            <div class="small text-muted mt-1">{{ $t('common.info') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <!-- Search by Code / Title -->
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">🔍</span>
                            <input
                                v-model="searchQuery"
                                class="form-control border-start-0"
                                :placeholder="$t('common.search') + '...'"
                                @input="debouncedSearch"
                            />
                        </div>
                    </div>

                    <!-- Channel Filter -->
                    <div class="col-md-2">
                        <select v-model="channelFilter" class="form-select" @change="loadCoupons(1)">
                            <option value="">-- {{ $t('orders.channel') }} ({{ $t('common.all') }}) --</option>
                            <option value="all">Universal (All)</option>
                            <option value="online_only">Web Online Only</option>
                            <option value="pos_only">In-Store POS Only</option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-md-2">
                        <select v-model="typeFilter" class="form-select" @change="loadCoupons(1)">
                            <option value="">-- {{ $t('coupons.discount_type') }} ({{ $t('common.all') }}) --</option>
                            <option value="percentage">{{ $t('coupons.percentage') }}</option>
                            <option value="fixed_cart">{{ $t('coupons.fixed') }}</option>
                            <option value="free_shipping">{{ $t('common.shipping') }}</option>
                            <option value="buy_x_get_y">{{ $t('coupons.buy_x_get_y') }}</option>
                            <option value="tiered">{{ $t('coupons.tiered') }}</option>
                        </select>
                    </div>

                    <!-- Expiry Status Filter -->
                    <div class="col-md-2">
                        <select v-model="expiryStatusFilter" class="form-select" @change="loadCoupons(1)">
                            <option value="">-- {{ $t('coupons.expiry_date') }} ({{ $t('common.all') }}) --</option>
                            <option value="active">{{ $t('common.active') }}</option>
                            <option value="expiring_soon">Expiring Soon</option>
                            <option value="expired">Expired</option>
                            <option value="upcoming">Upcoming</option>
                        </select>
                    </div>

                    <!-- Active/Inactive Status Filter -->
                    <div class="col-md-2">
                        <select v-model="statusFilter" class="form-select" @change="loadCoupons(1)">
                            <option value="">-- {{ $t('common.status') }} ({{ $t('common.all') }}) --</option>
                            <option value="1">{{ $t('common.active') }}</option>
                            <option value="0">{{ $t('common.inactive') }}</option>
                        </select>
                    </div>

                    <!-- Reset Filters Button -->
                    <div class="col-md-1 text-end">
                        <button
                            v-if="searchQuery || channelFilter || typeFilter || expiryStatusFilter || statusFilter || dateFromFilter || dateToFilter"
                            class="btn btn-outline-danger w-100 p-2"
                            :title="$t('common.reset')"
                            @click="clearFilters"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Date Range Filter Row -->
                    <div class="col-12 pt-2 border-top mt-2">
                        <div class="d-flex align-items-center gap-2 extra-small text-muted">
                            <span class="fw-semibold">📅 {{ $t('common.filter') }}:</span>
                            <input
                                v-model="dateFromFilter"
                                type="date"
                                class="form-control form-control-sm w-auto"
                                @change="loadCoupons(1)"
                            />
                            <span>{{ $t('common.to') }}</span>
                            <input
                                v-model="dateToFilter"
                                type="date"
                                class="form-control form-control-sm w-auto"
                                @change="loadCoupons(1)"
                            />
                            <button
                                v-if="dateFromFilter || dateToFilter"
                                class="btn btn-link btn-sm text-danger p-0 text-decoration-none"
                                @click="dateFromFilter = ''; dateToFilter = ''; loadCoupons(1);"
                            >
                                {{ $t('common.clear') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coupons Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th scope="col" style="min-width: 140px;">{{ $t('coupons.code') }}</th>
                            <th scope="col" style="min-width: 180px;">{{ $t('common.name') }}</th>
                            <th scope="col" style="min-width: 150px;">{{ $t('coupons.discount_type') }}</th>
                            <th scope="col">{{ $t('coupons.min_spend') }}</th>
                            <th scope="col">{{ $t('orders.channel') }}</th>
                            <th scope="col">{{ $t('coupons.usage_limit') }}</th>
                            <th scope="col">{{ $t('coupons.usage_history') }}</th>
                            <th scope="col" style="min-width: 140px;">{{ $t('coupons.expiry_date') }}</th>
                            <th scope="col" class="text-center">{{ $t('common.status') }}</th>
                            <th scope="col" class="text-end" style="min-width: 170px;">{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                {{ $t('common.loading') }}
                            </td>
                        </tr>

                        <tr v-else-if="coupons.length === 0">
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div>🏷️ {{ $t('common.no_data') }}</div>
                                <button class="btn btn-primary btn-sm mt-3" @click="openCreateModal">
                                    + {{ $t('coupons.add_coupon') }}
                                </button>
                            </td>
                        </tr>

                        <tr v-for="coupon in coupons" :key="coupon.id">
                            <!-- Code with copy button -->
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-primary-subtle text-primary font-monospace fs-6 px-2 py-1">
                                        {{ coupon.code }}
                                    </span>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link p-0 text-muted"
                                        title="Copy code"
                                        @click="copyCode(coupon.code)"
                                    >
                                        📋
                                    </button>
                                </div>
                            </td>

                            <!-- Title & first order tag -->
                            <td>
                                <div class="fw-bold text-dark">{{ coupon.name }}</div>
                                <div v-if="coupon.name_ar" class="extra-small text-muted" dir="rtl">{{ coupon.name_ar }}</div>
                                <div v-if="coupon.description" class="extra-small text-muted text-truncate" style="max-width: 180px;">
                                    {{ coupon.description }}
                                </div>
                                <span v-if="coupon.is_first_order_only" class="badge bg-warning-subtle text-warning extra-small mt-1">
                                    🌟 {{ $t('coupons.first_order_only') }}
                                </span>
                            </td>

                            <!-- Discount Structure -->
                            <td>
                                <div v-if="coupon.type === 'percentage'">
                                    <span class="fw-bold text-success">{{ coupon.value }}% OFF</span>
                                    <div v-if="coupon.max_discount_amount" class="extra-small text-muted">
                                        Max Cap: ₹{{ formatMoney(coupon.max_discount_amount) }}
                                    </div>
                                </div>
                                <div v-else-if="coupon.type === 'fixed_cart'">
                                    <span class="fw-bold text-primary">₹{{ formatMoney(coupon.value) }} Flat OFF</span>
                                </div>
                                <div v-else-if="coupon.type === 'free_shipping'">
                                    <span class="badge bg-info-subtle text-info fw-bold">🚚 Free Delivery</span>
                                </div>
                                <div v-else-if="coupon.type === 'buy_x_get_y'">
                                    <span class="badge bg-success-subtle text-success fw-bold">
                                        🎁 Buy {{ coupon.buy_qty }} Get {{ coupon.get_qty }}
                                    </span>
                                    <div class="extra-small text-muted">
                                        {{ coupon.get_discount_percent }}% off on free item(s)
                                    </div>
                                </div>
                                <div v-else-if="coupon.type === 'tiered'">
                                    <span class="badge bg-purple-subtle text-purple fw-bold">
                                        🪜 Tiered ({{ (coupon.tier_rules || []).length }} levels)
                                    </span>
                                    <div class="extra-small text-muted">
                                        Min ₹{{ formatMoney(coupon.tier_rules?.[0]?.min_amount || 0) }}
                                    </div>
                                </div>
                            </td>

                            <!-- Min Order -->
                            <td>
                                <span v-if="Number(coupon.min_order_amount) > 0" class="small fw-semibold">
                                    ₹{{ formatMoney(coupon.min_order_amount) }}
                                </span>
                                <span v-else class="text-muted small">None</span>
                            </td>

                            <!-- Channel Badge -->
                            <td>
                                <span v-if="coupon.channel === 'all'" class="badge bg-secondary-subtle text-secondary small">
                                    🌐 Universal
                                </span>
                                <span v-else-if="coupon.channel === 'online_only'" class="badge bg-info-subtle text-info small">
                                    💻 Web Only
                                </span>
                                <span v-else-if="coupon.channel === 'pos_only'" class="badge bg-success-subtle text-success small">
                                    🏪 POS Only
                                </span>
                            </td>

                            <!-- Scope -->
                            <td>
                                <span v-if="coupon.applies_to === 'all'" class="small text-muted">All Catalog</span>
                                <span v-else-if="coupon.applies_to === 'specific_categories'" class="badge bg-light text-dark border small" :title="getCategoryNamesTooltip(coupon)">
                                    📂 {{ coupon.restrictions?.length || 0 }} Categories
                                </span>
                                <span v-else-if="coupon.applies_to === 'specific_products'" class="badge bg-light text-dark border small" :title="getProductNamesTooltip(coupon)">
                                    📦 {{ coupon.restrictions?.length || 0 }} Products
                                </span>
                            </td>

                            <!-- Usage Redemptions -->
                            <td>
                                <div class="small fw-bold">
                                    {{ coupon.times_used }} / {{ coupon.usage_limit_total ? coupon.usage_limit_total : '∞' }}
                                </div>
                                <div class="extra-small text-muted">
                                    Max {{ coupon.usage_limit_per_customer || 1 }}/customer
                                </div>
                            </td>

                            <!-- Validity -->
                            <td>
                                <div v-if="coupon.end_date" class="small">
                                    <div :class="{ 'text-danger fw-bold': isPastDate(coupon.end_date) }">
                                        Exp: {{ formatDate(coupon.end_date) }}
                                    </div>
                                    <span v-if="isPastDate(coupon.end_date)" class="badge bg-danger-subtle text-danger extra-small">
                                        Expired
                                    </span>
                                    <span v-else-if="isExpiringSoon(coupon.end_date)" class="badge bg-warning-subtle text-warning extra-small">
                                        Expiring Soon
                                    </span>
                                </div>
                                <div v-else class="small text-muted">
                                    No Expiry
                                </div>
                            </td>

                            <!-- Status Switch -->
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input
                                        class="form-check-input cursor-pointer"
                                        type="checkbox"
                                        :checked="coupon.is_active"
                                        @change="toggleStatus(coupon)"
                                    />
                                </div>
                            </td>

                            <!-- Action Buttons -->
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <!-- QR Code button -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-dark"
                                        title="POS QR / Barcode"
                                        @click="openQrModal(coupon)"
                                    >
                                        📱
                                    </button>

                                    <!-- Reports & Audit button -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-info"
                                        title="Usage Reports & Audit Logs"
                                        @click="openReportsModal(coupon)"
                                    >
                                        📜
                                    </button>

                                    <!-- Duplicate button -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        title="Duplicate Coupon"
                                        @click="duplicateCoupon(coupon)"
                                    >
                                        📋
                                    </button>

                                    <!-- Edit button -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary"
                                        title="Edit Coupon"
                                        @click="openEditModal(coupon)"
                                    >
                                        ✏️
                                    </button>

                                    <!-- Delete button -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger"
                                        title="Delete Coupon"
                                        @click="deleteCoupon(coupon)"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > pagination.per_page" class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} coupons
                    </span>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <button class="page-link" @click="loadCoupons(currentPage - 1)">Previous</button>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Page {{ currentPage }} of {{ pagination.last_page }}</span>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === pagination.last_page }">
                            <button class="page-link" @click="loadCoupons(currentPage + 1)">Next</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- CREATE / EDIT COUPON MODAL                               -->
        <!-- ======================================================== -->
        <div class="modal fade" id="couponModal" tabindex="-1" ref="couponModalRef">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            {{ isEditing ? `✏️ Edit Coupon (${form.code})` : '🏷️ Create New Coupon' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form @submit.prevent="saveCoupon">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Coupon Code & Generator (Automatic Uppercase) -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Coupon Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input
                                            v-model="form.code"
                                            type="text"
                                            class="form-control text-uppercase font-monospace"
                                            placeholder="e.g. SUMMER20"
                                            required
                                            @input="form.code = form.code.toUpperCase()"
                                        />
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            title="Generate Random Code"
                                            @click="generateRandomCode"
                                        >
                                            🎲 Generate
                                        </button>
                                    </div>
                                    <div class="extra-small text-muted mt-1">Unique uppercase code customers enter at checkout.</div>
                                </div>

                                <!-- Coupon Name / Title -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $t('coupons.name') }} <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. Festive Mega Savings"
                                        required
                                    />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $t('coupons.name_ar') }}
                                        <span class="badge bg-light text-muted ms-1">عربي</span>
                                    </label>
                                    <input
                                        v-model="form.name_ar"
                                        type="text"
                                        dir="rtl"
                                        class="form-control"
                                        :placeholder="$t('coupons.name_ar_placeholder')"
                                    />
                                </div>

                                <!-- Description -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $t('common.description') }} <span class="text-muted fw-normal">({{ $t('common.info') }})</span></label>
                                    <input
                                        v-model="form.description"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. 20% discount on stationery for bills above ₹400"
                                    />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        {{ $t('coupons.description_ar') }}
                                        <span class="badge bg-light text-muted ms-1">عربي</span>
                                    </label>
                                    <input
                                        v-model="form.description_ar"
                                        type="text"
                                        dir="rtl"
                                        class="form-control"
                                        :placeholder="$t('coupons.description_ar_placeholder')"
                                    />
                                </div>

                                <!-- Sales Channel Selection -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Sales Channel <span class="text-danger">*</span></label>
                                    <select v-model="form.channel" class="form-select" required>
                                        <option value="all">🌐 Universal (Web Storefront + In-store POS)</option>
                                        <option value="online_only">💻 Online Storefront Only</option>
                                        <option value="pos_only">🏪 In-Store POS Register Only</option>
                                    </select>
                                </div>

                                <!-- Discount Type Selector -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Discount Type <span class="text-danger">*</span></label>
                                    <select v-model="form.type" class="form-select" required @change="onTypeChange">
                                        <option value="percentage">Percentage (%) Discount</option>
                                        <option value="fixed_cart">Fixed Cart (₹ Flat Off)</option>
                                        <option value="free_shipping">Free Shipping Waiver</option>
                                        <option value="buy_x_get_y">Buy X Get Y (BXGY) Offer</option>
                                        <option value="tiered">Tiered Spend Discount</option>
                                    </select>
                                </div>

                                <!-- Standard Value (Percentage or Fixed) -->
                                <div class="col-md-4" v-if="form.type === 'percentage' || form.type === 'fixed_cart'">
                                    <label class="form-label small fw-semibold">
                                        {{ form.type === 'percentage' ? 'Percentage (%)' : 'Discount Amount (₹)' }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ form.type === 'percentage' ? '%' : '₹' }}</span>
                                        <input
                                            v-model.number="form.value"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control"
                                            required
                                        />
                                    </div>
                                </div>

                                <!-- Max Discount Cap (Applicable ONLY to percentage discounts) -->
                                <div class="col-md-4" v-if="form.type === 'percentage'">
                                    <label class="form-label small fw-semibold">
                                        Max Discount Cap (₹) <span class="text-muted fw-normal">(Optional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input
                                            v-model.number="form.max_discount_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control"
                                            placeholder="No cap"
                                        />
                                    </div>
                                    <div class="extra-small text-muted mt-1">Applies only to percentage discount calculation.</div>
                                </div>

                                <!-- Min Order Amount -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Minimum Order Subtotal (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input
                                            v-model.number="form.min_order_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control"
                                        />
                                    </div>
                                </div>

                                <!-- BUY X GET Y (BXGY) CONFIGURATION -->
                                <div class="col-12" v-if="form.type === 'buy_x_get_y'">
                                    <div class="card border bg-light-subtle p-3">
                                        <h6 class="fw-bold mb-2 text-primary">🎁 Buy X Get Y Offer Configuration</h6>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label extra-small fw-semibold">Customer Buys (Quantity)</label>
                                                <input
                                                    v-model.number="form.buy_qty"
                                                    type="number"
                                                    min="1"
                                                    class="form-control"
                                                    placeholder="e.g. 2"
                                                    required
                                                />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label extra-small fw-semibold">Customer Gets (Quantity)</label>
                                                <input
                                                    v-model.number="form.get_qty"
                                                    type="number"
                                                    min="1"
                                                    class="form-control"
                                                    placeholder="e.g. 1"
                                                    required
                                                />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label extra-small fw-semibold">Discount on Free Item (%)</label>
                                                <div class="input-group">
                                                    <input
                                                        v-model.number="form.get_discount_percent"
                                                        type="number"
                                                        min="1"
                                                        max="100"
                                                        class="form-control"
                                                        placeholder="100 for 100% Free"
                                                        required
                                                    />
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="extra-small text-muted mt-2">
                                            💡 Rule: Customer buys <strong>{{ form.buy_qty || 1 }}</strong> item(s) and gets <strong>{{ form.get_qty || 1 }}</strong> item(s) with <strong>{{ form.get_discount_percent || 100 }}%</strong> discount (applied on lowest priced eligible items).
                                        </div>
                                    </div>
                                </div>

                                <!-- TIERED DISCOUNT CONFIGURATION -->
                                <div class="col-12" v-if="form.type === 'tiered'">
                                    <div class="card border bg-light-subtle p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold mb-0 text-primary">🪜 Tiered Spend Discount Rules</h6>
                                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addTierRow">
                                                + Add Tier Level
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white align-middle mb-1">
                                                <thead class="table-light extra-small">
                                                    <tr>
                                                        <th>Min Subtotal (₹)</th>
                                                        <th>Discount Value</th>
                                                        <th>Discount Type</th>
                                                        <th style="width: 40px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(tier, idx) in form.tier_rules" :key="idx">
                                                        <td>
                                                            <input
                                                                v-model.number="tier.min_amount"
                                                                type="number"
                                                                min="0"
                                                                class="form-control form-control-sm"
                                                                placeholder="e.g. 500"
                                                                required
                                                            />
                                                        </td>
                                                        <td>
                                                            <input
                                                                v-model.number="tier.discount"
                                                                type="number"
                                                                min="0"
                                                                class="form-control form-control-sm"
                                                                placeholder="e.g. 50"
                                                                required
                                                            />
                                                        </td>
                                                        <td>
                                                            <select v-model="tier.type" class="form-select form-select-sm">
                                                                <option value="percentage">Percentage (%)</option>
                                                                <option value="fixed">Flat Amount (₹)</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button
                                                                type="button"
                                                                class="btn btn-link text-danger p-0"
                                                                @click="removeTierRow(idx)"
                                                                :disabled="form.tier_rules.length <= 1"
                                                            >
                                                                ✕
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="extra-small text-muted">
                                            Highest qualified tier by cart spend will be unlocked automatically.
                                        </div>
                                    </div>
                                </div>

                                <!-- Scope / Applies To -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Catalog Scope Selection <span class="text-danger">*</span></label>
                                    <select v-model="form.applies_to" class="form-select" required>
                                        <option value="all">Entire Catalog (All Products)</option>
                                        <option value="specific_categories">Specific Categories Only</option>
                                        <option value="specific_products">Specific Products Only</option>
                                    </select>
                                </div>

                                <!-- Category Selection List with Search & Bulk Actions -->
                                <div class="col-12" v-if="form.applies_to === 'specific_categories'">
                                    <div class="p-3 border rounded bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label small fw-semibold mb-0">Select Eligible Categories ({{ form.category_ids.length }} selected)</label>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="selectAllCategories">
                                                    Select All
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="form.category_ids = []">
                                                    Clear All
                                                </button>
                                            </div>
                                        </div>

                                        <input
                                            v-model="categorySearch"
                                            type="text"
                                            class="form-control form-control-sm mb-2"
                                            placeholder="Search categories..."
                                        />

                                        <div class="p-2 border rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                                            <div v-for="cat in filteredCategories" :key="cat.id" class="form-check form-check-inline">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :id="`cat_${cat.id}`"
                                                    :value="cat.id"
                                                    v-model="form.category_ids"
                                                />
                                                <label class="form-check-label small" :for="`cat_${cat.id}`">
                                                    {{ cat.name }} ({{ cat.products_count || 0 }} items)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Selection List with Search & Bulk Actions -->
                                <div class="col-12" v-if="form.applies_to === 'specific_products'">
                                    <div class="p-3 border rounded bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label small fw-semibold mb-0">Select Eligible Products ({{ form.product_ids.length }} selected)</label>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="selectAllProducts">
                                                    Select All
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="form.product_ids = []">
                                                    Clear All
                                                </button>
                                            </div>
                                        </div>

                                        <input
                                            v-model="productSearch"
                                            type="text"
                                            class="form-control form-control-sm mb-2"
                                            placeholder="Search products by name or SKU..."
                                        />

                                        <div class="p-2 border rounded bg-light" style="max-height: 160px; overflow-y: auto;">
                                            <div v-for="prod in filteredProducts" :key="prod.id" class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :id="`prod_${prod.id}`"
                                                    :value="prod.id"
                                                    v-model="form.product_ids"
                                                />
                                                <label class="form-check-label small" :for="`prod_${prod.id}`">
                                                    {{ prod.name }} <span class="text-muted">(SKU: {{ prod.sku || prod.id }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Usage Limits -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Global Total Usage Limit</label>
                                    <input
                                        v-model.number="form.usage_limit_total"
                                        type="number"
                                        min="1"
                                        class="form-control"
                                        placeholder="Blank for Unlimited"
                                    />
                                    <div class="extra-small text-muted mt-1">Total times this coupon can be redeemed globally.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Per-Customer Usage Limit</label>
                                    <input
                                        v-model.number="form.usage_limit_per_customer"
                                        type="number"
                                        min="1"
                                        class="form-control"
                                        required
                                    />
                                    <div class="extra-small text-muted mt-1">Max redemptions allowed per customer ID or phone number.</div>
                                </div>

                                <!-- Start & End Date-Time Validation -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Start Date & Time</label>
                                    <input
                                        v-model="form.start_date"
                                        type="datetime-local"
                                        class="form-control"
                                    />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Expiry Date & Time</label>
                                    <input
                                        v-model="form.end_date"
                                        type="datetime-local"
                                        class="form-control"
                                    />
                                </div>

                                <!-- Checkbox Options -->
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-4 pt-2">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="firstOrderOnly"
                                                v-model="form.is_first_order_only"
                                            />
                                            <label class="form-check-label small fw-semibold" for="firstOrderOnly">
                                                Exclusively for first-time buyers
                                            </label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="couponActive"
                                                v-model="form.is_active"
                                            />
                                            <label class="form-check-label small fw-semibold" for="couponActive">
                                                Active & Enabled
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                {{ isEditing ? 'Update Coupon' : 'Create Coupon' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- POS QR CODE & BARCODE MODAL                              -->
        <!-- ======================================================== -->
        <div class="modal fade" id="qrModal" tabindex="-1" ref="qrModalRef">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center p-3">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">📱 POS Coupon Voucher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body py-3" id="printableVoucher">
                        <div class="p-3 border rounded-3 bg-white shadow-sm voucher-card">
                            <h5 class="fw-bold text-primary mb-1">{{ qrCoupon?.name }}</h5>
                            <div class="font-monospace fs-3 fw-bolder text-dark mb-2 letter-spacing-1">
                                {{ qrCoupon?.code }}
                            </div>

                            <!-- QR Code Image -->
                            <div class="my-3">
                                <img v-if="qrDataUrl" :src="qrDataUrl" alt="Coupon QR" class="img-fluid border p-2 rounded bg-light" style="width: 170px; height: 170px;" />
                            </div>

                            <!-- Barcode Canvas -->
                            <div class="my-2">
                                <svg ref="barcodeCanvasRef" class="img-fluid"></svg>
                            </div>

                            <div class="extra-small text-muted mt-2">
                                💡 Scan barcode or QR code with handheld barcode scanner at POS register to apply discount instantly.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0 d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary" @click="printVoucher">
                            🖨️ Print Voucher
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- USAGES, CUSTOMER REPORT & AUDIT LOGS MODAL               -->
        <!-- ======================================================== -->
        <div class="modal fade" id="reportsModal" tabindex="-1" ref="reportsModalRef">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">
                                📊 Redemption Reports: <span class="font-monospace text-primary">{{ viewingCoupon?.code }}</span>
                            </h5>
                            <div class="extra-small text-muted">{{ viewingCoupon?.name }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Tabs Header -->
                    <ul class="nav nav-tabs px-3 pt-2 bg-light">
                        <li class="nav-item">
                            <button
                                class="nav-link"
                                :class="{ active: activeTab === 'usages' }"
                                @click="activeTab = 'usages'"
                            >
                                📜 Order Redemptions
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link"
                                :class="{ active: activeTab === 'customers' }"
                                @click="switchTabToCustomers"
                            >
                                👥 Customer-wise Report
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link"
                                :class="{ active: activeTab === 'audit' }"
                                @click="switchTabToAudit"
                            >
                                🛡️ Audit Trail Logs
                            </button>
                        </li>
                    </ul>

                    <div class="modal-body p-0">
                        <!-- TAB 1: USAGES LEDGER -->
                        <div v-if="activeTab === 'usages'">
                            <div v-if="loadingUsages" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                Loading redemption ledger...
                            </div>
                            <div v-else-if="usagesList.length === 0" class="text-center py-5 text-muted">
                                🏷️ No redemptions recorded for this coupon yet.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase small text-muted">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Channel</th>
                                            <th>Customer</th>
                                            <th>Discount Given</th>
                                            <th>Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="usage in usagesList" :key="usage.id">
                                            <td class="font-monospace fw-bold text-dark">
                                                {{ usage.sale?.invoice_number || '-' }}
                                            </td>
                                            <td>
                                                <span v-if="usage.sale?.order_type === 'pos'" class="badge bg-success-subtle text-success small">
                                                    POS Register
                                                </span>
                                                <span v-else class="badge bg-info-subtle text-info small">
                                                    Web Store
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold small">{{ usage.customer?.name || 'Walk-in / Guest' }}</div>
                                                <div class="extra-small text-muted">{{ usage.customer_phone || usage.customer?.phone || '-' }}</div>
                                            </td>
                                            <td class="fw-bold text-success">
                                                -₹{{ formatMoney(usage.discount_amount) }}
                                            </td>
                                            <td class="small text-muted">
                                                {{ formatDateTime(usage.created_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: CUSTOMER-WISE REPORT -->
                        <div v-if="activeTab === 'customers'">
                            <div v-if="loadingCustomers" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                Calculating customer redemption statistics...
                            </div>
                            <div v-else-if="customerStatsList.length === 0" class="text-center py-5 text-muted">
                                👥 No customer redemptions recorded yet.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase small text-muted">
                                        <tr>
                                            <th>Customer Name</th>
                                            <th>Phone Number</th>
                                            <th class="text-center">Times Used</th>
                                            <th class="text-end">Total Saved (₹)</th>
                                            <th>Last Redeemed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(stat, idx) in customerStatsList" :key="idx">
                                            <td class="fw-semibold small">
                                                {{ stat.customer?.name || 'Guest / Walk-in' }}
                                            </td>
                                            <td class="small font-monospace">
                                                {{ stat.customer_phone || stat.customer?.phone || '-' }}
                                            </td>
                                            <td class="text-center fw-bold">
                                                <span class="badge bg-light text-dark border">
                                                    {{ stat.total_redemptions }}x
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                ₹{{ formatMoney(stat.total_savings) }}
                                            </td>
                                            <td class="small text-muted">
                                                {{ formatDateTime(stat.last_redeemed_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 3: AUDIT TRAIL LOGS -->
                        <div v-if="activeTab === 'audit'">
                            <div v-if="loadingAudit" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                Loading security audit logs...
                            </div>
                            <div v-else-if="auditLogsList.length === 0" class="text-center py-5 text-muted">
                                🛡️ No audit log records found for this coupon.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase small text-muted">
                                        <tr>
                                            <th>Action</th>
                                            <th>Admin User</th>
                                            <th>IP Address</th>
                                            <th>Timestamp</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="log in auditLogsList" :key="log.id">
                                            <td>
                                                <span class="badge" :class="getAuditActionClass(log.action)">
                                                    {{ log.action?.toUpperCase() }}
                                                </span>
                                            </td>
                                            <td class="small fw-semibold">
                                                {{ log.user?.name || 'System / Admin' }}
                                            </td>
                                            <td class="extra-small font-monospace text-muted">
                                                {{ log.ip_address || '-' }}
                                            </td>
                                            <td class="small text-muted">
                                                {{ formatDateTime(log.created_at) }}
                                            </td>
                                            <td class="extra-small text-muted font-monospace text-truncate" style="max-width: 250px;">
                                                {{ JSON.stringify(log.details) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { Modal } from 'bootstrap';
import QRCode from 'qrcode';
import JsBarcode from 'jsbarcode';
import api from '../../services/api';
import { useNotification } from '../../composables/useNotification';

const { toast, confirmDialog } = useNotification();

// State
const coupons = ref([]);
const loading = ref(false);
const saving = ref(false);
const currentPage = ref(1);
const pagination = reactive({
    total: 0,
    per_page: 15,
    last_page: 1,
    from: 0,
    to: 0,
});

// Filters
const searchQuery = ref('');
const channelFilter = ref('');
const typeFilter = ref('');
const expiryStatusFilter = ref('');
const statusFilter = ref('');
const dateFromFilter = ref('');
const dateToFilter = ref('');
let searchTimeout = null;

// Modal instances
const couponModalRef = ref(null);
const reportsModalRef = ref(null);
const qrModalRef = ref(null);
const barcodeCanvasRef = ref(null);

let couponModalInstance = null;
let reportsModalInstance = null;
let qrModalInstance = null;

// Form state
const isEditing = ref(false);
const editingId = ref(null);
const availableCategories = ref([]);
const availableProducts = ref([]);
const categorySearch = ref('');
const productSearch = ref('');

const form = reactive({
    code: '',
    name: '',
    name_ar: '',
    description: '',
    description_ar: '',
    type: 'percentage',
    value: 10,
    max_discount_amount: null,
    min_order_amount: 0,
    buy_qty: 2,
    get_qty: 1,
    get_discount_percent: 100,
    tier_rules: [
        { min_amount: 500, discount: 10, type: 'percentage' },
        { min_amount: 1000, discount: 20, type: 'percentage' },
    ],
    channel: 'all',
    applies_to: 'all',
    usage_limit_total: null,
    usage_limit_per_customer: 1,
    is_first_order_only: false,
    start_date: '',
    end_date: '',
    is_active: true,
    category_ids: [],
    product_ids: [],
});

// QR Code & Barcode Modal State
const qrCoupon = ref(null);
const qrDataUrl = ref('');

// Reports Modal State
const viewingCoupon = ref(null);
const activeTab = ref('usages');
const usagesList = ref([]);
const loadingUsages = ref(false);
const customerStatsList = ref([]);
const loadingCustomers = ref(false);
const auditLogsList = ref([]);
const loadingAudit = ref(false);

// KPI counts
const totalCouponsCount = computed(() => pagination.total || coupons.value.length);
const activeCouponsCount = computed(() => coupons.value.filter(c => c.is_active).length);
const totalRedemptionsCount = computed(() => {
    return coupons.value.reduce((sum, c) => sum + Number(c.times_used || 0), 0);
});

// Filtered categories and products for picker
const filteredCategories = computed(() => {
    if (!categorySearch.value.trim()) return availableCategories.value;
    const q = categorySearch.value.toLowerCase();
    return availableCategories.value.filter(c => c.name?.toLowerCase().includes(q));
});

const filteredProducts = computed(() => {
    if (!productSearch.value.trim()) return availableProducts.value;
    const q = productSearch.value.toLowerCase();
    return availableProducts.value.filter(p => p.name?.toLowerCase().includes(q) || p.sku?.toLowerCase().includes(q));
});

// Load Coupons
async function loadCoupons(page = 1) {
    loading.value = true;
    currentPage.value = page;

    try {
        const params = {
            page: page,
            per_page: 15,
        };

        if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
        if (channelFilter.value) params.channel = channelFilter.value;
        if (typeFilter.value) params.type = typeFilter.value;
        if (expiryStatusFilter.value) params.expiry_status = expiryStatusFilter.value;
        if (statusFilter.value !== '') params.status = statusFilter.value;
        if (dateFromFilter.value) params.date_from = dateFromFilter.value;
        if (dateToFilter.value) params.date_to = dateToFilter.value;

        const res = await api.get('/coupons', { params });
        coupons.value = res.data.data;
        pagination.total = res.data.total;
        pagination.per_page = res.data.per_page;
        pagination.last_page = res.data.last_page;
        pagination.from = res.data.from;
        pagination.to = res.data.to;
    } catch (err) {
        console.error('Failed to load coupons:', err);
        toast.error('Failed to load coupons list.', 'Load Error');
    } finally {
        loading.value = false;
    }
}

function debouncedSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadCoupons(1);
    }, 350);
}

function clearFilters() {
    searchQuery.value = '';
    channelFilter.value = '';
    typeFilter.value = '';
    expiryStatusFilter.value = '';
    statusFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    loadCoupons(1);
}

// Copy Code Helper
async function copyCode(code) {
    try {
        await navigator.clipboard.writeText(code);
        toast.success(`Coupon code '${code}' copied to clipboard!`, 'Copied');
    } catch (e) {
        toast.info(code, 'Coupon Code');
    }
}

// Toggle Status
async function toggleStatus(coupon) {
    try {
        const res = await api.patch(`/coupons/${coupon.id}/toggle-status`);
        coupon.is_active = res.data.is_active;
        toast.success(res.data.message || 'Status updated.', 'Success');
    } catch (err) {
        console.error('Toggle status error:', err);
        toast.error('Failed to update coupon status.', 'Error');
    }
}

// Duplicate Coupon (Requirement 1)
async function duplicateCoupon(coupon) {
    try {
        const res = await api.post(`/coupons/${coupon.id}/duplicate`);
        toast.success(res.data.message || 'Coupon duplicated successfully.', 'Duplicated');
        loadCoupons(1);
    } catch (err) {
        console.error('Duplicate error:', err);
        toast.error(err.response?.data?.message || 'Failed to duplicate coupon.', 'Error');
    }
}

// Load Categories & Products for Scope pickers
async function loadAuxiliaryData() {
    try {
        if (availableCategories.value.length === 0) {
            const catRes = await api.get('/categories');
            availableCategories.value = catRes.data.data || catRes.data;
        }
        if (availableProducts.value.length === 0) {
            const prodRes = await api.get('/products', { params: { per_page: 200 } });
            availableProducts.value = prodRes.data.data || prodRes.data;
        }
    } catch (e) {
        console.error('Failed to load auxiliary category/product data:', e);
    }
}

function selectAllCategories() {
    form.category_ids = availableCategories.value.map(c => c.id);
}

function selectAllProducts() {
    form.product_ids = availableProducts.value.map(p => p.id);
}

function onTypeChange() {
    if (form.type === 'buy_x_get_y') {
        if (!form.buy_qty) form.buy_qty = 2;
        if (!form.get_qty) form.get_qty = 1;
        if (!form.get_discount_percent) form.get_discount_percent = 100;
    } else if (form.type === 'tiered') {
        if (!form.tier_rules || form.tier_rules.length === 0) {
            form.tier_rules = [
                { min_amount: 500, discount: 10, type: 'percentage' },
                { min_amount: 1000, discount: 20, type: 'percentage' },
            ];
        }
    }
}

function addTierRow() {
    form.tier_rules.push({ min_amount: 1500, discount: 25, type: 'percentage' });
}

function removeTierRow(idx) {
    if (form.tier_rules.length > 1) {
        form.tier_rules.splice(idx, 1);
    }
}

// Open Create Modal
function openCreateModal() {
    isEditing.value = false;
    editingId.value = null;

    Object.assign(form, {
        code: '',
        name: '',
        name_ar: '',
        description: '',
        description_ar: '',
        type: 'percentage',
        value: 10,
        max_discount_amount: null,
        min_order_amount: 0,
        buy_qty: 2,
        get_qty: 1,
        get_discount_percent: 100,
        tier_rules: [
            { min_amount: 500, discount: 10, type: 'percentage' },
            { min_amount: 1000, discount: 20, type: 'percentage' },
        ],
        channel: 'all',
        applies_to: 'all',
        usage_limit_total: null,
        usage_limit_per_customer: 1,
        is_first_order_only: false,
        start_date: '',
        end_date: '',
        is_active: true,
        category_ids: [],
        product_ids: [],
    });

    loadAuxiliaryData();
    couponModalInstance?.show();
}

// Open Edit Modal
function openEditModal(coupon) {
    isEditing.value = true;
    editingId.value = coupon.id;

    const categoryIds = coupon.restrictions
        ?.filter(r => r.restrictable_type === 'category')
        .map(r => r.restrictable_id) || [];

    const productIds = coupon.restrictions
        ?.filter(r => r.restrictable_type === 'product')
        .map(r => r.restrictable_id) || [];

    let tiers = coupon.tier_rules;
    if (typeof tiers === 'string') {
        try { tiers = JSON.parse(tiers); } catch (e) { tiers = []; }
    }
    if (!tiers || tiers.length === 0) {
        tiers = [{ min_amount: 500, discount: 10, type: 'percentage' }];
    }

    Object.assign(form, {
        code: coupon.code,
        name: coupon.name,
        name_ar: coupon.name_ar || '',
        description: coupon.description || '',
        description_ar: coupon.description_ar || '',
        type: coupon.type,
        value: Number(coupon.value),
        max_discount_amount: coupon.max_discount_amount ? Number(coupon.max_discount_amount) : null,
        min_order_amount: Number(coupon.min_order_amount || 0),
        buy_qty: coupon.buy_qty || 2,
        get_qty: coupon.get_qty || 1,
        get_discount_percent: coupon.get_discount_percent || 100,
        tier_rules: tiers,
        channel: coupon.channel,
        applies_to: coupon.applies_to,
        usage_limit_total: coupon.usage_limit_total || null,
        usage_limit_per_customer: coupon.usage_limit_per_customer || 1,
        is_first_order_only: Boolean(coupon.is_first_order_only),
        start_date: coupon.start_date ? coupon.start_date.substring(0, 16) : '',
        end_date: coupon.end_date ? coupon.end_date.substring(0, 16) : '',
        is_active: Boolean(coupon.is_active),
        category_ids: categoryIds,
        product_ids: productIds,
    });

    loadAuxiliaryData();
    couponModalInstance?.show();
}

// Save Coupon
async function saveCoupon() {
    saving.value = true;
    try {
        const payload = {
            ...form,
            code: form.code.trim().toUpperCase(),
            value: form.type === 'free_shipping' ? 0 : Number(form.value || 0),
            max_discount_amount: form.type === 'percentage' && form.max_discount_amount ? Number(form.max_discount_amount) : null,
            min_order_amount: Number(form.min_order_amount || 0),
            usage_limit_total: form.usage_limit_total ? Number(form.usage_limit_total) : null,
            usage_limit_per_customer: Number(form.usage_limit_per_customer || 1),
            start_date: form.start_date ? form.start_date.replace('T', ' ') : null,
            end_date: form.end_date ? form.end_date.replace('T', ' ') : null,
        };

        if (form.type === 'buy_x_get_y') {
            payload.buy_qty = Number(form.buy_qty || 1);
            payload.get_qty = Number(form.get_qty || 1);
            payload.get_discount_percent = Number(form.get_discount_percent || 100);
        } else if (form.type === 'tiered') {
            payload.tier_rules = form.tier_rules;
        }

        if (isEditing.value) {
            await api.put(`/coupons/${editingId.value}`, payload);
            toast.success(`Coupon '${payload.code}' updated successfully.`, 'Updated');
        } else {
            await api.post('/coupons', payload);
            toast.success(`Coupon '${payload.code}' created successfully!`, 'Created');
        }

        couponModalInstance?.hide();
        loadCoupons(currentPage.value);
    } catch (err) {
        console.error('Save coupon error:', err);
        const msg = err.response?.data?.message || 'Failed to save coupon. Please check all required fields.';
        toast.error(msg, 'Save Error');
    } finally {
        saving.value = false;
    }
}

// Delete Coupon
async function deleteCoupon(coupon) {
    const ok = await confirmDialog({
        title: `Delete Coupon '${coupon.code}'?`,
        message: 'Are you sure you want to delete this promotional coupon? Existing orders that redeemed this coupon will still retain their records.',
        confirmText: 'Yes, Delete',
        type: 'danger',
    });

    if (ok) {
        try {
            await api.delete(`/coupons/${coupon.id}`);
            toast.success(`Coupon '${coupon.code}' deleted.`, 'Deleted');
            loadCoupons(currentPage.value);
        } catch (err) {
            toast.error('Failed to delete coupon.', 'Error');
        }
    }
}

// POS QR Code Modal & Generator (Requirement 5)
async function openQrModal(coupon) {
    qrCoupon.value = coupon;
    qrModalInstance?.show();

    try {
        qrDataUrl.value = await QRCode.toDataURL(coupon.code, {
            width: 200,
            margin: 1,
            color: { dark: '#000000', light: '#ffffff' },
        });

        await nextTick();
        if (barcodeCanvasRef.value) {
            JsBarcode(barcodeCanvasRef.value, coupon.code, {
                format: 'CODE128',
                width: 2,
                height: 48,
                displayValue: true,
                fontSize: 14,
            });
        }
    } catch (e) {
        console.error('QR / Barcode generation error:', e);
    }
}

function printVoucher() {
    const voucherEl = document.getElementById('printableVoucher');
    if (!voucherEl) return;

    const printWin = window.open('', '', 'width=450,height=600');
    printWin.document.write(`
        <html>
            <head>
                <title>Coupon Voucher - ${qrCoupon.value?.code}</title>
                <style>
                    body { font-family: monospace; text-align: center; padding: 20px; }
                    .code { font-size: 24px; font-weight: bold; margin: 10px 0; }
                    img, svg { margin: 10px auto; display: block; max-width: 90%; }
                </style>
            </head>
            <body>
                ${voucherEl.innerHTML}
            </body>
        </html>
    `);
    printWin.document.close();
    printWin.focus();
    printWin.print();
    printWin.close();
}

// Open Reports & Audit Modal (Requirement 7 & 17)
async function openReportsModal(coupon) {
    viewingCoupon.value = coupon;
    activeTab.value = 'usages';
    usagesList.value = [];
    customerStatsList.value = [];
    auditLogsList.value = [];
    reportsModalInstance?.show();

    loadUsages(coupon);
}

async function loadUsages(coupon) {
    loadingUsages.value = true;
    try {
        const res = await api.get(`/coupons/${coupon.id}/usages`);
        usagesList.value = res.data.data;
    } catch (err) {
        toast.error('Failed to load redemption history.', 'Error');
    } finally {
        loadingUsages.value = false;
    }
}

async function switchTabToCustomers() {
    activeTab.value = 'customers';
    if (customerStatsList.value.length === 0 && viewingCoupon.value) {
        loadingCustomers.value = true;
        try {
            const res = await api.get(`/coupons/${viewingCoupon.value.id}/customer-report`);
            customerStatsList.value = res.data.data || res.data;
        } catch (err) {
            toast.error('Failed to load customer reports.', 'Error');
        } finally {
            loadingCustomers.value = false;
        }
    }
}

async function switchTabToAudit() {
    activeTab.value = 'audit';
    if (auditLogsList.value.length === 0 && viewingCoupon.value) {
        loadingAudit.value = true;
        try {
            const res = await api.get(`/coupons/${viewingCoupon.value.id}/audit-logs`);
            auditLogsList.value = res.data.data;
        } catch (err) {
            toast.error('Failed to load audit logs.', 'Error');
        } finally {
            loadingAudit.value = false;
        }
    }
}

function getAuditActionClass(action) {
    switch (action) {
        case 'created': return 'bg-success-subtle text-success';
        case 'updated': return 'bg-primary-subtle text-primary';
        case 'duplicated': return 'bg-info-subtle text-info';
        case 'status_toggled': return 'bg-warning-subtle text-warning';
        case 'deleted': return 'bg-danger-subtle text-danger';
        default: return 'bg-secondary-subtle text-secondary';
    }
}

// Generate Random Code
function generateRandomCode() {
    const prefixes = ['SAVE', 'FESTIVE', 'SPECIAL', 'MEGA', 'SUPER', 'DEAL', 'BUYGET'];
    const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const num = Math.floor(10 + Math.random() * 90);
    form.code = `${prefix}${num}`;
}

// Tooltips
function getCategoryNamesTooltip(coupon) {
    return coupon.restrictions?.map(r => `Category #${r.restrictable_id}`).join(', ') || '';
}

function getProductNamesTooltip(coupon) {
    return coupon.restrictions?.map(r => `Product #${r.restrictable_id}`).join(', ') || '';
}

// Helpers
function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function isPastDate(dateStr) {
    if (!dateStr) return false;
    return new Date(dateStr) < new Date();
}

function isExpiringSoon(dateStr) {
    if (!dateStr) return false;
    const now = new Date();
    const d = new Date(dateStr);
    const diffDays = (d - now) / (1000 * 60 * 60 * 24);
    return diffDays > 0 && diffDays <= 7;
}

onMounted(() => {
    couponModalInstance = new Modal(couponModalRef.value);
    reportsModalInstance = new Modal(reportsModalRef.value);
    qrModalInstance = new Modal(qrModalRef.value);
    loadCoupons(1);
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.cursor-pointer {
    cursor: pointer;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
}

.letter-spacing-1 {
    letter-spacing: 1.5px;
}

.bg-purple-subtle {
    background-color: #f3e8ff;
}

.text-purple {
    color: #7e22ce;
}
</style>
