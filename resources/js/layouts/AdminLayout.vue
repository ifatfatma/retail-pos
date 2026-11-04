
<template>
    <div class="admin-wrapper">

        <Header />

        <div class="admin-body">

            <Sidebar />

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

.main-content {
    flex: 1;
    min-width: 0;
    padding: 25px;
    background: #f5f7fb;
}
</style>
