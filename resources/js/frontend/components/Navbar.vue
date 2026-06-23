<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import MenuNavItem from './MenuNavItem.vue';
import { siteSettings } from '../siteSettings';

const fallbackItems = [
    { id: 'fallback-home', title: 'Home', url: '/', target: '_self', children: [] },
    { id: 'fallback-about', title: 'About', url: '/about', target: '_self', children: [] },
    { id: 'fallback-services', title: 'Services', url: '/services', target: '_self', children: [] },
    { id: 'fallback-gallery', title: 'Gallery', url: '/gallery', target: '_self', children: [] },
    { id: 'fallback-blog', title: 'Blog', url: '/blog', target: '_self', children: [] },
    { id: 'fallback-contact', title: 'Contact', url: '/contact', target: '_self', children: [] },
];

const items = ref(fallbackItems);
const requestController = new AbortController();

onMounted(async () => {
    try {
        const response = await fetch('/api/menus/header', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (! response.ok) {
            return;
        }

        const payload = await response.json();
        const menuItems = payload.data?.items;

        if (Array.isArray(menuItems) && menuItems.length) {
            items.value = menuItems;
        }
    } catch (error) {
        if (error.name !== 'AbortError') {
            items.value = fallbackItems;
        }
    }
});

onBeforeUnmount(() => requestController.abort());
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <RouterLink class="navbar-brand d-flex align-items-center gap-2 fw-bold" to="/">
                <img
                    v-if="siteSettings.logoUrl"
                    class="site-logo"
                    :src="siteSettings.logoUrl"
                    :alt="`${siteSettings.siteName} logo`"
                >
                <span v-else>{{ siteSettings.siteName }}</span>
            </RouterLink>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#frontendNavbar"
                aria-controls="frontendNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="frontendNavbar" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <MenuNavItem
                        v-for="item in items"
                        :key="item.id || `${item.title}-${item.url}`"
                        :item="item"
                        root
                    />
                </ul>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.site-logo {
    height: 42px;
    max-width: 120px;
    object-fit: contain;
}

:deep(.nav-link.router-link-exact-active) {
    color: var(--bs-primary);
    font-weight: 600;
}
</style>
