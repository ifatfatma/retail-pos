<template>
    <div class="container-fluid py-4">

        <!-- Top Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Create Purchase</h3>
                <p class="text-muted mb-0">Record a new purchase order and receive product stock</p>
            </div>

            <router-link to="/admin/purchases" class="btn btn-outline-secondary">
                &larr; Back to Purchases
            </router-link>
        </div>

        <!-- Alert messages -->
        <div v-if="errorMessage" class="alert alert-danger py-2 mb-3">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="submitPurchase">
            <div class="row g-4">

                <!-- LEFT COLUMN: Supplier, Items Table, Notes -->
                <div class="col-lg-8">

                    <!-- Basic Info Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Purchase & Supplier Information
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Supplier Selection -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Supplier <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select
                                            v-model="form.supplier_id"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">-- Select Supplier --</option>
                                            <option
                                                v-for="sup in suppliers"
                                                :key="sup.id"
                                                :value="sup.id"
                                            >
                                                {{ sup.name }} {{ sup.company_name ? `(${sup.company_name})` : '' }}
                                            </option>
                                        </select>
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            @click="openQuickSupplierModal"
                                            title="Add new supplier"
                                        >
                                            + New
                                        </button>
                                    </div>
                                </div>

                                <!-- Purchase Date -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        Purchase Date <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.purchase_date"
                                        type="date"
                                        class="form-control"
                                        required
                                    />
                                </div>

                                <!-- Purchase Status -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select v-model="form.status" class="form-select" required>
                                        <option value="received">Received (Add to Stock)</option>
                                        <option value="pending">Pending (Order Draft)</option>
                                        <option value="ordered">Ordered</option>
                                    </select>
                                </div>

                                <!-- Reference / Supplier Bill No -->
                                <div class="col-md-6">
                                    <label class="form-label">Supplier Bill / Invoice #</label>
                                    <input
                                        v-model="form.reference_no"
                                        type="text"
                                        class="form-control"
                                        placeholder="e.g. INV-89214"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                            <span>Purchase Items ({{ form.items.length }})</span>
                        </div>

                        <div class="card-body">
                            <!-- Product Search Bar -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Search & Add Products</label>
                                <div class="input-group">
                                    <input
                                        v-model="productSearch"
                                        type="text"
                                        class="form-control"
                                        placeholder="Search by product name, SKU or barcode to add..."
                                        @input="debouncedSearchProducts"
                                    />
                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        @click="searchProductsNow"
                                    >
                                        Search
                                    </button>
                                </div>

                                <!-- Search Dropdown Results -->
                                <div
                                    v-if="searchResults.length > 0"
                                    class="list-group shadow-sm mt-1 position-absolute z-3 w-75 bg-white border"
                                >
                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                                        v-for="p in searchResults"
                                        :key="p.id"
                                        @click="addItem(p)"
                                    >
                                        <div>
                                            <span class="fw-semibold">{{ p.name }}</span>
                                            <span class="badge bg-light text-dark border ms-2">
                                                {{ p.default_variant?.sku || '-' }}
                                            </span>
                                            <small class="text-muted ms-2">
                                                Category: {{ p.category || '-' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small me-2">
                                                Cost: ₹{{ p.default_variant?.purchase_price || 0 }}
                                            </span>
                                            <span class="badge bg-primary">+ Add</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>#</th>
                                            <th>Product Name</th>
                                            <th width="140">Qty</th>
                                            <th width="140">Unit Cost (₹)</th>
                                            <th width="100">Tax (%)</th>
                                            <th width="130" class="text-end">Total (₹)</th>
                                            <th width="50" class="text-center"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-if="form.items.length === 0">
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                No items added yet. Search and click "+ Add" above.
                                            </td>
                                        </tr>

                                        <tr v-for="(item, idx) in form.items" :key="item.product_variant_id">
                                            <td>{{ idx + 1 }}</td>

                                            <td>
                                                <div class="fw-semibold">{{ item.product_name }}</div>
                                                <small class="text-muted">SKU: {{ item.sku }}</small>
                                            </td>

                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input
                                                        v-model.number="item.quantity"
                                                        type="number"
                                                        :step="item.allow_decimal ? '0.001' : '1'"
                                                        min="0.001"
                                                        class="form-control text-center"
                                                        required
                                                    />
                                                    <span class="input-group-text small">
                                                        {{ item.unit_name || 'Units' }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td>
                                                <input
                                                    v-model.number="item.unit_cost"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control form-control-sm text-end"
                                                    required
                                                />
                                            </td>

                                            <td>
                                                <input
                                                    v-model.number="item.tax_rate"
                                                    type="number"
                                                    step="0.1"
                                                    min="0"
                                                    max="100"
                                                    class="form-control form-control-sm text-center"
                                                />
                                            </td>

                                            <td class="text-end fw-bold">
                                                ₹{{ getItemTotal(item).toFixed(2) }}
                                            </td>

                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger py-0 px-2"
                                                    @click="removeItem(idx)"
                                                    title="Remove row"
                                                >
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <label class="form-label fw-semibold">Notes / Remarks</label>
                            <textarea
                                v-model="form.notes"
                                class="form-control"
                                rows="3"
                                placeholder="Any additional notes or instructions for this purchase order..."
                            ></textarea>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Order Summary & Payment -->
                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                        <div class="card-header bg-white fw-bold py-3">
                            Order Summary & Payment
                        </div>

                        <div class="card-body">
                            <!-- Subtotal -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-semibold">₹{{ subtotal.toFixed(2) }}</span>
                            </div>

                            <!-- Tax -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Tax:</span>
                                <span>+ ₹{{ taxTotal.toFixed(2) }}</span>
                            </div>

                            <!-- Discount Input -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Order Discount:</span>
                                    <div class="input-group input-group-sm w-50">
                                        <span class="input-group-text">₹</span>
                                        <input
                                            v-model.number="form.discount_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control text-end"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Cost Input -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Shipping Charges:</span>
                                    <div class="input-group input-group-sm w-50">
                                        <span class="input-group-text">₹</span>
                                        <input
                                            v-model.number="form.shipping_cost"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control text-end"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Grand Total -->
                            <div class="d-flex justify-content-between border-top border-bottom py-3 mb-3 fw-bold fs-5">
                                <span>Grand Total:</span>
                                <span class="text-primary">₹{{ grandTotal.toFixed(2) }}</span>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Method *</label>
                                <select v-model="form.payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer / NEFT</option>
                                    <option value="upi">UPI / GPay / PhonePe</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="card">Debit / Credit Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Paid Amount -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0">Paid Amount (₹) *</label>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link p-0 text-decoration-none"
                                        @click="form.paid_amount = grandTotal"
                                    >
                                        Pay Full
                                    </button>
                                </div>
                                <input
                                    v-model.number="form.paid_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control"
                                    required
                                />
                            </div>

                            <!-- Due Amount Display -->
                            <div class="d-flex justify-content-between mb-4">
                                <span class="text-muted">Due Balance:</span>
                                <span
                                    class="fw-bold"
                                    :class="dueAmount > 0 ? 'text-danger' : 'text-success'"
                                >
                                    ₹{{ dueAmount.toFixed(2) }}
                                </span>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="btn btn-primary w-100 py-2 fs-6 fw-semibold"
                                :disabled="submitting || form.items.length === 0"
                            >
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                                {{ form.status === 'received' ? 'Save & Receive Stock' : 'Save Purchase Order' }}
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </form>

        <!-- ============================================================ -->
        <!-- QUICK ADD SUPPLIER MODAL -->
        <!-- ============================================================ -->
        <div class="modal fade" id="quickSupplierModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form @submit.prevent="createQuickSupplier">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">+ Quick Add Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Supplier Name *</label>
                                <input
                                    v-model="quickSup.name"
                                    type="text"
                                    class="form-control"
                                    placeholder="e.g. Acme Wholesalers"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input
                                    v-model="quickSup.company_name"
                                    type="text"
                                    class="form-control"
                                    placeholder="Company / Trade name"
                                />
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Phone</label>
                                    <input
                                        v-model="quickSup.phone"
                                        type="text"
                                        class="form-control"
                                        placeholder="Phone number"
                                    />
                                </div>
                                <div class="col-6">
                                    <label class="form-label">GST / Tax ID</label>
                                    <input
                                        v-model="quickSup.gst_number"
                                        type="text"
                                        class="form-control"
                                        placeholder="GSTIN"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="savingQuickSup">
                                {{ savingQuickSup ? 'Saving...' : 'Save & Select' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { Modal } from 'bootstrap';
import api from '../../services/api';

const router = useRouter();

// Suppliers and Products
const suppliers = ref([]);
const searchResults = ref([]);
const productSearch = ref('');
let searchTimer = null;

// Submission state
const submitting = ref(false);
const errorMessage = ref('');

// Main Form
const form = reactive({
    supplier_id: '',
    purchase_date: new Date().toISOString().substring(0, 10),
    reference_no: '',
    status: 'received',
    payment_method: 'cash',
    discount_amount: 0,
    shipping_cost: 0,
    paid_amount: 0,
    notes: '',
    items: [],
});

// Quick Supplier Form
let quickSupplierModal = null;
const savingQuickSup = ref(false);
const quickSup = reactive({
    name: '',
    company_name: '',
    phone: '',
    gst_number: '',
});

// -------------------------------------------------------------------------
// Computed Financial Totals
// -------------------------------------------------------------------------
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const qty = Number(item.quantity || 0);
        const cost = Number(item.unit_cost || 0);
        return sum + (qty * cost);
    }, 0);
});

const taxTotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const qty = Number(item.quantity || 0);
        const cost = Number(item.unit_cost || 0);
        const rate = Number(item.tax_rate || 0);
        return sum + (qty * cost * (rate / 100));
    }, 0);
});

