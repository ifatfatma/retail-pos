<template>
    <nav class="navbar navbar-dark bg-dark px-3 px-md-4 d-flex justify-content-between align-items-center shadow-sm">
        <!-- Left: Logo & Portal Title -->
        <div class="d-flex align-items-center gap-3">
            <router-link to="/admin/dashboard" class="navbar-brand fw-bold mb-0 text-white text-decoration-none d-flex align-items-center gap-2">
                <span>🏪</span>
                <span class="d-none d-sm-inline">Retail POS Management</span>
                <span class="d-sm-none">POS Portal</span>
            </router-link>
            <span class="badge bg-secondary d-none d-md-inline">
                Admin Panel
            </span>
        </div>

        <!-- Right: Actions & User Dropdown -->
        <div class="d-flex align-items-center gap-2 gap-md-3 position-relative">
            <!-- View Online Store Button -->
            <router-link
                to="/"
                target="_blank"
                class="btn btn-outline-warning btn-sm fw-medium d-flex align-items-center gap-1 shadow-sm px-2 px-md-3"
            >
                <span>🌐</span>
                <span class="d-none d-sm-inline">View Online Store</span>
            </router-link>

            <!-- User Profile Dropdown Button -->
            <div class="position-relative">
                <button
                    class="btn btn-dark border-secondary btn-sm d-flex align-items-center gap-2 px-2 py-1 rounded-pill"
                    @click="showDropdown = !showDropdown"
                    type="button"
                >
                    <div class="user-avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small">
                        {{ userInitials }}
                    </div>
                    <div class="text-start d-none d-md-inline text-white small pe-1">
                        <span class="fw-semibold d-block lh-1">{{ userName }}</span>
                        <span class="extra-small text-muted lh-1">{{ userRole }}</span>
                    </div>
                    <span class="text-white small">▾</span>
                </button>

                <!-- Dropdown Menu Box -->
                <div
                    v-if="showDropdown"
                    class="dropdown-menu dropdown-menu-end show shadow-lg border-0 rounded-3 mt-2 py-2"
                    style="position: absolute; right: 0; min-width: 220px; z-index: 1050;"
                >
                    <div class="px-3 py-2 border-bottom mb-1">
                        <div class="fw-bold text-dark text-truncate">{{ userName }}</div>
                        <div class="extra-small text-muted text-truncate">{{ userEmail }}</div>
                        <span class="badge bg-success-subtle text-success extra-small mt-1">{{ userRole }}</span>
                    </div>

                    <router-link
                        to="/admin/profile"
                        class="dropdown-item d-flex align-items-center gap-2 py-2 small"
                        @click="showDropdown = false"
                    >
                        <span>👤</span>
                        <span>Edit Profile</span>
                    </router-link>

                    <router-link
                        to="/admin/profile"
                        class="dropdown-item d-flex align-items-center gap-2 py-2 small"
                        @click="showDropdown = false"
                    >
                        <span>🔑</span>
                        <span>Change Password</span>
                    </router-link>

                    <router-link
                        to="/admin/website-settings"
                        class="dropdown-item d-flex align-items-center gap-2 py-2 small"
                        @click="showDropdown = false"
                    >
                        <span>⚙️</span>
                        <span>Website CMS & SEO</span>
                    </router-link>

                    <a
                        href="/"
                        target="_blank"
                        class="dropdown-item d-flex align-items-center gap-2 py-2 small text-primary"
                        @click="showDropdown = false"
                    >
                        <span>🌐</span>
                        <span>Visit Website</span>
                    </a>

                    <div class="dropdown-divider my-1"></div>

                    <button
                        type="button"
                        class="dropdown-item d-flex align-items-center gap-2 py-2 small text-danger"
                        @click="onLogoutClick"
                    >
                        <span>🚪</span>
                        <span class="fw-semibold">Sign Out / Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useAuth } from '../../composables/useAuth';
import { useNotification } from '../../composables/useNotification';

const { userName, userEmail, userRole, fetchProfile, logout } = useAuth();
const { confirmDialog } = useNotification();
const showDropdown = ref(false);

const userInitials = computed(() => {
    if (!userName.value) return 'AD';
    return userName.value
        .split(' ')
        .map(w => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

async function onLogoutClick() {
    showDropdown.value = false;
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

function handleGlobalClick(e) {
    if (!e.target.closest('.position-relative')) {
        showDropdown.value = false;
    }
}

onMounted(() => {
    fetchProfile();
    document.addEventListener('click', handleGlobalClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleGlobalClick);
});
</script>

<style scoped>
.extra-small {
    font-size: 0.72rem;
}

.user-avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 0.75rem;
}
</style>
