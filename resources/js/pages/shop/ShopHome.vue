<template>
    <div class="shop-home">
        <!-- Homepage Hero Slideshow -->
        <ShopSlideshow
            v-if="settings.slideshow_enabled !== '0'"
            :custom-slides="settings.slideshow_slides_list"
            :autoplay="settings.slideshow_autoplay !== '0'"
            :interval="Number(settings.slideshow_speed || 5000)"
        />

        <!-- Fallback Static Hero Section (if slideshow disabled) -->
        <section v-else class="hero-section py-5 text-white bg-gradient-hero position-relative">
            <div class="container py-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
                            {{ settings.hero_badge }}
                        </span>
                        <h1 class="display-4 fw-extrabold mb-3">
                            {{ settings.hero_title }} <br />
                            <span class="text-warning">{{ settings.hero_subtitle }}</span>
                        </h1>
                        <p class="lead text-light-opacity mb-4" style="max-width: 580px;">
                            {{ settings.hero_description }}
                        </p>

                        <!-- Search Bar -->
                        <div class="card shadow-lg border-0 p-2 rounded-pill bg-white mb-4" style="max-width: 550px;">
                            <form @submit.prevent="submitSearch" class="d-flex align-items-center">
                                <span class="ps-3 pe-2 text-muted fs-5">🔍</span>
                                <input
                                    v-model="heroSearch"
                                    type="text"
                                    class="form-control border-0 shadow-none ps-1"
                                    placeholder="Search by product name, barcode, brand..."
                                />
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                                    Search
                                </button>
                            </form>
                        </div>

                        <!-- Highlights Pills -->
                        <div class="d-flex flex-wrap gap-3 small text-white-50">
                            <span class="d-flex align-items-center gap-1 text-light">
                                <span class="text-success">✔</span> Free delivery over ₹500
                            </span>
                            <span class="d-flex align-items-center gap-1 text-light">
                                <span class="text-success">✔</span> Cash on Delivery & UPI
                            </span>
                            <span class="d-flex align-items-center gap-1 text-light">
                                <span class="text-success">✔</span> Instant Inventory Reserve
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-5 d-none d-lg-block text-center">
                        <div class="hero-card-promo p-4 rounded-4 shadow-lg text-start bg-white text-dark position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-semibold">
                                    {{ settings.hero_promo_badge }}
                                </span>
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded">
                                    ● Real-time Sync
                                </span>
                            </div>
                            <h4 class="fw-bold mb-2">{{ settings.hero_promo_title }}</h4>
                            <p class="text-muted small mb-3">
                                {{ settings.hero_promo_text }}
                            </p>
                            <div class="d-flex gap-2">
                                <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    Browse All Products →
                                </router-link>
                                <router-link to="/track-order" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    Track Order
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features / Trust Banner -->
        <section class="bg-white border-bottom py-4">
            <div class="container">
                <div class="row g-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="fs-2 text-primary">🚚</div>
                            <div class="text-start">
                                <div class="fw-bold small">Fast Delivery</div>
                                <div class="text-muted extra-small">Free for orders > ₹500</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="fs-2 text-success">💵</div>
                            <div class="text-start">
                                <div class="fw-bold small">COD Available</div>
                                <div class="text-muted extra-small">Pay upon receiving</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="fs-2 text-warning">⚡</div>
                            <div class="text-start">
                                <div class="fw-bold small">Live Inventory</div>
                                <div class="text-muted extra-small">Accurate counter stock</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="fs-2 text-info">🛡️</div>
                            <div class="text-start">
                                <div class="fw-bold small">Verified Quality</div>
                                <div class="text-muted extra-small">100% Genuine items</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-5 bg-light" v-if="categories.length">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">Browse by Categories</h3>
                        <p class="text-muted small mb-0">Explore our curated collections of items</p>
                    </div>
                    <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        View All
                    </router-link>
                </div>

                <div class="row g-3">
                    <div
                        v-for="cat in categories"
                        :key="cat.id"
                        class="col-6 col-md-4 col-lg-3"
                    >
                        <router-link
                            :to="{ path: '/shop', query: { category_id: cat.id } }"
                            class="card h-100 border-0 shadow-sm category-card text-decoration-none text-dark p-3 text-center"
                        >
                            <div class="category-icon mb-2">🏷️</div>
                            <h6 class="fw-bold mb-1 text-truncate">{{ cat.name }}</h6>
                            <span class="badge bg-light text-secondary rounded-pill small">
                                {{ cat.products_count }} Products
                            </span>
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">Featured Products</h3>
                        <p class="text-muted small mb-0">Live counter stock ready for immediate delivery</p>
                    </div>
                    <router-link to="/shop" class="btn btn-primary btn-sm rounded-pill px-3">
                        Shop All Products →
                    </router-link>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Loading products catalog...</p>
                </div>

                <!-- Products Grid -->
                <div v-else-if="products.length" class="row g-4">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="col-12 col-sm-6 col-md-4 col-lg-3"
                    >
                        <div class="card h-100 border-0 shadow-sm product-card position-relative d-flex flex-column">
                            <!-- Wishlist Heart Button -->
                            <button
                                type="button"
                                class="btn btn-sm position-absolute top-0 end-0 m-2 rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm wishlist-btn"
                                :class="isInWishlist(product.id) ? 'btn-danger text-white' : 'btn-light text-muted'"
                                style="z-index: 3; width: 34px; height: 34px;"
                                :title="isInWishlist(product.id) ? 'Remove from Wishlist' : 'Add to Wishlist'"
                                @click.prevent.stop="toggleWishlist(product)"
                            >
                                <span style="font-size: 15px;">{{ isInWishlist(product.id) ? '❤️' : '🤍' }}</span>
                            </button>

                            <!-- Out of Stock Overlay Badge -->
                            <div
                                v-if="product.track_inventory && product.stock <= 0"
                                class="position-absolute top-0 start-0 m-2 badge bg-danger text-white rounded-pill px-2 py-1 shadow-sm"
                                style="z-index: 2;"
                            >
                                Out of Stock
                            </div>

                            <!-- Discount badge if MRP > selling_price -->
                            <div
                                v-else-if="product.variant && product.variant.mrp > product.variant.selling_price"
                                class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark rounded-pill px-2 py-1 shadow-sm"
                                style="z-index: 2;"
                            >
                                {{ calcDiscount(product.variant.selling_price, product.variant.mrp) }}% OFF
                            </div>

                            <!-- Product Image -->
                            <router-link :to="`/product/${product.id}`" class="product-img-wrap text-center p-3 bg-white">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.name"
                                    class="product-img img-fluid"
                                />
                                <div v-else class="product-placeholder d-flex align-items-center justify-content-center">
                                    <span class="fs-1 text-muted">📦</span>
                                </div>
                            </router-link>

                            <!-- Card Body -->
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="text-muted extra-small mb-1 text-uppercase">
                                    {{ product.category_name }}
                                </div>
                                <h6 class="card-title fw-bold text-dark text-truncate mb-2">
                                    <router-link :to="`/product/${product.id}`" class="text-dark text-decoration-none">
                                        {{ product.name }}
                                    </router-link>
                                </h6>

                                <!-- Stock status -->
                                <div class="small mb-3">
                                    <span v-if="!product.track_inventory || product.stock > 0" class="text-success fw-medium">
                                        ● In Stock ({{ product.stock }} {{ product.unit?.short_name || 'units' }})
                                    </span>
                                    <span v-else class="text-danger fw-medium">
                                        ● Sold Out
                                    </span>
                                </div>

                                <!-- Price and Add to Cart -->
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                    <div>
                                        <div class="fs-5 fw-bold text-primary">
                                            ₹{{ formatMoney(product.variant?.selling_price) }}
                                        </div>
                                        <div
                                            v-if="product.variant && product.variant.mrp > product.variant.selling_price"
                                            class="extra-small text-muted text-decoration-line-through"
                                        >
                                            MRP: ₹{{ formatMoney(product.variant.mrp) }}
                                        </div>
                                    </div>

                                    <button
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium d-flex align-items-center gap-1"
                                        :disabled="product.track_inventory && product.stock <= 0"
                                        @click="handleAddToCart(product)"
                                    >
                                        <span>+ Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <p class="text-muted mb-3 fs-5">No products available at the moment.</p>
                    <router-link to="/shop" class="btn btn-primary rounded-pill">
                        Browse All Categories
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Customer Testimonials Section -->
        <CustomerTestimonials
            :custom-testimonials="settings.testimonials_list_items"
        />

        <!-- CTA Callout Banner -->
        <section class="py-5 bg-primary text-white">
            <div class="container text-center py-3">
                <h2 class="fw-bold mb-3">Need Something Delivered Right Away?</h2>
                <p class="lead mb-4 text-white-50" style="max-width: 600px; margin: 0 auto;">
                    Order online and our store staff will pack and prepare your parcel immediately. Track every step from order confirmation to doorstep delivery.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <router-link to="/shop" class="btn btn-warning btn-lg fw-bold px-4 rounded-pill">
                        Start Shopping Now
                    </router-link>
                    <router-link to="/track-order" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                        Track An Existing Order
                    </router-link>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useCart } from '../../composables/useCart';
