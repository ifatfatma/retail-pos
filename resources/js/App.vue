<template>
    <div class="app-root">
        <router-view v-if="isLoginRoute" />
        <AdminLayout v-else-if="isAdminRoute" />
        <ShopLayout v-else />

        <!-- Global Themed Alert & Confirmation Host -->
        <NotificationHost />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from './layouts/AdminLayout.vue';
import ShopLayout from './layouts/ShopLayout.vue';
import NotificationHost from './components/common/NotificationHost.vue';
import { useTheme } from './composables/useTheme';

const { isDark } = useTheme();

const route = useRoute();

const isLoginRoute = computed(() => {
    return route.path === '/admin/login';
});

const isAdminRoute = computed(() => {
    return route.path.startsWith('/admin');
});
</script>
