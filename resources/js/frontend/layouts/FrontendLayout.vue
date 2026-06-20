<script setup>
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
import WhatsAppButton from '../components/WhatsAppButton.vue';
</script>

<template>
    <div class="frontend-layout d-flex flex-column min-vh-100">
        <Navbar />

        <main class="flex-grow-1">
            <RouterView />
        </main>

        <Footer />
        <WhatsAppButton />
    </div>
</template>

<style>
:root {
    color-scheme: light;
}

body {
    margin: 0;
    background: #f8f9fa;
    color: #212529;
}

.section-padding {
    padding: 5rem 0;
}
</style>
