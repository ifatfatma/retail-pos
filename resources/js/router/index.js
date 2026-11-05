import { createRouter, createWebHistory } from 'vue-router';

// Administrator Auth Pages
import AdminLogin from '../pages/auth/AdminLogin.vue';
import AdminProfile from '../pages/profile/AdminProfile.vue';
import WebsiteSettings from '../pages/settings/WebsiteSettings.vue';

// Admin Management Pages
import Dashboard from '../pages/dashboard/Dashboard.vue';
import CategoryIndex from '../pages/categories/CategoryIndex.vue';
import BrandIndex from '../pages/brands/BrandIndex.vue';
import UnitIndex from '../pages/units/UnitIndex.vue';
import ProductIndex from '../pages/products/ProductIndex.vue';
import ProductForm from '../pages/products/ProductForm.vue';
import InventoryIndex from '../pages/inventory/InventoryIndex.vue';
import PurchaseIndex from '../pages/purchases/PurchaseIndex.vue';
import PurchaseForm from '../pages/purchases/PurchaseForm.vue';
import PosCounter from '../pages/pos/PosCounter.vue';
import OrderIndex from '../pages/orders/OrderIndex.vue';
import CouponIndex from '../pages/coupons/CouponIndex.vue';
import ReportIndex from '../pages/reports/ReportIndex.vue';

// E-Commerce Storefront Pages
import ShopHome from '../pages/shop/ShopHome.vue';
import ShopCatalog from '../pages/shop/ShopCatalog.vue';
import ProductDetail from '../pages/shop/ProductDetail.vue';
import CartPage from '../pages/shop/CartPage.vue';
import CheckoutPage from '../pages/shop/CheckoutPage.vue';
import OrderSuccess from '../pages/shop/OrderSuccess.vue';
import TrackOrder from '../pages/shop/TrackOrder.vue';
import WishlistPage from '../pages/shop/WishlistPage.vue';

// Customer User Account Pages
import CustomerLogin from '../pages/shop/CustomerLogin.vue';
import CustomerRegister from '../pages/shop/CustomerRegister.vue';
import ForgotPassword from '../pages/shop/ForgotPassword.vue';
import CustomerAccount from '../pages/shop/CustomerAccount.vue';

const routes = [
    // Storefront Public Routes
    {
        path: '/',
        name: 'ShopHome',
        component: ShopHome,
    },
    {
        path: '/shop',
        name: 'ShopCatalog',
        component: ShopCatalog,
    },
    {
        path: '/product/:id',
        name: 'ProductDetail',
        component: ProductDetail,
    },
    {
        path: '/cart',
        name: 'CartPage',
        component: CartPage,
    },
    {
        path: '/checkout',
        name: 'CheckoutPage',
        component: CheckoutPage,
    },
    {
        path: '/order-success/:orderNumber',
        name: 'OrderSuccess',
        component: OrderSuccess,
    },
    {
        path: '/track-order',
        name: 'TrackOrder',
        component: TrackOrder,
    },
    {
        path: '/wishlist',
        name: 'WishlistPage',
        component: WishlistPage,
    },

    // Customer Authentication & Account Routes
    {
        path: '/login',
        name: 'CustomerLogin',
        component: CustomerLogin,
    },
    {
        path: '/forgot-password',
        name: 'ForgotPassword',
        component: ForgotPassword,
    },
    {
        path: '/register',
        name: 'CustomerRegister',
        component: CustomerRegister,
    },
    {
        path: '/account',
        name: 'CustomerAccount',
        component: CustomerAccount,
        meta: { requiresCustomerAuth: true },
    },

    // Admin Auth Routes
    {
        path: '/admin/login',
        name: 'AdminLogin',
        component: AdminLogin,
    },
    {
        path: '/admin/profile',
        name: 'AdminProfile',
        component: AdminProfile,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/website-settings',
        name: 'WebsiteSettings',
        component: WebsiteSettings,
        meta: { requiresAuth: true },
    },

    // Admin Management Routes
    {
        path: '/admin/dashboard',
        component: Dashboard,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/categories',
        component: CategoryIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/brands',
        component: BrandIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/units',
        component: UnitIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/products',
        component: ProductIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/products/create',
        component: ProductForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/products/:id/edit',
        component: ProductForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/inventory',
        component: InventoryIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/purchases',
        component: PurchaseIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/purchases/create',
        component: PurchaseForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/pos',
        component: PosCounter,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/orders',
        component: OrderIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/coupons',
        name: 'CouponIndex',
        component: CouponIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/reports',
        component: ReportIndex,
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

// Navigation Guards
router.beforeEach((to, from, next) => {
    // 1. Admin Authentication Guards
    const adminToken = localStorage.getItem('admin_access_token');
    const isAdminRoute = to.path.startsWith('/admin');
    const isAdminLoginRoute = to.path === '/admin/login';

    if (isAdminRoute && !isAdminLoginRoute && !adminToken) {
        next({
            path: '/admin/login',
            query: { redirect: to.fullPath },
        });
        return;
    }
    if (isAdminLoginRoute && adminToken) {
        next('/admin/dashboard');
        return;
    }

    // 2. Customer Authentication Guards
    const customerToken = localStorage.getItem('store_customer_token');
    const isCustomerAccountRoute = to.path.startsWith('/account');
    const isCustomerAuthRoute = to.path === '/login' || to.path === '/register';

    if (isCustomerAccountRoute && !customerToken) {
        next({
            path: '/login',
            query: { redirect: to.fullPath },
        });
        return;
    }
    if (isCustomerAuthRoute && customerToken) {
        next('/account');
        return;
    }

    next();
});

export default router;
