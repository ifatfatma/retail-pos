<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">Online Orders</h3>
                <p class="text-muted mb-0">
                    Track, dispatch and deliver orders from your web storefront, WhatsApp & delivery channels
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary"
                    @click="refreshData"
                    :disabled="loading"
                    title="Refresh"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    Refresh
                </button>

                <button
                    class="btn btn-primary"
                    @click="openCreateModal"
                >
                    + New Online Order
                </button>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">Total Online Revenue</div>
                            <h4 class="fw-bold mb-0">₹{{ Number(summary.total_revenue).toLocaleString() }}</h4>
                            <div class="small text-muted mt-1">{{ summary.total_orders }} total orders</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100 cursor-pointer"
                    :class="{ 'border-2 border-warning': statusFilter === 'pending' }"
                    @click="setStatusFilter('pending')"
                >
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.5-12a.5.5 0 0 0-1 0v4.793l3.146 3.147a.5.5 0 0 0 .708-.708L8.5 8.293V4z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">Pending Action</div>
                            <h4 class="fw-bold text-warning mb-0">{{ summary.pending_count }}</h4>
                            <div class="small text-muted mt-1">Needs confirmation</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100 cursor-pointer"
                    :class="{ 'border-2 border-info': statusFilter === 'processing' }"
                    @click="setStatusFilter('processing')"
                >
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info-subtle text-info rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">In Packing / Prep</div>
                            <h4 class="fw-bold text-info mb-0">{{ summary.processing_count }}</h4>
                            <div class="small text-muted mt-1">Confirmed & processing</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div
                    class="card border-0 shadow-sm h-100 cursor-pointer"
                    :class="{ 'border-2 border-primary': statusFilter === 'shipped' }"
                    @click="setStatusFilter('shipped')"
                >
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0 1 1 0 0 1-1-1V3.5zM11 5V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5V10c0 .324.154.614.39.8A1.99 1.99 0 0 1 3 10.5c.376 0 .726.104 1.026.286A1.5 1.5 0 0 1 5 11h5a1.5 1.5 0 0 1 .974-.214A1.99 1.99 0 0 1 12 10.5c.376 0 .726.104 1.026.286A.5.5 0 0 0 14.5 10V8h-2.5A1.5 1.5 0 0 1 10.5 6.5V5H11z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">Out for Delivery</div>
                            <h4 class="fw-bold text-primary mb-0">{{ summary.shipped_count }}</h4>
                            <div class="small text-muted mt-1">In transit with courier</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Stage Filter Pills -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'all' ? 'btn-dark' : 'btn-light border'"
                @click="setStatusFilter('all')"
            >
                All Orders ({{ summary.total_orders }})
            </button>
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-light border'"
                @click="setStatusFilter('pending')"
            >
                Pending ({{ summary.pending_count }})
            </button>
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'processing' ? 'btn-primary' : 'btn-light border'"
                @click="setStatusFilter('processing')"
            >
                Processing ({{ summary.processing_count }})
            </button>
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'shipped' ? 'btn-indigo text-white' : 'btn-light border'"
                @click="setStatusFilter('shipped')"
                style="background-color: #6610f2; border-color: #6610f2;"
                v-if="statusFilter === 'shipped'"
            >
                Shipped ({{ summary.shipped_count }})
            </button>
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'shipped' ? 'btn-light border' : 'btn-light border'"
                @click="setStatusFilter('shipped')"
                v-else
            >
                Shipped ({{ summary.shipped_count }})
            </button>
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'delivered' ? 'btn-success' : 'btn-light border'"
                @click="setStatusFilter('delivered')"
            >
                Delivered ({{ summary.delivered_count }})
            </button>
            <button
                class="btn btn-sm rounded-pill px-3"
                :class="statusFilter === 'cancelled' ? 'btn-danger' : 'btn-light border'"
                @click="setStatusFilter('cancelled')"
            >
                Cancelled ({{ summary.cancelled_count }})
            </button>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input
                            v-model="search"
                            type="text"
                            class="form-control"
                            placeholder="Search by order #, customer name, phone, or tracking..."
                            @input="debouncedLoad"
                        />
                    </div>

                    <div class="col-md-3">
                        <select
                            v-model="channelFilter"
                            class="form-select"
                            @change="loadOrders(1)"
                        >
                            <option value="all">All Channels</option>
                            <option value="web">Web Store</option>
                            <option value="whatsapp">WhatsApp Order</option>
                            <option value="phone">Phone / Direct</option>
                            <option value="app">Mobile App</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select
                            v-model="paymentStatusFilter"
                            class="form-select"
                            @change="loadOrders(1)"
                        >
                            <option value="all">All Payment Statuses</option>
                            <option value="paid">Paid (Pre-paid / Settled)</option>
                            <option value="unpaid">Unpaid / Cash on Delivery (COD)</option>
                            <option value="partial">Partially Paid</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-light w-100" @click="resetFilters" title="Reset filters">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Selection Action Bar -->
        <div v-if="selectedOrderIds.length > 0" class="alert alert-dark d-flex flex-wrap align-items-center justify-content-between p-3 mb-3 rounded-3 shadow-sm border-0">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary fs-6">{{ selectedOrderIds.length }}</span>
                <span class="fw-semibold">order(s) selected</span>
                <span class="text-white-50 small d-none d-sm-inline">| You can print shipping labels for all selected orders at once.</span>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                <button
                    type="button"
                    class="btn btn-warning btn-sm fw-bold d-flex align-items-center gap-1 shadow-sm rounded-pill px-3"
                    @click="printSelectedOrdersLabels"
                >
                    <span>🏷️</span>
                    <span>Print Shipping Labels ({{ selectedOrderIds.length }})</span>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-light btn-sm rounded-pill px-3"
                    @click="selectedOrderIds = []"
                >
                    Clear Selection
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input cursor-pointer"
                                    :checked="isAllSelected"
                                    @change="toggleSelectAll"
                                    title="Select / Deselect all orders on this page"
                                />
                            </th>
                            <th width="50">#</th>
                            <th>Order Details</th>
                            <th>Customer & Address</th>
                            <th>Items Summary</th>
                            <th class="text-end">Total Amount</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th class="text-center" width="240">Workflow Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="loading">
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                Loading orders...
                            </td>
                        </tr>

                        <tr v-else-if="orders.length === 0">
                            <td colspan="9" class="text-center py-5 text-muted">
                                No online orders found matching current criteria.
                            </td>
                        </tr>

                        <tr v-for="(order, index) in orders" :key="order.id" :class="{ 'table-active': selectedOrderIds.includes(order.id) }">
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input cursor-pointer"
                                    :value="order.id"
                                    v-model="selectedOrderIds"
                                />
                            </td>
                            <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>

                            <td>
                                <div
                                    class="fw-bold text-primary cursor-pointer"
                                    @click="openOrderDetails(order)"
                                >
                                    {{ order.invoice_number }}
                                </div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    <span class="badge" :class="channelBadgeClass(order.channel)">
                                        {{ channelLabel(order.channel) }}
                                    </span>
                                    <small class="text-muted">{{ formatDateTime(order.sale_date) }}</small>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ order.customer?.name || 'Guest Customer' }}
                                </div>
                                <div class="text-muted small">
                                    <span v-if="order.customer?.phone">📞 {{ order.customer.phone }}</span>
                                </div>
                                <small class="text-muted text-truncate d-block" style="max-width: 220px;" :title="order.shipping_address">
                                    📍 {{ order.shipping_address || 'No address provided' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ order.items?.length || 0 }} items
                                </span>
                                <div class="small text-muted text-truncate mt-1" style="max-width: 180px;">
                                    {{ getItemsSummaryText(order.items) }}
                                </div>
                            </td>

                            <td class="text-end">
                                <div class="fw-bold fs-6">
                                    ₹{{ Number(order.total_amount).toFixed(2) }}
                                </div>
                                <small class="text-muted" v-if="Number(order.shipping_cost) > 0">
                                    (incl. ₹{{ Number(order.shipping_cost).toFixed(2) }} ship)
                                </small>
                            </td>

                            <td>
                                <span class="badge" :class="paymentBadgeClass(order.payment_status)">
                                    {{ order.payment_status.toUpperCase() }}
                                </span>
                                <div class="small text-muted mt-1 text-uppercase">
                                    {{ order.payment_method }}
                                </div>
                            </td>

                            <td>
                                <span class="badge" :class="statusBadgeClass(order.status)">
                                    {{ statusLabel(order.status) }}
                                </span>
                                <div class="small text-muted mt-1" v-if="order.tracking_number">
                                    Track: <span class="fw-mono">{{ order.tracking_number }}</span>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <!-- Contextual Next Step Workflow Button -->
                                    <button
                                        v-if="order.status === 'pending'"
                                        class="btn btn-outline-success"
                                        @click="advanceStatus(order, 'confirmed')"
                                        title="Confirm order"
                                    >
                                        ✔ Accept
                                    </button>

                                    <button
                                        v-else-if="order.status === 'confirmed'"
                                        class="btn btn-outline-primary"
                                        @click="advanceStatus(order, 'processing')"
                                        title="Start packing"
                                    >
                                        📦 Pack
                                    </button>

                                    <button
                                        v-else-if="order.status === 'processing'"
                                        class="btn btn-outline-info"
                                        @click="openDispatchModal(order)"
                                        title="Dispatch with courier"
                                    >
                                        🚚 Dispatch
                                    </button>

                                    <button
                                        v-else-if="order.status === 'shipped'"
                                        class="btn btn-outline-success"
                                        @click="advanceStatus(order, 'delivered')"
                                        title="Mark as delivered"
                                    >
                                        ✅ Delivered
                                    </button>

                                    <!-- Details & Packing Slip Modal -->
                                    <button
                                        class="btn btn-outline-secondary"
                                        @click="openOrderDetails(order)"
                                        title="View Details"
                                    >
                                        View
                                    </button>

                                    <!-- Quick Shipping Label Print -->
                                    <button
                                        class="btn btn-outline-dark"
                                        @click="printSingleOrderLabel(order)"
                                        title="Print Shipping Label (4x6 Thermal Sticker)"
                                    >
                                        🏷️ Label
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
                    Showing {{ orders.length }} of {{ totalOrders }} orders
                </span>

                <div v-if="lastPage > 1">
                    <button
                        class="btn btn-sm btn-outline-secondary me-1"
                        :disabled="currentPage <= 1 || loading"
                        @click="loadOrders(currentPage - 1)"
                    >
                        &laquo; Prev
                    </button>

                    <span class="px-2 small text-muted">
                        Page {{ currentPage }} of {{ lastPage }}
                    </span>

                    <button
                        class="btn btn-sm btn-outline-secondary ms-1"
                        :disabled="currentPage >= lastPage || loading"
                        @click="loadOrders(currentPage + 1)"
                    >
                        Next &raquo;
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: ORDER DETAILS & DISPATCH WORKFLOW                     -->
        <!-- ============================================================ -->
        <div class="modal fade" id="orderDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow" v-if="selectedOrder">
                    <div class="modal-header pb-0 border-bottom-0">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold">{{ selectedOrder.invoice_number }}</h5>
                                <span class="badge" :class="channelBadgeClass(selectedOrder.channel)">
                                    {{ channelLabel(selectedOrder.channel) }}
                                </span>
                            </div>
                            <div class="small text-muted">
                                Placed on {{ formatDateTime(selectedOrder.sale_date) }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4" id="printablePackingSlip">
                        <!-- Workflow Status Stepper Bar -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-uppercase text-muted">Order Lifecycle Status</span>
                                <span class="badge" :class="statusBadgeClass(selectedOrder.status)">
                                    {{ statusLabel(selectedOrder.status) }}
                                </span>
                            </div>

                            <div class="btn-group w-100" role="group">
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    :class="selectedOrder.status === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'"
                                    @click="updateStatus(selectedOrder, 'pending')"
                                >
                                    1. Pending
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    :class="selectedOrder.status === 'confirmed' ? 'btn-info text-white fw-bold' : 'btn-outline-secondary'"
                                    @click="updateStatus(selectedOrder, 'confirmed')"
                                >
                                    2. Confirmed
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    :class="selectedOrder.status === 'processing' ? 'btn-primary fw-bold' : 'btn-outline-secondary'"
                                    @click="updateStatus(selectedOrder, 'processing')"
                                >
                                    3. Packing
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    :class="selectedOrder.status === 'shipped' ? 'btn-indigo text-white fw-bold' : 'btn-outline-secondary'"
                                    style="background-color: selectedOrder.status === 'shipped' ? '#6610f2' : ''"
                                    @click="updateStatus(selectedOrder, 'shipped')"
                                >
                                    4. Shipped
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    :class="selectedOrder.status === 'delivered' ? 'btn-success fw-bold' : 'btn-outline-secondary'"
                                    @click="updateStatus(selectedOrder, 'delivered')"
                                >
                                    5. Delivered
                                </button>
                            </div>

                            <div class="d-flex justify-content-end mt-2" v-if="selectedOrder.status !== 'cancelled'">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-link text-danger p-0"
                                    @click="cancelOrder(selectedOrder)"
                                >
                                    Cancel Order & Return Stock
                                </button>
                            </div>
                        </div>

                        <!-- Customer & Shipping info -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="card h-100 border p-3">
                                    <h6 class="fw-bold mb-2">👤 Customer Information</h6>
                                    <div><strong>Name:</strong> {{ selectedOrder.customer?.name || 'Walk-in / Guest' }}</div>
                                    <div v-if="selectedOrder.customer?.phone"><strong>Phone:</strong> {{ selectedOrder.customer.phone }}</div>
                                    <div v-if="selectedOrder.customer?.email"><strong>Email:</strong> {{ selectedOrder.customer.email }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="card h-100 border p-3">
                                    <h6 class="fw-bold mb-2">📍 Shipping & Delivery Destination</h6>
                                    <div class="text-dark">{{ selectedOrder.shipping_address || 'No address provided' }}</div>

                                    <div class="mt-2 pt-2 border-top small" v-if="selectedOrder.delivery_partner || selectedOrder.tracking_number">
                                        <div><strong>Courier Partner:</strong> {{ selectedOrder.delivery_partner || 'In-house' }}</div>
                                        <div><strong>Tracking #:</strong> <span class="fw-mono">{{ selectedOrder.tracking_number || '-' }}</span></div>
                                        <div v-if="selectedOrder.estimated_delivery_date">
                                            <strong>Est. Delivery:</strong> {{ selectedOrder.estimated_delivery_date }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light small">
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>SKU</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Tax</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, idx) in selectedOrder.items" :key="item.id">
                                        <td>{{ idx + 1 }}</td>
                                        <td class="fw-semibold">{{ item.product?.name || '-' }}</td>
                                        <td class="small text-muted">{{ item.variant?.sku || '-' }}</td>
                                        <td class="text-center fw-bold">
                                            {{ item.quantity }}
                                            <small class="text-muted fw-normal">{{ item.product?.unit?.short_name || '' }}</small>
                                        </td>
                                        <td class="text-end">₹{{ Number(item.unit_price).toFixed(2) }}</td>
                                        <td class="text-end small">₹{{ Number(item.tax_amount).toFixed(2) }}</td>
                                        <td class="text-end fw-bold">₹{{ Number(item.total).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Financials & Payment summary -->
                        <div class="row justify-content-between align-items-start">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="card bg-light border-0 p-3">
                                    <h6 class="fw-bold mb-2">Payment Details</h6>
                                    <div><strong>Method:</strong> <span class="text-uppercase">{{ selectedOrder.payment_method }}</span></div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <strong>Status:</strong>
                                        <span class="badge" :class="paymentBadgeClass(selectedOrder.payment_status)">
                                            {{ selectedOrder.payment_status.toUpperCase() }}
                                        </span>
                                    </div>

                                    <!-- Quick Mark as Paid Button if Unpaid/COD -->
                                    <button
                                        v-if="selectedOrder.payment_status !== 'paid'"
                                        type="button"
                                        class="btn btn-sm btn-outline-success mt-2"
                                        @click="markOrderPaid(selectedOrder)"
                                    >
                                        ✔ Mark Payment Received (₹{{ selectedOrder.total_amount }})
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Subtotal:</span>
                                    <span>₹{{ Number(selectedOrder.subtotal).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-1" v-if="Number(selectedOrder.tax_amount) > 0">
                                    <span class="text-muted">Taxes:</span>
                                    <span>+ ₹{{ Number(selectedOrder.tax_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Shipping Charges:</span>
                                    <span>+ ₹{{ Number(selectedOrder.shipping_cost || 0).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2" v-if="Number(selectedOrder.discount_amount) > 0">
                                    <span class="text-muted">Discount:</span>
                                    <span class="text-danger">- ₹{{ Number(selectedOrder.discount_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-2 fw-bold fs-5 text-primary">
                                    <span>Total:</span>
                                    <span>₹{{ Number(selectedOrder.total_amount).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <div>
                            <button
                                type="button"
                                class="btn btn-outline-info me-2"
                                @click="openDispatchModal(selectedOrder)"
                            >
                                🚚 Update Tracking
                            </button>
                            <button
                                type="button"
                                class="btn btn-dark me-2"
                                @click="printSingleOrderLabel(selectedOrder)"
                            >
                                🏷️ Print Label
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="printPackingSlip"
                            >
                                🖨️ Print Slip
                            </button>
                        </div>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: UPDATE COURIER & TRACKING                             -->
        <!-- ============================================================ -->
        <div class="modal fade" id="dispatchModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form @submit.prevent="submitTracking">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">🚚 Dispatch & Shipping Tracking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Delivery Partner / Courier *</label>
                                <select v-model="trackingForm.delivery_partner" class="form-select" required>
                                    <option value="Delhivery">Delhivery</option>
                                    <option value="BlueDart">BlueDart</option>
                                    <option value="FedEx">FedEx</option>
                                    <option value="DTDC">DTDC</option>
                                    <option value="Dunzo / Porter">Dunzo / Porter (Local Rider)</option>
                                    <option value="In-house Store Delivery">In-house Store Rider</option>
                                    <option value="India Post">India Post / Speed Post</option>
                                    <option value="Other">Other Courier</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tracking / AWB Number *</label>
                                <input
                                    v-model="trackingForm.tracking_number"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter tracking ID or courier reference number"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Estimated Delivery Date</label>
                                <input
                                    v-model="trackingForm.estimated_delivery_date"
                                    type="date"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-check form-switch">
                                <input
                                    v-model="trackingForm.auto_ship"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="autoShipSwitch"
                                />
                                <label class="form-check-label" for="autoShipSwitch">
                                    Automatically mark order as <strong>"Shipped"</strong>
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="savingTracking">
                                {{ savingTracking ? 'Saving...' : 'Save & Update Tracking' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: CREATE MANUAL ONLINE ORDER                            -->
        <!-- ============================================================ -->
        <div class="modal fade" id="createOrderModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form @submit.prevent="submitCreateOrder">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">+ Create Online / Delivery Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Customer Details -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Customer Name *</label>
                                    <input
                                        v-model="newOrderForm.customer_name"
                                        type="text"
                                        class="form-control"
                                        placeholder="Full name"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Customer Phone *</label>
                                    <input
                                        v-model="newOrderForm.customer_phone"
                                        type="text"
                                        class="form-control"
                                        placeholder="Mobile phone"
                                        required
                                    />
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Delivery Address *</label>
                                    <textarea
                                        v-model="newOrderForm.shipping_address"
                                        class="form-control"
                                        rows="2"
                                        placeholder="Complete street address, apartment, city, and pincode"
                                        required
                                    ></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order Channel</label>
                                    <select v-model="newOrderForm.channel" class="form-select">
                                        <option value="whatsapp">WhatsApp Order</option>
                                        <option value="web">Web Store</option>
                                        <option value="phone">Phone Call</option>
                                        <option value="app">Mobile App</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Payment Method</label>
                                    <select v-model="newOrderForm.payment_method" class="form-select">
                                        <option value="cod">Cash on Delivery (COD)</option>
                                        <option value="upi">Prepaid UPI / QR</option>
                                        <option value="card">Prepaid Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Payment Status</label>
                                    <select v-model="newOrderForm.payment_status" class="form-select">
                                        <option value="unpaid">Unpaid (Collect on delivery)</option>
                                        <option value="paid">Paid (Prepaid)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Product Search to Add to Order -->
                            <div class="card bg-light border-0 p-3 mb-3">
                                <label class="form-label fw-semibold">Select Products to Add</label>
                                <div class="input-group mb-2">
                                    <input
                                        v-model="modalProductSearch"
                                        type="text"
                                        class="form-control"
                                        placeholder="Search product to add..."
                                        @input="debouncedModalProductSearch"
                                    />
                                </div>

                                <div v-if="modalProductResults.length > 0" class="list-group mb-2 border">
                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        v-for="p in modalProductResults"
                                        :key="p.id"
                                        @click="addModalItem(p)"
                                    >
                                        <div>
                                            <strong>{{ p.name }}</strong>
                                            <span class="text-muted small ms-2">(Stock: {{ p.stock }})</span>
                                        </div>
                                        <div class="fw-bold text-primary">
                                            ₹{{ p.variant?.selling_price }} + Add
                                        </div>
                                    </button>
                                </div>

                                <!-- Items List -->
                                <table class="table table-sm table-bordered bg-white" v-if="newOrderForm.items.length > 0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th width="100">Qty</th>
                                            <th width="120">Price</th>
                                            <th width="120" class="text-end">Total</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(it, i) in newOrderForm.items" :key="i">
                                            <td>{{ it.name }}</td>
                                            <td>
                                                <input
                                                    v-model.number="it.quantity"
                                                    type="number"
                                                    min="1"
                                                    class="form-control form-control-sm text-center"
                                                />
                                            </td>
                                            <td>₹{{ it.unit_price }}</td>
                                            <td class="text-end fw-bold">₹{{ (it.quantity * it.unit_price).toFixed(2) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="newOrderForm.items.splice(i, 1)">&times;</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Financial details -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted">Shipping Fee (₹)</label>
                                    <input
                                        v-model.number="newOrderForm.shipping_cost"
                                        type="number"
                                        min="0"
                                        class="form-control form-control-sm"
                                    />
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Discount (₹)</label>
                                    <input
                                        v-model.number="newOrderForm.discount_amount"
                                        type="number"
                                        min="0"
                                        class="form-control form-control-sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="creatingOrder || newOrderForm.items.length === 0"
                            >
                                {{ creatingOrder ? 'Creating Order...' : 'Place Online Order' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Shipping Label Print Modal Component -->
        <ShippingLabelModal
            :is-open="isLabelModalOpen"
            :orders="labelOrders"
            @close="isLabelModalOpen = false"
        />

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { Modal } from 'bootstrap';
import api from '../../services/api';
import { useNotification } from '../../composables/useNotification';
import ShippingLabelModal from '../../components/orders/ShippingLabelModal.vue';

const { toast, confirmDialog } = useNotification();

// Bulk Selection & Shipping Label State
const selectedOrderIds = ref([]);
const isLabelModalOpen = ref(false);
const labelOrders = ref([]);

const isAllSelected = computed(() => {
    return orders.value.length > 0 && selectedOrderIds.value.length === orders.value.length;
});

function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedOrderIds.value = orders.value.map(o => o.id);
    } else {
        selectedOrderIds.value = [];
    }
}

function printSingleOrderLabel(order) {
    if (!order) return;
    labelOrders.value = [order];
    isLabelModalOpen.value = true;
}

function printSelectedOrdersLabels() {
    if (selectedOrderIds.value.length === 0) return;
    const selected = orders.value.filter(o => selectedOrderIds.value.includes(o.id));
    labelOrders.value = selected;
    isLabelModalOpen.value = true;
}

// Orders state
const orders = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const totalOrders = ref(0);

// Filters
const search = ref('');
const statusFilter = ref('all');
const paymentStatusFilter = ref('all');
const channelFilter = ref('all');

// Summary state
const summary = reactive({
    total_orders: 0,
    total_revenue: 0,
    pending_count: 0,
    processing_count: 0,
    shipped_count: 0,
    delivered_count: 0,
    cancelled_count: 0,
});

// Modals
let orderDetailsModal = null;
let dispatchModal = null;
let createOrderModal = null;
const selectedOrder = ref(null);

// Tracking form state
const savingTracking = ref(false);
const trackingForm = reactive({
    delivery_partner: 'Delhivery',
    tracking_number: '',
    estimated_delivery_date: '',
    auto_ship: true,
});

// Create Order form state
const creatingOrder = ref(false);
const modalProductSearch = ref('');
const modalProductResults = ref([]);
let productSearchTimer = null;

const newOrderForm = reactive({
    customer_name: '',
    customer_phone: '',
    shipping_address: '',
    channel: 'whatsapp',
    payment_method: 'cod',
    payment_status: 'unpaid',
    shipping_cost: 0,
    discount_amount: 0,
    items: [],
});

let debounceTimer = null;

// -------------------------------------------------------------------------
// Loaders
// -------------------------------------------------------------------------
async function loadSummary() {
    try {
        const res = await api.get('/orders/summary');
        Object.assign(summary, res.data);
    } catch (err) {
        console.error('Failed to load orders summary:', err);
    }
}

async function loadOrders(page = 1) {
    loading.value = true;
    try {
        const res = await api.get('/orders', {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
                status: statusFilter.value,
                payment_status: paymentStatusFilter.value,
                channel: channelFilter.value,
            }
        });

        orders.value = res.data.data || [];
        currentPage.value = res.data.current_page || 1;
        lastPage.value = res.data.last_page || 1;
        totalOrders.value = res.data.total || 0;

    } catch (err) {
        console.error('Failed to load online orders:', err);
    } finally {
        loading.value = false;
    }
}

function debouncedLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        loadOrders(1);
    }, 350);
}

function refreshData() {
    loadSummary();
    loadOrders(currentPage.value);
}

function setStatusFilter(status) {
    statusFilter.value = status;
    loadOrders(1);
}

function resetFilters() {
    search.value = '';
    statusFilter.value = 'all';
    paymentStatusFilter.value = 'all';
    channelFilter.value = 'all';
    loadOrders(1);
}

// -------------------------------------------------------------------------
// Order Status Workflow
// -------------------------------------------------------------------------
async function advanceStatus(order, targetStatus) {
    try {
        const res = await api.patch(`/orders/${order.id}/status`, { status: targetStatus });
        toast.success(res.data.message || 'Order status updated successfully.');
        refreshData();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Error updating order status.');
    }
}

async function updateStatus(order, targetStatus) {
    try {
        const res = await api.patch(`/orders/${order.id}/status`, { status: targetStatus });
        selectedOrder.value = res.data.data;
        toast.success(res.data.message || 'Order status updated.');
        refreshData();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Error updating order status.');
    }
}

async function cancelOrder(order) {
    const ok = await confirmDialog({
        title: 'Cancel Order?',
        message: `Are you sure you want to cancel order ${order.invoice_number}? All items will be returned to store stock automatically.`,
        confirmText: 'Yes, Cancel Order',
        type: 'danger',
    });
    if (!ok) return;

    try {
        const res = await api.patch(`/orders/${order.id}/status`, { status: 'cancelled' });
        toast.success('Order cancelled and inventory restored successfully.');
        selectedOrder.value = res.data.data;
        refreshData();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to cancel order.');
    }
}

async function markOrderPaid(order) {
    try {
        const res = await api.patch(`/orders/${order.id}/payment`, {
            payment_status: 'paid',
            paid_amount: order.total_amount,
        });
        selectedOrder.value.payment_status = 'paid';
        refreshData();
        toast.success('Payment recorded successfully.');
    } catch (err) {
        toast.error(err.response?.data?.message || 'Error recording payment.');
    }
}

// -------------------------------------------------------------------------
// Dispatch & Tracking Modal
// -------------------------------------------------------------------------
function openDispatchModal(order) {
    selectedOrder.value = order;
    trackingForm.delivery_partner = order.delivery_partner || 'Delhivery';
    trackingForm.tracking_number = order.tracking_number || '';
    trackingForm.estimated_delivery_date = order.estimated_delivery_date || '';
    trackingForm.auto_ship = true;
    dispatchModal?.show();
}

async function submitTracking() {
    if (!selectedOrder.value) return;

    savingTracking.value = true;
    try {
        const res = await api.patch(`/orders/${selectedOrder.value.id}/tracking`, trackingForm);
        selectedOrder.value = res.data.data;
        dispatchModal?.hide();
        refreshData();
        toast.success('Courier dispatch details updated successfully.');
    } catch (err) {
        toast.error(err.response?.data?.message || 'Error saving tracking details.');
    } finally {
        savingTracking.value = false;
    }
}

// -------------------------------------------------------------------------
// Create Manual Order
// -------------------------------------------------------------------------
function openCreateModal() {
    Object.assign(newOrderForm, {
        customer_name: '',
        customer_phone: '',
        shipping_address: '',
        channel: 'whatsapp',
        payment_method: 'cod',
        payment_status: 'unpaid',
        shipping_cost: 50,
        discount_amount: 0,
        items: [],
    });
    modalProductResults.value = [];
    modalProductSearch.value = '';
    createOrderModal?.show();
}

function debouncedModalProductSearch() {
    clearTimeout(productSearchTimer);
    productSearchTimer = setTimeout(async () => {
        if (!modalProductSearch.value.trim()) {
            modalProductResults.value = [];
            return;
        }
        try {
            const res = await api.get('/pos/products', { params: { search: modalProductSearch.value } });
            modalProductResults.value = res.data.data || [];
        } catch (err) {
            console.error(err);
        }
    }, 300);
}

function addModalItem(prod) {
    const existing = newOrderForm.items.find(i => i.product_variant_id === prod.variant?.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        newOrderForm.items.push({
            product_id: prod.id,
            product_variant_id: prod.variant?.id,
            name: prod.name,
            quantity: 1,
            unit_price: Number(prod.variant?.selling_price || 0),
            tax_rate: Number(prod.variant?.tax_rate || 0),
        });
    }
    modalProductSearch.value = '';
    modalProductResults.value = [];
}

async function submitCreateOrder() {
    if (newOrderForm.items.length === 0) {
        toast.warning('Please add at least one item to order.');
        return;
    }

    creatingOrder.value = true;
    try {
        const res = await api.post('/orders', newOrderForm);
        toast.success(res.data.message || 'Online order created successfully.');
        createOrderModal?.hide();
        refreshData();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Unable to create order. Please check stock levels.');
    } finally {
        creatingOrder.value = false;
    }
}

// -------------------------------------------------------------------------
// View Details & Print
// -------------------------------------------------------------------------
function openOrderDetails(order) {
    selectedOrder.value = order;
    orderDetailsModal?.show();
}

function printPackingSlip() {
    document.body.classList.add('printing-packing-slip');
    window.print();
    setTimeout(() => {
        document.body.classList.remove('printing-packing-slip');
    }, 1500);
}

// -------------------------------------------------------------------------
// Formatters & Badges
// -------------------------------------------------------------------------
function formatDateTime(str) {
    if (!str) return '-';
    return new Date(str).toLocaleString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getItemsSummaryText(items) {
    if (!items || items.length === 0) return 'No items';
    return items.map(i => `${i.product?.name || 'Item'} x${i.quantity}`).join(', ');
}

function channelLabel(channel) {
    const map = {
        web: 'Web Store',
        whatsapp: 'WhatsApp',
        phone: 'Phone Order',
        app: 'Mobile App',
    };
    return map[channel] || channel;
}

function channelBadgeClass(channel) {
    if (channel === 'whatsapp') return 'bg-success text-white';
    if (channel === 'web') return 'bg-primary text-white';
    if (channel === 'phone') return 'bg-secondary text-white';
    return 'bg-info text-dark';
}

function statusBadgeClass(status) {
    if (status === 'pending') return 'bg-warning text-dark';
    if (status === 'confirmed') return 'bg-info text-dark';
    if (status === 'processing') return 'bg-primary';
    if (status === 'shipped') return 'bg-indigo text-white';
    if (status === 'delivered') return 'bg-success';
    if (status === 'cancelled') return 'bg-danger';
    return 'bg-secondary';
}

function statusLabel(status) {
    if (status === 'pending') return 'Pending';
    if (status === 'confirmed') return 'Confirmed';
    if (status === 'processing') return 'Packing';
    if (status === 'shipped') return 'Out for Delivery';
    if (status === 'delivered') return 'Delivered';
    if (status === 'cancelled') return 'Cancelled';
    return status;
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
    const odEl = document.getElementById('orderDetailsModal');
    if (odEl) orderDetailsModal = new Modal(odEl);

    const dpEl = document.getElementById('dispatchModal');
    if (dpEl) dispatchModal = new Modal(dpEl);

    const coEl = document.getElementById('createOrderModal');
    if (coEl) createOrderModal = new Modal(coEl);

    loadSummary();
    loadOrders(1);
});

onBeforeUnmount(() => {
    orderDetailsModal?.dispose();
    dispatchModal?.dispose();
    createOrderModal?.dispose();
    clearTimeout(debounceTimer);
    clearTimeout(productSearchTimer);
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

.bg-indigo {
    background-color: #6610f2 !important;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Printable Packing Slip */
@media print {
    body.printing-packing-slip * {
        visibility: hidden !important;
    }
    body.printing-packing-slip #printablePackingSlip,
    body.printing-packing-slip #printablePackingSlip * {
        visibility: visible !important;
    }
    body.printing-packing-slip #printablePackingSlip {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
    }
}
</style>

