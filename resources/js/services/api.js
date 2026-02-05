import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Add a request interceptor to attach the JWT token
api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

// Add a response interceptor to handle errors
api.interceptors.response.use(response => {
    return response;
}, async error => {
    const originalRequest = error.config;

    const isLoginRequest = originalRequest.url.includes('/auth/login');
    const hasToken = !!localStorage.getItem('auth_token');

    if (error.response && error.response.status === 401 && !originalRequest._retry && !isLoginRequest && hasToken) {
        originalRequest._retry = true;

        try {
            // Call refresh endpoint to get a new token
            const response = await axios.post('/api/auth/refresh', {}, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });

            const newToken = response.data.access_token;

            // Update local storage
            localStorage.setItem('auth_token', newToken);

            // Update header and retry original request
            originalRequest.headers.Authorization = `Bearer ${newToken}`;
            return api(originalRequest);
        } catch (refreshError) {
            // Handle refresh token failure (e.g., token already expired)
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            window.location.href = '/login';
            return Promise.reject(refreshError);
        }
    }

    return Promise.reject(error);
});

export default api;
