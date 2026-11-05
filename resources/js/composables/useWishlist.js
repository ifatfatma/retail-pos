import { ref, computed } from 'vue';
import axios from 'axios';
import { useNotification } from './useNotification';
import { useCart } from './useCart';

const WISHLIST_STORAGE_KEY = 'pos_retail_ecommerce_wishlist';
const GUEST_TOKEN_KEY = 'store_guest_token';

// Singleton State
const wishlistItems = ref([]);
const wishlistMeta = ref({
    total: 0,
    in_stock_count: 0,
    out_of_stock_count: 0,
    price_drop_count: 0,
});
const loading = ref(false);
const syncing = ref(false);

// Load cached wishlist items from localStorage for immediate UI rendering
try {
    const saved = localStorage.getItem(WISHLIST_STORAGE_KEY);
    if (saved) {
        wishlistItems.value = JSON.parse(saved);
        wishlistMeta.value.total = wishlistItems.value.length;
    }
} catch (e) {
    console.error('Failed to load wishlist from localStorage:', e);
}

// Generate or retrieve persistent guest token
export function getGuestToken() {
    let token = localStorage.getItem(GUEST_TOKEN_KEY);
    if (!token) {
        token = 'gst_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now().toString(36);
        localStorage.setItem(GUEST_TOKEN_KEY, token);
    }
    return token;
}

// Save to localStorage
function persistWishlist() {
    try {
        localStorage.setItem(WISHLIST_STORAGE_KEY, JSON.stringify(wishlistItems.value));
    } catch (e) {
        console.error('Failed to save wishlist cache:', e);
    }
}

// Axios request helper with Guest Token
function getRequestConfig() {
    const guestToken = getGuestToken();
    return {
        headers: {
            'X-Guest-Token': guestToken,
        },
    };
}

