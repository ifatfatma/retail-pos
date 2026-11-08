<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('purchases.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('purchases.subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary"
                    @click="openSuppliersModal"
                >
                    {{ $t('purchases.supplier_name') }}
                </button>

                <button
                    class="btn btn-outline-secondary"
                    @click="refreshData"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    {{ $t('common.refresh') }}
                </button>

                <router-link
                    to="/admin/purchases/create"
                    class="btn btn-primary"
                >
                    + {{ $t('purchases.new_purchase') }}
                </router-link>
            </div>
        </div>

        <!-- Metric Summary Cards -->
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
                            <div class="text-muted small fw-medium">{{ $t('reports.stock_purchases') }}</div>
                            <h4 class="fw-bold mb-0">₹{{ Number(summary.total_amount).toLocaleString() }}</h4>
                            <div class="small text-muted mt-1">{{ summary.invoice_count }} {{ $t('common.total') }}</div>
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
                            <div class="text-muted small fw-medium">{{ $t('common.paid') }}</div>
                            <h4 class="fw-bold text-success mb-0">₹{{ Number(summary.total_paid).toLocaleString() }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.completed') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-danger-subtle text-danger rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('reports.supplier_payables') }}</div>
                            <h4 class="fw-bold text-danger mb-0">₹{{ Number(summary.total_due).toLocaleString() }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.unpaid') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info-subtle text-info rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm5.5 1.5v2a1 1 0 0 0 1 1h2l-3-3zM4.5 9a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zM4 11.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ $t('dashboard.sales_overview') }}</div>
                            <h4 class="fw-bold mb-0">{{ summary.invoice_count }}</h4>
                            <div class="small text-muted mt-1">{{ $t('common.total') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input
                            v-model="search"
                            type="text"
                            class="form-control"
                            :placeholder="$t('common.search') + '...'"
                            @input="debouncedLoad"
                        />
                    </div>

                    <div class="col-md-3">
                        <select
                            v-model="supplierFilter"
                            class="form-select"
                            @change="loadPurchases(1)"
                        >
                            <option value="">-- {{ $t('purchases.supplier_name') }} ({{ $t('common.all') }}) --</option>
                            <option
                                v-for="sup in suppliersList"
                                :key="sup.id"
                                :value="sup.id"
                            >
                                {{ sup.name }} {{ sup.company_name ? `(${sup.company_name})` : '' }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select
                            v-model="statusFilter"
                            class="form-select"
                            @change="loadPurchases(1)"
                        >
                            <option value="">-- {{ $t('common.status') }} ({{ $t('common.all') }}) --</option>
                            <option value="received">{{ $t('common.completed') }}</option>
                            <option value="pending">{{ $t('common.pending') }}</option>
                            <option value="ordered">{{ $t('orders.title') }}</option>
                            <option value="cancelled">{{ $t('common.cancelled') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select
                            v-model="paymentStatusFilter"
                            class="form-select"
                            @change="loadPurchases(1)"
                        >
                            <option value="">-- {{ $t('orders.payment_status') }} ({{ $t('common.all') }}) --</option>
                            <option value="paid">{{ $t('common.paid') }}</option>
                            <option value="partial">{{ $t('common.partial') }}</option>
                            <option value="unpaid">{{ $t('common.unpaid') }}</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-light w-100" @click="resetFilters" :title="$t('common.clear')">
                            {{ $t('common.clear') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>{{ $t('common.date') }}</th>
                            <th>{{ $t('purchases.purchase_number') }}</th>
                            <th>{{ $t('purchases.supplier_name') }}</th>
                            <th>{{ $t('common.status') }}</th>
                            <th>{{ $t('orders.payment_status') }}</th>
                            <th class="text-end">{{ $t('common.grand_total') }}</th>
                            <th class="text-end">{{ $t('common.paid') }}</th>
                            <th class="text-end">{{ $t('reports.supplier_payables') }}</th>
                            <th class="text-center" width="160">{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="loading">
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                {{ $t('common.loading') }}
                            </td>
                        </tr>

                        <tr v-else-if="purchases.length === 0">
                            <td colspan="10" class="text-center py-5 text-muted">
                                {{ $t('common.no_data') }}
                            </td>
                        </tr>

                        <tr v-for="(purchase, index) in purchases" :key="purchase.id">
                            <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>

                            <td class="small text-muted">
                                {{ formatDate(purchase.purchase_date) }}
                            </td>

                            <td>
                                <div class="fw-semibold text-primary cursor-pointer" @click="viewInvoice(purchase.id)">
                                    {{ purchase.purchase_number }}
                                </div>
                                <small class="text-muted" v-if="purchase.reference_no">
                                    Ref: {{ purchase.reference_no }}
                                </small>
                            </td>

                            <td>
                                <div class="fw-medium text-dark">
                                    {{ purchase.supplier?.name || '-' }}
                                </div>
                                <small class="text-muted" v-if="purchase.supplier?.company_name">
                                    {{ purchase.supplier.company_name }}
                                </small>
                            </td>

                            <td>
                                <span class="badge" :class="statusBadgeClass(purchase.status)">
                                    {{ purchase.status.toUpperCase() }}
                                </span>
                            </td>

                            <td>
                                <span class="badge" :class="paymentBadgeClass(purchase.payment_status)">
                                    {{ purchase.payment_status.toUpperCase() }}
                                </span>
                            </td>

                            <td class="text-end fw-bold">
                                ₹{{ Number(purchase.total_amount).toFixed(2) }}
                            </td>

                            <td class="text-end text-success fw-semibold">
                                ₹{{ Number(purchase.paid_amount).toFixed(2) }}
                            </td>

                            <td class="text-end fw-semibold" :class="Number(purchase.due_amount) > 0 ? 'text-danger' : 'text-muted'">
                                ₹{{ Number(purchase.due_amount).toFixed(2) }}
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button
                                        class="btn btn-outline-primary"
                                        @click="viewInvoice(purchase.id)"
                                        :title="$t('common.view')"
                                    >
                                        {{ $t('common.view') }}
                                    </button>

                                    <button
                                        class="btn btn-outline-danger"
                                        @click="deletePurchase(purchase)"
                                        :title="$t('common.delete')"
                                    >
                                        {{ $t('common.delete') }}
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
                    {{ $t('common.showing') }} {{ purchases.length }} {{ $t('common.of') }} {{ totalPurchases }}
                </span>

                <div v-if="lastPage > 1">
                    <button
                        class="btn btn-sm btn-outline-secondary me-1"
                        :disabled="currentPage <= 1 || loading"
                        @click="loadPurchases(currentPage - 1)"
                    >
                        &laquo; {{ $t('common.previous') }}
                    </button>

                    <span class="px-2 small text-muted">
                        Page {{ currentPage }} of {{ lastPage }}
                    </span>

                    <button
                        class="btn btn-sm btn-outline-secondary ms-1"
                        :disabled="currentPage >= lastPage || loading"
                        @click="loadPurchases(currentPage + 1)"
                    >
                        Next &raquo;
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: VIEW PURCHASE INVOICE -->
        <!-- ============================================================ -->
        <div class="modal fade" id="invoiceModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom-0 pb-0 d-print-none">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4" id="printablePurchaseInvoice" v-if="currentInvoice">
                        <!-- Invoice Header -->
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                            <div>
                                <h4 class="fw-bold text-primary mb-1">{{ $t('receipt.purchase_invoice') }}</h4>
                                <div class="fs-6 fw-semibold">{{ currentInvoice.purchase_number }}</div>
                                <div class="small text-muted" v-if="currentInvoice.reference_no">
                                    {{ $t('receipt.supplier_ref') }}: {{ currentInvoice.reference_no }}
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="mb-1">
                                    <span class="badge me-1" :class="statusBadgeClass(currentInvoice.status)">
                                        {{ currentInvoice.status.toUpperCase() }}
                                    </span>
                                    <span class="badge" :class="paymentBadgeClass(currentInvoice.payment_status)">
                                        {{ currentInvoice.payment_status.toUpperCase() }}
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    {{ $t('common.date') }}: <strong>{{ formatDate(currentInvoice.purchase_date) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier & Store details -->
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <div class="text-muted small text-uppercase fw-bold mb-1">{{ $t('receipt.supplier_details') }}</div>
                                <div class="fw-bold fs-6">{{ currentInvoice.supplier?.name || 'Walk-in / Cash Supplier' }}</div>
                                <div class="text-muted small" v-if="currentInvoice.supplier?.company_name">
                                    {{ currentInvoice.supplier.company_name }}
                                </div>
                                <div class="text-muted small" v-if="currentInvoice.supplier?.phone">
                                    {{ $t('checkout.phone') }}: {{ currentInvoice.supplier.phone }}
                                </div>
                                <div class="text-muted small" v-if="currentInvoice.supplier?.gst_number">
                                    GST/Tax ID: {{ currentInvoice.supplier.gst_number }}
                                </div>
                            </div>

                            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                                <div class="text-muted small text-uppercase fw-bold mb-1">{{ $t('receipt.payment_mode') }}</div>
                                <div class="fw-semibold">{{ formatPaymentMethod(currentInvoice.payment_method) }}</div>
                                <div class="text-muted small" v-if="currentInvoice.creator">
                                    {{ $t('receipt.recorded_by') }}: {{ currentInvoice.creator.name }}
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ $t('products.product_name') }}</th>
                                        <th>{{ $t('common.sku') }}</th>
                                        <th class="text-end">{{ $t('products.purchase_price') }}</th>
                                        <th class="text-center">{{ $t('receipt.qty') }}</th>
                                        <th class="text-end">{{ $t('common.tax') }}</th>
                                        <th class="text-end">{{ $t('common.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, idx) in currentInvoice.items" :key="item.id">
                                        <td>{{ idx + 1 }}</td>
                                        <td class="fw-medium">{{ item.product?.name || '-' }}</td>
                                        <td class="small text-muted">{{ item.variant?.sku || '-' }}</td>
                                        <td class="text-end">₹{{ Number(item.unit_cost).toFixed(2) }}</td>
                                        <td class="text-center fw-bold">
                                            {{ item.quantity }}
                                            <small class="text-muted fw-normal">{{ item.product?.unit?.short_name || '' }}</small>
                                        </td>
                                        <td class="text-end small">
                                            ₹{{ Number(item.tax_amount).toFixed(2) }}
                                            <span class="text-muted">({{ item.tax_rate }}%)</span>
                                        </td>
                                        <td class="text-end fw-bold">₹{{ Number(item.total).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Totals -->
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">{{ $t('common.subtotal') }}:</span>
                                    <span>₹{{ Number(currentInvoice.subtotal).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-1" v-if="Number(currentInvoice.tax_amount) > 0">
                                    <span class="text-muted">{{ $t('common.tax') }}:</span>
                                    <span>+ ₹{{ Number(currentInvoice.tax_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-1" v-if="Number(currentInvoice.shipping_cost) > 0">
                                    <span class="text-muted">{{ $t('receipt.shipping_charges') }}:</span>
                                    <span>+ ₹{{ Number(currentInvoice.shipping_cost).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2" v-if="Number(currentInvoice.discount_amount) > 0">
                                    <span class="text-muted">{{ $t('common.discount') }}:</span>
                                    <span class="text-danger">- ₹{{ Number(currentInvoice.discount_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-2 mb-2 fw-bold fs-5">
                                    <span>{{ $t('common.grand_total') }}:</span>
                                    <span class="text-primary">₹{{ Number(currentInvoice.total_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between text-success mb-1">
                                    <span>{{ $t('receipt.amount_tendered') }}:</span>
                                    <span class="fw-semibold">₹{{ Number(currentInvoice.paid_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between" :class="Number(currentInvoice.due_amount) > 0 ? 'text-danger fw-bold' : 'text-muted'">
                                    <span>{{ $t('purchases.due_amount') || 'Balance Due' }}:</span>
                                    <span>₹{{ Number(currentInvoice.due_amount).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="alert alert-light border mt-4 mb-0 small" v-if="currentInvoice.notes">
                            <strong>{{ $t('receipt.notes_remarks') }}:</strong> {{ currentInvoice.notes }}
                        </div>
                    </div>

                    <div class="modal-footer d-print-none">
                        <button type="button" class="btn btn-outline-secondary" @click="printInvoice">
                            🖨️ {{ $t('receipt.print_invoice') }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ $t('common.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: SUPPLIERS MANAGEMENT -->
        <!-- ============================================================ -->
        <div class="modal fade" id="suppliersModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Supplier Directory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Add Supplier Inline Form -->
                        <div class="card bg-light border-0 mb-4 p-3">
                            <h6 class="fw-bold mb-3">{{ editingSupplierId ? 'Edit Supplier' : '+ Add New Supplier' }}</h6>
                            <form @submit.prevent="saveSupplier">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input
                                            v-model="supplierForm.name"
                                            type="text"
                                            class="form-control"
                                            placeholder="Supplier Name *"
                                            required
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <input
                                            v-model="supplierForm.name_ar"
                                            type="text"
                                            dir="rtl"
                                            class="form-control"
                                            placeholder="اسم المورد بالعربية"
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <input
                                            v-model="supplierForm.company_name"
                                            type="text"
                                            class="form-control"
                                            placeholder="Company Name"
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <input
                                            v-model="supplierForm.company_name_ar"
                                            type="text"
                                            dir="rtl"
                                            class="form-control"
                                            placeholder="اسم الشركة بالعربية"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <input
                                            v-model="supplierForm.phone"
                                            type="text"
                                            class="form-control"
                                            placeholder="Phone Number"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <input
                                            v-model="supplierForm.email"
                                            type="email"
                                            class="form-control"
                                            placeholder="Email"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <input
                                            v-model="supplierForm.gst_number"
                                            type="text"
                                            class="form-control"
                                            placeholder="GST / Tax ID"
                                        />
                                    </div>
                                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light"
                                            v-if="editingSupplierId"
                                            @click="resetSupplierForm"
                                        >
                                            Cancel Edit
                                        </button>
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-primary"
                                            :disabled="savingSupplier"
                                        >
                                            {{ savingSupplier ? 'Saving...' : (editingSupplierId ? 'Update Supplier' : 'Save Supplier') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Suppliers Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th class="text-center" width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="suppliersList.length === 0">
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No suppliers registered yet.
                                        </td>
                                    </tr>
                                    <tr v-for="sup in suppliersList" :key="sup.id">
                                        <td class="fw-semibold">
                                            <div>{{ sup.name }}</div>
                                            <div v-if="sup.name_ar" class="small text-muted" dir="rtl">{{ sup.name_ar }}</div>
                                        </td>
                                        <td class="text-muted">
                                            <div>{{ sup.company_name || '-' }}</div>
                                            <div v-if="sup.company_name_ar" class="extra-small text-muted" dir="rtl">{{ sup.company_name_ar }}</div>
                                        </td>
                                        <td>{{ sup.phone || '-' }}</td>
                                        <td>{{ sup.email || '-' }}</td>
                                        <td class="text-center">
                                            <button
                                                class="btn btn-sm btn-link text-primary p-0 me-2"
                                                @click="editSupplier(sup)"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                class="btn btn-sm btn-link text-danger p-0"
                                                @click="deleteSupplier(sup.id)"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { Modal } from 'bootstrap';
import api from '../../services/api';

const { t } = useI18n();

// Purchases state
const purchases = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const totalPurchases = ref(0);

// Filters
const search = ref('');
const supplierFilter = ref('');
const statusFilter = ref('');
const paymentStatusFilter = ref('');

// Summary metrics
const summary = reactive({
    total_amount: 0,
    total_paid: 0,
    total_due: 0,
    invoice_count: 0,
});

// Suppliers list for dropdowns
const suppliersList = ref([]);

// Modals
let invoiceModal = null;
let suppliersModal = null;
const currentInvoice = ref(null);

// Supplier Form
const editingSupplierId = ref(null);
const savingSupplier = ref(false);
const supplierForm = reactive({
    name: '',
    company_name: '',
    phone: '',
    email: '',
    gst_number: '',
    address: '',
    status: true,
});

let debounceTimer = null;

// -------------------------------------------------------------------------
// Loaders
// -------------------------------------------------------------------------
async function loadSummary() {
    try {
        const res = await api.get('/purchases/summary');
        Object.assign(summary, res.data);
    } catch (err) {
        console.error('Failed to load summary:', err);
    }
}

async function loadSuppliers() {
    try {
        const res = await api.get('/suppliers');
        suppliersList.value = res.data.data || res.data || [];
    } catch (err) {
        console.error('Failed to load suppliers:', err);
    }
}

async function loadPurchases(page = 1) {
    loading.value = true;
    try {
        const res = await api.get('/purchases', {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
                supplier_id: supplierFilter.value,
                status: statusFilter.value,
                payment_status: paymentStatusFilter.value,
            }
        });

        purchases.value = res.data.data || [];
        currentPage.value = res.data.current_page || 1;
        lastPage.value = res.data.last_page || 1;
        totalPurchases.value = res.data.total || 0;

    } catch (err) {
        console.error('Failed to load purchases:', err);
    } finally {
        loading.value = false;
    }
}

function debouncedLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        loadPurchases(1);
    }, 350);
}

function refreshData() {
    loadSummary();
    loadPurchases(currentPage.value);
    loadSuppliers();
}

function resetFilters() {
    search.value = '';
    supplierFilter.value = '';
    statusFilter.value = '';
    paymentStatusFilter.value = '';
    loadPurchases(1);
}

// -------------------------------------------------------------------------
// Invoice View & Delete
// -------------------------------------------------------------------------
async function viewInvoice(id) {
    try {
        const res = await api.get(`/purchases/${id}`);
        currentInvoice.value = res.data.data;
        invoiceModal?.show();
    } catch (err) {
        alert('Failed to load invoice details.');
    }
}

function printInvoice() {
    document.body.classList.add('printing-purchase-invoice');
    window.print();
    setTimeout(() => {
        document.body.classList.remove('printing-purchase-invoice');
    }, 1500);
}

async function deletePurchase(purchase) {
    const msg = purchase.status === 'received'
        ? `Are you sure you want to delete ${purchase.purchase_number}? This will also reverse the received inventory stock.`
        : `Are you sure you want to delete purchase order ${purchase.purchase_number}?`;

    if (!confirm(msg)) return;

    try {
        await api.delete(`/purchases/${purchase.id}`);
        alert('Purchase order deleted successfully.');
        refreshData();
    } catch (err) {
        alert(err.response?.data?.message || 'Unable to delete purchase.');
    }
}

// -------------------------------------------------------------------------
// Supplier Management Modal
// -------------------------------------------------------------------------
function openSuppliersModal() {
    resetSupplierForm();
    suppliersModal?.show();
}

function resetSupplierForm() {
    editingSupplierId.value = null;
    Object.assign(supplierForm, {
        name: '',
        name_ar: '',
        company_name: '',
        company_name_ar: '',
        phone: '',
        email: '',
        gst_number: '',
        address: '',
        status: true,
    });
}

function editSupplier(sup) {
    editingSupplierId.value = sup.id;
    Object.assign(supplierForm, {
        name: sup.name,
        name_ar: sup.name_ar || '',
        company_name: sup.company_name || '',
        company_name_ar: sup.company_name_ar || '',
        phone: sup.phone || '',
        email: sup.email || '',
        gst_number: sup.gst_number || '',
        address: sup.address || '',
        status: Boolean(sup.status),
    });
}

async function saveSupplier() {
    savingSupplier.value = true;
    try {
        if (editingSupplierId.value) {
            await api.put(`/suppliers/${editingSupplierId.value}`, supplierForm);
        } else {
            await api.post('/suppliers', supplierForm);
        }
        resetSupplierForm();
        loadSuppliers();
    } catch (err) {
        alert(err.response?.data?.message || 'Error saving supplier.');
    } finally {
        savingSupplier.value = false;
    }
}

async function deleteSupplier(id) {
    if (!confirm('Are you sure you want to delete this supplier?')) return;
    try {
        await api.delete(`/suppliers/${id}`);
        loadSuppliers();
    } catch (err) {
        alert(err.response?.data?.message || 'Unable to delete supplier.');
    }
}

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatPaymentMethod(method) {
    const map = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        upi: 'UPI / Online',
        cheque: 'Cheque',
        card: 'Card',
        other: 'Other'
    };
    return map[method] || method;
}

function statusBadgeClass(status) {
    if (status === 'received') return 'bg-success';
    if (status === 'pending') return 'bg-warning text-dark';
    if (status === 'ordered') return 'bg-info text-dark';
    return 'bg-secondary';
}

function paymentBadgeClass(status) {
    if (status === 'paid') return 'bg-success';
    if (status === 'partial') return 'bg-warning text-dark';
    return 'bg-danger';
}

// -------------------------------------------------------------------------
// Lifecycle
// -------------------------------------------------------------------------
onMounted(() => {
    const invEl = document.getElementById('invoiceModal');
    if (invEl) invoiceModal = new Modal(invEl);

    const supEl = document.getElementById('suppliersModal');
    if (supEl) suppliersModal = new Modal(supEl);

    loadSummary();
    loadSuppliers();
    loadPurchases(1);
});

onBeforeUnmount(() => {
    invoiceModal?.dispose();
    suppliersModal?.dispose();
    clearTimeout(debounceTimer);
});
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Printable Purchase Invoice */
@media print {
    body.printing-purchase-invoice * {
        visibility: hidden !important;
    }
    body.printing-purchase-invoice #printablePurchaseInvoice,
    body.printing-purchase-invoice #printablePurchaseInvoice * {
        visibility: visible !important;
    }
    body.printing-purchase-invoice #printablePurchaseInvoice {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
    }
    html[dir="rtl"] body.printing-purchase-invoice #printablePurchaseInvoice {
        direction: rtl !important;
        text-align: right !important;
        font-family: 'Noto Sans Arabic', Tahoma, sans-serif !important;
    }
    html[dir="rtl"] body.printing-purchase-invoice #printablePurchaseInvoice table th,
    html[dir="rtl"] body.printing-purchase-invoice #printablePurchaseInvoice table td {
        text-align: right !important;
    }
    html[dir="rtl"] body.printing-purchase-invoice #printablePurchaseInvoice table th.text-end,
    html[dir="rtl"] body.printing-purchase-invoice #printablePurchaseInvoice table td.text-end {
        text-align: left !important;
    }
}
</style>