const grandTotal = computed(() => {
    const disc = Number(form.discount_amount || 0);
    const ship = Number(form.shipping_cost || 0);
    const total = subtotal.value + taxTotal.value + ship - disc;
    return Math.max(0, total);
});

const dueAmount = computed(() => {
    const paid = Number(form.paid_amount || 0);
    return Math.max(0, grandTotal.value - paid);
});

function getItemTotal(item) {
    const qty = Number(item.quantity || 0);
    const cost = Number(item.unit_cost || 0);
    const rate = Number(item.tax_rate || 0);
    const sub = qty * cost;
    const tax = sub * (rate / 100);
    return sub + tax;
}

// -------------------------------------------------------------------------
// Load Initial Data
// -------------------------------------------------------------------------
async function loadSuppliers() {
    try {
        const res = await api.get('/suppliers/all');
        suppliers.value = res.data.data || [];
        if (suppliers.value.length > 0 && !form.supplier_id) {
            form.supplier_id = suppliers.value[0].id;
        }
    } catch (err) {
        console.error('Failed to load suppliers:', err);
    }
}

// -------------------------------------------------------------------------
// Product Search & Add
// -------------------------------------------------------------------------
async function searchProductsNow() {
    if (!productSearch.value.trim()) {
        searchResults.value = [];
        return;
    }

    try {
        const res = await api.get('/inventory', {
            params: { search: productSearch.value.trim(), per_page: 8 }
        });
        searchResults.value = res.data.data || [];
    } catch (err) {
        console.error('Failed to search products:', err);
    }
}