import { useShopSettings } from '../../composables/useShopSettings';
import { useNotification } from '../../composables/useNotification';
import { useWishlist } from '../../composables/useWishlist';
import ShopSlideshow from '../../components/shop/ShopSlideshow.vue';
import CustomerTestimonials from '../../components/shop/CustomerTestimonials.vue';

const router = useRouter();
const { addToCart } = useCart();
const { isInWishlist, toggleWishlist } = useWishlist();
const { settings, loadSettings } = useShopSettings();
const { toast } = useNotification();

const heroSearch = ref('');
const categories = ref([]);
const products = ref([]);
const loading = ref(true);

function submitSearch() {
    if (heroSearch.value.trim()) {
        router.push({ path: '/shop', query: { search: heroSearch.value.trim() } });
    } else {
        router.push('/shop');
    }
}

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

function handleAddToCart(product) {
    const success = addToCart(product, 1);
    if (success) {
        toast.success(`'${product.name}' added to your shopping cart!`, 'Cart Updated');
    }
}

async function loadData() {
    loading.value = true;
    try {
        const [catsRes, prodsRes] = await Promise.all([
            axios.get('/api/shop/categories'),
            axios.get('/api/shop/products?per_page=8')
        ]);
        categories.value = catsRes.data.data || [];
        products.value = prodsRes.data.data || [];
    } catch (e) {
        console.error('Failed to load shop home data:', e);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadSettings();
    loadData();
});
</script>

<style scoped>
.bg-gradient-hero {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.text-light-opacity {
    color: rgba(255, 255, 255, 0.85);
}

.extra-small {
    font-size: 0.75rem;
}

.category-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08) !important;
}

.category-icon {
    font-size: 2rem;
}

.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1) !important;
}

.product-img-wrap {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fdfdfd;
}

.product-img {
    max-height: 160px;
    max-width: 100%;
    object-fit: contain;
}

.product-placeholder {
    height: 140px;
    background: #f8f9fa;
    border-radius: 8px;
    width: 100%;
}
</style>

