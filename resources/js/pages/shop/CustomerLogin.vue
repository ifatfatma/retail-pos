<template>
    <div class="customer-login py-5">
        <div class="container" style="max-width: 460px;">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="fs-1">👤</span>
                    <h3 class="fw-bold mb-1">{{ $t('auth.customer_sign_in') }}</h3>
                    <p class="text-muted small">{{ $t('auth.customer_login_sub') }}</p>
                </div>

                <form @submit.prevent="handleLogin">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('auth.email_or_phone') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">📱</span>
                            <input
                                v-model="loginField"
                                type="text"
                                class="form-control border-start-0"
                                :placeholder="$t('auth.email_placeholder')"
                                required
                                autocomplete="username"
                            />
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-0">{{ $t('auth.password') }}</label>
                            <router-link
                                :to="{ path: '/forgot-password', query: { login: loginField || undefined, redirect: route.query.redirect } }"
                                class="text-primary extra-small text-decoration-none fw-semibold"
                            >
                                {{ $t('auth.forgot_password') }}
                            </router-link>
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
                            >
                                {{ showPassword ? '🙈' : '👁️' }}
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        :disabled="loading"
                    >
                        <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
                        <span>{{ loading ? $t('auth.signing_in') : $t('auth.customer_sign_in') }}</span>
                    </button>
                </form>

                <div class="text-center pt-3 border-top small">
                    <span class="text-muted">{{ $t('auth.no_account') }} </span>
                    <router-link :to="{ path: '/register', query: route.query }" class="fw-bold text-primary text-decoration-none">
                        {{ $t('auth.create_account') }} →
                    </router-link>
                </div>

                <div class="text-center mt-3">
                    <router-link to="/" class="text-muted extra-small text-decoration-none">
                        ← {{ $t('auth.continue_guest') }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useCustomerAuth } from '../../composables/useCustomerAuth';

const router = useRouter();
const route = useRoute();
const { login, loading } = useCustomerAuth();

const loginField = ref('');
const password = ref('');
const showPassword = ref(false);

async function handleLogin() {
    if (!loginField.value.trim() || !password.value) return;

    const res = await login(loginField.value.trim(), password.value);
    if (res.success) {
        const redirectPath = route.query.redirect || '/account';
        router.push(redirectPath);
    }
}
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}
</style>

