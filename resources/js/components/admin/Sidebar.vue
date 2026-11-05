<template>
    <aside class="sidebar">

        <div class="sidebar-heading">
            MAIN MENU
        </div>

        <router-link to="/admin/dashboard" class="menu-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </router-link>

        <div class="sidebar-heading">
            CATALOG MANAGEMENT
        </div>

        <router-link to="/admin/categories" class="menu-link">
            Categories
        </router-link>

        <router-link to="/admin/brands" class="menu-link">
            Brands
        </router-link>

        <router-link to="/admin/units" class="menu-link">
            Units
        </router-link>

        <router-link to="/admin/products" class="menu-link">
            Products
        </router-link>

        <div class="sidebar-heading">
            INVENTORY
        </div>

        <router-link to="/admin/inventory" class="menu-link">
            Stock Management
        </router-link>

        <router-link to="/admin/purchases" class="menu-link">
            Purchases
        </router-link>

        <div class="sidebar-heading">
            SALES & FULFILLMENT
        </div>

        <router-link to="/admin/pos" class="menu-link">
            POS Counter
        </router-link>

        <router-link to="/admin/orders" class="menu-link">
            Online Orders
        </router-link>

        <router-link to="/admin/coupons" class="menu-link">
            🏷️ Coupons & Discounts
        </router-link>

        <router-link to="/admin/reports" class="menu-link">
            Reports
        </router-link>

        <div class="sidebar-heading">
            STOREFRONT & CMS
        </div>

        <router-link to="/admin/website-settings" class="menu-link">
            ⚙️ Website CMS & SEO
        </router-link>

        <a href="/" target="_blank" class="menu-link text-primary fw-medium">
            🌐 Visit Website
        </a>

        <div class="sidebar-heading">
            ACCOUNT & SECURITY
        </div>

        <router-link to="/admin/profile" class="menu-link">
            👤 Edit Profile / Password
        </router-link>

        <button type="button" class="menu-link text-danger border-0 bg-transparent w-100 text-start" @click="handleLogout">
            🚪 Logout
        </button>

    </aside>
</template>

<script setup>
import { useAuth } from '../../composables/useAuth';
import { useNotification } from '../../composables/useNotification';

const { logout } = useAuth();
const { confirmDialog } = useNotification();

async function handleLogout() {
    const ok = await confirmDialog({
        title: 'Sign Out of Portal?',
        message: 'Are you sure you want to log out of the Admin Portal?',
        confirmText: 'Sign Out',
        cancelText: 'Stay Logged In',
        type: 'danger',
    });
    if (ok) {
        logout();
    }
}
</script>

<style scoped>
.sidebar {
    width: 245px;
    flex-shrink: 0;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    padding: 20px 12px;
}

.sidebar-heading {
    font-size: 11px;
    font-weight: 700;
    color: #9299a4;
    padding: 18px 12px 8px;
}

.menu-link {
    display: block;
    padding: 11px 12px;
    margin-bottom: 4px;
    border-radius: 6px;
    color: #495057;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
}

.menu-link:hover,
.menu-link.router-link-active {
    background: #eaf2ff;
    color: #0d6efd;
}

@media(max-width: 768px) {
    .sidebar {
        width: 175px;
    }
}
</style>
