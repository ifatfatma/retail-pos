<template>
    <aside class="sidebar">

        <div class="sidebar-heading">
            {{ $t('nav.main_menu') }}
        </div>

        <router-link to="/admin/dashboard" class="menu-link">
            <span>📊</span> {{ $t('nav.dashboard') }}
        </router-link>

        <div class="sidebar-heading">
            {{ $t('nav.catalog_management') }}
        </div>

        <router-link to="/admin/categories" class="menu-link">
            <span>📁</span> {{ $t('nav.categories') }}
        </router-link>

        <router-link to="/admin/brands" class="menu-link">
            <span>🏷️</span> {{ $t('nav.brands') }}
        </router-link>

        <router-link to="/admin/units" class="menu-link">
            <span>📏</span> {{ $t('nav.units') }}
        </router-link>

        <router-link to="/admin/products" class="menu-link">
            <span>📦</span> {{ $t('nav.products') }}
        </router-link>

        <div class="sidebar-heading">
            {{ $t('nav.inventory') }}
        </div>

        <router-link to="/admin/inventory" class="menu-link">
            <span>📈</span> {{ $t('nav.stock_management') }}
        </router-link>

        <router-link to="/admin/purchases" class="menu-link">
            <span>🛒</span> {{ $t('nav.purchases') }}
        </router-link>

        <div class="sidebar-heading">
            {{ $t('nav.sales_fulfillment') }}
        </div>

        <router-link to="/admin/pos" class="menu-link">
            <span>💻</span> {{ $t('nav.pos_counter') }}
        </router-link>

        <router-link to="/admin/orders" class="menu-link">
            <span>🛍️</span> {{ $t('nav.online_orders') }}
        </router-link>

        <router-link to="/admin/coupons" class="menu-link">
            <span>🏷️</span> {{ $t('nav.coupons_discounts') }}
        </router-link>

        <router-link to="/admin/reports" class="menu-link">
            <span>📑</span> {{ $t('nav.reports') }}
        </router-link>

        <div class="sidebar-heading">
            {{ $t('nav.storefront_cms') }}
        </div>

        <router-link to="/admin/website-settings" class="menu-link">
            <span>⚙️</span> {{ $t('nav.website_settings') }}
        </router-link>

        <a href="/" target="_blank" class="menu-link text-primary fw-medium">
            <span>🌐</span> {{ $t('nav.visit_website') }}
        </a>

        <div class="sidebar-heading">
            {{ $t('nav.account_security') }}
        </div>

        <router-link to="/admin/profile" class="menu-link">
            <span>👤</span> {{ $t('nav.edit_profile') }}
        </router-link>

        <button type="button" class="menu-link text-danger border-0 bg-transparent w-100 text-start" @click="handleLogout">
            <span>🚪</span> {{ $t('nav.logout') }}
        </button>

    </aside>
</template>

<script setup>
import { useAuth } from '../../composables/useAuth';
import { useNotification } from '../../composables/useNotification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { logout } = useAuth();
const { confirmDialog } = useNotification();

async function handleLogout() {
    const ok = await confirmDialog({
        title: t('header.portal_title'),
        message: t('common.are_you_sure'),
        confirmText: t('nav.logout'),
        cancelText: t('common.cancel'),
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

:global(html[dir="rtl"]) .sidebar {
    border-right: none !important;
    border-left: 1px solid #e5e7eb !important;
    text-align: right !important;
}

:global(html[dir="rtl"]) .menu-link {
    text-align: right !important;
    justify-content: flex-start !important;
}

:global(html[dir="rtl"]) .sidebar-heading {
    text-align: right !important;
}

.sidebar-heading {
    font-size: 11px;
    font-weight: 700;
    color: #9299a4;
    padding: 18px 12px 8px;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 8px;
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
