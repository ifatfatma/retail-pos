<template>
    <div class="shop-catalog py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none">Home</router-link></li>
                    <li class="breadcrumb-item active" aria-current="page">Shop Catalog</li>
                </ol>
            </nav>

            <div class="row g-4">
                <!-- Sidebar Filters -->
                <aside class="col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 p-3 sticky-top" style="top: 80px; z-index: 10;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Filters</h5>
                            <button
                                v-if="selectedCategory || searchQuery || sortBy !== 'newest'"
                                @click="resetFilters"
                                class="btn btn-link btn-sm text-danger text-decoration-none p-0"
                            >
                                Reset All
                            </button>
                        </div>

                        <!-- Search within catalog -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Search</label>
                            <div class="input-group input-group-sm">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="form-control"
                                    placeholder="Search catalog..."
                                    @keyup.enter="applyFilters"
                                />
                                <button class="btn btn-outline-primary" @click="applyFilters">
                                    🔍
                                </button>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Categories</label>
                            <div class="list-group list-group-flush small">
                                <button
                                    type="button"
                                    class="list-group-item list-group-item-action border-0 px-2 py-2 rounded d-flex justify-content-between align-items-center"
                                    :class="{ 'active fw-bold': !selectedCategory }"
                                    @click="selectCategory(null)"
                                >
                                    <span>All Products</span>
                                </button>
                                <button
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    type="button"
                                    class="list-group-item list-group-item-action border-0 px-2 py-2 rounded d-flex justify-content-between align-items-center"
                                    :class="{ 'active fw-bold': selectedCategory == cat.id }"
                                    @click="selectCategory(cat.id)"
                                >
                                    <span class="text-truncate">{{ cat.name }}</span>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                        {{ cat.products_count }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Sort By</label>
                            <select v-model="sortBy" class="form-select form-select-sm" @change="applyFilters">
                                <option value="newest">Newest Arrivals</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </aside>

                <!-- Product List Area -->
                <main class="col-lg-9">
                    <!-- Top Bar with Counts and Active Filters -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="fw-bold mb-0">
                                    {{ currentCategoryName }}
                                </h4>
                                <span class="text-muted small">
                                    Showing {{ pagination.total }} items in stock
                                </span>
                            </div>

                            <!-- Mobile Quick Sort -->
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted d-none d-sm-inline">Sort:</span>
                                <select v-model="sortBy" class="form-select form-select-sm" style="width: auto;" @change="applyFilters">
                                    <option value="newest">Newest</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                </select>
                            </div>
                        </div>

                        <!-- Active Filter Pills -->
                        <div v-if="searchQuery" class="mt-2 d-flex gap-2 align-items-center flex-wrap">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                Search: "{{ searchQuery }}"
                                <button type="button" class="btn-close btn-close-xs ms-1" style="font-size: 0.5rem;" @click="clearSearch"></button>
                            </span>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading catalog...</p>
                    </div>

                    <!-- Products Grid -->
                    <div v-else-if="products.length" class="row g-3">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="col-12 col-sm-6 col-md-4"
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

                                <!-- Out of stock badge -->
                                <div
                                    v-if="product.track_inventory && product.stock <= 0"
                                    class="position-absolute top-0 start-0 m-2 badge bg-danger text-white rounded-pill px-2 py-1 shadow-sm"
                                    style="z-index: 2;"
                                >
                                    Out of Stock
                                </div>
                                <!-- Discount badge -->
                                <div
                                    v-else-if="product.variant && product.variant.mrp > product.variant.selling_price"
                                    class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark rounded-pill px-2 py-1 shadow-sm"
                                    style="z-index: 2;"
                                >
                                    {{ calcDiscount(product.variant.selling_price, product.variant.mrp) }}% OFF
                                </div>

                                <!-- Image -->
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

                                <!-- Body -->
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="text-muted extra-small mb-1 text-uppercase">
                                        {{ product.category_name }}
                                    </div>
                                    <h6 class="card-title fw-bold text-dark text-truncate mb-2">
                                        <router-link :to="`/product/${product.id}`" class="text-dark text-decoration-none">
                                            {{ product.name }}
                                        </router-link>
                                    </h6>

                                    <!-- Stock info -->
                                    <div class="small mb-3">
                                        <span v-if="!product.track_inventory || product.stock > 0" class="text-success fw-medium">
                                            ● In Stock ({{ product.stock }} {{ product.unit?.short_name || 'units' }})
                                        </span>
                                        <span v-else class="text-danger fw-medium">
                                            ● Out of Stock
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
                                                ₹{{ formatMoney(product.variant.mrp) }}
                                            </div>
                                        </div>

                                        <button
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium"
                                            :disabled="product.track_inventory && product.stock <= 0"
                                            @click="handleAddToCart(product)"
                                        >
                                            + Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="card border-0 shadow-sm rounded-3 text-center py-5">
                        <div class="fs-1 mb-2">🔍</div>
                        <h5 class="fw-bold">No Products Found</h5>
                        <p class="text-muted small mb-3">
                            We couldn't find any products matching your selected filters.
                        </p>
                        <div>
                            <button class="btn btn-primary btn-sm rounded-pill px-4" @click="resetFilters">
                                Reset Filters
                            </button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                                    <button class="page-link" @click="changePage(pagination.current_page - 1)">
                                        « Prev
                                    </button>
                                </li>
                                <li
                                    v-for="p in pagination.last_page"
                                    :key="p"
                                    class="page-item"
                                    :class="{ active: pagination.current_page === p }"
                                >
                                    <button class="page-link" @click="changePage(p)">
                                        {{ p }}
                                    </button>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                                    <button class="page-link" @click="changePage(pagination.current_page + 1)">
                                        Next »
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
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

const categories = ref([]);
const products = ref([]);
const loading = ref(false);

const selectedCategory = ref(route.query.category_id || null);
const searchQuery = ref(route.query.search || '');
const sortBy = ref(route.query.sort || 'newest');
const currentPage = ref(1);

const pagination = ref({
    total: 0,
    current_page: 1,
    last_page: 1,
});

const currentCategoryName = computed(() => {
    if (!selectedCategory.value) return 'All Products';
    const found = categories.value.find(c => c.id == selectedCategory.value);
    return found ? found.name : 'Category Catalog';
});

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

function selectCategory(catId) {
    selectedCategory.value = catId;
    currentPage.value = 1;
    applyFilters();
}

function clearSearch() {
    searchQuery.value = '';
    applyFilters();
}

function resetFilters() {
    selectedCategory.value = null;
    searchQuery.value = '';
    sortBy.value = 'newest';
    currentPage.value = 1;
    applyFilters();
}

function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    currentPage.value = page;
    fetchProducts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function applyFilters() {
    router.replace({
        path: '/shop',
        query: {
            category_id: selectedCategory.value || undefined,
            search: searchQuery.value.trim() || undefined,
            sort: sortBy.value !== 'newest' ? sortBy.value : undefined,
        }
    });
    fetchProducts();
}

async function fetchCategories() {
    try {
        const res = await axios.get('/api/shop/categories');
        categories.value = res.data.data || [];
    } catch (e) {
        console.error('Failed to load categories:', e);
    }
}

async function fetchProducts() {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: 12,
            sort: sortBy.value,
        };
        if (selectedCategory.value) params.category_id = selectedCategory.value;
        if (searchQuery.value.trim()) params.search = searchQuery.value.trim();

        const res = await axios.get('/api/shop/products', { params });
        products.value = res.data.data || [];
        pagination.value = {
            total: res.data.total || 0,
            current_page: res.data.current_page || 1,
            last_page: res.data.last_page || 1,
        };
    } catch (e) {
        console.error('Failed to fetch products:', e);
    } finally {
        loading.value = false;
    }
}

watch(
    () => route.query,
    (newQuery) => {
        selectedCategory.value = newQuery.category_id || null;
        searchQuery.value = newQuery.search || '';
        sortBy.value = newQuery.sort || 'newest';
        fetchProducts();
    }
);

onMounted(async () => {
    await fetchCategories();
    fetchProducts();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
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

.wishlist-btn {
    transition: transform 0.2s ease, background-color 0.2s ease;
    border: none;
}

.wishlist-btn:hover {
    transform: scale(1.1);
}
</style>

