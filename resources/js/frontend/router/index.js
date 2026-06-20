import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import AboutPage from '../pages/AboutPage.vue';
import ServicesPage from '../pages/ServicesPage.vue';
import GalleryPage from '../pages/GalleryPage.vue';
import BlogPage from '../pages/BlogPage.vue';
import BlogDetails from '../pages/BlogDetails.vue';
import ContactPage from '../pages/ContactPage.vue';
import DynamicPage from '../pages/DynamicPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'home', component: HomePage },
        { path: '/about', name: 'about', component: AboutPage },
        { path: '/services', name: 'services', component: ServicesPage },
        { path: '/gallery', name: 'gallery', component: GalleryPage },
        { path: '/blog', name: 'blog', component: BlogPage },
        { path: '/blog/:slug', name: 'blog-details', component: BlogDetails },
        { path: '/contact', name: 'contact', component: ContactPage },
        { path: '/:slug', name: 'dynamic-page', component: DynamicPage },
    ],
    scrollBehavior() {
        return { top: 0 };
    },
});

router.afterEach((to) => {
    if (['dynamic-page', 'blog-details'].includes(to.name)) {
        return;
    }

    const pageName = to.name
        ? `${String(to.name).charAt(0).toUpperCase()}${String(to.name).slice(1)}`
        : 'Home';

    document.title = `${pageName} | ${import.meta.env.VITE_APP_NAME || 'Billsoft'}`;
});

export default router;
