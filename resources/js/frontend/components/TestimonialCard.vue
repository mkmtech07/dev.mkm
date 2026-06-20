<script setup>
import { computed } from 'vue';

const props = defineProps({
    clientName: { type: String, default: '' },
    company: { type: String, default: '' },
    designation: { type: String, default: '' },
    review: { type: String, default: '' },
    rating: {
        type: Number,
        default: 5,
        validator: (value) => value >= 1 && value <= 5,
    },
    image: { type: String, default: '' },
    featured: { type: Boolean, default: false },
    quote: { type: String, default: '' },
    name: { type: String, default: '' },
    role: { type: String, default: '' },
});

const displayName = computed(() => props.clientName || props.name);
const displayReview = computed(() => props.review || props.quote);
const displayMeta = computed(() => [
    props.designation || props.role,
    props.company,
].filter(Boolean).join(', '));
const initial = computed(() => displayName.value.trim().charAt(0).toUpperCase() || 'C');
</script>

<template>
    <figure class="card h-100 border-0 shadow-sm mb-0">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="d-flex justify-content-center mb-4">
                <img
                    v-if="image"
                    :src="image"
                    class="client-image rounded-circle object-fit-cover"
                    :alt="displayName"
                >
                <div v-else class="client-image client-placeholder rounded-circle" aria-hidden="true">
                    {{ initial }}
                </div>
            </div>

            <div class="text-warning fs-5 mb-3" :aria-label="`${rating} out of 5 stars`">
                <span
                    v-for="star in 5"
                    :key="star"
                    :class="{ 'text-body-tertiary': star > rating }"
                    aria-hidden="true"
                >&#9733;</span>
            </div>

            <blockquote class="blockquote fs-5 mb-4">
                <span aria-hidden="true">&ldquo;</span>{{ displayReview }}<span aria-hidden="true">&rdquo;</span>
            </blockquote>

            <figcaption>
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <span class="fw-bold">{{ displayName }}</span>
                    <span v-if="featured" class="badge text-bg-warning">Featured</span>
                </div>
                <div v-if="displayMeta" class="text-secondary small mt-1">{{ displayMeta }}</div>
            </figcaption>
        </div>
    </figure>
</template>

<style scoped>
.client-image {
    display: grid;
    width: 5rem;
    height: 5rem;
    place-items: center;
}

.client-placeholder {
    background: #e7f1ff;
    color: #0d6efd;
    font-size: 1.75rem;
    font-weight: 700;
}
</style>
