<template>
    <div class="shop-wrapper d-flex flex-column min-vh-100">

        <!-- Top Announcement Bar (Dynamic) -->
        <div
            v-if="settings.announcement_enabled === '1' || settings.announcement_enabled === true"
            class="bg-dark text-white py-1 px-3 small"
        >
            <div class="container d-flex justify-content-between align-items-center">
                <span>
                    {{ settings.announcement_text }}
                </span>

                <div class="d-flex align-items-center gap-3">
                    <router-link to="/track-order" class="text-white text-decoration-none">
                        📍 Track Order
                    </router-link>
                    <span class="text-secondary">|</span>
                    <router-link v-if="!isCustomerAuthenticated" to="/login" class="text-white text-decoration-none">
                        👤 Sign In / Register
                    </router-link>
                    <router-link v-else to="/account" class="text-warning text-decoration-none fw-semibold">
                        👤 Hi, {{ customerName }}
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Main Storefront Header & Navigation -->
        <header class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
            <div class="container">
                <!-- Brand / Logo (Dynamic) -->
                <router-link to="/" class="navbar-brand fw-bold text-primary fs-4 d-flex align-items-center gap-2">
                    <img
                        v-if="settings.site_logo_url"
                        :src="settings.site_logo_url"
                        :alt="settings.site_name"
                        class="store-logo"
                    />
                    <span v-else class="fs-3">🛍️</span>
                    <span>{{ settings.site_name || 'Retail Store' }}</span>
                </router-link>

                <!-- Navbar Toggler for mobile -->
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#shopNav"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="shopNav">
                    <!-- Nav links -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                        <li class="nav-item">
                            <router-link to="/" class="nav-link fw-medium" exact-active-class="active text-primary fw-bold">
                                Home
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/shop" class="nav-link fw-medium" active-class="active text-primary fw-bold">
                                All Products
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/track-order" class="nav-link fw-medium" active-class="active text-primary fw-bold">
                                Track My Order
                            </router-link>
                        </li>
                    </ul>

                    <!-- Search Input Form -->
                    <form class="d-flex me-3 my-2 my-lg-0" @submit.prevent="onSearch">
                        <div class="input-group input-group-sm">
                            <input
                                v-model="navSearch"
                                type="text"
                                class="form-control"
                                placeholder="Search products..."
                                style="min-width: 190px;"
                            />
                            <button class="btn btn-outline-primary" type="submit">
                                🔍
                            </button>
                        </div>
                    </form>

                    <!-- Wishlist Button with Live Counter Badge -->
                    <router-link to="/wishlist" class="btn btn-outline-danger d-flex align-items-center gap-1 px-3 position-relative rounded-pill me-2">
                        <span>❤️</span>
                        <span class="d-none d-sm-inline">Wishlist</span>
                        <span class="badge bg-danger text-white rounded-pill fw-bold" v-if="wishlistCount > 0">
                            {{ wishlistCount }}
                        </span>
                    </router-link>

                    <!-- Cart Button with Live Counter Badge -->
                    <router-link to="/cart" class="btn btn-primary d-flex align-items-center gap-2 px-3 position-relative rounded-pill">
                        <span>🛒 Cart</span>
                        <span class="badge bg-white text-primary rounded-pill fw-bold" v-if="cartCount > 0">
                            {{ cartCount }}
                        </span>
                        <span class="badge bg-white text-secondary rounded-pill fw-normal" v-else>
                            0
                        </span>
                    </router-link>

                    <!-- Customer Account Dropdown or Sign In Button -->
                    <div v-if="isCustomerAuthenticated" class="position-relative ms-2">
                        <button
                            class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-2 px-3 py-1"
                            type="button"
                            @click="showUserDropdown = !showUserDropdown"
                        >
                            <span class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                {{ customerInitials }}
                            </span>
                            <span class="small fw-semibold text-dark d-none d-md-inline">{{ customerName }}</span>
                            <span class="small text-muted">▾</span>
                        </button>

                        <div
                            v-if="showUserDropdown"
                            class="dropdown-menu dropdown-menu-end show shadow-lg border-0 rounded-3 mt-2 py-2"
                            style="position: absolute; right: 0; min-width: 210px; z-index: 1050;"
                        >
                            <div class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold small text-dark text-truncate">{{ customerName }}</div>
                                <div class="extra-small text-muted text-truncate">{{ customerEmail || customerPhone }}</div>
                            </div>
                            <router-link to="/account" class="dropdown-item py-2 small" @click="showUserDropdown = false">
                                📊 Dashboard
                            </router-link>
                            <router-link to="/account" class="dropdown-item py-2 small" @click="showUserDropdown = false">
                                📦 My Orders
                            </router-link>
                            <router-link to="/wishlist" class="dropdown-item py-2 small" @click="showUserDropdown = false">
                                ❤️ My Wishlist <span class="badge bg-danger text-white rounded-pill ms-1" v-if="wishlistCount > 0">{{ wishlistCount }}</span>
                            </router-link>
                            <router-link to="/account" class="dropdown-item py-2 small" @click="showUserDropdown = false">
                                👤 Profile & Address
                            </router-link>
                            <div class="dropdown-divider my-1"></div>
                            <button type="button" class="dropdown-item py-2 small text-danger" @click="handleCustomerLogout">
                                🚪 Sign Out
                            </button>
                        </div>
                    </div>

                    <router-link
                        v-else
                        to="/login"
                        class="btn btn-outline-primary btn-sm rounded-pill px-3 ms-2 d-flex align-items-center gap-1"
                    >
                        <span>👤</span>
                        <span class="d-none d-sm-inline">Sign In</span>
                    </router-link>
                </div>
            </div>
        </header>

        <!-- Main View Container -->
        <main class="flex-grow-1 bg-light-subtle">
            <router-view />
        </main>

        <!-- Storefront Footer (Dynamic) -->
        <footer class="bg-dark text-white pt-5 pb-4 mt-auto">
            <div class="container">
                <div class="row g-4 mb-4">
                    <div class="col-lg-4 col-md-6">
                        <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                            <img
                                v-if="settings.site_logo_url"
                                :src="settings.site_logo_url"
                                :alt="settings.site_name"
                                class="store-logo-footer"
                            />
                            <span v-else>🛍️</span>
                            <span>{{ settings.site_name || 'Retail Online Store' }}</span>
                        </h5>
                        <p class="text-secondary small mb-3">
                            {{ settings.footer_about }}
                        </p>
                        <div class="small text-secondary">
                            📍 {{ settings.contact_address }}<br>
                            📞 {{ settings.contact_phone }} | ✉️ {{ settings.contact_email }}
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <h6 class="fw-bold text-uppercase text-secondary small mb-3">Quick Links</h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2"><router-link to="/" class="text-white text-decoration-none">Home</router-link></li>
                            <li class="mb-2"><router-link to="/shop" class="text-white text-decoration-none">Shop Catalog</router-link></li>
                            <li class="mb-2"><router-link to="/cart" class="text-white text-decoration-none">My Cart</router-link></li>
                            <li class="mb-2"><router-link to="/wishlist" class="text-white text-decoration-none">My Wishlist</router-link></li>
                            <li class="mb-2"><router-link to="/account" class="text-white text-decoration-none">My Account</router-link></li>
                            <li class="mb-2"><router-link to="/track-order" class="text-white text-decoration-none">Track Order</router-link></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <h6 class="fw-bold text-uppercase text-secondary small mb-3">Customer Service</h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2 text-secondary">🚚 Free Delivery on orders over ₹500</li>
                            <li class="mb-2 text-secondary">💵 Cash on Delivery (COD) Supported</li>
                            <li class="mb-2 text-secondary">⚡ Real-time Live Inventory Tracking</li>
                            <li class="mb-2 text-secondary">🛡️ 100% Secure Checkout</li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <h6 class="fw-bold text-uppercase text-secondary small mb-3">Store Hours & Support</h6>
                        <p class="small text-secondary mb-2" style="white-space: pre-line;">
                            {{ settings.store_hours }}
                        </p>
                        <p class="small text-secondary mb-0">
                            Have questions about your order? Visit our <router-link to="/track-order" class="text-warning text-decoration-none">Track Order</router-link> page or contact customer support.
                        </p>
                    </div>
                </div>

                <div class="border-top border-secondary pt-3 d-flex flex-wrap justify-content-between align-items-center small text-secondary">
                    <span>&copy; {{ new Date().getFullYear() }} {{ settings.copyright_text }}</span>
                    <span>Powered by Retail POS Live Inventory System</span>
                </div>
            </div>
        </footer>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useCart } from '../composables/useCart';
