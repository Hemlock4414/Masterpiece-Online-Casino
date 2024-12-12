import axios from 'axios';

export const apiClient = axios.create({
    baseURL: 'http://localhost/api',
    headers: { 'Content-Type': 'application/json' },
  });

// Optional: Interceptors für Fehlerbehandlung
apiClient.interceptors.response.use(
    response => response,
    error => {
        console.error('API Error:', error);
        return Promise.reject(error);
    }
);

export default apiClient;