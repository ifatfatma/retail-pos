<template>
    <div class="customer-register py-5">
        <div class="container" style="max-width: 580px;">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="fs-1">🛍️</span>
                    <h3 class="fw-bold mb-1">Create Customer Account</h3>
                    <p class="text-muted small">Join us for seamless order tracking and instant checkout</p>
                </div>

                <form @submit.prevent="handleRegister">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Rahul Sharma"
                                required
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Email Address <span class="text-danger">*</span></label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-control"
                                placeholder="e.g. rahul@example.com"
                                required
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Phone Number <span class="text-danger">*</span></label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="form-control"
                                placeholder="e.g. 9876543210"
                                required
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control border-end-0"
                                    placeholder="Min. 6 characters"
                                    required
                                    minlength="6"
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

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Confirm Password <span class="text-danger">*</span></label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="form-control"
                                placeholder="Repeat password"
                                required
                                minlength="6"
                            />
                        </div>

                        <!-- Address fields -->
                        <div class="col-12 pt-2 border-top">
                            <div class="fw-semibold small text-muted text-uppercase mb-2">Default Delivery Address (Optional)</div>
                            <input
                                v-model="form.address"
                                type="text"
                                class="form-control mb-2"
                                placeholder="House / Flat No., Street, Landmark"
                            />
                        </div>

                        <div class="col-md-4">
                            <input
                                v-model="form.city"
                                type="text"
                                class="form-control"
                                placeholder="City"
                            />
                        </div>

                        <div class="col-md-4">
                            <input
                                v-model="form.state"
                                type="text"
                                class="form-control"
                                placeholder="State"
                            />
                        </div>

                        <div class="col-md-4">
                            <input
                                v-model="form.pincode"
                                type="text"
                                class="form-control"
                                placeholder="Pincode"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        :disabled="loading"
                    >
                        <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
                        <span>{{ loading ? 'Creating Account...' : 'Complete Registration' }}</span>
                    </button>
                </form>

                <div class="text-center pt-3 border-top small">
                    <span class="text-muted">Already have an account? </span>
                    <router-link :to="{ path: '/login', query: route.query }" class="fw-bold text-primary text-decoration-none">
                        Sign In Here →
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
import { useNotification } from '../../composables/useNotification';

const router = useRouter();
const route = useRoute();
const { register, loading } = useCustomerAuth();
const { toast } = useNotification();

const showPassword = ref(false);

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    address: '',
    city: '',
    state: '',
    pincode: '',
});

async function handleRegister() {
    if (form.value.password !== form.value.password_confirmation) {
        toast.error('Password and Confirmation do not match.', 'Validation Error');
        return;
    }

    const res = await register(form.value);
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

