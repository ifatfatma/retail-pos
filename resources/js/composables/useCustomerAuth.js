import { ref, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useNotification } from './useNotification';

const CUSTOMER_TOKEN_KEY = 'store_customer_token';
const CUSTOMER_USER_KEY = 'store_customer_profile';

// Singleton customer reactive state
const customerToken = ref(localStorage.getItem(CUSTOMER_TOKEN_KEY) || null);
const customer = ref(null);
const loading = ref(false);

try {
    const saved = localStorage.getItem(CUSTOMER_USER_KEY);
    if (saved) {
        customer.value = JSON.parse(saved);
    }
} catch (e) {
    console.error('Failed to parse saved customer profile:', e);
}

// Axios interceptor to automatically route between admin token and customer token
let interceptorInstalled = false;
if (!interceptorInstalled && typeof window !== 'undefined') {
    interceptorInstalled = true;
    axios.interceptors.request.use((config) => {
        const isShopApi = config.url && (config.url.startsWith('/api/shop') || config.url.includes('/shop/'));
        const custTok = localStorage.getItem(CUSTOMER_TOKEN_KEY);
        const adminTok = localStorage.getItem('admin_access_token');

        if (isShopApi && custTok) {
            config.headers['Authorization'] = `Bearer ${custTok}`;
        } else if (!isShopApi && adminTok) {
            config.headers['Authorization'] = `Bearer ${adminTok}`;
        }

        return config;
    });
}

