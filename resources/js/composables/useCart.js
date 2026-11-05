import { ref, computed, watch } from 'vue';
import { useNotification } from './useNotification';

const CART_STORAGE_KEY = 'pos_retail_ecommerce_cart';
const COUPON_STORAGE_KEY = 'pos_retail_ecommerce_coupon';
const SAVED_STORAGE_KEY = 'pos_retail_ecommerce_saved_for_later';

// Shared singleton reactive state
const cartItems = ref([]);
const appliedCoupon = ref(null);
const savedForLaterItems = ref([]);

// Load from localStorage on initialization
try {
    const saved = localStorage.getItem(CART_STORAGE_KEY);
    if (saved) {
        cartItems.value = JSON.parse(saved);
    }
} catch (e) {
    console.error('Failed to load cart from localStorage:', e);
}

try {
    const savedCoupon = localStorage.getItem(COUPON_STORAGE_KEY);
    if (savedCoupon) {
        appliedCoupon.value = JSON.parse(savedCoupon);
    }
} catch (e) {
    console.error('Failed to load coupon from localStorage:', e);
}

try {
    const savedLater = localStorage.getItem(SAVED_STORAGE_KEY);
    if (savedLater) {
        savedForLaterItems.value = JSON.parse(savedLater);
    }
} catch (e) {
    console.error('Failed to load saved-for-later items:', e);
}

// Watch for changes and persist
watch(
    cartItems,
    (newVal) => {
        try {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(newVal));
            if (!newVal || newVal.length === 0) {
                appliedCoupon.value = null;
            }
        } catch (e) {
            console.error('Failed to save cart to localStorage:', e);
        }
    },
    { deep: true }
);

watch(
    appliedCoupon,
    (newVal) => {
        try {
            if (newVal) {
                localStorage.setItem(COUPON_STORAGE_KEY, JSON.stringify(newVal));
            } else {
                localStorage.removeItem(COUPON_STORAGE_KEY);
            }
        } catch (e) {
            console.error('Failed to save coupon to localStorage:', e);
        }
    },
    { deep: true }
);

watch(
    savedForLaterItems,
    (newVal) => {
        try {
            localStorage.setItem(SAVED_STORAGE_KEY, JSON.stringify(newVal));
        } catch (e) {
            console.error('Failed to save saved-for-later items:', e);
        }
    },
    { deep: true }
);

