import axios from 'axios';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

// Pusher global verfügbar machen (wird von Echo benötigt)
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';
window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = '/api';

// Reverb Konfiguration (via Pusher-Protokoll)
window.Echo = new Echo({
    broadcaster: 'reverb', // Reverb: Laravel's eigener WebSocket-Server
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/api/broadcasting/auth' 
});

