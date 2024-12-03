import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js'; // Pusher wird für Echo benötigt

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,  // Port aus env
    encrypted: false,
    disableStats: true,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1'
});

