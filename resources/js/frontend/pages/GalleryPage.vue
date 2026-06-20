<script setup>
import GalleryItem from '../components/GalleryItem.vue';

const items = [
    { title: 'Retail workspace', category: 'Retail', image: 'https://picsum.photos/seed/billsoft-retail/800/600' },
    { title: 'Team dashboard', category: 'Operations', image: 'https://picsum.photos/seed/billsoft-dashboard/800/600' },
    { title: 'Modern storefront', category: 'Commerce', image: 'https://picsum.photos/seed/billsoft-store/800/600' },
    { title: 'Customer experience', category: 'Service', image: 'https://picsum.photos/seed/billsoft-customer/800/600' },
    { title: 'Growing inventory', category: 'Inventory', image: 'https://picsum.photos/seed/billsoft-inventory/800/600' },
    { title: 'Business insights', category: 'Reporting', image: 'https://picsum.photos/seed/billsoft-reporting/800/600' },
];
</script>

<template>
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <p class="text-primary text-uppercase fw-semibold">Gallery</p>
                <h1 class="display-5 fw-bold">A glimpse at the work</h1>
            </div>
            <div class="row g-4">
                <div v-for="item in items" :key="item.title" class="col-md-6 col-lg-4">
                    <GalleryItem v-bind="item" />
                </div>
            </div>
        </div>
    </section>
</template>
