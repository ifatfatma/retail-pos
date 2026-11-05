<template>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">👤 Admin Profile & Security</h3>
                <p class="text-muted mb-0">Manage your administrator account details, email address, and login credentials</p>
            </div>

            <div>
                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 d-flex align-items-center gap-1" @click="handleLogout">
                    <span>🚪</span>
                    <span>Sign Out</span>
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: User Summary Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-4">
                    <div class="avatar-circle mx-auto mb-3 bg-primary text-white fs-2 fw-bold d-flex align-items-center justify-content-center shadow-sm">
                        {{ userInitials }}
                    </div>

                    <h4 class="fw-bold mb-1 text-dark">{{ user?.name || 'Administrator' }}</h4>
                    <p class="text-muted small mb-2">{{ user?.email || 'admin@retailpos.local' }}</p>

                    <div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                            🛡️ {{ user?.role || 'Admin' }}
                        </span>
                    </div>

                    <hr class="my-4 text-muted" />

                    <div class="text-start small">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Account Status:</span>
                            <span class="text-success fw-bold">Active</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">System Role:</span>
                            <span class="fw-semibold text-dark">{{ user?.role || 'Super Admin' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Registered Since:</span>
                            <span class="text-dark">{{ user?.created_at || 'Recent' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Session & Quick Logout Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="fw-bold mb-2">🔐 Active Session</h6>
                    <p class="text-muted extra-small mb-3">
                        You are currently authenticated with administrative privileges across the Retail POS counter, inventory stock, and orders.
                    </p>
                    <button class="btn btn-outline-danger w-100 rounded-pill fw-semibold" @click="handleLogout">
                        Sign Out of Session
                    </button>
                </div>
            </div>

            <!-- Right Column: Edit Profile & Change Password Forms -->
            <div class="col-lg-8">
                <!-- 1. Edit Profile Information Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="fs-4">✏️</span>
                        <h5 class="fw-bold mb-0">Edit Profile Information</h5>
                    </div>

                    <!-- Profile Alerts -->
                    <div v-if="profileSuccess" class="alert alert-success alert-dismissible fade show small rounded-3 mb-3" role="alert">
                        <strong>Success:</strong> {{ profileSuccess }}
                        <button type="button" class="btn-close" @click="profileSuccess = ''"></button>
                    </div>

                    <div v-if="profileError" class="alert alert-danger alert-dismissible fade show small rounded-3 mb-3" role="alert">
                        <strong>Error:</strong> {{ profileError }}
                        <button type="button" class="btn-close" @click="profileError = ''"></button>
                    </div>

                    <form @submit.prevent="submitProfile">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    class="form-control"
                                    placeholder="e.g. Administrator"
                                    required
                                />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Email Address <span class="text-danger">*</span></label>
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    class="form-control"
                                    placeholder="e.g. admin@retailpos.local"
                                    required
                                />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Assigned Role</label>
                                <input
                                    type="text"
                                    class="form-control bg-light"
                                    :value="user?.role || 'Admin'"
                                    disabled
                                    readonly
                                />
                                <div class="extra-small text-muted mt-1">Role assignments can be configured in Roles & Permissions.</div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button
                                type="submit"
                                class="btn btn-primary px-4 rounded-pill fw-semibold"
                                :disabled="savingProfile"
                            >
                                <span v-if="savingProfile" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ savingProfile ? 'Saving Changes...' : 'Save Profile Changes' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. Change Password Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" id="password-section">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="fs-4">🔑</span>
                        <h5 class="fw-bold mb-0">Change Security Password</h5>
                    </div>

                    <!-- Password Alerts -->
                    <div v-if="passwordSuccess" class="alert alert-success alert-dismissible fade show small rounded-3 mb-3" role="alert">
                        <strong>Success:</strong> {{ passwordSuccess }}
                        <button type="button" class="btn-close" @click="passwordSuccess = ''"></button>
                    </div>

                    <div v-if="passwordError" class="alert alert-danger alert-dismissible fade show small rounded-3 mb-3" role="alert">
                        <strong>Error:</strong> {{ passwordError }}
                        <button type="button" class="btn-close" @click="passwordError = ''"></button>
                    </div>

                    <form @submit.prevent="submitPassword">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    v-model="passwordForm.current_password"
                                    :type="showCurrentPassword ? 'text' : 'password'"
                                    class="form-control border-end-0"
                                    placeholder="Enter your current password"
                                    required
                                />
                                <button
                                    type="button"
                                    class="input-group-text bg-light border-start-0 text-muted"
                                    @click="showCurrentPassword = !showCurrentPassword"
                                >
                                    {{ showCurrentPassword ? '🙈' : '👁️' }}
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted text-uppercase">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input
                                        v-model="passwordForm.password"
                                        :type="showNewPassword ? 'text' : 'password'"
                                        class="form-control border-end-0"
                                        placeholder="Minimum 6 characters"
                                        required
                                        minlength="6"
                                    />
                                    <button
                                        type="button"
                                        class="input-group-text bg-light border-start-0 text-muted"
                                        @click="showNewPassword = !showNewPassword"
                                    >
                                        {{ showNewPassword ? '🙈' : '👁️' }}
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Confirm New Password <span class="text-danger">*</span></label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="form-control"
                                    placeholder="Re-type new password"
                                    required
                                    minlength="6"
                                />
                            </div>
                        </div>

                        <div class="text-end">
                            <button
                                type="submit"
                                class="btn btn-warning px-4 rounded-pill fw-semibold text-dark"
                                :disabled="savingPassword"
                            >
                                <span v-if="savingPassword" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ savingPassword ? 'Updating Password...' : 'Update Password' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import { useNotification } from '../../composables/useNotification';

const { user, fetchProfile, updateProfile, changePassword, logout } = useAuth();
const { confirmDialog } = useNotification();

const profileForm = ref({
    name: '',
    email: '',
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);

const savingProfile = ref(false);
const profileSuccess = ref('');
const profileError = ref('');

const savingPassword = ref(false);
const passwordSuccess = ref('');
const passwordError = ref('');

const userInitials = computed(() => {
    if (!user.value?.name) return 'AD';
    return user.value.name
        .split(' ')
        .map(w => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

async function submitProfile() {
    profileSuccess.value = '';
    profileError.value = '';
    savingProfile.value = true;

    const res = await updateProfile({
        name: profileForm.value.name.trim(),
        email: profileForm.value.email.trim(),
    });

    if (res.success) {
        profileSuccess.value = res.message;
    } else {
        profileError.value = res.message;
    }
    savingProfile.value = false;
}

async function submitPassword() {
    passwordSuccess.value = '';
    passwordError.value = '';

    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
        passwordError.value = 'New password and confirmation password do not match.';
        return;
    }

    savingPassword.value = true;

    const res = await changePassword({
        current_password: passwordForm.value.current_password,
        password: passwordForm.value.password,
        password_confirmation: passwordForm.value.password_confirmation,
    });

    if (res.success) {
        passwordSuccess.value = res.message;
        passwordForm.value.current_password = '';
        passwordForm.value.password = '';
        passwordForm.value.password_confirmation = '';
    } else {
        passwordError.value = res.message;
    }
    savingPassword.value = false;
}

async function handleLogout() {
    const ok = await confirmDialog({
        title: 'Sign Out of Portal?',
        message: 'Are you sure you want to sign out of the Admin Portal?',
        confirmText: 'Sign Out',
        cancelText: 'Stay Logged In',
        type: 'danger',
    });
    if (ok) {
        logout();
    }
}

onMounted(async () => {
    await fetchProfile();
    if (user.value) {
        profileForm.value.name = user.value.name || '';
        profileForm.value.email = user.value.email || '';
    }
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
}
</style>

