<template>
    <div class="product-detail py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none">{{ $t('storefront.home') }}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/shop" class="text-decoration-none">{{ $t('storefront.catalog') }}</router-link></li>
                    <li class="breadcrumb-item" v-if="product?.category_name">
                        <router-link :to="{ path: '/shop', query: { category_id: product.category_id } }" class="text-decoration-none">
                            {{ product.category_name }}
                        </router-link>
                    </li>
                    <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 200px;">
                        {{ product?.name || 'Loading...' }}
                    </li>
                </ol>
            </nav>

            <!-- Loading Spinner -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">{{ $t('storefront.loading_details') }}</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="card border-0 shadow-sm p-5 text-center">
                <div class="fs-1 text-danger mb-2">⚠️</div>
                <h4 class="fw-bold">{{ $t('storefront.product_not_found') }}</h4>
                <p class="text-muted small mb-3">{{ error }}</p>
                <div>
                    <router-link to="/shop" class="btn btn-primary rounded-pill px-4">
                        {{ $t('storefront.back_to_shop') }}
                    </router-link>
                </div>
            </div>

            <!-- Product Detail View -->
            <div v-else-if="product" class="row g-5">
                <!-- Gallery Section -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white text-center">
                        <div class="main-image-wrap d-flex align-items-center justify-content-center">
                            <img
                                v-if="activeImage"
                                :src="activeImage"
                                :alt="product.name"
                                class="main-image img-fluid"
                            />
                            <div v-else class="fs-1 text-muted py-5">📦 {{ $t('storefront.no_image') }}</div>
                        </div>
                    </div>

                    <!-- Thumbnails row if multiple images -->
                    <div v-if="product.images && product.images.length > 1" class="d-flex gap-2 flex-wrap">
                        <div
                            v-for="(img, idx) in product.images"
                            :key="idx"
                            class="thumb-box p-1 border rounded cursor-pointer"
                            :class="{ 'border-primary border-2': activeImage === (img.url || img) }"
                            @click="activeImage = (img.url || img)"
                        >
                            <img :src="img.url || img" class="thumb-img" />
                        </div>
                    </div>
                </div>

                <!-- Info & Purchase Section -->
                <div class="col-lg-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary-subtle text-primary text-uppercase px-2 py-1">
                            {{ product.category_name }}
                        </span>
                        <span v-if="product.brand_name" class="badge bg-secondary-subtle text-secondary px-2 py-1">
                            {{ $t('products.brand') }}: {{ product.brand_name }}
                        </span>
                    </div>

                    <h2 class="fw-extrabold text-dark mb-2">{{ product.name }}</h2>

                    <div class="small text-muted mb-3 d-flex align-items-center gap-3">
                        <span v-if="product.variant?.sku">SKU: <strong>{{ product.variant.sku }}</strong></span>
                        <span v-if="product.variant?.barcode">Barcode: <strong>{{ product.variant.barcode }}</strong></span>
                    </div>

                    <!-- Pricing Display -->
                    <div class="p-3 bg-light rounded-3 mb-4 d-flex align-items-center gap-3">
                        <div>
                            <div class="fs-2 fw-bold text-primary">
                                ₹{{ formatMoney(product.variant?.selling_price) }}
                            </div>
                            <div class="extra-small text-muted">
                                {{ $t('storefront.inclusive_tax') }} ({{ product.variant?.tax_rate || 0 }}% GST)
                            </div>
                        </div>

                        <div v-if="product.variant && product.variant.mrp > product.variant.selling_price" class="border-start ps-3">
                            <div class="text-muted text-decoration-line-through small">
                                MRP: ₹{{ formatMoney(product.variant.mrp) }}
                            </div>
                            <span class="badge bg-success text-white rounded-pill px-2 py-1">
                                {{ $t('storefront.save_discount') }} {{ calcDiscount(product.variant.selling_price, product.variant.mrp) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Live Inventory Status -->
                    <div class="mb-4">
                        <div v-if="!product.track_inventory || product.stock > 0" class="d-flex align-items-center gap-2 text-success">
                            <span class="fs-5">✔</span>
                            <span class="fw-bold">
                                {{ $t('storefront.in_stock_ready') }}
                            </span>
                            <span class="badge bg-success-subtle text-success small">
                                {{ product.stock }} {{ product.unit?.short_name || 'units' }} available
                            </span>
                        </div>
                        <div v-else class="d-flex align-items-center gap-2 text-danger">
                            <span class="fs-5">✖</span>
                            <span class="fw-bold">{{ $t('storefront.currently_out_of_stock') }}</span>
                            <span class="badge bg-danger-subtle text-danger small">
                                {{ $t('storefront.check_back_soon') }}
                            </span>
                        </div>
                    </div>

                    <!-- Quantity Stepper & Buttons -->
                    <div class="mb-4" v-if="!product.track_inventory || product.stock > 0">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-2">{{ $t('common.quantity') }}</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group" style="width: 140px;">
                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    :disabled="quantity <= 1"
                                    @click="decreaseQty"
                                >
                                    -
                                </button>
                                <input
                                    v-model.number="quantity"
                                    type="number"
                                    class="form-control text-center fw-bold"
                                    min="1"
                                    :max="product.track_inventory ? product.stock : 999"
                                    @change="validateQty"
                                />
                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    :disabled="product.track_inventory && quantity >= product.stock"
                                    @click="increaseQty"
                                >
                                    +
                                </button>
                            </div>
                            <span class="small text-muted">
                                {{ $t('products.unit') }}: {{ product.unit?.name || 'Piece' }}
                            </span>
                        </div>
                    </div>

                    <!-- Call To Action Buttons -->
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <button
                            class="btn btn-outline-primary btn-lg px-4 flex-grow-1 fw-bold rounded-pill"
                            :disabled="product.track_inventory && product.stock <= 0"
                            @click="handleAddToCart"
                        >
                            🛒 {{ $t('storefront.add_to_cart') }}
                        </button>
                        <button
                            class="btn btn-primary btn-lg px-4 flex-grow-1 fw-bold rounded-pill"
                            :disabled="product.track_inventory && product.stock <= 0"
                            @click="handleBuyNow"
                        >
                            ⚡ {{ $t('storefront.buy_now') }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-lg rounded-pill px-4 fw-semibold border d-flex align-items-center justify-content-center gap-2"
                            :class="isInWishlist(product.id) ? 'btn-danger text-white border-danger shadow-sm' : 'btn-light border text-dark'"
                            @click="toggleWishlist(product)"
                        >
                            <span style="font-size: 1.2rem;">{{ isInWishlist(product.id) ? '❤️' : '🤍' }}</span>
                            <span>{{ isInWishlist(product.id) ? $t('storefront.saved_in_wishlist') : $t('storefront.add_to_wishlist') }}</span>
                        </button>
                    </div>

                    <!-- Trust Cards -->
                    <div class="card border-0 bg-light-subtle rounded-3 p-3 mb-4">
                        <div class="row g-2 text-muted small">
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span>🚚</span> {{ $t('storefront.free_delivery') }}
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span>💵</span> {{ $t('checkout.cash_on_delivery') }}
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span>⚡</span> {{ $t('storefront.direct_stock') }}
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <span>🛡️</span> {{ $t('storefront.secure_checkout') }}
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="card border-0 shadow-sm rounded-3 p-4">
                        <h5 class="fw-bold mb-3">{{ $t('storefront.product_description') }}</h5>
                        <p class="text-secondary mb-0" style="white-space: pre-line;">
                            {{ product.description || $t('storefront.no_description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useCart } from '../../composables/useCart';
import { useWishlist } from '../../composables/useWishlist';
import { useNotification } from '../../composables/useNotification';

const route = useRoute();
const router = useRouter();
const { addToCart } = useCart();
const { isInWishlist, toggleWishlist } = useWishlist();
const { toast } = useNotification();

const product = ref(null);
const activeImage = ref(null);
const quantity = ref(1);
const loading = ref(true);
const error = ref(null);

function calcDiscount(selling, mrp) {
    if (!mrp || mrp <= selling) return 0;
    return Math.round(((mrp - selling) / mrp) * 100);
}

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function increaseQty() {
    if (product.value?.track_inventory && quantity.value >= product.value.stock) {
        return;
    }
    quantity.value++;
}

function decreaseQty() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}

function validateQty() {
    if (quantity.value < 1) quantity.value = 1;
    if (product.value?.track_inventory && quantity.value > product.value.stock) {
        quantity.value = product.value.stock;
    }
}

function handleAddToCart() {
    if (!product.value) return;
    const ok = addToCart(product.value, quantity.value);
    if (ok) {
        toast.success(`Added ${quantity.value} item(s) to your shopping cart!`, 'Cart Updated');
    }
}

function handleBuyNow() {
    if (!product.value) return;
    const ok = addToCart(product.value, quantity.value);
    if (ok) {
        router.push('/checkout');
    }
}

async function fetchProduct() {
    loading.value = true;
    error.value = null;
    try {
        const id = route.params.id;
        const res = await axios.get(`/api/shop/products/${id}`);
        product.value = res.data.data;
        const firstImg = product.value.images?.[0];
        activeImage.value = product.value.image_url || (firstImg?.url || firstImg) || null;
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to load product details.';
    } finally {
        loading.value = false;
    }
}

watch(
    () => route.params.id,
    () => {
        fetchProduct();
    }
);

onMounted(() => {
    fetchProduct();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.main-image-wrap {
    min-height: 380px;
    max-height: 480px;
}

.main-image {
    max-height: 440px;
    max-width: 100%;
    object-fit: contain;
}

.thumb-box {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.thumb-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}
</style>
