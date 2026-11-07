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
                    <ThemeToggle theme="dark" size="sm" />
                    <LanguageSelector theme="dark" />
                    <router-link to="/track-order" class="text-white text-decoration-none">
                        📍 {{ $t('storefront.track_my_order') }}
                    </router-link>
                    <span class="text-secondary">|</span>
                    <router-link v-if="!isCustomerAuthenticated" to="/login" class="text-white text-decoration-none">
                        👤 {{ $t('nav.sign_in') }} / {{ $t('nav.register') }}
                    </router-link>
                    <router-link v-else to="/account" class="text-warning text-decoration-none fw-semibold">
                        👤 {{ customerName }}
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Main Storefront Header & Navigation -->
        <header class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top store-header-navbar py-2 py-lg-3">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <!-- Brand / Logo (Dynamic) -->
                <router-link to="/" class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 me-2 me-lg-4 py-0">
                    <img
                        v-if="settings.site_logo_url"
                        :src="settings.site_logo_url"
                        :alt="settings.site_name"
                        class="store-logo"
                    />
                    <span v-else class="fs-3">🛍️</span>
                    <span class="store-name-text">{{ settings.site_name || 'Retail Store' }}</span>
                </router-link>

                <!-- Navbar Toggler for mobile -->
                <button
                    class="navbar-toggler border-0 p-1 ms-auto d-lg-none"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#shopNav"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation, Search & User Actions -->
                <div class="collapse navbar-collapse" id="shopNav">
                    <!-- Left: Nav links (Pill style, no wrapping) -->
                    <ul class="navbar-nav align-items-lg-center gap-lg-1 me-lg-2 mb-3 mb-lg-0">
                        <li class="nav-item">
                            <router-link to="/" class="store-nav-link" exact-active-class="active">
                                {{ $t('storefront.home') }}
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/shop" class="store-nav-link" active-class="active">
                                {{ $t('storefront.all_products') }}
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/track-order" class="store-nav-link" active-class="active">
                                {{ $t('storefront.track_my_order') }}
                            </router-link>
                        </li>
                    </ul>

                    <!-- Spacer: creates a clean, luxurious gap between Nav Links and Right Tools -->
                    <div class="me-auto d-none d-lg-block"></div>

                    <!-- Right Side Cluster: Compact Search, Theme, Wishlist, Cart, Profile -->
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap mt-3 mt-lg-0 ms-lg-auto">
                        <!-- Compact Search Input Form -->
                        <form class="store-search-form my-1 my-lg-0" @submit.prevent="onSearch">
                            <div class="input-group input-group-sm flex-nowrap store-search-box">
                                <span class="input-group-text bg-transparent border-0 text-muted ps-2 pe-1">
                                    🔍
                                </span>
                                <input
                                    v-model="navSearch"
                                    type="text"
                                    class="form-control border-0 bg-transparent py-1 ps-1 pe-2 shadow-none store-search-input"
                                    :placeholder="$t('storefront.search_store')"
                                />
                                <button class="btn btn-primary btn-sm rounded-pill px-2 py-0 my-1 me-1 fw-semibold store-search-submit" type="submit">
                                    {{ $t('common.search') }}
                                </button>
                            </div>
                        </form>

                        <!-- Wishlist Icon Button with Floating Live Badge -->
                        <router-link
                            to="/wishlist"
                            class="store-icon-btn store-wishlist-btn position-relative"
                            :title="$t('nav.wishlist')"
                            :aria-label="$t('nav.wishlist')"
                        >
                            <span class="action-icon">❤️</span>
                            <span class="store-badge-count bg-danger text-white" v-if="wishlistCount > 0">
                                {{ wishlistCount }}
                            </span>
                        </router-link>

                        <!-- Cart Icon Button with Floating Live Badge -->
                        <router-link
                            to="/cart"
                            class="store-icon-btn store-cart-btn position-relative"
                            :title="$t('nav.cart')"
                            :aria-label="$t('nav.cart')"
                        >
                            <span class="action-icon">🛒</span>
                            <span class="store-badge-count bg-primary text-white" v-if="cartCount > 0">
                                {{ cartCount }}
                            </span>
                            <span class="store-badge-count bg-secondary text-white" v-else>
                                0
                            </span>
                        </router-link>

                        <!-- Customer Account Dropdown or Sign In Button -->
                        <div v-if="isCustomerAuthenticated" class="position-relative">
                            <button
                                class="store-account-pill d-inline-flex align-items-center gap-1 px-2 border"
                                type="button"
                                @click="showUserDropdown = !showUserDropdown"
                                :title="customerName"
                            >
                                <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                    {{ customerInitials }}
                                </div>
                                <span class="small fw-semibold text-dark text-truncate d-none d-xxl-inline" style="max-width: 90px;">
                                    {{ customerFirstName }}
                                </span>
                                <span class="extra-small text-muted">▾</span>
                            </button>

                            <div
                                v-if="showUserDropdown"
                                class="dropdown-menu dropdown-menu-end show shadow-lg border-0 rounded-3 mt-2 py-2 store-dropdown-menu"
                            >
                                <div class="px-3 py-2 border-bottom mb-1">
                                    <div class="fw-bold small text-dark text-truncate">{{ customerName }}</div>
                                    <div class="extra-small text-muted text-truncate">{{ customerEmail || customerPhone }}</div>
                                </div>
                                <router-link to="/account" class="dropdown-item py-2 small d-flex align-items-center gap-2" @click="showUserDropdown = false">
                                    <span>📊</span> <span>{{ $t('account.tab_overview') }}</span>
                                </router-link>
                                <router-link to="/account" class="dropdown-item py-2 small d-flex align-items-center gap-2" @click="showUserDropdown = false">
                                    <span>📦</span> <span>{{ $t('account.tab_orders') }}</span>
                                </router-link>
                                <router-link to="/wishlist" class="dropdown-item py-2 small d-flex align-items-center gap-2" @click="showUserDropdown = false">
                                    <span>❤️</span> <span>{{ $t('account.tab_wishlist') }}</span>
                                    <span class="badge bg-danger text-white rounded-pill ms-auto" v-if="wishlistCount > 0">{{ wishlistCount }}</span>
                                </router-link>
                                <router-link to="/account" class="dropdown-item py-2 small d-flex align-items-center gap-2" @click="showUserDropdown = false">
                                    <span>👤</span> <span>{{ $t('account.tab_profile') }}</span>
                                </router-link>
                                <div class="dropdown-divider my-1"></div>
                                <button type="button" class="dropdown-item py-2 small text-danger d-flex align-items-center gap-2" @click="handleCustomerLogout">
                                    <span>🚪</span> <span>{{ $t('nav.logout') }}</span>
                                </button>
                            </div>
                        </div>

                        <router-link
                            v-else
                            to="/login"
                            class="store-icon-btn"
                            :title="$t('nav.sign_in')"
                            :aria-label="$t('nav.sign_in')"
                        >
                            <span>👤</span>
                        </router-link>
                    </div>
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
import LanguageSelector from '../components/common/LanguageSelector.vue';
import ThemeToggle from '../components/common/ThemeToggle.vue';

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

