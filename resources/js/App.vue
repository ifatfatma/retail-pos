<template>
    <div class="app-root">
        <router-view v-if="isLoginRoute || isInstallRoute" />
        <AdminLayout v-else-if="isAdminRoute" />
        <ShopLayout v-else />

        <!-- Global Themed Alert & Confirmation Host -->
        <NotificationHost />
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from './layouts/AdminLayout.vue';
import ShopLayout from './layouts/ShopLayout.vue';
import NotificationHost from './components/common/NotificationHost.vue';
import { useTheme } from './composables/useTheme';
import { useShopSettings } from './composables/useShopSettings';

const { isDark } = useTheme();
const { loadSettings } = useShopSettings();

const route = useRoute();

const isLoginRoute = computed(() => {
    return route.path === '/admin/login';
});

const isInstallRoute = computed(() => {
    return route.path.startsWith('/install');
});

const isAdminRoute = computed(() => {
    return route.path.startsWith('/admin');
});

onMounted(() => {
    if (!window.location.pathname.startsWith('/install')) {
        loadSettings();
    }
});
</script>
