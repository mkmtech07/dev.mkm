<script setup>
import ServiceCard from '../components/ServiceCard.vue';

const services = [
    { icon: '01', title: 'Billing Solutions', description: 'Fast, accurate invoicing for everyday business operations.' },
    { icon: '02', title: 'Inventory Management', description: 'Keep products, stock levels, and movements organized.' },
    { icon: '03', title: 'Business Reports', description: 'Turn day-to-day activity into clear, useful insights.' },
    { icon: '04', title: 'Custom Development', description: 'Purpose-built workflows shaped around your organization.' },
    { icon: '05', title: 'Data Migration', description: 'Move important business records carefully and reliably.' },
    { icon: '06', title: 'Training & Support', description: 'Help your team get comfortable and productive quickly.' },
];
</script>

<template>
    <section class="section-padding">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 42rem">
                <p class="text-primary text-uppercase fw-semibold">Services</p>
                <h1 class="display-5 fw-bold">Solutions that fit your business</h1>
                <p class="lead text-secondary">Choose what you need today and add more as your business grows.</p>
            </div>
            <div class="row g-4">
                <div v-for="service in services" :key="service.title" class="col-md-6 col-lg-4">
                    <ServiceCard v-bind="service" />
                </div>
            </div>
        </div>
    </section>
</template>
