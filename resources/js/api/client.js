import axios from 'axios';

// Create Axios instance
const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
});

// Loading state counter (supports concurrent requests)
let activeRequests = 0;

function setLoading(isLoading) {
    if (isLoading) {
        activeRequests++;
    } else {
        activeRequests = Math.max(0, activeRequests - 1);
    }
    const indicator = document.getElementById('global-loading-indicator');
    if (indicator) {
        indicator.classList.toggle('d-none', activeRequests === 0);
    }
}

// Request interceptor — attach CSRF token and auth token, manage loading
apiClient.interceptors.request.use(
    (config) => {
        // CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        // Auth token from appData (Sanctum token if stored)
        const token = window.appData?.token || localStorage.getItem('api_token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        setLoading(true);
        return config;
    },
    (error) => {
        setLoading(false);
        return Promise.reject(error);
    }
);

// Response interceptor — handle errors globally
apiClient.interceptors.response.use(
    (response) => {
        setLoading(false);
        return response;
    },
    (error) => {
        setLoading(false);

        if (!error.response) {
            // Network error
            window.utils?.showToast('Network error. Please check your connection.', 'error');
            return Promise.reject(error);
        }

        const { status, data } = error.response;

        switch (status) {
            case 401:
                // Redirect to login on unauthorized
                window.utils?.showToast('Session expired. Redirecting to login...', 'warning');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);
                break;

            case 403:
                window.utils?.showToast('You do not have permission to perform this action.', 'error');
                break;

            case 404:
                window.utils?.showToast('The requested resource was not found.', 'error');
                break;

            case 422:
                // Validation errors — let the caller handle inline display
                // but show a generic toast if no handler catches it
                break;

            case 500:
            default:
                window.utils?.showToast(data?.message || 'A server error occurred. Please try again.', 'error');
                break;
        }

        return Promise.reject(error);
    }
);

export default apiClient;
