import { ref, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const TOKEN_KEY = 'admin_access_token';
const USER_KEY = 'admin_user_profile';

// Singleton reactive state
const token = ref(localStorage.getItem(TOKEN_KEY) || null);
const user = ref(null);
const loading = ref(false);

try {
    const savedUser = localStorage.getItem(USER_KEY);
    if (savedUser) {
        user.value = JSON.parse(savedUser);
    }
} catch (e) {
    console.error('Failed to parse saved admin user:', e);
}

// Configure axios Authorization header immediately if token exists
if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
}

export function useAuth() {
    const router = useRouter();

    const isAuthenticated = computed(() => {
        return !!token.value && !!user.value;
    });

    const userName = computed(() => user.value?.name || 'Administrator');
    const userEmail = computed(() => user.value?.email || 'admin@retailpos.local');
    const userRole = computed(() => user.value?.role || 'Admin');

    function setAuthData(newToken, userData) {
        token.value = newToken;
        user.value = userData;

        if (newToken) {
            localStorage.setItem(TOKEN_KEY, newToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
        } else {
            localStorage.removeItem(TOKEN_KEY);
            delete axios.defaults.headers.common['Authorization'];
        }

        if (userData) {
            localStorage.setItem(USER_KEY, JSON.stringify(userData));
        } else {
            localStorage.removeItem(USER_KEY);
        }
    }

    async function login(email, password) {
        loading.value = true;
        try {
            const res = await axios.post('/api/auth/login', {
                email,
                password,
            });

            setAuthData(res.data.token, res.data.user);
            return { success: true, user: res.data.user };
        } catch (error) {
            console.error('Login error:', error);
            const message = error.response?.data?.errors?.email?.[0]
                || error.response?.data?.message
                || 'Invalid login credentials.';
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function fetchProfile() {
        if (!token.value) return null;
        try {
            const res = await axios.get('/api/auth/me');
            if (res.data.user) {
                user.value = res.data.user;
                localStorage.setItem(USER_KEY, JSON.stringify(res.data.user));
            }
            return user.value;
        } catch (error) {
            if (error.response?.status === 401) {
                // Token expired or invalid
                logout();
            }
            return null;
        }
    }

    async function updateProfile({ name, email }) {
        loading.value = true;
        try {
            const res = await axios.put('/api/auth/profile', { name, email });
            if (res.data.user) {
                user.value = res.data.user;
                localStorage.setItem(USER_KEY, JSON.stringify(res.data.user));
            }
            return { success: true, message: res.data.message || 'Profile updated successfully.' };
        } catch (error) {
            console.error('Update profile error:', error);
            const message = error.response?.data?.errors?.email?.[0]
                || error.response?.data?.errors?.name?.[0]
                || error.response?.data?.message
                || 'Failed to update profile.';
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function changePassword({ current_password, password, password_confirmation }) {
        loading.value = true;
        try {
            const res = await axios.put('/api/auth/password', {
                current_password,
                password,
                password_confirmation,
            });
            return { success: true, message: res.data.message || 'Password changed successfully.' };
        } catch (error) {
            console.error('Change password error:', error);
            const message = error.response?.data?.errors?.current_password?.[0]
                || error.response?.data?.errors?.password?.[0]
                || error.response?.data?.message
                || 'Failed to change password.';
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        try {
            if (token.value) {
                await axios.post('/api/auth/logout');
            }
        } catch (e) {
            // Ignore server error on logout
        } finally {
            setAuthData(null, null);
            if (router) {
                router.push('/admin/login');
            } else {
                window.location.href = '/admin/login';
            }
        }
    }

    return {
        token,
        user,
        loading,
        isAuthenticated,
        userName,
        userEmail,
        userRole,
        setAuthData,
        login,
        fetchProfile,
        updateProfile,
        changePassword,
        logout,
    };
}

