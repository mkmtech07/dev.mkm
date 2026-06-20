<template>
    <footer class="bg-dark py-4 text-white">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 text-center text-md-start">
            <span>&copy; {{ currentYear }} Billsoft. All rights reserved.</span>
            <span class="text-white-50">Built to make business simpler.</span>
        </div>
    </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>
