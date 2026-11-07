<template>
    <div class="wishlist-page py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none">Home</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/shop" class="text-decoration-none">Shop</router-link></li>
                    <li class="breadcrumb-item active" aria-current="page">My Wishlist</li>
                </ol>
            </nav>

            <!-- Page Header Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="wishlist-header-icon bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center fs-3 shadow-sm">
                            ❤️
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-dark">{{ $t('wishlist.title') }}</h3>
                            <p class="text-muted small mb-0">
                                {{ wishlistCount }} {{ $t('wishlist.items_saved') }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap align-items-center gap-2" v-if="wishlistCount > 0">
                        <button
                            class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                            @click="shareWishlist"
                            :title="$t('wishlist.share_wishlist')"
                        >
                            <span>🔗</span>
                            <span>{{ $t('wishlist.share_wishlist') }}</span>
                        </button>
                        <button
                            class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                            @click="moveAllToCart"
                            :disabled="wishlistMeta.in_stock_count === 0"
                        >
                            <span>🛒</span>
                            <span>{{ $t('wishlist.add_all_to_cart') }} ({{ wishlistMeta.in_stock_count }})</span>
                        </button>
                        <button
                            class="btn btn-outline-danger btn-sm rounded-pill px-3"
                            @click="confirmClearWishlist"
                        >
                            🗑️ {{ $t('common.clear_all') }}
                        </button>
                    </div>
                </div>

                <!-- Summary Metrics Strip -->
                <div class="row g-3 mt-3 pt-3 border-top" v-if="wishlistCount > 0">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-light text-center">
                            <span class="text-muted extra-small text-uppercase fw-semibold d-block">{{ $t('common.total') }}</span>
                            <span class="fs-5 fw-bold text-dark">{{ wishlistCount }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-success-subtle text-center">
                            <span class="text-success extra-small text-uppercase fw-semibold d-block">{{ $t('common.in_stock') }}</span>
                            <span class="fs-5 fw-bold text-success">{{ wishlistMeta.in_stock_count }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-danger-subtle text-center">
                            <span class="text-danger extra-small text-uppercase fw-semibold d-block">{{ $t('common.out_of_stock') }}</span>
                            <span class="fs-5 fw-bold text-danger">{{ wishlistMeta.out_of_stock_count }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-warning-subtle text-center">
                            <span class="text-warning-emphasis extra-small text-uppercase fw-semibold d-block">{{ $t('wishlist.price_drop_badge') }}</span>
                            <span class="fs-5 fw-bold text-warning-emphasis">{{ wishlistMeta.price_drop_count || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-danger" role="status"></div>
                <p class="text-muted small mt-2">{{ $t('common.loading') }}</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="wishlistCount === 0" class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <div class="empty-icon-wrap mb-3 mx-auto d-flex align-items-center justify-content-center bg-light rounded-circle">
                    <span style="font-size: 3rem;">🤍</span>
                </div>
                <h4 class="fw-bold text-dark mb-2">{{ $t('wishlist.empty_title') }}</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
                    {{ $t('wishlist.empty_sub') }}
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <router-link to="/shop" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        🛍️ {{ $t('cart.explore_catalog') }}
                    </router-link>
                    <router-link to="/" class="btn btn-outline-secondary rounded-pill px-4">
                        {{ $t('storefront.home') }}
                    </router-link>
                </div>
            </div>

            <!-- Wishlist Items Grid -->
            <div v-else class="row g-4">
                <div
                    v-for="item in wishlistItems"
                    :key="item.id || item.product_id"
                    class="col-12 col-sm-6 col-lg-4 col-xl-3"
                >
                    <div class="card h-100 border-0 shadow-sm rounded-4 product-wish-card d-flex flex-column position-relative overflow-hidden">
                        <!-- Top Remove Button -->
                        <button
                            type="button"
                            class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm remove-btn text-muted"
                            title="Remove from wishlist"
                            @click="removeFromWishlist(item.product_id)"
                        >
                            ✕
                        </button>

                        <!-- Stock Status Badge -->
                        <div
                            v-if="!item.in_stock"
                            class="position-absolute top-0 start-0 m-2 badge bg-danger text-white rounded-pill px-2 py-1 shadow-sm small"
                        >
                            Out of Stock
                        </div>
                        <div
                            v-else-if="item.price_dropped"
                            class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark rounded-pill px-2 py-1 shadow-sm small fw-bold"
                        >
                            📉 Drop ₹{{ formatMoney(item.price_diff) }}!
                        </div>

                        <!-- Product Image -->
                        <router-link :to="`/product/${item.product_id}`" class="wish-img-wrap text-center p-3 bg-white">
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.name"
                                class="wish-img img-fluid"
                            />
                            <div v-else class="wish-placeholder d-flex align-items-center justify-content-center">
                                <span class="fs-1 text-muted">📦</span>
                            </div>
                        </router-link>

                        <!-- Product Details -->
                        <div class="card-body p-3 d-flex flex-column flex-grow-1">
                            <div class="text-muted extra-small text-uppercase mb-1">
                                {{ item.category_name || 'Stationery' }}
                            </div>

                            <h6 class="card-title fw-bold text-dark text-truncate mb-2" :title="item.name">
                                <router-link :to="`/product/${item.product_id}`" class="text-dark text-decoration-none">
                                    {{ item.name }}
                                </router-link>
                            </h6>

                            <!-- Live Stock Status Text -->
                            <div class="small mb-2">
                                <span v-if="item.in_stock" class="text-success fw-medium">
                                    ● In Stock ({{ item.stock }} {{ item.unit_name || 'units' }})
                                </span>
                                <span v-else class="text-danger fw-medium">
                                    ● Currently Unavailable
                                </span>
                            </div>

                            <!-- Price Dropped Banner -->
                            <div v-if="item.price_dropped" class="alert alert-warning py-1 px-2 extra-small rounded-2 mb-2 d-flex align-items-center gap-1">
                                <span>🎉</span>
                                <span>Price dropped from ₹{{ formatMoney(item.initial_price) }} to ₹{{ formatMoney(item.selling_price) }}</span>
                            </div>

                            <!-- Pricing -->
                            <div class="mt-auto pt-2 border-top">
                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <div class="fs-5 fw-bold text-primary">
                                        ₹{{ formatMoney(item.selling_price) }}
                                    </div>
                                    <div
                                        v-if="item.mrp && item.mrp > item.selling_price"
                                        class="extra-small text-muted text-decoration-line-through"
                                    >
                                        ₹{{ formatMoney(item.mrp) }}
                                    </div>
                                    <span
                                        v-if="item.mrp && item.mrp > item.selling_price"
                                        class="badge bg-success-subtle text-success extra-small rounded-pill"
                                    >
                                        {{ calcDiscount(item.selling_price, item.mrp) }}% OFF
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="d-grid gap-2">
                                    <button
                                        v-if="item.in_stock"
                                        class="btn btn-primary btn-sm rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2"
                                        @click="moveItemToCart(item)"
                                    >
                                        <span>🛒</span>
                                        <span>Move to Cart</span>
                                    </button>
                                    <button
                                        v-else
                                        class="btn btn-secondary btn-sm rounded-pill disabled"
                                        disabled
                                    >
                                        Out of Stock
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Added At -->
                        <div class="card-footer bg-light border-0 py-1 px-3 d-flex justify-content-between text-muted extra-small">
                            <span>Saved: {{ item.added_at }}</span>
                            <router-link :to="`/product/${item.product_id}`" class="text-decoration-none text-muted">
                                View details →
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useWishlist } from '../../composables/useWishlist';
import { useNotification } from '../../composables/useNotification';

const {
    wishlistItems,
    wishlistCount,
    wishlistMeta,
    loading,
    loadWishlist,
    removeFromWishlist,
    moveItemToCart,
    moveAllToCart,
    clearWishlist,
} = useWishlist();

const { toast, confirmDialog } = useNotification();

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function calcDiscount(selling, mrp) {
    if (!mrp || mrp <= selling) return 0;
    return Math.round(((mrp - selling) / mrp) * 100);
}

async function confirmClearWishlist() {
    const ok = await confirmDialog({
        title: 'Clear Wishlist?',
        message: 'Are you sure you want to remove all saved items from your wishlist?',
        confirmText: 'Clear Wishlist',
        cancelText: 'Cancel',
        type: 'danger',
    });
    if (ok) {
        await clearWishlist();
    }
}

async function shareWishlist() {
    const shareUrl = window.location.href;
    const shareData = {
        title: 'My Wishlist on The Smart Stationery',
        text: 'Check out my favorite stationery items saved on The Smart Stationery!',
        url: shareUrl,
    };

    if (navigator.share) {
        try {
            await navigator.share(shareData);
            return;
        } catch (e) {
            // Fallback to clipboard
        }
    }

    try {
        await navigator.clipboard.writeText(shareUrl);
        toast.success('Wishlist link copied to clipboard! You can share it with friends.', 'Link Copied');
    } catch (err) {
        toast.info(shareUrl, 'Share Link');
    }
}

onMounted(() => {
    loadWishlist();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.wishlist-header-icon {
    width: 54px;
    height: 54px;
}

.empty-icon-wrap {
    width: 100px;
    height: 100px;
}

.product-wish-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-wish-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
}

.wish-img-wrap {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fdfdfd;
}

.wish-img {
    max-height: 160px;
    max-width: 100%;
    object-fit: contain;
}

.wish-placeholder {
    height: 140px;
    background: #f8f9fa;
    border-radius: 8px;
    width: 100%;
}

.remove-btn {
    width: 30px;
    height: 30px;
    font-size: 12px;
    line-height: 1;
    z-index: 5;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.remove-btn:hover {
    background-color: #dc3545 !important;
    color: #fff !important;
}
</style>

