import axios from 'axios';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

// Pusher global verfügbar machen (wie von Echo benötigt)
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Reverb Konfiguration
window.Echo = new Echo({
    broadcaster: 'pusher', // Wichtig: 'pusher' statt 'reverb' // Reverb als Broadcaster angeben
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1',  // Hinzugefügt
    encrypted: false  // Hinzugefügt
});

