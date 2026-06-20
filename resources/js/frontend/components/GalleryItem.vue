<script setup>
defineProps({
    title: { type: String, required: true },
    image: { type: String, required: true },
    category: { type: String, default: 'Project' },
});
</script>

<template>
    <figure class="card h-100 overflow-hidden border-0 shadow-sm">
        <img :src="image" class="card-img-top gallery-image" :alt="title">
        <figcaption class="card-body">
            <small class="text-primary text-uppercase fw-semibold">{{ category }}</small>
            <h2 class="h5 mt-1 mb-0">{{ title }}</h2>
        </figcaption>
    </figure>
</template>

<style scoped>
.gallery-image {
    height: 15rem;
    object-fit: cover;
}
</style>
