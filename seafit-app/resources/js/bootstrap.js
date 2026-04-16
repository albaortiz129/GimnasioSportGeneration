// Configuración base de Axios para peticiones AJAX en Laravel.
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
