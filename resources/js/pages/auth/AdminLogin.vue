<template>
    <div class="admin-login-wrapper d-flex align-items-center justify-content-center min-vh-100 bg-slate position-relative p-3">
        <!-- Top Language Selector -->
        <div class="position-absolute top-0 end-0 m-3 z-3">
            <LanguageSelector theme="dark" />
        </div>

        <div class="login-card-container p-2" style="max-width: 440px; width: 100%;">
            <!-- Brand Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-dark text-white text-center py-4 border-0">
                    <div class="fs-1 mb-1">🏪</div>
                    <h4 class="fw-bold mb-0">{{ $t('header.portal_short') }}</h4>
                    <p class="text-white-50 small mb-0">{{ $t('auth.admin_login_sub') }}</p>
                </div>

                <div class="card-body p-4 p-sm-5">
                    <!-- Error Alert -->
                    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show small rounded-3 mb-4" role="alert">
                        <strong>{{ $t('common.error') }}:</strong> {{ errorMessage }}
                        <button type="button" class="btn-close" @click="errorMessage = ''"></button>
                    </div>

                    <!-- Login Form -->
                    <form @submit.prevent="handleLogin">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('common.email') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">📧</span>
                                <input
                                    v-model="email"
                                    type="email"
                                    class="form-control border-start-0"
                                    placeholder="admin@retailpos.local"
                                    required
                                    autocomplete="email"
                                />
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted text-uppercase mb-0">{{ $t('auth.password') }}</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">🔑</span>
                                <input
                                    v-model="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control border-start-0 border-end-0"
                                    :placeholder="$t('auth.password_placeholder')"
                                    required
                                    autocomplete="current-password"
                                />
                                <button
                                    type="button"
                                    class="input-group-text bg-light border-start-0 text-muted"
                                    @click="showPassword = !showPassword"
                                    title="Toggle password visibility"
                                >
                                    {{ showPassword ? '🙈' : '👁️' }}
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
                            <span>{{ loading ? $t('common.processing') : $t('auth.admin_sign_in') }}</span>
                        </button>
                    </form>

                    <!-- Quick Auto-fill Credentials for convenience -->
                    <div class="quick-credentials p-3 bg-light rounded-3 text-center border mb-3">
                        <div class="extra-small text-muted mb-1 fw-semibold text-uppercase">{{ $t('header.admin_panel') }}</div>
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill extra-small"
                            @click="fillAdminCreds"
                        >
                            ⚡ Auto-fill: <strong>admin@retailpos.local</strong>
                        </button>
                    </div>

                    <!-- Link to storefront -->
                    <div class="text-center pt-2">
                        <router-link to="/" class="text-decoration-none small text-muted">
                            &larr; {{ $t('nav.view_store') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '../../composables/useAuth';
import LanguageSelector from '../../components/common/LanguageSelector.vue';

const router = useRouter();
const route = useRoute();
const { login, loading } = useAuth();

const email = ref('admin@retailpos.local');
const password = ref('password');
const showPassword = ref(false);
const errorMessage = ref('');

function fillAdminCreds() {
    email.value = 'admin@retailpos.local';
    password.value = 'password';
}

async function handleLogin() {
    errorMessage.value = '';
    const res = await login(email.value.trim(), password.value);
    if (res.success) {
        const redirectPath = route.query.redirect || '/admin/dashboard';
        router.push(redirectPath);
    } else {
        errorMessage.value = res.message;
    }
}
</script>

<style scoped>
.bg-slate {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.extra-small {
    font-size: 0.75rem;
}
</style>
