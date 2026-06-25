<script setup>
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
import WhatsAppButton from '../components/WhatsAppButton.vue';
import MaintenancePage from '../pages/MaintenancePage.vue';
import { maintenanceStatus } from '../maintenance';
</script>

<template>
    <div class="frontend-layout d-flex flex-column min-vh-100">
        <MaintenancePage v-if="maintenanceStatus.enabled" :status="maintenanceStatus" />

        <template v-else>
            <Navbar />

            <main class="flex-grow-1">
                <RouterView />
            </main>

            <Footer />
            <WhatsAppButton />
        </template>
    </div>
</template>

<style>
:root {
    color-scheme: light;
    --site-primary-color: #0d6efd;
    --site-secondary-color: #6c757d;
}

body {
    margin: 0;
    background: #f8f9fa;
    color: #212529;
}

.section-padding {
    padding: 5rem 0;
}

.text-primary { color: var(--site-primary-color) !important; }
.text-secondary { color: var(--site-secondary-color) !important; }
.bg-primary { background-color: var(--site-primary-color) !important; }
.bg-secondary { background-color: var(--site-secondary-color) !important; }
.border-primary { border-color: var(--site-primary-color) !important; }
.border-secondary { border-color: var(--site-secondary-color) !important; }
.btn-primary {
    --bs-btn-bg: var(--site-primary-color);
    --bs-btn-border-color: var(--site-primary-color);
    --bs-btn-hover-bg: var(--site-primary-color);
    --bs-btn-hover-border-color: var(--site-primary-color);
    --bs-btn-active-bg: var(--site-primary-color);
    --bs-btn-active-border-color: var(--site-primary-color);
}
.btn-outline-primary {
    --bs-btn-color: var(--site-primary-color);
    --bs-btn-border-color: var(--site-primary-color);
    --bs-btn-hover-bg: var(--site-primary-color);
    --bs-btn-hover-border-color: var(--site-primary-color);
    --bs-btn-active-bg: var(--site-primary-color);
    --bs-btn-active-border-color: var(--site-primary-color);
}
.text-bg-primary,
.page-item.active .page-link {
    color: #fff !important;
    background-color: var(--site-primary-color) !important;
    border-color: var(--site-primary-color) !important;
}
.page-link {
    color: var(--site-primary-color);
}
</style>
