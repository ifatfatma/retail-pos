<template>
    <div class="pos-container">

        <!-- Top Status Bar -->
        <div class="pos-header bg-white border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <span class="me-1">🛒</span> {{ $t('pos.title') }}
                </h5>
                <span class="badge bg-light text-secondary border">
                    {{ $t('common.active') }}
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Held Bills button if any -->
                <button
                    v-if="heldBills.length > 0"
                    class="btn btn-sm btn-outline-warning"
                    @click="openHeldBillsModal"
                >
                    {{ $t('pos.recall_bill') }} ({{ heldBills.length }})
                </button>

                <!-- Today's Recent Sales History -->
                <button
                    class="btn btn-sm btn-outline-secondary"
                    @click="openHistoryModal"
                >
                    {{ $t('dashboard.today_sales') }}
                </button>

                <!-- Theme Toggle (Dark / Light) -->
                <ThemeToggle theme="auto" size="sm" />

                <!-- Fullscreen toggle -->
                <button
                    class="btn btn-sm btn-outline-secondary"
                    @click="toggleFullscreen"
                    title="Toggle Fullscreen"
                >
                    {{ isFullscreen ? $t('common.close') : 'Fullscreen' }}
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
                                :placeholder="$t('pos.search_products')"
                                @input="debouncedSearch"
                                @keydown.enter.prevent="onSearchEnter"
                            />
                            <button
                                v-if="searchQuery"
                                class="btn btn-outline-secondary"
                                type="button"
                                @click="clearSearch"
                            >
                                {{ $t('common.clear') }}
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
                        {{ $t('storefront.all_products') }}
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
                        {{ $t('common.loading') }}
                    </div>

                    <div v-else-if="products.length === 0" class="text-center py-5 text-muted">
                        {{ $t('common.no_data') }}
                    </div>

                    <div v-else class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-4 g-3">
                        <div
                            class="col"
                            v-for="prod in products"
                            :key="prod.id"
                        >
                            <div
                                class="card h-100 product-card border-0 shadow-sm overflow-hidden"
                                :class="{ 'out-of-stock-card': prod.track_inventory && prod.stock <= 0 }"
                                @click="addToCart(prod)"
                            >
                                <!-- Product Image Thumbnail Banner -->
                                <div class="product-thumb-wrap position-relative bg-light d-flex align-items-center justify-content-center">
                                    <img
                                        v-if="prod.image_url"
                                        :src="prod.image_url"
                                        :alt="prod.name"
                                        class="product-thumb-img"
                                        loading="lazy"
                                        @error="(e) => e.target.style.display = 'none'"
                                    />
                                    <div v-else class="product-thumb-placeholder d-flex flex-column align-items-center justify-content-center">
                                        <span class="fs-4">📦</span>
                                    </div>

                                    <!-- Category badge over image -->
                                    <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2 small py-1 px-2 text-truncate" style="max-width: 65%;">
                                        {{ prod.category_name }}
                                    </span>

                                    <!-- Stock badge over image -->
                                    <span
                                        v-if="prod.track_inventory"
                                        class="badge position-absolute top-0 end-0 m-2 py-1 px-2 shadow-sm"
                                        :class="prod.stock <= 0 ? 'bg-danger' : (prod.stock <= prod.low_stock_threshold ? 'bg-warning text-dark' : 'bg-success')"
                                    >
                                        {{ prod.stock }} {{ prod.unit?.short_name || '' }}
                                    </span>
                                </div>

                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
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
                        <label class="form-label fw-bold small text-muted text-uppercase mb-0">{{ $t('dashboard.customer') }}</label>
                        <button
                            class="btn btn-link btn-sm p-0 text-decoration-none"
                            @click="openQuickCustomerModal"
                        >
                            {{ $t('pos.quick_add') }}
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
                        <div class="fw-semibold">{{ $t('pos.cart_empty') }}</div>
                        <small>{{ $t('pos.cart_empty_sub') }}</small>
                    </div>

                    <div v-else class="cart-list">
                        <div
                            class="cart-row mb-2 p-2 rounded-3 border bg-white shadow-xs d-flex justify-content-between align-items-center"
                            v-for="(item, idx) in cart"
                            :key="item.variant_id"
                        >
                            <!-- Item Image Thumbnail -->
                            <div class="cart-item-thumb-box me-2 flex-shrink-0 rounded border bg-light overflow-hidden d-flex align-items-center justify-content-center">
                                <img
                                    v-if="item.image_url"
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="cart-item-thumb-img"
                                    @error="(e) => e.target.style.display = 'none'"
                                />
                                <span v-else class="cart-item-thumb-icon">📦</span>
                            </div>

                            <div class="flex-grow-1 me-2 overflow-hidden min-w-0">
                                <div class="fw-semibold text-truncate small text-dark">
                                    {{ item.name }}
                                </div>
                                <div class="d-flex align-items-center text-muted extra-small">
                                    <span>₹{{ Number(item.unit_price).toFixed(2) }}</span>
                                    <span class="mx-1">&times;</span>
                                    <span class="badge bg-light text-secondary border py-0 px-1">
                                        {{ item.unit_name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Quantity Stepper -->
                            <div class="d-flex align-items-center me-2 flex-shrink-0">
                                <button
                                    class="btn btn-sm btn-light border px-2 py-0 fw-bold stepper-btn"
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
                                    class="btn btn-sm btn-light border px-2 py-0 fw-bold stepper-btn"
                                    type="button"
                                    @click="increaseQty(idx)"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Row Total & Remove -->
                            <div class="text-end flex-shrink-0 d-flex flex-column align-items-end" style="min-width: 65px;">
                                <div class="fw-bold small text-primary">
                                    ₹{{ getItemTotal(item).toFixed(2) }}
                                </div>
                                <button
                                    class="btn btn-link text-danger p-0 extra-small text-decoration-none remove-item-btn"
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
                            :title="$t('pos.hold_bill')"
                        >
                            ⏸️ {{ $t('pos.hold_bill') }}
                        </button>
                        <button
                            class="btn btn-outline-danger"
                            @click="clearCart"
                            :title="$t('pos.clear_cart')"
                        >
                            🗑️ {{ $t('common.clear') }}
                        </button>
                    </div>
                </div>

                <!-- Financial Totals Box -->
                <div class="p-3 border-bottom bg-white">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>{{ $t('common.subtotal') }}:</span>
                        <span>₹{{ cartSubtotal.toFixed(2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mb-1" v-if="cartTaxTotal > 0">
                        <span>{{ $t('common.tax') }}:</span>
                        <span>+ ₹{{ cartTaxTotal.toFixed(2) }}</span>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">{{ $t('coupons.title') }}:</span>
                            <button
                                v-if="appliedCoupon"
                                type="button"
                                class="btn btn-link btn-sm p-0 text-danger text-decoration-none extra-small"
                                @click="removePosCoupon"
                            >
                                ✕ {{ $t('common.remove') }}
                            </button>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">🏷️</span>
                            <input
                                v-model="posCouponCode"
                                type="text"
                                class="form-control text-uppercase"
                                :placeholder="$t('cart.coupon_placeholder')"
                                :disabled="applyingCoupon || !!appliedCoupon"
                                @input="posCouponCode = posCouponCode.toUpperCase()"
                                @keydown.enter.prevent="applyPosCoupon"
                            />
                            <button
                                class="btn btn-outline-primary"
                                type="button"
                                :disabled="applyingCoupon || !posCouponCode.trim() || !!appliedCoupon"
                                @click="applyPosCoupon"
                            >
                                <span v-if="applyingCoupon" class="spinner-border spinner-border-sm"></span>
                                <span v-else>{{ $t('cart.apply') }}</span>
                            </button>
                        </div>
                        <div v-if="appliedCoupon" class="extra-small text-success mt-1 fw-semibold">
                            ✓ {{ appliedCoupon.name || appliedCoupon.code }} (-₹{{ Number(discountAmount).toFixed(2) }})
                        </div>
                        <div v-if="couponError" class="extra-small text-danger mt-1">
                            ⚠️ {{ couponError }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">{{ $t('common.discount') }}:</span>
                        <div class="input-group input-group-sm w-50">
                            <span class="input-group-text">₹</span>
                            <input
                                v-model.number="discountAmount"
                                type="number"
                                min="0"
                                step="1"
                                class="form-control text-end"
                                :disabled="!!appliedCoupon"
                            />
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="fw-bold fs-6">{{ $t('common.grand_total') }}:</span>
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
                            💵 {{ $t('pos.cash') }}
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
                            📱 {{ $t('pos.upi_qr') }}
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
                            💳 {{ $t('pos.card') }}
                        </label>
                    </div>

                    <!-- ============================================== -->
                    <!-- PAYMENT MODE 1: CASH                           -->
                    <!-- ============================================== -->
                    <div v-if="paymentMethod === 'cash'">
                        <!-- Quick Cash Buttons -->
                        <div class="mb-2">
                            <div class="d-flex gap-1 flex-wrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light border py-1 flex-grow-1 small"
                                    @click="paidAmount = cartGrandTotal"
                                >
                                    {{ $t('common.all') }}
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
                                <label class="form-label small text-muted mb-1">{{ $t('pos.tendered') }} (₹)</label>
                                <input
                                    v-model.number="paidAmount"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="form-control form-control-sm text-end fw-bold"
                                />
                            </div>

                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">{{ $t('pos.change_return') }} (₹)</label>
                                <div class="form-control form-control-sm text-end fw-bold bg-success-subtle text-success border-success-subtle">
                                    ₹{{ changeDue.toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Cash Checkout Button -->
                        <button
                            type="button"
                            class="btn btn-success w-100 py-3 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                            :disabled="cart.length === 0 || checkingOut"
                            @click="processCheckout"
                        >
                            <span v-if="checkingOut" class="spinner-border spinner-border-sm"></span>
                            <span v-else>💵</span>
                            <span>{{ checkingOut ? $t('common.processing') : `${$t('pos.pay_and_print')} (₹${cartGrandTotal.toFixed(2)})` }}</span>
                        </button>
                    </div>

                    <!-- ============================================== -->
                    <!-- PAYMENT MODE 2: UPI / DYNAMIC QR CODE          -->
                    <!-- ============================================== -->
                    <div v-else-if="paymentMethod === 'upi'">
                        <div v-if="cart.length === 0" class="text-center py-4 text-muted bg-white rounded-3 border mb-3">
                            <div class="fs-2 mb-1">📱</div>
                            <div class="fw-semibold small">Dynamic UPI QR Code</div>
                            <small class="text-muted">Add products to your cart to generate a live QR code with the exact bill amount.</small>
                        </div>

                        <div v-else class="card border-0 shadow-xs rounded-3 p-3 bg-white mb-3">
                            <!-- QR Header -->
                            <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="badge bg-primary text-white fw-bold px-2 py-1">
                                        ⚡ DYNAMIC UPI QR
                                    </span>
                                    <div class="extra-small text-muted text-truncate mt-1" style="max-width: 160px;" :title="storeSettings.upi_id">
                                        {{ storeSettings.upi_id || 'smartstationery@upi' }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="extra-small text-muted text-uppercase d-block">Amount Due</span>
                                    <span class="fs-4 fw-bold text-success">₹{{ cartGrandTotal.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Live Dynamic QR Code Card -->
                            <div class="upi-qr-box text-center p-2 rounded-3 bg-light border position-relative mb-2">
                                <div v-if="generatingQr" class="py-4 text-muted small">
                                    <div class="spinner-border spinner-border-sm me-1 text-primary"></div> Generating QR...
                                </div>
                                <div v-else-if="upiQrDataUrl">
                                    <img :src="upiQrDataUrl" alt="Scan UPI QR" class="img-fluid rounded bg-white p-1 border shadow-xs upi-counter-qr" />
                                    <div class="extra-small fw-bold text-dark mt-1">
                                        Scan with GPay, PhonePe, Paytm, or Any UPI
                                    </div>
                                </div>
                                <div v-else class="py-4 text-muted small">
                                    Unable to generate QR code.
                                </div>

                                <!-- Supported Apps Pills -->
                                <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                                    <span class="badge bg-white text-dark border extra-small">Google Pay</span>
                                    <span class="badge bg-white text-dark border extra-small">PhonePe</span>
                                    <span class="badge bg-white text-dark border extra-small">Paytm</span>
                                    <span class="badge bg-white text-dark border extra-small">BHIM</span>
                                    <span class="badge bg-white text-dark border extra-small">Any UPI</span>
                                </div>
                            </div>

                            <!-- UPI Quick Actions -->
                            <div class="d-flex gap-1 mb-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary flex-grow-1 extra-small fw-semibold py-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs"
                                    @click="openCustomerUpiModal"
                                    title="Show enlarged customer display QR"
                                >
                                    <span>🔍 Enlarge QR</span>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary extra-small py-1 px-2"
                                    @click="copyUpiId"
                                    title="Copy UPI VPA ID"
                                >
                                    {{ copyUpiText }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary extra-small py-1 px-2"
                                    @click="toggleEditUpi"
                                    title="Configure UPI ID"
                                >
                                    ⚙️
                                </button>
                            </div>

                            <!-- Inline UPI ID Edit Drawer -->
                            <div v-if="editingUpi" class="mb-2 p-2 bg-light rounded border">
                                <label class="form-label extra-small fw-bold text-muted mb-1">Store UPI VPA / ID</label>
                                <div class="input-group input-group-sm">
                                    <input
                                        v-model="tempUpiId"
                                        type="text"
                                        class="form-control form-control-sm"
                                        placeholder="e.g. store@okhdfcbank"
                                    />
                                    <button class="btn btn-primary btn-sm px-2" type="button" @click="saveQuickUpiId">Save</button>
                                </div>
                            </div>

                            <!-- Optional Transaction Ref / UTR -->
                            <div class="mb-2">
                                <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">
                                    UPI Ref / UTR # (Optional)
                                </label>
                                <input
                                    v-model="upiReference"
                                    type="text"
                                    class="form-control form-control-sm font-monospace"
                                    placeholder="e.g. 12-digit UTR from customer app"
                                    maxlength="30"
                                />
                            </div>

                            <!-- UPI Checkout Button -->
                            <button
                                type="button"
                                class="btn btn-primary w-100 py-3 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                                :disabled="cart.length === 0 || checkingOut"
                                @click="processCheckout"
                            >
                                <span v-if="checkingOut" class="spinner-border spinner-border-sm"></span>
                                <span v-else>✅</span>
                                <span>{{ checkingOut ? 'Processing...' : `Confirm UPI (₹${cartGrandTotal.toFixed(2)}) & Print` }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- PAYMENT MODE 3: CARD / POS MACHINE             -->
                    <!-- ============================================== -->
                    <div v-else-if="paymentMethod === 'card'">
                        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="badge bg-secondary text-white fw-bold px-2 py-1">
                                    💳 CARD SWIPE / CHIP
                                </span>
                                <span class="fs-5 fw-bold text-primary">₹{{ cartGrandTotal.toFixed(2) }}</span>
                            </div>

                            <div class="mb-2">
                                <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">
                                    Card Network
                                </label>
                                <div class="btn-group btn-group-sm w-100">
                                    <button
                                        type="button"
                                        class="btn"
                                        :class="cardType === 'Visa' ? 'btn-primary' : 'btn-outline-secondary'"
                                        @click="cardType = 'Visa'"
                                    >
                                        Visa
                                    </button>
                                    <button
                                        type="button"
                                        class="btn"
                                        :class="cardType === 'Mastercard' ? 'btn-primary' : 'btn-outline-secondary'"
                                        @click="cardType = 'Mastercard'"
                                    >
                                        Mastercard
                                    </button>
                                    <button
                                        type="button"
                                        class="btn"
                                        :class="cardType === 'RuPay' ? 'btn-primary' : 'btn-outline-secondary'"
                                        @click="cardType = 'RuPay'"
                                    >
                                        RuPay
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">
                                    Card Auth / Approval Code (Optional)
                                </label>
                                <input
                                    v-model="cardReference"
                                    type="text"
                                    class="form-control form-control-sm font-monospace"
                                    placeholder="e.g. 6-digit approval code from swipe machine"
                                    maxlength="20"
                                />
                            </div>

                            <!-- Card Checkout Button -->
                            <button
                                type="button"
                                class="btn btn-secondary w-100 py-3 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                                :disabled="cart.length === 0 || checkingOut"
                                @click="processCheckout"
                            >
                                <span v-if="checkingOut" class="spinner-border spinner-border-sm"></span>
                                <span v-else>💳</span>
                                <span>{{ checkingOut ? 'Processing...' : `Confirm Card (₹${cartGrandTotal.toFixed(2)}) & Print` }}</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ============================================================ -->
        <!-- MODAL: PRINTABLE RECEIPT                                     -->
        <!-- ============================================================ -->
        <div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span>🧾</span>
                            <span class="fw-bold small text-uppercase">{{ $t('receipt.receipt_preview') }}</span>
                        </div>
                        <span class="badge bg-secondary">{{ $t('receipt.thermal_roll') }}</span>
                    </div>

                    <div class="modal-body p-3 bg-light" id="printableThermalReceipt">
                        <div class="pos-receipt-paper bg-white p-3 mx-auto shadow-sm text-start font-monospace" :dir="isRtl(getCurrentLanguage()) ? 'rtl' : 'ltr'">
                            <!-- Store Header -->
                            <div class="text-center mb-2 pb-2 border-bottom border-dark">
                                <h5 class="fw-bold mb-0 text-uppercase tracking-wider">
                                    {{ storeSettings.site_name || 'Retail POS Store' }}
                                </h5>
                                <div class="small fw-semibold text-muted text-uppercase">{{ $t('receipt.tax_invoice_receipt') }}</div>
                                <div class="extra-small text-secondary" v-if="storeSettings.contact_address">
                                    {{ storeSettings.contact_address }}
                                </div>
                                <div class="extra-small text-secondary" v-if="storeSettings.contact_phone">
                                    Tel: {{ storeSettings.contact_phone }}
                                </div>
                            </div>

                            <!-- Invoice & Sale Metadata -->
                            <div class="receipt-meta small border-bottom border-dark border-dashed pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span><strong>{{ $t('receipt.inv_no') }}:</strong> {{ lastSale?.invoice_number }}</span>
                                    <span><strong>{{ $t('receipt.mode') }}:</strong> {{ lastSale?.payment_method?.toUpperCase() }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><strong>{{ $t('common.date') }}:</strong> {{ formatDateTime(lastSale?.sale_date) }}</span>
                                </div>
                                <div class="d-flex justify-content-between" v-if="lastSale?.customer?.name">
                                    <span><strong>{{ $t('orders.customer') }}:</strong> {{ lastSale?.customer?.name }}</span>
                                    <span v-if="lastSale?.customer?.phone">{{ lastSale?.customer?.phone }}</span>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <table class="receipt-table w-100 mb-2">
                                <thead>
                                    <tr class="border-top border-bottom border-dark border-dashed">
                                        <th class="py-1 text-start">{{ $t('receipt.item') }}</th>
                                        <th class="py-1 text-center" style="width: 45px;">{{ $t('receipt.qty') }}</th>
                                        <th class="py-1 text-end" style="width: 65px;">{{ $t('receipt.rate') }}</th>
                                        <th class="py-1 text-end" style="width: 75px;">{{ $t('receipt.amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in lastSale?.items" :key="item.id" class="border-bottom border-light">
                                        <td class="py-1 text-start">
                                            <div class="fw-semibold text-truncate" style="max-width: 140px;" :title="item.product?.name || item.name">
                                                {{ item.product?.name || item.name || '-' }}
                                            </div>
                                            <small class="text-muted" v-if="item.variant?.sku">
                                                {{ item.variant.sku }}
                                            </small>
                                        </td>
                                        <td class="py-1 text-center align-top">{{ Number(item.quantity) }}</td>
                                        <td class="py-1 text-end align-top">₹{{ Number(item.unit_price).toFixed(2) }}</td>
                                        <td class="py-1 text-end align-top fw-bold">₹{{ Number(item.total).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Totals & Calculations -->
                            <div class="border-top border-dark border-dashed pt-2 small">
                                <div class="d-flex justify-content-between py-0">
                                    <span class="text-muted">{{ $t('receipt.total_items') }}:</span>
                                    <span>{{ lastSale?.items?.length || 0 }} ({{ $t('receipt.qty') }}: {{ totalReceiptQty }})</span>
                                </div>
                                <div class="d-flex justify-content-between py-0">
                                    <span>{{ $t('common.subtotal') }}:</span>
                                    <span>₹{{ Number(lastSale?.subtotal || 0).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-0" v-if="Number(lastSale?.tax_amount) > 0">
                                    <span>{{ $t('common.tax') }}:</span>
                                    <span>+ ₹{{ Number(lastSale?.tax_amount).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-0" v-if="Number(lastSale?.discount_amount) > 0">
                                    <span>{{ $t('common.discount') }}{{ lastSale?.coupon_code ? ` (${lastSale.coupon_code})` : '' }}:</span>
                                    <span>- ₹{{ Number(lastSale?.discount_amount).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between fw-bold fs-6 border-top border-bottom border-dark border-dashed my-1 py-1">
                                    <span>{{ $t('receipt.net_payable') }}:</span>
                                    <span>₹{{ Number(lastSale?.total_amount || 0).toFixed(2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between py-0">
                                    <span>{{ $t('receipt.payment_mode') }}:</span>
                                    <span class="fw-bold">{{ lastSale?.payment_method?.toUpperCase() }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-0" v-if="lastSale?.payment_method === 'cash'">
                                    <span>{{ $t('receipt.amount_tendered') }}:</span>
                                    <span>₹{{ Number(lastSale?.paid_amount || 0).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold text-success" v-if="lastSale?.payment_method === 'cash' && Number(lastSale?.change_amount) > 0">
                                    <span>{{ $t('receipt.change_returned') }}:</span>
                                    <span>₹{{ Number(lastSale?.change_amount).toFixed(2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-0 extra-small" v-if="lastSale?.notes">
                                    <span class="text-muted">{{ $t('receipt.ref_txn') }}:</span>
                                    <span class="fw-semibold text-dark">{{ lastSale.notes }}</span>
                                </div>
                            </div>

                            <!-- Receipt Barcode -->
                            <div class="text-center my-2 pt-2 border-top border-dashed" v-if="lastSale?.invoice_number">
                                <svg id="receipt-invoice-barcode" class="receipt-barcode"></svg>
                            </div>

                            <!-- Footer Thank You -->
                            <div class="text-center border-top border-dark border-dashed pt-2 extra-small text-muted">
                                <div class="fw-bold text-dark mb-1">{{ $t('receipt.thank_you_shopping') }}</div>
                                <div>{{ $t('receipt.retain_receipt') }}</div>
                                <div>{{ $t('receipt.computer_generated') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer p-2 justify-content-between bg-light">
                        <button type="button" class="btn btn-sm btn-primary px-3 fw-bold shadow-sm d-flex align-items-center gap-1" @click="printReceipt">
                            <span>🖨️</span>
                            <span>{{ $t('receipt.print_receipt') }}</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" @click="closeReceiptAndReset">
                            {{ $t('receipt.new_sale') }}
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
                                        <th class="text-end">Receipt</th>
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
                                        <td class="text-end">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary py-0 px-2"
                                                @click="reprintHistorySale(sale)"
                                                title="Print receipt"
                                            >
                                                🖨️ Print
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: CUSTOMER DISPLAY UPI QR CODE                          -->
        <!-- ============================================================ -->
        <div class="modal fade" id="customerUpiModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">📱</span>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Scan & Pay via UPI</h5>
                                <small class="text-white-50">{{ storeSettings.site_name || 'Retail POS Store' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4 text-center bg-white">
                        <!-- Amount Banner -->
                        <div class="mb-3">
                            <span class="extra-small text-uppercase fw-bold text-muted tracking-wider">Payable Total</span>
                            <div class="display-5 fw-bold text-success my-1 font-monospace">
                                ₹{{ cartGrandTotal.toFixed(2) }}
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                Exact Bill Amount Auto-Loaded
                            </span>
                        </div>

                        <!-- High-Res QR Display -->
                        <div class="p-3 bg-light rounded-4 d-inline-block border shadow-sm my-2 position-relative">
                            <img v-if="upiQrDataUrl" :src="upiQrDataUrl" alt="Scan to Pay UPI" class="img-fluid" style="width: 250px; height: 250px;" />
                            <div v-else class="py-5 text-muted">Generating QR...</div>
                        </div>

                        <!-- Merchant / VPA Info -->
                        <div class="mt-3 small">
                            <div class="text-muted">Payee Merchant:</div>
                            <div class="fw-bold fs-6 text-dark">{{ storeSettings.upi_merchant_name || storeSettings.site_name }}</div>
                            <div class="text-muted font-monospace extra-small bg-light d-inline-block px-3 py-1 rounded-pill mt-1 border">
                                VPA: {{ storeSettings.upi_id || 'smartstationery@upi' }}
                            </div>
                        </div>

                        <!-- Supported Apps Badges -->
                        <div class="d-flex justify-content-center align-items-center gap-1 mt-3 pt-3 border-top flex-wrap">
                            <span class="badge bg-light text-dark border">Google Pay</span>
                            <span class="badge bg-light text-dark border">PhonePe</span>
                            <span class="badge bg-light text-dark border">Paytm</span>
                            <span class="badge bg-light text-dark border">BHIM</span>
                            <span class="badge bg-light text-dark border">Cred</span>
                            <span class="badge bg-light text-dark border">Any UPI App</span>
                        </div>
                    </div>

                    <div class="modal-footer p-3 bg-light justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button
                            type="button"
                            class="btn btn-success fw-bold px-4 rounded-pill shadow-sm d-flex align-items-center gap-2"
                            :disabled="checkingOut"
                            @click="processCheckout"
                        >
                            <span v-if="checkingOut" class="spinner-border spinner-border-sm"></span>
                            <span v-else>✅</span>
                            <span>Payment Received - Complete Bill</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { getCurrentLanguage, isRtl } from '../../i18n';
import { Modal } from 'bootstrap';
import api from '../../services/api';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';
import ThemeToggle from '../../components/common/ThemeToggle.vue';

const { t } = useI18n();

// Store Settings
const storeSettings = ref({
    site_name: 'Smart Stationery',
    contact_address: '',
    contact_phone: '',
    contact_email: '',
    upi_id: 'smartstationery@upi',
    upi_merchant_name: 'Smart Stationery Store',
    upi_enabled: '1',
});

// UPI & Card Payment State
const upiQrDataUrl = ref('');
const upiReference = ref('');
const cardReference = ref('');
const cardType = ref('Visa');
const generatingQr = ref(false);
const editingUpi = ref(false);
const tempUpiId = ref('');
const copyUpiText = ref('📋 Copy');
let customerUpiModal = null;

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
const posCouponCode = ref('');
const appliedCoupon = ref(null);
const couponDiscountAmount = ref(0);
const applyingCoupon = ref(false);
const couponError = ref('');
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

const totalReceiptQty = computed(() => {
    if (!lastSale.value?.items) return 0;
    return lastSale.value.items.reduce((sum, it) => sum + Number(it.quantity || 0), 0);
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
            image_url: prod.image_url || null,
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
        appliedCoupon.value = null;
        couponDiscountAmount.value = 0;
        posCouponCode.value = '';
        couponError.value = '';
        paidAmount.value = 0;
        focusBarcodeInput();
    }
}

// -------------------------------------------------------------------------
// Coupon Handlers
// -------------------------------------------------------------------------
async function applyPosCoupon() {
    couponError.value = '';
    const code = posCouponCode.value.trim().toUpperCase();
    if (!code) return;

    if (cart.value.length === 0) {
        couponError.value = 'Cart is empty. Add products before applying a coupon.';
        return;
    }

    applyingCoupon.value = true;
    try {
        const payload = {
            code: code,
            customer_id: selectedCustomerId.value || null,
            items: cart.value.map(item => ({
                product_id: item.product_id,
                quantity: Number(item.quantity),
                unit_price: Number(item.unit_price),
            })),
        };

        const res = await api.post('/pos/coupons/validate', payload);
        const data = res.data.data;
        appliedCoupon.value = data.coupon;
        couponDiscountAmount.value = Number(data.discount_amount || 0);
        discountAmount.value = couponDiscountAmount.value;
        posCouponCode.value = data.coupon.code;
        syncPaidAmount();

        if (paymentMethod.value === 'upi') {
            generateUpiQr();
        }
    } catch (err) {
        couponError.value = err.response?.data?.message || 'Invalid or ineligible coupon code.';
    } finally {
        applyingCoupon.value = false;
    }
}

function removePosCoupon() {
    appliedCoupon.value = null;
    posCouponCode.value = '';
    couponDiscountAmount.value = 0;
    discountAmount.value = 0;
    couponError.value = '';
    syncPaidAmount();

    if (paymentMethod.value === 'upi') {
        generateUpiQr();
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
        coupon: appliedCoupon.value,
        couponDiscount: couponDiscountAmount.value,
        couponCode: posCouponCode.value,
        grandTotal: cartGrandTotal.value,
        heldAt: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    });

    cart.value = [];
    discountAmount.value = 0;
    appliedCoupon.value = null;
    couponDiscountAmount.value = 0;
    posCouponCode.value = '';
    couponError.value = '';
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
    appliedCoupon.value = bill.coupon || null;
    couponDiscountAmount.value = bill.couponDiscount || 0;
    posCouponCode.value = bill.couponCode || '';
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
        let checkoutNotes = '';
        if (paymentMethod.value === 'upi') {
            checkoutNotes = upiReference.value ? `UPI UTR: ${upiReference.value}` : 'UPI QR Payment';
        } else if (paymentMethod.value === 'card') {
            checkoutNotes = cardReference.value ? `Card (${cardType.value}) Ref: ${cardReference.value}` : `Card Payment (${cardType.value})`;
        }

        const payload = {
            customer_id: selectedCustomerId.value,
            payment_method: paymentMethod.value,
            paid_amount: Number(paidAmount.value || cartGrandTotal.value),
            discount_amount: Number(discountAmount.value || 0),
            coupon_code: appliedCoupon.value ? appliedCoupon.value.code : null,
            notes: checkoutNotes,
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

        // Close customer display modal if open
        customerUpiModal?.hide();

        // Open Printable Receipt & render barcode
        receiptModal?.show();
        renderReceiptBarcode();

        // Reset payment references
        upiReference.value = '';
        cardReference.value = '';

        // Refresh stock catalog
        loadProducts();

    } catch (err) {
        alert(err.response?.data?.message || 'Checkout failed. Please check stock and amounts.');
    } finally {
        checkingOut.value = false;
    }
}

// -------------------------------------------------------------------------
// UPI Payment Handlers
// -------------------------------------------------------------------------
async function generateUpiQr() {
    if (cartGrandTotal.value <= 0) {
        upiQrDataUrl.value = '';
        return;
    }

    generatingQr.value = true;
    try {
        const upiId = storeSettings.value?.upi_id || 'smartstationery@upi';
        const payeeName = storeSettings.value?.upi_merchant_name || storeSettings.value?.site_name || 'Retail POS';
        const amount = Number(cartGrandTotal.value).toFixed(2);
        const customerObj = customers.value.find(c => c.id === selectedCustomerId.value);
        const note = `POS Bill ${customerObj?.name ? customerObj.name.substring(0, 15) : 'Retail'}`;

        const uri = `upi://pay?pa=${encodeURIComponent(upiId)}&pn=${encodeURIComponent(payeeName)}&am=${amount}&cu=INR&tn=${encodeURIComponent(note)}`;

        upiQrDataUrl.value = await QRCode.toDataURL(uri, {
            width: 260,
            margin: 1,
            color: {
                dark: '#0f172a',
                light: '#ffffff',
            },
        });
    } catch (err) {
        console.error('Error generating UPI QR:', err);
    } finally {
        generatingQr.value = false;
    }
}

watch(
    [() => cartGrandTotal.value, () => paymentMethod.value, () => storeSettings.value?.upi_id],
    () => {
        if (paymentMethod.value === 'upi') {
            paidAmount.value = cartGrandTotal.value;
            generateUpiQr();
        } else if (paymentMethod.value === 'card') {
            paidAmount.value = cartGrandTotal.value;
        }
    },
    { immediate: true }
);

function openCustomerUpiModal() {
    if (cartGrandTotal.value <= 0) {
        alert('Please add products to cart first.');
        return;
    }
    customerUpiModal?.show();
}

function copyUpiId() {
    const upiId = storeSettings.value?.upi_id || 'smartstationery@upi';
    navigator.clipboard?.writeText(upiId);
    copyUpiText.value = '✓ Copied';
    setTimeout(() => {
        copyUpiText.value = '📋 Copy';
    }, 2000);
}

function toggleEditUpi() {
    editingUpi.value = !editingUpi.value;
    if (editingUpi.value) {
        tempUpiId.value = storeSettings.value?.upi_id || 'smartstationery@upi';
    }
}

async function saveQuickUpiId() {
    if (!tempUpiId.value) return;
    const cleanId = tempUpiId.value.trim();
    storeSettings.value.upi_id = cleanId;
    editingUpi.value = false;
    try {
        await api.post('/settings', { upi_id: cleanId });
    } catch (e) {
        // Ignored
    }
    generateUpiQr();
}

// Render barcode on receipt modal
function renderReceiptBarcode() {
    nextTick(() => {
        if (!lastSale.value?.invoice_number) return;
        try {
            const el = document.getElementById('receipt-invoice-barcode');
            if (el) {
                JsBarcode(el, lastSale.value.invoice_number, {
                    format: 'CODE128',
                    width: 1.4,
                    height: 36,
                    displayValue: true,
                    fontSize: 10,
                    textMargin: 2,
                    margin: 0,
                });
            }
        } catch (e) {
            console.error('Receipt barcode render error:', e);
        }
    });
}

// Reprint receipt from past history sale
async function reprintHistorySale(sale) {
    try {
        const res = await api.get(`/pos/invoice/${sale.id}`);
        lastSale.value = res.data.data;
        historyModal?.hide();
        receiptModal?.show();
        renderReceiptBarcode();
    } catch (e) {
        console.error('Failed to load invoice for reprint:', e);
        alert('Could not load receipt details for this invoice.');
    }
}

// Clean isolated iframe printing - multilingual & RTL compliant
function printReceipt() {
    const container = document.getElementById('printableThermalReceipt');
    if (!container) return;

    // Remove any previous frame
    const existing = document.getElementById('pos-receipt-print-frame');
    if (existing) {
        existing.remove();
    }

    // Create an isolated hidden iframe
    const printFrame = document.createElement('iframe');
    printFrame.id = 'pos-receipt-print-frame';
    printFrame.style.position = 'fixed';
    printFrame.style.top = '-9999px';
    printFrame.style.left = '-9999px';
    printFrame.style.width = '80mm';
    printFrame.style.height = '150mm';
    printFrame.style.border = 'none';
    document.body.appendChild(printFrame);

    const frameDoc = printFrame.contentWindow.document;
    const currentLang = getCurrentLanguage();
    const rtl = isRtl(currentLang);

    const fontFamily = rtl
        ? "'Noto Sans Arabic', Tahoma, Arial, sans-serif"
        : (currentLang === 'hi'
            ? "'Noto Sans Devanagari', 'Segoe UI', Arial, sans-serif"
            : (currentLang === 'bn'
                ? "'Noto Sans Bengali', 'Segoe UI', Arial, sans-serif"
                : "'Courier New', Courier, monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, sans-serif"));

    const styles = `
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;600;700&family=Noto+Sans+Bengali:wght@400;600;700&family=Noto+Sans+Devanagari:wght@400;600;700&display=swap');
        @page {
            size: 80mm auto;
            margin: 0;
        }
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
            font-family: ${fontFamily};
            font-size: 11px;
            line-height: 1.35;
            width: 80mm;
            direction: ${rtl ? 'rtl' : 'ltr'};
            text-align: ${rtl ? 'right' : 'left'};
        }
        .pos-receipt-paper {
            width: 76mm;
            margin: 0 auto;
            padding: 4mm 1.5mm;
            background: #ffffff !important;
            color: #000000 !important;
            direction: ${rtl ? 'rtl' : 'ltr'};
            text-align: ${rtl ? 'right' : 'left'};
        }
        .text-center { text-align: center !important; }
        .text-start { text-align: ${rtl ? 'right' : 'left'} !important; }
        .text-end { text-align: ${rtl ? 'left' : 'right'} !important; }
        .fw-bold { font-weight: bold; }
        .fw-semibold { font-weight: 600; }
        .text-uppercase { text-transform: uppercase; }
        .text-muted, .text-secondary { color: #333333 !important; }
        .border-bottom { border-bottom: 1px dashed #000000; }
        .border-top { border-top: 1px dashed #000000; }
        .border-dashed { border-style: dashed !important; }
        .border-dark { border-color: #000000 !important; }
        .my-1 { margin-top: 3px; margin-bottom: 3px; }
        .my-2 { margin-top: 6px; margin-bottom: 6px; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 3px; }
        .mb-2 { margin-bottom: 6px; }
        .pb-2 { padding-bottom: 6px; }
        .pt-2 { padding-top: 6px; }
        .py-1 { padding-top: 2px; padding-bottom: 2px; }
        .py-0 { padding-top: 1px; padding-bottom: 1px; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-top { vertical-align: top; }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 6px 0;
        }
        .receipt-table th {
            border-top: 1px dashed #000000;
            border-bottom: 1px dashed #000000;
            padding: 3px 1px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: ${rtl ? 'right' : 'left'};
        }
        .receipt-table th.text-center { text-align: center !important; }
        .receipt-table th.text-end { text-align: ${rtl ? 'left' : 'right'} !important; }
        .receipt-table td {
            padding: 2.5px 1px;
            text-align: ${rtl ? 'right' : 'left'};
        }
        .receipt-table td.text-center { text-align: center !important; }
        .receipt-table td.text-end { text-align: ${rtl ? 'left' : 'right'} !important; }
        .receipt-barcode {
            max-width: 90%;
            height: 36px;
            display: inline-block;
        }
        .extra-small {
            font-size: 9.5px;
            line-height: 1.25;
        }
        .no-print, .btn, .modal-footer, .modal-header {
            display: none !important;
        }
    `;

    // Extract the receipt paper HTML
    const receiptPaper = container.querySelector('.pos-receipt-paper');
    const receiptHtml = receiptPaper ? receiptPaper.outerHTML : container.innerHTML;

    frameDoc.open();
    frameDoc.write(`<!DOCTYPE html>
<html lang="${currentLang}" dir="${rtl ? 'rtl' : 'ltr'}">
<head>
    <meta charset="utf-8">
    <title>${t('receipt.tax_invoice_receipt')} - ${lastSale.value?.invoice_number || 'Sale'}</title>
    <style>${styles}</style>
</head>
<body dir="${rtl ? 'rtl' : 'ltr'}">
    ${receiptHtml}
</body>
</html>`);
    frameDoc.close();

    // Trigger print in the isolated iframe after rendering
    setTimeout(() => {
        try {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        } catch (e) {
            console.error('Receipt iframe print error:', e);
            window.print();
        }
        setTimeout(() => {
            printFrame.remove();
        }, 3000);
    }, 250);
}

function closeReceiptAndReset() {
    receiptModal?.hide();
    cart.value = [];
    discountAmount.value = 0;
    appliedCoupon.value = null;
    couponDiscountAmount.value = 0;
    posCouponCode.value = '';
    couponError.value = '';
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

async function loadStoreSettings() {
    try {
        const res = await api.get('/settings');
        if (res.data && res.data.data) {
            storeSettings.value = { ...storeSettings.value, ...res.data.data };
        }
    } catch (e) {
        // Fallback default
    }
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

    const cUpiEl = document.getElementById('customerUpiModal');
    if (cUpiEl) customerUpiModal = new Modal(cUpiEl);

    loadCategories();
    loadCustomers();
    loadProducts();
    loadStoreSettings();
    focusBarcodeInput();
});

onBeforeUnmount(() => {
    receiptModal?.dispose();
    quickCustomerModal?.dispose();
    heldBillsModal?.dispose();
    historyModal?.dispose();
    customerUpiModal?.dispose();
    clearTimeout(searchDebounceTimer);
    const existing = document.getElementById('pos-receipt-print-frame');
    if (existing) {
        existing.remove();
    }
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
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.pos-checkout::-webkit-scrollbar {
    width: 5px;
}

.pos-checkout::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}

.pos-checkout::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
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

.product-thumb-wrap {
    height: 120px;
    width: 100%;
    overflow: hidden;
    background-color: #f1f5f9;
}

.product-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.25s ease;
}

.product-card:hover .product-thumb-img {
    transform: scale(1.06);
}

.product-thumb-placeholder {
    height: 100%;
    width: 100%;
    color: #94a3b8;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.cart-item-thumb-box {
    width: 36px;
    height: 36px;
    border-color: #e2e8f0 !important;
}

.cart-item-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-thumb-icon {
    font-size: 15px;
    opacity: 0.65;
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
    overflow-x: hidden;
    flex: 1 1 auto;
    min-height: 120px;
    max-height: 280px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.cart-items-wrap::-webkit-scrollbar {
    width: 5px;
}

.cart-items-wrap::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}

.cart-items-wrap::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

.cart-row {
    min-height: 52px;
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.cart-row:hover {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.stepper-btn {
    width: 26px;
    height: 26px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 13px;
    line-height: 1;
}

.stepper-btn:hover {
    background-color: #e2e8f0;
}

.remove-item-btn {
    font-size: 16px;
    line-height: 1;
    opacity: 0.6;
    transition: opacity 0.15s ease;
}

.remove-item-btn:hover {
    opacity: 1;
}

.cart-qty-input {
    width: 48px;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Receipt Paper Modal Display Styling */
.pos-receipt-paper {
    width: 100%;
    max-width: 340px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-family: 'Courier New', Courier, monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
}

.border-dashed {
    border-style: dashed !important;
}

.extra-small {
    font-size: 10px;
    line-height: 1.3;
}

.receipt-barcode {
    max-width: 90%;
    height: 38px;
    margin: 0 auto;
}

.upi-counter-qr {
    width: 140px;
    height: 140px;
    object-fit: contain;
}

.shadow-xs {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

/* Receipt Print Styling: isolate and prevent web page bleed */
@media print {
    body.pos-printing-receipt {
        visibility: hidden !important;
    }
}
</style>