const customerFirstName = computed(() => {
    if (!customerName.value) return '';
    return customerName.value.split(' ')[0];
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

/* Storefront White Navbar Styling */
.store-header-navbar {
    background-color: #ffffff !important;
    border-bottom: 1px solid #eef2f6 !important;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04) !important;
}

.store-name-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
    white-space: nowrap;
}

.store-logo {
    max-height: 40px;
    max-width: 170px;
    object-fit: contain;
}

.store-logo-footer {
    max-height: 30px;
    max-width: 140px;
    object-fit: contain;
}

/* Nav Links - Sleek, Horizontal, No Box */
.store-nav-link {
    display: inline-flex;
    align-items: center;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 500;
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.18s ease;
    outline: none !important;
}

.store-nav-link:hover {
    color: #0d6efd;
    background-color: #f8fafc;
}

.store-nav-link.active {
    color: #0d6efd;
    font-weight: 600;
    background-color: #eff6ff;
}

/* Compact Search Form */
.store-search-form {
    width: 200px;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.store-search-form:focus-within {
    width: 250px;
}

.store-search-box {
    height: 36px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 50rem;
    overflow: hidden;
    transition: all 0.2s ease;
    align-items: center;
}

.store-search-box:focus-within {
    background: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.store-search-input {
    font-size: 0.82rem;
}

.store-search-submit {
    font-size: 0.75rem;
    padding: 0.2rem 0.65rem;
    white-space: nowrap;
    height: 28px;
    line-height: 1;
}

/* Compact Circular Icon Buttons (Wishlist, Cart, Guest Login) */
.store-icon-btn {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    position: relative;
    user-select: none;
}

.store-icon-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.store-wishlist-btn:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #ef4444;
}

.store-cart-btn {
    border-color: #bfdbfe;
    background: #eff6ff;
}

.store-cart-btn:hover {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1d4ed8;
}

/* Floating Notification Count Badges */
.store-badge-count {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 700;
    line-height: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Account Pill */
.store-account-pill {
    height: 36px;
    border-radius: 50rem;
    background: #ffffff;
    border-color: #e2e8f0 !important;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.store-account-pill:hover {
    background: #f8fafc;
    border-color: #cbd5e1 !important;
    transform: translateY(-1px);
}

.avatar-initials {
    width: 24px;
    height: 24px;
    font-size: 0.68rem;
    font-weight: 700;
}

.store-dropdown-menu {
    position: absolute;
    right: 0;
    min-width: 210px;
    z-index: 1050;
}

:global(html[dir="rtl"]) .store-dropdown-menu {
    right: auto !important;
    left: 0 !important;
}

:global(html[dir="rtl"]) .store-search-box .store-search-submit {
    margin-left: 0.25rem !important;
    margin-right: 0 !important;
}

:global(html[dir="rtl"]) .store-badge-count {
    right: auto !important;
    left: -5px !important;
}
</style>
