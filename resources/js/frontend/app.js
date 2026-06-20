import { createApp } from 'vue';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import FrontendLayout from './layouts/FrontendLayout.vue';
import router from './router';

createApp(FrontendLayout)
    .use(router)
    .mount('#frontend-app');