export function useCart() {
    const { toast } = useNotification();

    function addToCart(product, quantity = 1) {
        const variant = product.variant;
        if (!variant) return false;

        if (product.track_inventory && product.stock <= 0) {
            toast.warning(`Sorry, '${product.name}' is currently out of stock.`, 'Out of Stock');
            return false;
        }

        const existing = cartItems.value.find(i => i.product_variant_id === variant.id);

        if (existing) {
            const nextQty = existing.quantity + quantity;
            if (product.track_inventory && nextQty > product.stock) {
                toast.warning(`Cannot add more: Only ${product.stock} units available in stock.`, 'Stock Limit');
                return false;
            }
            existing.quantity = nextQty;
        } else {
            if (product.track_inventory && quantity > product.stock) {
                toast.warning(`Cannot add: Only ${product.stock} units available in stock.`, 'Stock Limit');
                return false;
            }
            cartItems.value.push({
                product_id: product.id,
                product_variant_id: variant.id,
                name: product.name,
                slug: product.slug,
                sku: variant.sku,
                image_url: product.image_url || null,
                unit_name: product.unit?.short_name || 'pcs',
                allow_decimal: Boolean(product.unit?.allow_decimal),
                selling_price: Number(variant.selling_price || 0),
                mrp: Number(variant.mrp || 0),
                tax_rate: Number(variant.tax_rate || 0),
                available_stock: Number(product.stock || 0),
                track_inventory: Boolean(product.track_inventory),
                quantity: quantity,
            });
        }
        return true;
    }

    function removeFromCart(variantId) {
        const idx = cartItems.value.findIndex(i => i.product_variant_id === variantId);
        if (idx > -1) {
            cartItems.value.splice(idx, 1);
        }
        if (cartItems.value.length === 0) {
            appliedCoupon.value = null;
        }
    }

    function updateQuantity(variantId, qty) {
        const item = cartItems.value.find(i => i.product_variant_id === variantId);
        if (!item) return;

        if (qty <= 0) {
            removeFromCart(variantId);
            return;
        }

        if (item.track_inventory && qty > item.available_stock) {
            toast.warning(`Only ${item.available_stock} units available in stock.`, 'Stock Limit');
            item.quantity = item.available_stock;
            return;
        }

        item.quantity = qty;
    }

    function applyCoupon(couponData) {
        appliedCoupon.value = couponData;
    }

    function removeCoupon() {
        appliedCoupon.value = null;
    }

    function clearCart() {
        cartItems.value = [];
        appliedCoupon.value = null;
    }

    // -------------------------------------------------------------
    // Save for Later Functionality
    // -------------------------------------------------------------
    function saveForLater(variantId) {
        const item = cartItems.value.find(i => i.product_variant_id === variantId);
        if (!item) return false;

        // Check if already in savedForLaterItems
        const existingSaved = savedForLaterItems.value.find(i => i.product_variant_id === variantId);
        if (existingSaved) {
            existingSaved.quantity += item.quantity;
        } else {
            savedForLaterItems.value.push({ ...item });
        }

        removeFromCart(variantId);
        toast.info(`'${item.name}' moved to Saved for Later.`, 'Saved for Later');
        return true;
    }

    function moveToCartFromSaved(variantId) {
        const item = savedForLaterItems.value.find(i => i.product_variant_id === variantId);
        if (!item) return false;

        // Verify live stock before moving back to active cart
        if (item.track_inventory && item.available_stock <= 0) {
            toast.warning(`Sorry, '${item.name}' is currently out of stock.`, 'Out of Stock');
            return false;
        }

        // Add back to cart
        const productPayload = {
            id: item.product_id,
            name: item.name,
            slug: item.slug,
            image_url: item.image_url,
            track_inventory: item.track_inventory,
            stock: item.available_stock,
            unit: { short_name: item.unit_name, allow_decimal: item.allow_decimal },
            variant: {
                id: item.product_variant_id,
                sku: item.sku,
                selling_price: item.selling_price,
                mrp: item.mrp,
                tax_rate: item.tax_rate,
            },
        };

        const added = addToCart(productPayload, item.quantity || 1);
        if (added) {
            removeFromSaved(variantId);
            toast.success(`'${item.name}' moved back to your cart!`, 'Cart Updated');
            return true;
        }
        return false;
    }

    function removeFromSaved(variantId) {
        const idx = savedForLaterItems.value.findIndex(i => i.product_variant_id === variantId);
        if (idx > -1) {
            savedForLaterItems.value.splice(idx, 1);
        }
    }

    function clearSaved() {
        savedForLaterItems.value = [];
    }

    // -------------------------------------------------------------
    // Totals & Computations
    // -------------------------------------------------------------
    const cartCount = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    });

    const cartSubtotal = computed(() => {
        return cartItems.value.reduce((sum, item) => {
            return sum + (Number(item.quantity || 0) * Number(item.selling_price || 0));
        }, 0);
    });

    const cartTaxTotal = computed(() => {
        return cartItems.value.reduce((sum, item) => {
            const sub = Number(item.quantity || 0) * Number(item.selling_price || 0);
            return sum + (sub * (Number(item.tax_rate || 0) / 100));
        }, 0);
    });

    const cartShipping = computed(() => {
        if (cartSubtotal.value === 0) return 0;
        if (appliedCoupon.value && (appliedCoupon.value.type === 'free_shipping' || appliedCoupon.value.free_shipping)) {
            return 0;
        }
        return cartSubtotal.value >= 500 ? 0 : 50;
    });

    const couponDiscount = computed(() => {
        if (!appliedCoupon.value) return 0;
        const coupon = appliedCoupon.value;
        if (coupon.type === 'percentage') {
            let disc = (cartSubtotal.value * Number(coupon.value || 0)) / 100;
            if (coupon.max_discount_amount && disc > Number(coupon.max_discount_amount)) {
                disc = Number(coupon.max_discount_amount);
            }
            return Math.min(disc, cartSubtotal.value);
        } else if (coupon.type === 'fixed_cart') {
            return Math.min(Number(coupon.value || 0), cartSubtotal.value);
        } else if (coupon.type === 'free_shipping') {
            return 0;
        }
        return Math.min(Number(coupon.discount_amount || 0), cartSubtotal.value);
    });

    const cartGrandTotal = computed(() => {
        const total = cartSubtotal.value + cartTaxTotal.value + cartShipping.value - couponDiscount.value;
        return Math.max(0, Math.round(total * 100) / 100);
    });

    return {
        cartItems,
        appliedCoupon,
        applyCoupon,
        removeCoupon,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        savedForLaterItems,
        saveForLater,
        moveToCartFromSaved,
        removeFromSaved,
        clearSaved,
        cartCount,
        cartSubtotal,
        cartTaxTotal,
        cartShipping,
        couponDiscount,
        cartGrandTotal,
    };
}