export function useCustomerAuth() {
    const router = useRouter();
    const { toast } = useNotification();

    const isCustomerAuthenticated = computed(() => {
        return !!customerToken.value && !!customer.value;
    });

    const customerName = computed(() => customer.value?.name || 'Customer');
    const customerEmail = computed(() => customer.value?.email || '');
    const customerPhone = computed(() => customer.value?.phone || '');

    function setCustomerAuthData(newToken, customerData) {
        customerToken.value = newToken;
        customer.value = customerData;

        if (newToken) {
            localStorage.setItem(CUSTOMER_TOKEN_KEY, newToken);
        } else {
            localStorage.removeItem(CUSTOMER_TOKEN_KEY);
        }

        if (customerData) {
            localStorage.setItem(CUSTOMER_USER_KEY, JSON.stringify(customerData));
        } else {
            localStorage.removeItem(CUSTOMER_USER_KEY);
        }
    }

    async function register(payload) {
        loading.value = true;
        try {
            const res = await axios.post('/api/shop/auth/register', payload);
            setCustomerAuthData(res.data.token, res.data.customer);
            toast.success(res.data.message || 'Account created successfully!', 'Welcome');

            // Sync guest wishlist if any
            try {
                const guestToken = localStorage.getItem('store_guest_token');
                if (guestToken) {
                    await axios.post('/api/shop/wishlist/sync', { guest_token: guestToken });
                }
            } catch (e) {
                console.error('Wishlist sync on register error:', e);
            }

            return { success: true, customer: res.data.customer };
        } catch (error) {
            console.error('Customer register error:', error);
            const errs = error.response?.data?.errors;
            const message = errs ? Object.values(errs).flat()[0] : (error.response?.data?.message || 'Registration failed.');
            toast.error(message, 'Registration Error');
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function login(loginCredential, password) {
        loading.value = true;
        try {
            const res = await axios.post('/api/shop/auth/login', {
                login: loginCredential,
                password,
            });
            setCustomerAuthData(res.data.token, res.data.customer);
            toast.success(res.data.message || 'Signed in successfully!', 'Welcome Back');

            // Sync guest wishlist if any
            try {
                const guestToken = localStorage.getItem('store_guest_token');
                if (guestToken) {
                    await axios.post('/api/shop/wishlist/sync', { guest_token: guestToken });
                }
            } catch (e) {
                console.error('Wishlist sync on login error:', e);
            }

            return { success: true, customer: res.data.customer };
        } catch (error) {
            console.error('Customer login error:', error);
            const message = error.response?.data?.errors?.login?.[0]
                || error.response?.data?.message
                || 'Invalid email/phone or password.';
            toast.error(message, 'Login Failed');
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function fetchCustomerProfile() {
        if (!customerToken.value) return null;
        try {
            const res = await axios.get('/api/shop/auth/me');
            if (res.data.customer) {
                customer.value = res.data.customer;
                localStorage.setItem(CUSTOMER_USER_KEY, JSON.stringify(res.data.customer));
            }
            return customer.value;
        } catch (error) {
            if (error.response?.status === 401) {
                logout();
            }
            return null;
        }
    }

    async function updateProfile(payload) {
        loading.value = true;
        try {
            const res = await axios.put('/api/shop/auth/profile', payload);
            if (res.data.customer) {
                customer.value = res.data.customer;
                localStorage.setItem(CUSTOMER_USER_KEY, JSON.stringify(res.data.customer));
            }
            toast.success(res.data.message || 'Profile updated successfully!', 'Saved');
            return { success: true, customer: res.data.customer };
        } catch (error) {
            console.error('Customer update profile error:', error);
            const errs = error.response?.data?.errors;
            const message = errs ? Object.values(errs).flat()[0] : (error.response?.data?.message || 'Update failed.');
            toast.error(message, 'Update Failed');
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function changePassword(payload) {
        loading.value = true;
        try {
            const res = await axios.put('/api/shop/auth/password', payload);
            toast.success(res.data.message || 'Password changed successfully!', 'Security Updated');
            return { success: true };
        } catch (error) {
            console.error('Customer change password error:', error);
            const errs = error.response?.data?.errors;
            const message = errs ? Object.values(errs).flat()[0] : (error.response?.data?.message || 'Failed to update password.');
            toast.error(message, 'Password Error');
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function forgotPassword(loginCredential) {
        loading.value = true;
        try {
            const res = await axios.post('/api/shop/auth/forgot-password', {
                login: loginCredential,
            });
            toast.success(res.data.message || 'Verification code sent.', 'Code Sent');
            return {
                success: true,
                data: res.data,
            };
        } catch (error) {
            console.error('Customer forgot password error:', error);
            const message = error.response?.data?.errors?.login?.[0]
                || error.response?.data?.message
                || 'Failed to process password reset request.';
            toast.error(message, 'Request Failed');
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function resetPassword(payload) {
        loading.value = true;
        try {
            const res = await axios.post('/api/shop/auth/reset-password', payload);
            setCustomerAuthData(res.data.token, res.data.customer);
            toast.success(res.data.message || 'Password reset successfully! Welcome back.', 'Password Reset');

            // Sync guest wishlist if any
            try {
                const guestToken = localStorage.getItem('store_guest_token');
                if (guestToken) {
                    await axios.post('/api/shop/wishlist/sync', { guest_token: guestToken });
                }
            } catch (e) {
                console.error('Wishlist sync on password reset error:', e);
            }

            return {
                success: true,
                customer: res.data.customer,
            };
        } catch (error) {
            console.error('Customer reset password error:', error);
            const errs = error.response?.data?.errors;
            const message = errs ? Object.values(errs).flat()[0] : (error.response?.data?.message || 'Password reset failed.');
            toast.error(message, 'Reset Failed');
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function fetchOrders(page = 1) {
        try {
            const res = await axios.get(`/api/shop/account/orders?page=${page}`);
            return res.data;
        } catch (error) {
            console.error('Failed to fetch customer orders:', error);
            return null;
        }
    }

    async function fetchOrderDetails(orderNumber) {
        try {
            const res = await axios.get(`/api/shop/account/orders/${orderNumber}`);
            return res.data.data;
        } catch (error) {
            console.error('Failed to fetch order details:', error);
            return null;
        }
    }

    function logout() {
        if (customerToken.value) {
            axios.post('/api/shop/auth/logout').catch(() => {});
        }
        setCustomerAuthData(null, null);
        toast.info('You have signed out of your account.', 'Signed Out');
        if (router) {
            router.push('/');
        }
    }

    return {
        customerToken,
        customer,
        loading,
        isCustomerAuthenticated,
        customerName,
        customerEmail,
        customerPhone,
        register,
        login,
        fetchCustomerProfile,
        updateProfile,
        changePassword,
        forgotPassword,
        resetPassword,
        fetchOrders,
        fetchOrderDetails,
        logout,
    };
}

