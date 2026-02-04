import { ref, computed } from 'vue';
import api from '../services/api';
import { useRouter } from 'vue-router';
import { useToast } from './useToast';

const user = ref(JSON.parse(localStorage.getItem('auth_user')) || null);
const token = ref(localStorage.getItem('auth_token') || null);
const loading = ref(false);
const error = ref(null);

export function useAuth() {
    const router = useRouter();

    const isAuthenticated = computed(() => !!token.value);

    const login = async (credentials) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post('/auth/login', credentials);
            const { access_token: authToken, user: authUser } = response.data.data;

            token.value = authToken;
            user.value = authUser;

            localStorage.setItem('auth_token', authToken);
            localStorage.setItem('auth_user', JSON.stringify(authUser));

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Login failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const register = async (userData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post('/auth/register', userData);

            // Auto-login after registration (save token)
            if (response.data.data?.access_token) {
                const { access_token: authToken, user: authUser } = response.data.data;
                token.value = authToken;
                user.value = authUser;
                localStorage.setItem('auth_token', authToken);
                localStorage.setItem('auth_user', JSON.stringify(authUser));
            }

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Registration failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const { success, error: toastError } = useToast();

    const logout = async () => {
        try {
            await api.post('/auth/logout');
            success('Logged out successfully');
        } catch (err) {
            console.error('Logout error', err);
            // Optional: toastError('Logout encountered an issue');
        } finally {
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            router.push({ name: 'login' });
        }
    };

    const verifyEmail = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post('/auth/verify-email', data);
            // After verification, we might get user data
            if (response.data.data) {
                user.value = response.data.data;
                localStorage.setItem('auth_user', JSON.stringify(response.data.data));
            }
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Verification failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const resendOtp = async () => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post('/auth/resend-otp');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to resend code';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const forgotPassword = async (email) => {
        loading.value = true;
        error.value = null;
        try {
            // Backend expects 'identity'
            const response = await api.post('/auth/forgot-password', { identity: email });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Request failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const resetPassword = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            // Backend expects identity, otp, password, password_confirmation
            const payload = {
                identity: data.email,
                otp: data.token,
                password: data.password,
                password_confirmation: data.password_confirmation
            };
            const response = await api.post('/auth/reset-password', payload);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Reset failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        login,
        register,
        logout,
        verifyEmail,
        resendOtp,
        forgotPassword,
        resetPassword
    };
}
