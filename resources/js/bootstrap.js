import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const pusherKey = window.PUSHER_CONFIG?.key || import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = window.PUSHER_CONFIG?.cluster || import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1';

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
    });
}