export function useWishlist() {
    const { toast } = useNotification();
    const { addToCart } = useCart();

    const wishlistCount = computed(() => {
        return wishlistItems.value.length;
    });

    function isInWishlist(productId) {
        if (!productId) return false;
        return wishlistItems.value.some(item => Number(item.product_id) === Number(productId));
    }

    async function loadWishlist() {
        loading.value = true;
        try {
            const res = await axios.get('/api/shop/wishlist', getRequestConfig());
            wishlistItems.value = res.data.data || [];
            if (res.data.meta) {
                wishlistMeta.value = res.data.meta;
            }
            persistWishlist();
        } catch (err) {
            console.error('Failed to load wishlist:', err);
        } finally {
            loading.value = false;
        }
    }

    async function toggleWishlist(product) {
        if (!product || !product.id) return;

        const prodId = Number(product.id);
        const wasInWishlist = isInWishlist(prodId);

        // Optimistic UI update
        if (wasInWishlist) {
            wishlistItems.value = wishlistItems.value.filter(i => Number(i.product_id) !== prodId);
        } else {
            wishlistItems.value.push({
                id: Date.now(),
                product_id: prodId,
                product_variant_id: product.variant?.id || null,
                name: product.name,
                slug: product.slug,
                category_name: product.category_name || product.category?.name || 'General',
                image_url: product.image_url || null,
                selling_price: Number(product.variant?.selling_price || 0),
                mrp: Number(product.variant?.mrp || 0),
                initial_price: Number(product.variant?.selling_price || 0),
                in_stock: !product.track_inventory || Number(product.stock || 0) > 0,
                stock: Number(product.stock || 0),
                unit_name: product.unit?.short_name || 'pcs',
                added_at: 'Just now',
                variant: product.variant || null,
            });
        }
        persistWishlist();

        try {
            const payload = {
                product_id: prodId,
                product_variant_id: product.variant?.id || null,
                guest_token: getGuestToken(),
            };

            const res = await axios.post('/api/shop/wishlist/toggle', payload, getRequestConfig());

            if (res.data.in_wishlist) {
                toast.success(res.data.message || `'${product.name}' saved to your wishlist!`, 'Wishlist');
            } else {
                toast.info(res.data.message || `'${product.name}' removed from your wishlist.`, 'Wishlist');
            }

            // Sync with server state
            loadWishlist();
        } catch (err) {
            console.error('Toggle wishlist error:', err);
            // Revert optimistic change
            loadWishlist();
            toast.error('Failed to update wishlist. Please try again.', 'Error');
        }
    }

    async function removeFromWishlist(productId) {
        if (!productId) return;
        const prodId = Number(productId);

        wishlistItems.value = wishlistItems.value.filter(i => Number(i.product_id) !== prodId);
        persistWishlist();

        try {
            const res = await axios.delete(`/api/shop/wishlist/${prodId}`, getRequestConfig());
            toast.info(res.data.message || 'Item removed from wishlist.', 'Wishlist');
            loadWishlist();
        } catch (err) {
            console.error('Remove wishlist error:', err);
            loadWishlist();
        }
    }

    async function moveItemToCart(item) {
        if (!item) return;

        if (item.track_inventory && item.stock <= 0) {
            toast.warning(`Sorry, '${item.name}' is currently out of stock.`, 'Out of Stock');
            return false;
        }

        const productPayload = {
            id: item.product_id,
            name: item.name,
            slug: item.slug,
            image_url: item.image_url,
            track_inventory: item.track_inventory,
            stock: item.stock,
            unit: { short_name: item.unit_name },
            variant: item.variant || {
                id: item.product_variant_id,
                sku: item.name,
                selling_price: item.selling_price,
                mrp: item.mrp,
            },
        };

        const added = addToCart(productPayload, 1);
        if (added) {
            await removeFromWishlist(item.product_id);
            toast.success(`'${item.name}' moved to your shopping cart!`, 'Moved to Cart');
            return true;
        }
        return false;
    }

    async function moveAllToCart() {
        const inStockItems = wishlistItems.value.filter(i => !i.track_inventory || i.stock > 0);

        if (inStockItems.length === 0) {
            toast.warning('No available in-stock items to add to cart.', 'Cart');
            return;
        }

        let count = 0;
        for (const item of inStockItems) {
            const productPayload = {
                id: item.product_id,
                name: item.name,
                slug: item.slug,
                image_url: item.image_url,
                track_inventory: item.track_inventory,
                stock: item.stock,
                unit: { short_name: item.unit_name },
                variant: item.variant || {
                    id: item.product_variant_id,
                    sku: item.name,
                    selling_price: item.selling_price,
                    mrp: item.mrp,
                },
            };
            if (addToCart(productPayload, 1)) {
                await removeFromWishlist(item.product_id);
                count++;
            }
        }

        if (count > 0) {
            toast.success(`Added ${count} item(s) from your wishlist to cart!`, 'Cart Updated');
        }
    }

    async function clearWishlist() {
        wishlistItems.value = [];
        persistWishlist();
        try {
            await axios.delete('/api/shop/wishlist', getRequestConfig());
            toast.info('Wishlist cleared.', 'Wishlist');
            loadWishlist();
        } catch (err) {
            console.error('Clear wishlist error:', err);
        }
    }

    async function syncGuestWishlist() {
        const guestToken = localStorage.getItem(GUEST_TOKEN_KEY);
        if (!guestToken) return;

        syncing.value = true;
        try {
            const res = await axios.post('/api/shop/wishlist/sync', { guest_token: guestToken });
            if (res.data.synced_count > 0) {
                toast.success(`Merged ${res.data.synced_count} saved item(s) into your account!`, 'Wishlist Synced');
            }
            await loadWishlist();
        } catch (err) {
            console.error('Wishlist sync error:', err);
        } finally {
            syncing.value = false;
        }
    }

    return {
        wishlistItems,
        wishlistCount,
        wishlistMeta,
        loading,
        syncing,
        isInWishlist,
        toggleWishlist,
        removeFromWishlist,
        moveItemToCart,
        moveAllToCart,
        clearWishlist,
        loadWishlist,
        syncGuestWishlist,
    };
}

