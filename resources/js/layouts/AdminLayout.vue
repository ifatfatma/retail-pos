<template>
    <div class="admin-wrapper">

        <Header class="d-print-none" />

        <div class="admin-body">

            <Sidebar class="d-print-none" />

            <main class="main-content">
                <router-view />
            </main>

        </div>

    </div>
</template>

<script setup>
import Header from '../components/admin/Header.vue';
import Sidebar from '../components/admin/Sidebar.vue';
</script>

<style scoped>
.admin-body {
    display: flex;
    min-height: calc(100vh - 60px);
}

:global(html[dir="rtl"]) .admin-body {
    flex-direction: row !important;
}

.main-content {
    flex: 1;
    min-width: 0;
    padding: 25px;
    background: #f5f7fb;
}
</style>

<style>
@media print {
    /* Hide entire navigation, header, sidebar and any d-print-none elements */
    .d-print-none,
    header,
    aside,
    .sidebar,
    .navbar {
        display: none !important;
    }

    /* Reset body and container for clean paper printing */
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .admin-wrapper {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .admin-body {
        display: block !important;
        min-height: auto !important;
    }

    .main-content {
        padding: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
        width: 100% !important;
        flex: none !important;
    }
}
</style>
