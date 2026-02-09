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
let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });

    failedQueue = [];
};

api.interceptors.response.use(response => {
    return response;
}, async error => {
    const originalRequest = error.config;

    const isLoginRequest = originalRequest.url.includes('/auth/login');
    const hasToken = !!localStorage.getItem('auth_token');

    if (error.response && error.response.status === 401 && !originalRequest._retry && !isLoginRequest && hasToken) {
        if (isRefreshing) {
            return new Promise((resolve, reject) => {
                failedQueue.push({ resolve, reject });
            }).then(token => {
                originalRequest.headers['Authorization'] = 'Bearer ' + token;
                return api(originalRequest);
            }).catch(err => {
                return Promise.reject(err);
            });
        }

        originalRequest._retry = true;
        isRefreshing = true;

        try {
            const currentToken = localStorage.getItem('auth_token');
            if (!currentToken) {
                throw new Error('No token found');
            }

            // Call refresh endpoint to get a new token
            const response = await axios.post('/api/auth/refresh', {}, {
                headers: {
                    'Authorization': `Bearer ${currentToken}`,
                    'Accept': 'application/json'
                }
            });

            const newToken = response.data.access_token;

            // Update local storage
            localStorage.setItem('auth_token', newToken);

            // If user data is returned, update it too (optional but good)
            if (response.data.user) {
                localStorage.setItem('auth_user', JSON.stringify(response.data.user));
            }

            // Update header and retry original request
            api.defaults.headers.common['Authorization'] = 'Bearer ' + newToken;
            originalRequest.headers['Authorization'] = 'Bearer ' + newToken;

            // Process queued requests
            processQueue(null, newToken);

            return api(originalRequest);
        } catch (refreshError) {
            // Handle refresh token failure (e.g., token already expired)
            processQueue(refreshError, null);
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            window.location.href = '/login';
            return Promise.reject(refreshError);
        } finally {
            isRefreshing = false;
        }
    }

    return Promise.reject(error);
});

export default api;
