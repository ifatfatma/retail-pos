<template>
    <div class="pos-container">

        <!-- Top Status Bar -->
        <div class="pos-header bg-white border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <span class="me-1">🛒</span> POS Counter
                </h5>
                <span class="badge bg-light text-secondary border">
                    Cash Register Active
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Held Bills button if any -->
                <button
                    v-if="heldBills.length > 0"
                    class="btn btn-sm btn-outline-warning"
                    @click="openHeldBillsModal"
                >
                    Held Bills ({{ heldBills.length }})
                </button>

                <!-- Today's Recent Sales History -->
                <button
                    class="btn btn-sm btn-outline-secondary"
                    @click="openHistoryModal"
                >
                    Today's Sales
                </button>

                <!-- Fullscreen toggle -->
                <button
                    class="btn btn-sm btn-outline-secondary"
                    @click="toggleFullscreen"
                    title="Toggle Fullscreen"
                >
                    {{ isFullscreen ? 'Exit Fullscreen' : 'Fullscreen' }}
                </button>
            </div>
        </div>

        <!-- POS Workspace -->
        <div class="pos-body row g-0">

            <!-- ======================================================== -->
            <!-- LEFT PANEL: PRODUCT CATALOG (65% width on large screens) -->
            <!-- ======================================================== -->
            <div class="col-lg-7 col-xl-8 p-3 pos-catalog border-end">

                <!-- Search & Barcode input -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                            </span>
                            <input
                                ref="barcodeInput"
                                v-model="searchQuery"
                                type="text"
                                class="form-control border-start-0 py-2"
                                placeholder="Scan barcode or type product name / SKU (Press Enter)..."
                                @input="debouncedSearch"
                                @keydown.enter.prevent="onSearchEnter"
                            />
                            <button
                                v-if="searchQuery"
                                class="btn btn-outline-secondary"
                                type="button"
                                @click="clearSearch"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Category Filters (Horizontal scroll pills) -->
                <div class="category-pills-wrap mb-3">
                    <button
                        class="btn btn-sm rounded-pill me-2 px-3"
                        :class="selectedCategory === '' ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="selectCategory('')"
                    >
                        All Items
                    </button>

                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        class="btn btn-sm rounded-pill me-2 px-3 text-nowrap"
                        :class="selectedCategory === cat.id ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="selectCategory(cat.id)"
                    >
                        {{ cat.name }}
                    </button>
                </div>

                <!-- Products Grid -->
                <div class="product-grid-container">
                    <div v-if="loadingProducts" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Loading catalog...
                    </div>

                    <div v-else-if="products.length === 0" class="text-center py-5 text-muted">
                        No products match your search or category filter.
                    </div>

                    <div v-else class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-4 g-3">
                        <div
                            class="col"
                            v-for="prod in products"
                            :key="prod.id"
                        >
                            <div
                                class="card h-100 product-card border-0 shadow-sm"
                                :class="{ 'out-of-stock-card': prod.track_inventory && prod.stock <= 0 }"
                                @click="addToCart(prod)"
                            >
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-light text-dark border small">
                                                {{ prod.category_name }}
                                            </span>

                                            <!-- Stock status badge -->
                                            <span
                                                v-if="prod.track_inventory"
                                                class="badge"
                                                :class="prod.stock <= 0 ? 'bg-danger' : (prod.stock <= prod.low_stock_threshold ? 'bg-warning text-dark' : 'bg-success-subtle text-success border border-success-subtle')"
                                            >
                                                {{ prod.stock }} {{ prod.unit?.short_name || '' }}
                                            </span>
                                        </div>

                                        <h6 class="fw-bold product-name mb-1 text-dark" :title="prod.name">
                                            {{ prod.name }}
                                        </h6>

                                        <div class="text-muted small fw-mono mb-2">
                                            {{ prod.variant?.sku || '-' }}
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end mt-2 pt-2 border-top">
                                        <div>
                                            <div class="fs-5 fw-bold text-primary">
                                                ₹{{ Number(prod.variant?.selling_price || 0).toFixed(2) }}
                                            </div>
                                            <small
                                                class="text-muted text-decoration-line-through"
                                                v-if="prod.variant?.mrp && prod.variant.mrp > prod.variant.selling_price"
                                            >
                                                ₹{{ Number(prod.variant.mrp).toFixed(2) }}
                                            </small>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary rounded-circle add-btn"
                                            :disabled="prod.track_inventory && prod.stock <= 0"
                                            title="Add to cart"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ======================================================== -->
            <!-- RIGHT PANEL: CART & CHECKOUT TERMINAL (35% width)        -->
            <!-- ======================================================== -->
            <div class="col-lg-5 col-xl-4 pos-checkout bg-white d-flex flex-column">

                <!-- Customer Selection Header -->
                <div class="p-3 border-bottom bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-0">Customer</label>
                        <button
                            class="btn btn-link btn-sm p-0 text-decoration-none"
                            @click="openQuickCustomerModal"
                        >
                            + Quick Add
                        </button>
                    </div>

                    <select v-model="selectedCustomerId" class="form-select form-select-sm">
                        <option
                            v-for="cust in customers"
                            :key="cust.id"
                            :value="cust.id"
                        >
                            {{ cust.name }} {{ cust.phone ? `(${cust.phone})` : '' }}
                        </option>
                    </select>
                </div>

                <!-- Cart Items Container -->
                <div class="cart-items-wrap flex-grow-1 p-3">
                    <div v-if="cart.length === 0" class="text-center py-5 text-muted">
                        <div class="fs-1 mb-2">🛒</div>
                        <div class="fw-semibold">Register Cart is Empty</div>
                        <small>Click products or scan barcodes to begin bill.</small>
                    </div>

                    <div v-else class="cart-list">
                        <div
                            class="cart-row mb-2 p-2 rounded border bg-light d-flex justify-content-between align-items-center"
                            v-for="(item, idx) in cart"
                            :key="item.variant_id"
                        >
                            <div class="flex-grow-1 me-2 overflow-hidden">
                                <div class="fw-semibold text-truncate small" :title="item.name">
                                    {{ item.name }}
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <span>₹{{ Number(item.unit_price).toFixed(2) }}</span>
                                    <span class="mx-1">&times;</span>
                                    <span class="badge bg-white text-dark border py-0 px-1">
                                        {{ item.unit_name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Quantity Stepper -->
                            <div class="d-flex align-items-center me-2">
                                <button
                                    class="btn btn-sm btn-white border px-2 py-0 fw-bold"
                                    type="button"
                                    @click="decreaseQty(idx)"
                                >
                                    -
                                </button>
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    :step="item.allow_decimal ? '0.001' : '1'"
                                    min="0.001"
                                    class="form-control form-control-sm text-center px-1 mx-1 cart-qty-input"
                                    @change="validateQty(idx)"
                                />
                                <button
                                    class="btn btn-sm btn-white border px-2 py-0 fw-bold"
                                    type="button"
                                    @click="increaseQty(idx)"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Row Total & Remove -->
                            <div class="text-end">
                                <div class="fw-bold small">
                                    ₹{{ getItemTotal(item).toFixed(2) }}
                                </div>
                                <button
                                    class="btn btn-link text-danger p-0 small text-decoration-none"
                                    @click="removeFromCart(idx)"
                                    title="Remove item"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Action Bar (Clear & Hold buttons) -->
                <div class="px-3 py-2 border-top border-bottom bg-light d-flex justify-content-between align-items-center" v-if="cart.length > 0">
                    <span class="small text-muted">
                        {{ cart.length }} items ({{ totalCartQty }} units)
                    </span>

                    <div class="btn-group btn-group-sm">
                        <button
                            class="btn btn-outline-warning"
                            @click="holdCurrentBill"
                            title="Pause bill to serve next customer"
                        >
                            ⏸️ Hold
                        </button>
                        <button
                            class="btn btn-outline-danger"
                            @click="clearCart"
                            title="Clear entire cart"
                        >
                            🗑️ Clear
                        </button>
                    </div>
                </div>

                <!-- Financial Totals Box -->
                <div class="p-3 border-bottom bg-white">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Subtotal:</span>
                        <span>₹{{ cartSubtotal.toFixed(2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mb-1" v-if="cartTaxTotal > 0">
                        <span>Tax:</span>
                        <span>+ ₹{{ cartTaxTotal.toFixed(2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Discount:</span>
                        <div class="input-group input-group-sm w-50">
                            <span class="input-group-text">₹</span>
                            <input
                                v-model.number="discountAmount"
                                type="number"
                                min="0"
                                step="1"
                                class="form-control text-end"
                            />
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="fw-bold fs-6">Grand Total:</span>
                        <span class="fw-bold fs-4 text-primary">₹{{ cartGrandTotal.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Payment Details Section -->
                <div class="p-3 bg-light-subtle">
                    <!-- Payment Method Tabs -->
                    <div class="btn-group w-100 mb-2" role="group">
                        <input
                            type="radio"
                            class="btn-check"
                            name="payMethod"
                            id="payCash"
                            value="cash"
                            v-model="paymentMethod"
                        />
                        <label class="btn btn-sm btn-outline-primary" for="payCash">
                            💵 Cash
                        </label>

                        <input
                            type="radio"
                            class="btn-check"
                            name="payMethod"
                            id="payUPI"
                            value="upi"
                            v-model="paymentMethod"
                        />
                        <label class="btn btn-sm btn-outline-primary" for="payUPI">
                            📱 UPI / QR
                        </label>

                        <input
                            type="radio"
                            class="btn-check"
                            name="payMethod"
                            id="payCard"
                            value="card"
                            v-model="paymentMethod"
                        />
                        <label class="btn btn-sm btn-outline-primary" for="payCard">
                            💳 Card
                        </label>
                    </div>

                    <!-- Quick Cash Buttons (Only when cash selected) -->
                    <div v-if="paymentMethod === 'cash'" class="mb-2">
                        <div class="d-flex gap-1 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-light border py-1 flex-grow-1 small"
                                @click="paidAmount = cartGrandTotal"
                            >
                                Exact
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-light border py-1 flex-grow-1 small"
                                @click="setCashReceived(50)"
                            >
                                ₹50
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-light border py-1 flex-grow-1 small"
                                @click="setCashReceived(100)"
                            >
                                ₹100
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-light border py-1 flex-grow-1 small"
                                @click="setCashReceived(200)"
                            >
                                ₹200
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-light border py-1 flex-grow-1 small"
                                @click="setCashReceived(500)"
                            >
                                ₹500
                            </button>
                        </div>
                    </div>

                    <!-- Tendered Cash & Return Amount -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Tendered (₹)</label>
                            <input
                                v-model.number="paidAmount"
                                type="number"
                                min="0"
                                step="1"
                                class="form-control form-control-sm text-end fw-bold"
                            />
                        </div>

                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Change Return (₹)</label>
                            <div class="form-control form-control-sm text-end fw-bold bg-success-subtle text-success border-success-subtle">
                                ₹{{ changeDue.toFixed(2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button
                        type="button"
                        class="btn btn-success w-100 py-3 fw-bold fs-6 shadow-sm"
                        :disabled="cart.length === 0 || checkingOut"
                        @click="processCheckout"
                    >
                        <span v-if="checkingOut" class="spinner-border spinner-border-sm me-2"></span>
                        {{ checkingOut ? 'Processing...' : `Pay ₹${cartGrandTotal.toFixed(2)} & Print Receipt` }}
                    </button>
                </div>

            </div>

        </div>

        <!-- ============================================================ -->
        <!-- MODAL: PRINTABLE RECEIPT                                     -->
        <!-- ============================================================ -->
        <div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-body p-3" id="printableThermalReceipt">
                        <div class="receipt-wrap text-center font-monospace">
                            <h5 class="fw-bold mb-0">RETAIL POS STORE</h5>
                            <div class="small text-muted">Point of Sale Cash Receipt</div>
                            <div class="small">Phone: 9876543210</div>

                            <div class="border-top border-bottom my-2 py-1 text-start small">
                                <div><strong>Invoice:</strong> {{ lastSale?.invoice_number }}</div>
                                <div><strong>Date:</strong> {{ formatDateTime(lastSale?.sale_date) }}</div>
                                <div><strong>Customer:</strong> {{ lastSale?.customer?.name || 'Walk-in Customer' }}</div>
                            </div>

                            <table class="table table-borderless table-sm small text-start my-2">
                                <thead>
                                    <tr class="border-bottom">
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in lastSale?.items" :key="item.id">
                                        <td class="text-truncate" style="max-width: 120px;">
                                            {{ item.product?.name || '-' }}
                                        </td>
                                        <td class="text-center">{{ item.quantity }}</td>
                                        <td class="text-end">₹{{ Number(item.total).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="border-top pt-2 small text-end">
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span>₹{{ Number(lastSale?.subtotal || 0).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between" v-if="Number(lastSale?.tax_amount) > 0">
                                    <span>Tax:</span>
                                    <span>₹{{ Number(lastSale?.tax_amount).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between" v-if="Number(lastSale?.discount_amount) > 0">
                                    <span>Discount:</span>
                                    <span>-₹{{ Number(lastSale?.discount_amount).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-6 border-top my-1 pt-1">
                                    <span>Total:</span>
                                    <span>₹{{ Number(lastSale?.total_amount || 0).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Paid ({{ lastSale?.payment_method?.toUpperCase() }}):</span>
                                    <span>₹{{ Number(lastSale?.paid_amount || 0).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-success fw-bold" v-if="Number(lastSale?.change_amount) > 0">
                                    <span>Change:</span>
                                    <span>₹{{ Number(lastSale?.change_amount).toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="border-top mt-3 pt-2 small text-center text-muted">
                                Thank You For Shopping With Us!<br>
                                Please Visit Again
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer p-2 justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="printReceipt">
                            🖨️ Print Receipt
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" @click="closeReceiptAndReset">
                            New Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: QUICK ADD CUSTOMER                                    -->
        <!-- ============================================================ -->
        <div class="modal fade" id="quickCustomerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form @submit.prevent="createCustomer">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">+ Add Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer Name *</label>
                                <input
                                    v-model="newCustomer.name"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter full name"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input
                                    v-model="newCustomer.phone"
                                    type="text"
                                    class="form-control"
                                    placeholder="Mobile / Contact number"
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input
                                    v-model="newCustomer.email"
                                    type="email"
                                    class="form-control"
                                    placeholder="Email"
                                />
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="savingCustomer">
                                {{ savingCustomer ? 'Saving...' : 'Save & Select' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: HELD BILLS                                            -->
        <!-- ============================================================ -->
        <div class="modal fade" id="heldBillsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Parked / Held Bills</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div v-if="heldBills.length === 0" class="text-center py-4 text-muted">
                            No bills currently on hold.
                        </div>

                        <div class="list-group list-group-flush" v-else>
                            <div
                                class="list-group-item d-flex justify-content-between align-items-center"
                                v-for="(bill, i) in heldBills"
                                :key="i"
                            >
                                <div>
                                    <div class="fw-bold">{{ bill.customerName }}</div>
                                    <small class="text-muted">
                                        {{ bill.cart.length }} items | Held at: {{ bill.heldAt }}
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-primary">₹{{ bill.grandTotal.toFixed(2) }}</span>
                                    <button
                                        class="btn btn-sm btn-primary"
                                        @click="restoreHeldBill(i)"
                                    >
                                        Resume
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: TODAY'S POS SALES HISTORY                             -->
        <!-- ============================================================ -->
        <div class="modal fade" id="historyModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Today's POS Sales History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div v-if="salesHistory.length === 0" class="text-center py-5 text-muted">
                            No sales registered yet today.
                        </div>

                        <div class="table-responsive" v-else>
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="sale in salesHistory" :key="sale.id">
                                        <td class="text-muted">{{ formatTime(sale.sale_date) }}</td>
                                        <td class="fw-bold">{{ sale.invoice_number }}</td>
                                        <td>{{ sale.customer?.name || 'Walk-in' }}</td>
                                        <td>{{ sale.items?.length || 0 }} items</td>
                                        <td class="fw-bold text-primary">₹{{ Number(sale.total_amount).toFixed(2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ sale.payment_method.toUpperCase() }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Modal } from 'bootstrap';
import api from '../../services/api';

// Catalog & Categories
const products = ref([]);
const categories = ref([]);
const selectedCategory = ref('');
const searchQuery = ref('');
const loadingProducts = ref(false);
const barcodeInput = ref(null);
let searchDebounceTimer = null;

// Customer State
const customers = ref([]);
const selectedCustomerId = ref(1);

// Cart State
const cart = ref([]);
const discountAmount = ref(0);
const paidAmount = ref(0);
const paymentMethod = ref('cash');
const checkingOut = ref(false);

// Held Bills & Sales History
const heldBills = ref([]);
const salesHistory = ref([]);
const lastSale = ref(null);
const isFullscreen = ref(false);

// Modals
let receiptModal = null;
let quickCustomerModal = null;
let heldBillsModal = null;
let historyModal = null;

// Quick Customer Form
const savingCustomer = ref(false);
const newCustomer = reactive({
    name: '',
    phone: '',
    email: '',
});

// -------------------------------------------------------------------------
// Computed Totals
// -------------------------------------------------------------------------
const totalCartQty = computed(() => {
    return cart.value.reduce((acc, item) => acc + Number(item.quantity || 0), 0);
});

const cartSubtotal = computed(() => {
    return cart.value.reduce((acc, item) => {
        const qty = Number(item.quantity || 0);
        const price = Number(item.unit_price || 0);
        return acc + (qty * price);
    }, 0);
});

const cartTaxTotal = computed(() => {
    return cart.value.reduce((acc, item) => {
        const qty = Number(item.quantity || 0);
        const price = Number(item.unit_price || 0);
        const rate = Number(item.tax_rate || 0);
        return acc + (qty * price * (rate / 100));
    }, 0);
});

const cartGrandTotal = computed(() => {
    const disc = Number(discountAmount.value || 0);
    const total = cartSubtotal.value + cartTaxTotal.value - disc;
    return Math.max(0, total);
});

const changeDue = computed(() => {
    const paid = Number(paidAmount.value || 0);
    return Math.max(0, paid - cartGrandTotal.value);
});

function getItemTotal(item) {
    const qty = Number(item.quantity || 0);
    const price = Number(item.unit_price || 0);
    const rate = Number(item.tax_rate || 0);
    const sub = qty * price;
    return sub + (sub * (rate / 100));
}

// -------------------------------------------------------------------------
// Data Fetching
// -------------------------------------------------------------------------
async function loadCategories() {
    try {
        const res = await api.get('/categories');
        categories.value = res.data.data || res.data || [];
    } catch (err) {
        console.error('Failed to load categories:', err);
    }
}

async function loadCustomers() {
    try {
        const res = await api.get('/customers/all');
        customers.value = res.data.data || [];
        if (customers.value.length > 0 && !selectedCustomerId.value) {
            selectedCustomerId.value = customers.value[0].id;
        }
    } catch (err) {
        console.error('Failed to load customers:', err);
    }
}

async function loadProducts() {
    loadingProducts.value = true;
    try {
        const res = await api.get('/pos/products', {
            params: {
                search: searchQuery.value,
                category_id: selectedCategory.value,
                limit: 50,
            }
        });
        products.value = res.data.data || [];
    } catch (err) {
        console.error('Failed to load POS products:', err);
    } finally {
        loadingProducts.value = false;
    }
}

function debouncedSearch() {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        loadProducts();
    }, 300);
}

function clearSearch() {
    searchQuery.value = '';
    loadProducts();
    focusBarcodeInput();
}

function selectCategory(catId) {
    selectedCategory.value = catId;
    loadProducts();
}

// Barcode scanner auto-add on Enter
function onSearchEnter() {
    if (!searchQuery.value.trim()) return;

    const query = searchQuery.value.trim().toLowerCase();
    const exactMatch = products.value.find(p =>
        (p.variant?.barcode && p.variant.barcode.toLowerCase() === query) ||
        (p.variant?.sku && p.variant.sku.toLowerCase() === query)
    );

    if (exactMatch) {
        addToCart(exactMatch);
        searchQuery.value = '';
        loadProducts();
    } else if (products.value.length === 1) {
        addToCart(products.value[0]);
        searchQuery.value = '';
        loadProducts();
    }
}

// -------------------------------------------------------------------------
// Cart Operations
// -------------------------------------------------------------------------
function addToCart(prod) {
    if (!prod.variant) return;

    if (prod.track_inventory && prod.stock <= 0) {
        alert(`Cannot add '${prod.name}': Item is out of stock.`);
        return;
    }

    const existingIndex = cart.value.findIndex(item => item.variant_id === prod.variant.id);

    if (existingIndex > -1) {
        const item = cart.value[existingIndex];
        const nextQty = item.quantity + 1;

        if (prod.track_inventory && nextQty > prod.stock) {
            alert(`Maximum available stock reached for '${prod.name}' (${prod.stock} units available).`);
            return;
        }
        item.quantity = nextQty;
    } else {
        cart.value.push({
            product_id: prod.id,
            variant_id: prod.variant.id,
            name: prod.name,
            sku: prod.variant.sku,
            unit_price: Number(prod.variant.selling_price),
            tax_rate: Number(prod.variant.tax_rate || 0),
            unit_name: prod.unit?.short_name || 'pcs',
            allow_decimal: Boolean(prod.unit?.allow_decimal),
            track_inventory: Boolean(prod.track_inventory),
            available_stock: Number(prod.stock),
            quantity: 1,
        });
    }

    // Auto update paid amount to match new total if previously exact
    syncPaidAmount();
}

function increaseQty(idx) {
    const item = cart.value[idx];
    if (item.track_inventory && item.quantity + 1 > item.available_stock) {
        alert(`Only ${item.available_stock} units available in stock.`);
        return;
    }
    item.quantity += 1;
    syncPaidAmount();
}

function decreaseQty(idx) {
    const item = cart.value[idx];
    if (item.quantity > 1) {
        item.quantity -= 1;
    } else {
        removeFromCart(idx);
    }
    syncPaidAmount();
}

function validateQty(idx) {
    const item = cart.value[idx];
    if (item.quantity <= 0) {
        removeFromCart(idx);
        return;
    }
    if (item.track_inventory && item.quantity > item.available_stock) {
        alert(`Only ${item.available_stock} units available in stock.`);
        item.quantity = item.available_stock;
    }
    syncPaidAmount();
}

function removeFromCart(idx) {
    cart.value.splice(idx, 1);
    syncPaidAmount();
}

function clearCart() {
    if (cart.value.length === 0) return;
    if (confirm('Clear all items from current register?')) {
        cart.value = [];
        discountAmount.value = 0;
        paidAmount.value = 0;
        focusBarcodeInput();
    }
}

function syncPaidAmount() {
    nextTick(() => {
        paidAmount.value = cartGrandTotal.value;
    });
}

function setCashReceived(amt) {
    paidAmount.value = amt;
}

// -------------------------------------------------------------------------
// Hold / Park Bills
// -------------------------------------------------------------------------
function holdCurrentBill() {
    if (cart.value.length === 0) return;

    const customerObj = customers.value.find(c => c.id === selectedCustomerId.value);

    heldBills.value.push({
        customer_id: selectedCustomerId.value,
        customerName: customerObj?.name || 'Walk-in',
        cart: JSON.parse(JSON.stringify(cart.value)),
        discount: discountAmount.value,
        grandTotal: cartGrandTotal.value,
        heldAt: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    });

    cart.value = [];
    discountAmount.value = 0;
    paidAmount.value = 0;
    alert('Bill paused and parked. You can now serve the next customer.');
}

function openHeldBillsModal() {
    heldBillsModal?.show();
}

function restoreHeldBill(idx) {
    const bill = heldBills.value[idx];
    if (cart.value.length > 0 && !confirm('Discard or overwrite current active cart with held bill?')) {
        return;
    }

    selectedCustomerId.value = bill.customer_id;
    cart.value = bill.cart;
    discountAmount.value = bill.discount;
    syncPaidAmount();

    heldBills.value.splice(idx, 1);
    heldBillsModal?.hide();
}

// -------------------------------------------------------------------------
// Checkout Process
// -------------------------------------------------------------------------
async function processCheckout() {
    if (cart.value.length === 0) return;

    if (paidAmount.value < cartGrandTotal.value && paymentMethod.value === 'cash') {
        if (!confirm(`Paid amount (₹${paidAmount.value}) is less than total (₹${cartGrandTotal.value}). Proceed anyway?`)) {
            return;
        }
    }

    checkingOut.value = true;

    try {
        const payload = {
            customer_id: selectedCustomerId.value,
            payment_method: paymentMethod.value,
            paid_amount: Number(paidAmount.value || cartGrandTotal.value),
            discount_amount: Number(discountAmount.value || 0),
            items: cart.value.map(item => ({
                product_id: item.product_id,
                product_variant_id: item.variant_id,
                quantity: Number(item.quantity),
                unit_price: Number(item.unit_price),
                tax_rate: Number(item.tax_rate || 0),
            })),
        };

        const res = await api.post('/pos/checkout', payload);
        lastSale.value = res.data.data;

        // Open Printable Receipt
        receiptModal?.show();

        // Refresh stock catalog
        loadProducts();

    } catch (err) {
        alert(err.response?.data?.message || 'Checkout failed. Please check stock and amounts.');
    } finally {
        checkingOut.value = false;
    }
}

function printReceipt() {
    window.print();
}

function closeReceiptAndReset() {
    receiptModal?.hide();
    cart.value = [];
    discountAmount.value = 0;
    paidAmount.value = 0;
    lastSale.value = null;
    focusBarcodeInput();
}

// -------------------------------------------------------------------------
// Quick Customer Modal
// -------------------------------------------------------------------------
function openQuickCustomerModal() {
    Object.assign(newCustomer, { name: '', phone: '', email: '' });
    quickCustomerModal?.show();
}

async function createCustomer() {
    savingCustomer.value = true;
    try {
        const res = await api.post('/customers', newCustomer);
        const created = res.data.data;
        customers.value.push(created);
        selectedCustomerId.value = created.id;
        quickCustomerModal?.hide();
    } catch (err) {
        alert(err.response?.data?.message || 'Error adding customer.');
    } finally {
        savingCustomer.value = false;
    }
}

// -------------------------------------------------------------------------
// Sales History Modal
// -------------------------------------------------------------------------
async function openHistoryModal() {
    try {
        const res = await api.get('/pos/history');
        salesHistory.value = res.data.data || [];
        historyModal?.show();
    } catch (err) {
        alert('Failed to load recent sales history.');
    }
}

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------
function focusBarcodeInput() {
    nextTick(() => {
        barcodeInput.value?.focus();
    });
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        isFullscreen.value = true;
    } else if (document.exitFullscreen) {
        document.exitFullscreen();
        isFullscreen.value = false;
    }
}

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

function formatTime(str) {
    if (!str) return '-';
    return new Date(str).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}

// -------------------------------------------------------------------------
// Lifecycle
// -------------------------------------------------------------------------
onMounted(() => {
    const rcEl = document.getElementById('receiptModal');
    if (rcEl) receiptModal = new Modal(rcEl);

    const qcEl = document.getElementById('quickCustomerModal');
    if (qcEl) quickCustomerModal = new Modal(qcEl);

    const hbEl = document.getElementById('heldBillsModal');
    if (hbEl) heldBillsModal = new Modal(hbEl);

    const hsEl = document.getElementById('historyModal');
    if (hsEl) historyModal = new Modal(hsEl);

    loadCategories();
    loadCustomers();
    loadProducts();
    focusBarcodeInput();
});

onBeforeUnmount(() => {
    receiptModal?.dispose();
    quickCustomerModal?.dispose();
    heldBillsModal?.dispose();
    historyModal?.dispose();
    clearTimeout(searchDebounceTimer);
});
</script>

<style scoped>
.pos-container {
    height: calc(100vh - 60px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #f5f7fb;
}

.pos-body {
    flex: 1;
    min-height: 0;
}

.pos-catalog {
    height: 100%;
    overflow-y: auto;
}

.pos-checkout {
    height: 100%;
    overflow-y: auto;
}

.category-pills-wrap {
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 4px;
}

.product-card {
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    border-radius: 10px;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.product-name {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 38px;
    font-size: 13px;
}

.add-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    line-height: 30px;
    font-size: 18px;
}

.out-of-stock-card {
    opacity: 0.55;
    cursor: not-allowed;
}

.cart-items-wrap {
    overflow-y: auto;
    max-height: 320px;
}

.cart-qty-input {
    width: 48px;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Receipt Print Styling */
@media print {
    body * {
        visibility: hidden;
    }
    #printableThermalReceipt, #printableThermalReceipt * {
        visibility: visible;
    }
    #printableThermalReceipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>

