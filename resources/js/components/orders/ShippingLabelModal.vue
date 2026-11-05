<template>
    <div
        v-if="isOpen"
        class="shipping-label-modal-backdrop d-flex flex-column align-items-center justify-content-center p-2 p-md-3"
        @click.self="closeModal"
    >
        <div class="shipping-label-dialog bg-white rounded-4 shadow-2xl d-flex flex-column overflow-hidden">
            <!-- Modal Header (Non-printable toolbar) -->
            <div class="modal-header-toolbar px-4 py-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 no-print">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">🏷️</span>
                    <div>
                        <h5 class="mb-0 fw-bold">Shipping Label Print Preview</h5>
                        <small class="text-muted">
                            {{ orders.length }} {{ orders.length === 1 ? 'order' : 'orders' }} selected for label printing
                        </small>
                    </div>
                </div>

                <!-- Label Print Controls & Options -->
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <!-- Label Size Format Selector -->
                    <div class="d-flex align-items-center gap-1">
                        <label class="small fw-semibold text-muted text-nowrap">Size:</label>
                        <select v-model="labelFormat" class="form-select form-select-sm" style="width: auto;">
                            <option value="thermal-4x6">4" x 6" Thermal (100×150mm)</option>
                            <option value="thermal-3inch">3" Compact Roll (80mm)</option>
                            <option value="a4-sheet">A4 Sheet (2×2 Grid)</option>
                        </select>
                    </div>

                    <!-- Toggle Manifest Checkbox -->
                    <div class="form-check form-switch mb-0">
                        <input
                            id="toggleManifest"
                            v-model="showManifest"
                            class="form-check-input"
                            type="checkbox"
                        />
                        <label class="form-check-label small fw-semibold" for="toggleManifest">
                            Item Summary
                        </label>
                    </div>

                    <!-- Toggle Return Address Checkbox -->
                    <div class="form-check form-switch mb-0">
                        <input
                            id="toggleReturn"
                            v-model="showReturnAddress"
                            class="form-check-input"
                            type="checkbox"
                        />
                        <label class="form-check-label small fw-semibold" for="toggleReturn">
                            Return Address
                        </label>
                    </div>

                    <!-- Print Action Button -->
                    <button
                        type="button"
                        class="btn btn-primary btn-sm px-3 fw-bold rounded-pill d-flex align-items-center gap-1 shadow-sm"
                        @click="triggerPrint"
                    >
                        <span>🖨️</span>
                        <span>Print {{ orders.length > 1 ? `${orders.length} Labels` : 'Label' }}</span>
                    </button>

                    <!-- Close Button -->
                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                        @click="closeModal"
                    >
                        ✕ Close
                    </button>
                </div>
            </div>

            <!-- Modal Body / Label Preview Viewport -->
            <div class="modal-body-viewport flex-grow-1 overflow-auto p-4 bg-secondary-subtle">
                <!-- Instruction banner (no-print) -->
                <div class="alert alert-light border small text-muted py-2 px-3 mb-3 d-flex align-items-center justify-content-between rounded-3 no-print">
                    <div>
                        💡 <strong>Tip:</strong> In your browser print dialog, set <strong>Margins: None</strong> and choose paper size <strong>4x6" / 100×150mm</strong> for direct thermal sticker rolls.
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary">{{ labelFormatLabel }}</span>
                </div>

                <!-- Printable Labels Container -->
                <div
                    id="printableShippingLabels"
                    class="printable-labels-wrapper d-flex flex-column align-items-center gap-4"
                    :class="[`format-${labelFormat}`, { 'pure-mono': highContrast }]"
                >
                    <div
                        v-for="(order, idx) in orders"
                        :key="order.id || idx"
                        class="shipping-label-card bg-white position-relative"
                    >
                        <!-- ============================================== -->
                        <!-- 1. TOP CARRIER & ROUTING HEADER                -->
                        <!-- ============================================== -->
                        <div class="label-header p-2 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="store-badge-text fw-bold text-uppercase">
                                    {{ storeSettings.site_name || 'Retail POS Store' }}
                                </span>
                            </div>

                            <!-- Courier Partner & Routing Badge -->
                            <div class="text-end">
                                <span class="courier-badge fw-bold text-uppercase px-2 py-1 rounded">
                                    {{ order.delivery_partner || 'STANDARD COURIER' }}
                                </span>
                                <span class="routing-type-badge fw-bold text-uppercase ms-1 px-1 py-1 rounded">
                                    SURFACE
                                </span>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 2. TRACKING & AWB BARCODE SECTION              -->
                        <!-- ============================================== -->
                        <div class="label-awb-section p-2 border-bottom text-center">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="text-start">
                                    <div class="extra-small text-muted text-uppercase fw-semibold">Tracking / AWB Number</div>
                                    <div class="fw-bold tracking-number-text font-monospace">
                                        {{ order.tracking_number || order.invoice_number }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="extra-small text-muted text-uppercase fw-semibold">Destination PIN</div>
                                    <div class="destination-pincode-badge fw-bold font-monospace">
                                        {{ extractPincode(order) }}
                                    </div>
                                </div>
                            </div>

                            <!-- SVG Barcode Target -->
                            <div class="barcode-container my-1">
                                <svg
                                    :id="`barcode-awb-${order.id}`"
                                    class="barcode-target"
                                    :data-value="order.tracking_number || order.invoice_number"
                                ></svg>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 3. SHIP TO / CONSIGNEE SECTION                 -->
                        <!-- ============================================== -->
                        <div class="label-consignee-section p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="label-section-tag fw-bold text-uppercase">DELIVER TO:</span>
                                <span class="badge bg-light text-dark border extra-small">CONSIGNEE</span>
                            </div>

                            <!-- Customer Name -->
                            <div class="customer-name-heading fw-black text-uppercase mb-1">
                                {{ getCustomerName(order) }}
                            </div>

                            <!-- Full Address -->
                            <div class="customer-address-text mb-2">
                                {{ getShippingAddress(order) }}
                            </div>

                            <!-- City, State, PIN -->
                            <div class="d-flex justify-content-between align-items-center pt-1 border-top border-secondary-subtle">
                                <div class="city-state-text fw-bold text-uppercase">
                                    {{ getCityState(order) }}
                                </div>
                                <div class="pincode-highlight fw-black font-monospace">
                                    PIN: {{ extractPincode(order) }}
                                </div>
                            </div>

                            <!-- Contact Phone -->
                            <div class="customer-contact-text mt-1 fw-bold">
                                📞 Mobile: {{ getCustomerPhone(order) }}
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 4. COD / PAYMENT & ORDER META SECTION          -->
                        <!-- ============================================== -->
                        <div class="label-payment-order-grid border-bottom d-flex">
                            <!-- Left: Payment Banner -->
                            <div class="payment-box p-2 flex-grow-1 border-end d-flex flex-column justify-content-center text-center"
                                 :class="isCodOrder(order) ? 'payment-cod-box' : 'payment-prepaid-box'">
                                <template v-if="isCodOrder(order)">
                                    <div class="payment-mode-title fw-black text-uppercase">
                                        CASH ON DELIVERY (COD)
                                    </div>
                                    <div class="cod-amount-badge fw-black">
                                        COLLECT: ₹{{ Number(order.total_amount).toFixed(2) }}
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="payment-mode-title fw-black text-uppercase">
                                        PREPAID ORDER
                                    </div>
                                    <div class="prepaid-amount-badge fw-bold">
                                        DO NOT COLLECT CASH
                                    </div>
                                    <div class="extra-small text-muted mt-1">Paid: ₹{{ Number(order.total_amount).toFixed(2) }}</div>
                                </template>
                            </div>

                            <!-- Right: Order ID & Barcode -->
                            <div class="order-id-box p-2 text-center d-flex flex-column justify-content-center" style="width: 45%;">
                                <div class="extra-small text-muted text-uppercase fw-semibold">Order / Invoice #</div>
                                <div class="fw-bold font-monospace small mb-1">{{ order.invoice_number }}</div>
                                <div class="barcode-small-container">
                                    <svg
                                        :id="`barcode-order-${order.id}`"
                                        class="barcode-target-order"
                                        :data-value="order.invoice_number"
                                    ></svg>
                                </div>
                                <div class="extra-small text-muted mt-1">
                                    Date: {{ formatDate(order.sale_date) }}
                                </div>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 5. ITEM MANIFEST SUMMARY (Optional)            -->
                        <!-- ============================================== -->
                        <div v-if="showManifest && order.items && order.items.length > 0" class="label-manifest-section p-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="extra-small fw-bold text-uppercase text-muted">Package Items Manifest:</span>
                                <span class="extra-small text-muted">
                                    Total: {{ getTotalItemsCount(order) }} pcs
                                </span>
                            </div>

                            <table class="table table-sm table-bordered manifest-table mb-1">
                                <thead>
                                    <tr>
                                        <th class="extra-small py-0 text-start">SKU / Item</th>
                                        <th class="extra-small py-0 text-center" style="width: 45px;">Qty</th>
                                        <th class="extra-small py-0 text-end" style="width: 65px;">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(it, iIdx) in order.items" :key="iIdx">
                                        <td class="extra-small py-0 text-truncate text-start" style="max-width: 170px;">
                                            <span class="fw-semibold">{{ it.product?.name || it.name || 'Item' }}</span>
                                            <span v-if="it.variant?.sku" class="text-muted font-monospace"> ({{ it.variant.sku }})</span>
                                        </td>
                                        <td class="extra-small py-0 text-center fw-bold">{{ it.quantity }}</td>
                                        <td class="extra-small py-0 text-end">₹{{ Number(it.unit_price || it.total || 0).toFixed(0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ============================================== -->
                        <!-- 6. RETURN / SHIPPER ADDRESS (Optional)         -->
                        <!-- ============================================== -->
                        <div v-if="showReturnAddress" class="label-return-section p-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="extra-small fw-bold text-uppercase text-muted">If Undelivered, Return To:</span>
                                <span class="extra-small fw-semibold text-muted">SHIPPER</span>
                            </div>
                            <div class="extra-small fw-bold text-uppercase">{{ storeSettings.site_name || 'Retail POS Store' }}</div>
                            <div class="extra-small text-secondary">{{ storeSettings.contact_address || 'Warehouse / Store Address' }}</div>
                            <div class="extra-small text-secondary">
                                📞 Tel: {{ storeSettings.contact_phone || 'Customer Care' }} | ✉️ {{ storeSettings.contact_email || 'support@store.com' }}
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 7. FOOTER / SAFETY INSTRUCTIONS                -->
                        <!-- ============================================== -->
                        <div class="label-footer p-2 text-center bg-light">
                            <div class="extra-small fw-bold text-uppercase text-dark d-flex align-items-center justify-content-center gap-1">
                                <span>⚠️</span>
                                <span>HANDLE WITH CARE — DO NOT ACCEPT IF PACKAGE IS DAMAGED OR TAMPERED</span>
                            </div>
                            <div class="extra-small text-muted mt-1">
                                Computer generated courier label. No physical signature required.
                            </div>
                        </div>

                        <!-- Page break element for multi-label print spool -->
                        <div class="label-page-divider no-screen"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import JsBarcode from 'jsbarcode';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    orders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

// Print format state
const labelFormat = ref('thermal-4x6');
const showManifest = ref(true);
const showReturnAddress = ref(true);
const highContrast = ref(false);

// Store settings for sender return address
const storeSettings = ref({
    site_name: 'Smart Stationery',
    contact_address: '42 Market Street, Commercial Hub, Mumbai',
    contact_phone: '+91 12345 67890',
    contact_email: 'support@thesmartstationery.com',
});

const labelFormatLabel = computed(() => {
    if (labelFormat.value === 'thermal-4x6') return '4" x 6" Thermal Sticker (100×150mm)';
    if (labelFormat.value === 'thermal-3inch') return '3" Thermal Receipt / Roll (80mm)';
    return 'A4 Multi-label Sheet (4 labels/page)';
});

// Load settings from backend
async function loadStoreSettings() {
    try {
        const res = await axios.get('/api/shop/settings');
        if (res.data && res.data.data) {
            storeSettings.value = { ...storeSettings.value, ...res.data.data };
        }
    } catch (e) {
        // Fallback default
    }
}

// Generate barcodes on all visible targets
function renderBarcodes() {
    nextTick(() => {
        // 1. Render primary AWB / Tracking barcode (Large)
        document.querySelectorAll('.barcode-target').forEach((el) => {
            const val = el.getAttribute('data-value');
            if (val) {
                try {
                    JsBarcode(el, val, {
                        format: 'CODE128',
                        lineColor: '#000000',
                        width: labelFormat.value === 'thermal-3inch' ? 1.5 : 2,
                        height: labelFormat.value === 'thermal-3inch' ? 36 : 46,
                        displayValue: false,
                        margin: 0,
                    });
                } catch (e) {
                    console.warn('Barcode render error for:', val, e);
                }
            }
        });

        // 2. Render Order # Barcode (Small)
        document.querySelectorAll('.barcode-target-order').forEach((el) => {
            const val = el.getAttribute('data-value');
            if (val) {
                try {
                    JsBarcode(el, val, {
                        format: 'CODE128',
                        lineColor: '#000000',
                        width: 1.2,
                        height: 24,
                        displayValue: false,
                        margin: 0,
                    });
                } catch (e) {
                    // Ignore fallback
                }
            }
        });
    });
}

// Manage body class & re-render barcodes when modal opens
watch(
    () => props.isOpen,
    (val) => {
        if (val) {
            document.body.classList.add('modal-open-shipping-label');
            renderBarcodes();
        } else {
            document.body.classList.remove('modal-open-shipping-label');
        }
    },
    { immediate: true }
);

watch(
    () => [props.orders, labelFormat.value],
    () => {
        if (props.isOpen) {
            renderBarcodes();
        }
    },
    { deep: true }
);

onMounted(() => {
    loadStoreSettings();
});

onBeforeUnmount(() => {
    document.body.classList.remove('modal-open-shipping-label');
    const existing = document.getElementById('shipping-label-print-frame');
    if (existing) {
        existing.remove();
    }
});

function closeModal() {
    emit('close');
}

// Isolated print styles for the dedicated print iframe (no web page background bleed!)
function getIframePrintStyles() {
    let pageCss = '@page { size: 100mm 150mm; margin: 0; }';
    let cardWidth = '100mm';
    let cardMinHeight = '145mm';
    let cardMaxHeight = '150mm';
    let cardMargin = '0 auto';
    let cardFloat = 'none';

    if (labelFormat.value === 'thermal-4x6') {
        pageCss = '@page { size: 100mm 150mm; margin: 0; }';
        cardWidth = '100mm';
        cardMinHeight = '145mm';
        cardMaxHeight = '150mm';
        cardMargin = '0 auto';
    } else if (labelFormat.value === 'thermal-3inch') {
        pageCss = '@page { size: 80mm auto; margin: 0; }';
        cardWidth = '76mm';
        cardMinHeight = '120mm';
        cardMaxHeight = 'none';
        cardMargin = '0 auto';
    } else {
        pageCss = '@page { size: A4 portrait; margin: 6mm; }';
        cardWidth = '95mm';
        cardMinHeight = '140mm';
        cardMaxHeight = 'none';
        cardMargin = '2mm';
        cardFloat = 'left';
    }

    return `
        ${pageCss}
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #000000 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.25;
        }
        .printable-labels-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .shipping-label-card {
            background: #ffffff !important;
            color: #000000 !important;
            border: 2px solid #000000 !important;
            width: ${cardWidth} !important;
            min-height: ${cardMinHeight} !important;
            max-height: ${cardMaxHeight} !important;
            margin: ${cardMargin} !important;
            float: ${cardFloat} !important;
            page-break-after: always !important;
            break-after: page !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 8px;
            border-bottom: 2px solid #000000;
        }
        .store-badge-text {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .courier-badge {
            background: #000000 !important;
            color: #ffffff !important;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 3px 6px;
            border-radius: 3px;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .routing-type-badge {
            background: #e2e8f0 !important;
            color: #000000 !important;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 2px 4px;
            border: 1px solid #94a3b8;
            border-radius: 3px;
            margin-left: 4px;
            display: inline-block;
        }
        .label-awb-section {
            padding: 6px 8px;
            border-bottom: 2px solid #000000;
            text-align: center;
        }
        .tracking-number-text {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1.5px;
            font-family: monospace;
        }
        .destination-pincode-badge {
            font-size: 18px;
            font-weight: 900;
            background: #000000 !important;
            color: #ffffff !important;
            padding: 2px 8px;
            border-radius: 4px;
            letter-spacing: 1px;
            font-family: monospace;
            display: inline-block;
        }
        .barcode-container {
            margin: 4px auto;
            text-align: center;
        }
        .barcode-target, .barcode-target-order {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .label-consignee-section {
            padding: 8px;
            border-bottom: 2px solid #000000;
        }
        .label-section-tag {
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .customer-name-heading {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            color: #000000;
            margin-bottom: 4px;
        }
        .customer-address-text {
            font-size: 12px;
            color: #000000;
            line-height: 1.35;
            word-break: break-word;
            margin-bottom: 6px;
        }
        .city-state-text {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .pincode-highlight {
            font-size: 15px;
            font-weight: 900;
            font-family: monospace;
        }
        .customer-contact-text {
            font-size: 13px;
            font-weight: 800;
            color: #000000;
            margin-top: 4px;
        }
        .label-payment-order-grid {
            display: flex;
            border-bottom: 2px solid #000000;
        }
        .payment-box {
            padding: 8px;
            flex: 1 1 55%;
            border-right: 2px solid #000000;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .payment-cod-box {
            background: #000000 !important;
            color: #ffffff !important;
        }
        .payment-cod-box .payment-mode-title {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #ffffff !important;
            text-transform: uppercase;
        }
        .payment-cod-box .cod-amount-badge {
            font-size: 15px;
            font-weight: 900;
            color: #ffffff !important;
            margin-top: 2px;
        }
        .payment-prepaid-box {
            background: #f1f5f9 !important;
            color: #000000 !important;
        }
        .payment-prepaid-box .payment-mode-title {
            font-size: 12px;
            font-weight: 900;
            color: #047857 !important;
            text-transform: uppercase;
        }
        .payment-prepaid-box .prepaid-amount-badge {
            font-size: 12px;
            font-weight: 800;
            color: #047857 !important;
        }
        .order-id-box {
            padding: 6px 8px;
            width: 45%;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .manifest-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 4px;
        }
        .manifest-table th, .manifest-table td {
            border: 1px solid #000000;
            padding: 2px 4px;
        }
        .label-manifest-section {
            padding: 6px 8px;
            border-bottom: 2px solid #000000;
        }
        .label-return-section {
            padding: 6px 8px;
            border-bottom: 2px solid #000000;
            font-size: 10px;
            line-height: 1.3;
        }
        .label-footer {
            padding: 6px 8px;
            text-align: center;
            background: #f8fafc !important;
            font-size: 9.5px;
            line-height: 1.25;
        }
        .extra-small { font-size: 10px; }
        .fw-bold { font-weight: 700; }
        .fw-black { font-weight: 900; }
        .text-uppercase { text-transform: uppercase; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .align-items-start { align-items: flex-start; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .border-top { border-top: 1px solid #000000; }
        .p-2 { padding: 6px; }
        .p-3 { padding: 8px; }
        .mb-1 { margin-bottom: 3px; }
        .mb-2 { margin-bottom: 6px; }
        .mt-1 { margin-top: 3px; }
        .no-print, .btn, .alert { display: none !important; }
        .no-screen { display: block !important; }
    `;
}

// Print via isolated iframe to ensure ZERO web page elements or sidebars leak into the printout
function triggerPrint() {
    const container = document.getElementById('printableShippingLabels');
    if (!container) return;

    // Remove any previous frame
    const existing = document.getElementById('shipping-label-print-frame');
    if (existing) {
        existing.remove();
    }

    // Create an isolated hidden iframe
    const printFrame = document.createElement('iframe');
    printFrame.id = 'shipping-label-print-frame';
    printFrame.style.position = 'fixed';
    printFrame.style.top = '-9999px';
    printFrame.style.left = '-9999px';
    printFrame.style.width = '100mm';
    printFrame.style.height = '150mm';
    printFrame.style.border = 'none';
    document.body.appendChild(printFrame);

    const frameDoc = printFrame.contentWindow.document;
    const styles = getIframePrintStyles();
    const labelContent = container.innerHTML;

    frameDoc.open();
    frameDoc.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shipping Label</title>
    <style>${styles}</style>
</head>
<body class="format-${labelFormat.value}">
    <div class="printable-labels-wrapper">
        ${labelContent}
    </div>
</body>
</html>`);
    frameDoc.close();

    // Trigger print in the isolated iframe after browser finishes rendering
    setTimeout(() => {
        try {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        } catch (e) {
            console.error('Iframe print error, falling back:', e);
            window.print();
        }
        setTimeout(() => {
            printFrame.remove();
        }, 3000);
    }, 250);
}

// Helper formatting functions
function getCustomerName(order) {
    return order.customer?.name || order.customer_name || 'Valued Customer';
}

function getCustomerPhone(order) {
    return order.customer?.phone || order.customer_phone || 'Not provided';
}

function getShippingAddress(order) {
    return order.shipping_address || order.customer?.address || 'Standard Delivery Address';
}

function getCityState(order) {
    const city = order.customer?.city || order.city;
    const state = order.customer?.state || order.state;
    if (city && state) return `${city}, ${state}`;
    if (city) return city;
    if (state) return state;

    // Try extracting from address string if available
    const addr = order.shipping_address || '';
    const parts = addr.split(',');
    if (parts.length >= 2) {
        return parts.slice(-2).join(',').trim();
    }
    return 'India';
}

function extractPincode(order) {
    if (order.customer?.pincode) return order.customer.pincode;
    if (order.pincode) return order.pincode;

    // Search 6-digit Indian PIN code in address string
    const match = (order.shipping_address || '').match(/\b(\d{6})\b/);
    if (match) return match[1];

    return '400001'; // Default / Fallback
}

function isCodOrder(order) {
    const method = String(order.payment_method || '').toLowerCase();
    const status = String(order.payment_status || '').toLowerCase();
    return method.includes('cod') || method.includes('cash') || status === 'unpaid';
}

function getTotalItemsCount(order) {
    if (!order.items) return 0;
    return order.items.reduce((sum, it) => sum + Number(it.quantity || 1), 0);
}

function formatDate(dateStr) {
    if (!dateStr) return 'Today';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    } catch (e) {
        return dateStr;
    }
}
</script>

<style scoped>
/* Modal backdrop & container */
.shipping-label-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    z-index: 1060;
}

.shipping-label-dialog {
    width: 95%;
    max-width: 920px;
    height: 92vh;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.modal-body-viewport {
    min-height: 0;
}

/* ====================================================== */
/* SCREEN LABEL CARD STYLING                              */
/* ====================================================== */
.shipping-label-card {
    background: #ffffff;
    color: #111827;
    border: 2px solid #000000;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    line-height: 1.3;
}

/* 4x6 Thermal Label Standard Dimensions (approx 100mm x 150mm at 96dpi = 380px x 570px) */
.format-thermal-4x6 .shipping-label-card {
    width: 400px;
    min-height: 580px;
}

/* 3 inch roll (approx 80mm = ~300px) */
.format-thermal-3inch .shipping-label-card {
    width: 320px;
    min-height: 520px;
}

/* A4 Sheet View */
.format-a4-sheet .shipping-label-card {
    width: 440px;
    min-height: 600px;
}

/* Inner label typography & elements */
.store-badge-text {
    font-size: 0.95rem;
    letter-spacing: 0.5px;
}

.courier-badge {
    background: #000000;
    color: #ffffff;
    font-size: 0.78rem;
    letter-spacing: 0.5px;
}

.routing-type-badge {
    background: #e2e8f0;
    color: #0f172a;
    font-size: 0.75rem;
    border: 1px solid #cbd5e1;
}

.tracking-number-text {
    font-size: 1.15rem;
    letter-spacing: 1.2px;
}

.destination-pincode-badge {
    font-size: 1.25rem;
    background: #000000;
    color: #ffffff;
    padding: 2px 8px;
    border-radius: 4px;
    letter-spacing: 1px;
}

.label-section-tag {
    font-size: 0.75rem;
    color: #64748b;
    letter-spacing: 0.5px;
}

.customer-name-heading {
    font-size: 1.15rem;
    font-weight: 800;
    color: #000000;
}

.customer-address-text {
    font-size: 0.88rem;
    color: #1e293b;
    word-break: break-word;
}

.city-state-text {
    font-size: 0.92rem;
}

.pincode-highlight {
    font-size: 1.05rem;
}

.customer-contact-text {
    font-size: 0.9rem;
    color: #000000;
}

/* COD / Payment box */
.payment-cod-box {
    background: #000000;
    color: #ffffff;
}

.payment-cod-box .payment-mode-title {
    font-size: 0.92rem;
    letter-spacing: 0.5px;
    color: #ffffff;
}

.payment-cod-box .cod-amount-badge {
    font-size: 1.1rem;
    color: #ffffff;
    margin-top: 2px;
}

.payment-prepaid-box {
    background: #f1f5f9;
    color: #0f172a;
}

.payment-prepaid-box .payment-mode-title {
    font-size: 0.88rem;
    color: #047857;
    font-weight: 800;
}

.payment-prepaid-box .prepaid-amount-badge {
    font-size: 0.85rem;
    color: #047857;
}

.manifest-table {
    border-color: #000000 !important;
}

.manifest-table th, .manifest-table td {
    padding: 2px 4px !important;
}

.extra-small {
    font-size: 0.72rem;
    line-height: 1.2;
}

.fw-black {
    font-weight: 900;
}

.no-screen {
    display: none;
}

/* ====================================================== */
/* PRINT MEDIA STYLING (FOR THERMAL & LASER PRINTERS)    */
/* ====================================================== */
@media print {
    /* Hide everything unless specifically printing shipping label */
    body.printing-shipping-label * {
        visibility: hidden !important;
    }

    .no-print,
    .modal-header-toolbar,
    .alert,
    .shipping-label-modal-backdrop {
        background: transparent !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        position: static !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
    }

    .shipping-label-dialog {
        width: 100% !important;
        max-width: none !important;
        height: auto !important;
        border: none !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    .modal-body-viewport {
        padding: 0 !important;
        background: #ffffff !important;
        overflow: visible !important;
    }

    body.printing-shipping-label #printableShippingLabels,
    body.printing-shipping-label #printableShippingLabels * {
        visibility: visible !important;
    }

    body.printing-shipping-label #printableShippingLabels {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 0 !important;
    }

    .shipping-label-card {
        border: 2px solid #000000 !important;
        box-shadow: none !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: always !important;
        break-after: page !important;
        margin: 0 auto 0 auto !important;
    }

    .format-thermal-4x6 .shipping-label-card {
        width: 100mm !important;
        min-height: 145mm !important;
        max-height: 150mm !important;
    }

    .format-thermal-3inch .shipping-label-card {
        width: 76mm !important;
        min-height: 130mm !important;
    }

    .format-a4-sheet .shipping-label-card {
        width: 95mm !important;
        min-height: 140mm !important;
        float: left !important;
        margin: 4mm !important;
    }

    .label-page-divider {
        display: block !important;
        page-break-after: always !important;
        break-after: page !important;
    }

    /* Thermal print optimization: pure black & white */
    .payment-cod-box {
        background: #000000 !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .courier-badge, .destination-pincode-badge {
        background: #000000 !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<!-- Unscoped global print styling to isolate printing when modal is active -->
<style>
@media print {
    body.modal-open-shipping-label {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    body.modal-open-shipping-label .admin-wrapper,
    body.modal-open-shipping-label .admin-body,
    body.modal-open-shipping-label .main-content {
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        display: block !important;
    }
    body.modal-open-shipping-label header,
    body.modal-open-shipping-label aside,
    body.modal-open-shipping-label .admin-sidebar,
    body.modal-open-shipping-label .admin-header,
    body.modal-open-shipping-label .card:not(.shipping-label-card),
    body.modal-open-shipping-label .alert,
    body.modal-open-shipping-label .table-responsive,
    body.modal-open-shipping-label .pagination,
    body.modal-open-shipping-label .no-print,
    body.modal-open-shipping-label .modal-header-toolbar {
        display: none !important;
        visibility: hidden !important;
    }
    body.modal-open-shipping-label .shipping-label-modal-backdrop {
        position: static !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    body.modal-open-shipping-label .shipping-label-dialog {
        width: 100% !important;
        max-width: none !important;
        height: auto !important;
        border: none !important;
        box-shadow: none !important;
    }
    body.modal-open-shipping-label .modal-body-viewport {
        padding: 0 !important;
        background: transparent !important;
        overflow: visible !important;
    }
    body.modal-open-shipping-label #printableShippingLabels {
        display: block !important;
        position: static !important;
        margin: 0 auto !important;
        padding: 0 !important;
    }
}
</style>