import { useShopSettings } from '../composables/useShopSettings';
import { useCustomerAuth } from '../composables/useCustomerAuth';
import { useWishlist } from '../composables/useWishlist';

const router = useRouter();
const { cartCount } = useCart();
const { wishlistCount, loadWishlist } = useWishlist();
const { settings, loadSettings } = useShopSettings();
const { customer, customerName, customerEmail, customerPhone, isCustomerAuthenticated, fetchCustomerProfile, logout } = useCustomerAuth();

const navSearch = ref('');
const showUserDropdown = ref(false);

const customerInitials = computed(() => {
    if (!customerName.value) return 'CU';
    return customerName.value
        .split(' ')
        .map(w => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

function onSearch() {
    if (navSearch.value.trim()) {
        router.push({ path: '/shop', query: { search: navSearch.value.trim() } });
    } else {
        router.push('/shop');
    }
}

function handleCustomerLogout() {
    showUserDropdown.value = false;
    logout();
}

function handleGlobalClick(e) {
    if (!e.target.closest('.position-relative')) {
        showUserDropdown.value = false;
    }
}

onMounted(() => {
    loadSettings();
    loadWishlist();
    if (isCustomerAuthenticated.value) {
        fetchCustomerProfile();
    }
    document.addEventListener('click', handleGlobalClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleGlobalClick);
});
</script>

<style scoped>
.shop-wrapper {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.extra-small {
    font-size: 0.75rem;
}

.store-logo {
    max-height: 38px;
    max-width: 160px;
    object-fit: contain;
}

.store-logo-footer {
    max-height: 30px;
    max-width: 140px;
    object-fit: contain;
}

.avatar-initials {
    width: 24px;
    height: 24px;
    font-size: 0.7rem;
    font-weight: bold;
}
</style>
