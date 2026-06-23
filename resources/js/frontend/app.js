import { createApp } from 'vue';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap';
import FrontendLayout from './layouts/FrontendLayout.vue';
import router from './router';
import { loadSchemaMarkup, loadTrackingIntegrations, loadWebsiteSettings } from './siteSettings';

const start = async () => {
    await loadWebsiteSettings();
    loadTrackingIntegrations();
    loadSchemaMarkup();

    createApp(FrontendLayout)
        .use(router)
        .mount('#frontend-app');
};

start();
