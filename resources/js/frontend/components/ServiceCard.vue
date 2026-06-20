<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    icon: { type: String, default: '01' },
    image: { type: String, default: '' },
});
</script>

<template>
    <article class="card h-100 border-0 shadow-sm">
        <img v-if="image" :src="image" class="card-img-top service-image" :alt="title">
        <div class="card-body p-4">
            <div class="service-icon mb-3" aria-hidden="true">{{ icon }}</div>
            <h2 class="h5 card-title">{{ title }}</h2>
            <p class="card-text text-secondary mb-0">{{ description }}</p>
        </div>
    </article>
</template>

<style scoped>
.service-image {
    height: 13rem;
    object-fit: cover;
}

.service-icon {
    display: grid;
    width: 3rem;
    height: 3rem;
    place-items: center;
    border-radius: 0.75rem;
    background: #e7f1ff;
    color: #0d6efd;
    font-size: 1.25rem;
    font-weight: 700;
}
</style>