function debouncedSearchProducts() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        searchProductsNow();
    }, 300);
}

function addItem(prod) {
    const variantId = prod.default_variant?.id;
    if (!variantId) {
        alert('Product variant missing.');
        return;
    }

    // Check if already added
    const existing = form.items.find(i => i.product_variant_id === variantId);
    if (existing) {
        existing.quantity += 1;
    } else {
        form.items.push({
            product_id: prod.id,
            product_variant_id: variantId,
            product_name: prod.name,
            sku: prod.default_variant?.sku || '-',
            unit_name: prod.unit?.short_name || 'Units',
            allow_decimal: Boolean(prod.unit?.allow_decimal),
            quantity: 1,
            unit_cost: Number(prod.default_variant?.purchase_price || 0),
            tax_rate: 0,
        });
    }

    // Default paid amount to grand total if previously untouched or 0
    setTimeout(() => {
        form.paid_amount = grandTotal.value;
    }, 50);

    productSearch.value = '';
    searchResults.value = [];
}

function removeItem(idx) {
    form.items.splice(idx, 1);
    form.paid_amount = grandTotal.value;
}

// -------------------------------------------------------------------------
// Quick Supplier Modal
// -------------------------------------------------------------------------
function openQuickSupplierModal() {
    Object.assign(quickSup, {
        name: '',
        company_name: '',
        phone: '',
        gst_number: '',
    });
    quickSupplierModal?.show();
}

async function createQuickSupplier() {
    savingQuickSup.value = true;
    try {
        const res = await api.post('/suppliers', quickSup);
        const newSup = res.data.data;
        suppliers.value.push(newSup);
        form.supplier_id = newSup.id;
        quickSupplierModal?.hide();
    } catch (err) {
        alert(err.response?.data?.message || 'Error adding supplier.');
    } finally {
        savingQuickSup.value = false;
    }
}

// -------------------------------------------------------------------------
// Submit Purchase
// -------------------------------------------------------------------------
async function submitPurchase() {
    if (form.items.length === 0) {
        errorMessage.value = 'Please add at least one product item to the purchase.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';

    try {
        const payload = {
            supplier_id: form.supplier_id || null,
            purchase_date: form.purchase_date,
            reference_no: form.reference_no.trim() || null,
            status: form.status,
            payment_method: form.payment_method,
            discount_amount: Number(form.discount_amount || 0),
            shipping_cost: Number(form.shipping_cost || 0),
            paid_amount: Number(form.paid_amount || 0),
            notes: form.notes ? form.notes.trim() : null,
            items: form.items.map(item => ({
                product_id: item.product_id,
                product_variant_id: item.product_variant_id,
                quantity: Number(item.quantity),
                unit_cost: Number(item.unit_cost),
                tax_rate: Number(item.tax_rate || 0),
            })),
        };

        const res = await api.post('/purchases', payload);
        alert(res.data.message || 'Purchase created successfully.');
        router.push('/admin/purchases');

    } catch (err) {
        errorMessage.value = err.response?.data?.message
            || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : 'Unable to create purchase.');
    } finally {
        submitting.value = false;
    }
}

// -------------------------------------------------------------------------
// Lifecycle
// -------------------------------------------------------------------------
onMounted(() => {
    const el = document.getElementById('quickSupplierModal');
    if (el) quickSupplierModal = new Modal(el);

    loadSuppliers();
});

onBeforeUnmount(() => {
    quickSupplierModal?.dispose();
    clearTimeout(searchTimer);
});
</script>

<style scoped>
.z-3 {
    z-index: 1050;
}
</style>

