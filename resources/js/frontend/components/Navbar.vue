<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <RouterLink class="navbar-brand fw-bold" to="/">Billsoft</RouterLink>

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
                    <li v-for="link in links" :key="link.to" class="nav-item">
                        <RouterLink class="nav-link" :to="link.to">{{ link.label }}</RouterLink>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
const links = [
    { label: 'Home', to: '/' },
    { label: 'About', to: '/about' },
    { label: 'Services', to: '/services' },
    { label: 'Gallery', to: '/gallery' },
    { label: 'Blog', to: '/blog' },
    { label: 'Contact', to: '/contact' },
];
</script>

<style scoped>
.router-link-exact-active {
    color: #fff;
    font-weight: 600;
}
</style>
