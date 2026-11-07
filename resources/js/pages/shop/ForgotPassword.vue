<template>
    <div class="forgot-password py-5">
        <div class="container" style="max-width: 480px;">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">

                <!-- STEP 1: Enter Email or Phone -->
                <div v-if="step === 1">
                    <div class="text-center mb-4">
                        <div class="icon-bubble bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                            <span class="fs-2">🔐</span>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $t('auth.forgot_password_title') }}</h3>
                        <p class="text-muted small">
                            {{ $t('auth.forgot_password_sub') }}
                        </p>
                    </div>

                    <form @submit.prevent="handleRequestCode">
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase">
                                {{ $t('auth.email_or_phone') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">📱</span>
                                <input
                                    v-model="loginField"
                                    type="text"
                                    class="form-control border-start-0"
                                    :placeholder="$t('auth.email_placeholder')"
                                    required
                                    autofocus
                                />
                            </div>
                            <div class="form-text extra-small text-muted mt-1">
                                {{ $t('auth.reset_code_help') }}
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
                            <span>{{ loading ? $t('auth.sending_code') : $t('auth.send_code') + ' →' }}</span>
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top small">
                        <router-link
                            :to="{ path: '/login', query: route.query }"
                            class="fw-semibold text-primary text-decoration-none"
                        >
                            ← {{ $t('auth.back_to_sign_in') }}
                        </router-link>
                    </div>
                </div>

                <!-- STEP 2: Enter Verification Code & New Password -->
                <div v-else-if="step === 2">
                    <div class="text-center mb-4">
                        <div class="icon-bubble bg-success-subtle text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                            <span class="fs-2">🔑</span>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $t('auth.set_new_password') }}</h3>
                        <p class="text-muted small mb-0">
                            {{ $t('auth.enter_code_sent_to') }} <strong>{{ resetResponse?.identifier || loginField }}</strong>
                        </p>
                    </div>

                    <!-- Demo / Local Dev Code Banner -->
                    <div
                        v-if="resetResponse?.debug_code"
                        class="alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <span>⚡ <strong>{{ $t('auth.verification_code') }}:</strong> </span>
                            <code class="fs-6 fw-bold ms-1 text-dark">{{ resetResponse.debug_code }}</code>
                        </div>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary py-0 px-2 extra-small rounded-pill"
                            @click="code = resetResponse.debug_code"
                        >
                            {{ $t('auth.use_code') }}
                        </button>
                    </div>

                    <form @submit.prevent="handleResetPassword">
                        <!-- Verification Code -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">
                                {{ $t('auth.verification_code_label') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">🔢</span>
                                <input
                                    v-model="code"
                                    type="text"
                                    class="form-control border-start-0 text-center fw-bold fs-5 letter-spacing"
                                    placeholder="••••••"
                                    maxlength="6"
                                    required
                                    autofocus
                                />
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('auth.new_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">🔒</span>
                                <input
                                    v-model="newPassword"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control border-start-0 border-end-0"
                                    :placeholder="$t('auth.password_placeholder')"
                                    minlength="6"
                                    required
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

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('auth.confirm_new_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">🔒</span>
                                <input
                                    v-model="confirmPassword"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control border-start-0"
                                    :placeholder="$t('auth.password_placeholder')"
                                    minlength="6"
                                    required
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-success btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
                            <span>{{ loading ? $t('auth.updating_password') : $t('auth.reset_password') }}</span>
                        </button>
                    </form>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                        <button
                            type="button"
                            class="btn btn-link btn-sm text-decoration-none text-muted p-0"
                            @click="step = 1"
                        >
                            ← {{ $t('auth.change_identifier') }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-link btn-sm text-decoration-none text-primary p-0"
                            :disabled="resending"
                            @click="handleResendCode"
                        >
                            <span v-if="resending" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ $t('auth.resend_code') }}
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Success Confirmation -->
                <div v-else-if="step === 3" class="text-center py-3">
                    <div class="icon-bubble bg-success-subtle text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <span class="fs-1">🎉</span>
                    </div>
                    <h4 class="fw-bold mb-2 text-dark">{{ $t('auth.reset_successful') }}</h4>
                    <p class="text-muted small mb-4">
                        {{ $t('auth.reset_successful_sub') }}
                    </p>
                    <button
                        type="button"
                        class="btn btn-primary rounded-pill px-4 fw-semibold"
                        @click="goToNext"
                    >
                        {{ $t('auth.go_to_account') }} →
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useCustomerAuth } from '../../composables/useCustomerAuth';
import { useNotification } from '../../composables/useNotification';

const router = useRouter();
const route = useRoute();
const { forgotPassword, resetPassword, loading } = useCustomerAuth();
const { toast } = useNotification();

const step = ref(1);
const loginField = ref(route.query.login || '');
const resetResponse = ref(null);

const code = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const showPassword = ref(false);
const resending = ref(false);

async function handleRequestCode() {
    if (!loginField.value.trim()) {
        toast.warning('Please enter your email or phone number.', 'Input Required');
        return;
    }

    const res = await forgotPassword(loginField.value.trim());
    if (res.success) {
        resetResponse.value = res.data;
        if (res.data.debug_code) {
            code.value = res.data.debug_code;
        }
        step.value = 2;
    }
}

async function handleResendCode() {
    if (!loginField.value.trim()) return;
    resending.value = true;
    const res = await forgotPassword(loginField.value.trim());
    if (res.success) {
        resetResponse.value = res.data;
        if (res.data.debug_code) {
            code.value = res.data.debug_code;
        }
    }
    resending.value = false;
}

async function handleResetPassword() {
    if (!code.value.trim() || code.value.trim().length !== 6) {
        toast.warning('Please enter a valid 6-digit verification code.', 'Validation Error');
        return;
    }

    if (newPassword.value !== confirmPassword.value) {
        toast.error('New password and confirmation do not match.', 'Validation Error');
        return;
    }

    const payload = {
        identifier: loginField.value.trim(),
        code: code.value.trim(),
        password: newPassword.value,
        password_confirmation: confirmPassword.value,
    };

    const res = await resetPassword(payload);
    if (res.success) {
        step.value = 3;
        setTimeout(() => {
            goToNext();
        }, 2000);
    }
}

function goToNext() {
    const redirectPath = route.query.redirect || '/account';
    router.push(redirectPath);
}

onMounted(() => {
    if (route.query.login) {
        loginField.value = route.query.login;
    }
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.icon-bubble {
    width: 64px;
    height: 64px;
}

.letter-spacing {
    letter-spacing: 4px;
}
</style>

