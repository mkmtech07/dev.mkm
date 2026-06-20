<script setup>
import { computed } from 'vue';
import BaseBadge from './base/BaseBadge.vue';

const props = defineProps({
    blog: {
        type: Object,
        required: true,
    },
});

const publishedDate = computed(() => {
    if (! props.blog.published_at) {
        return '';
    }

    return new Intl.DateTimeFormat('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(props.blog.published_at));
});
</script>

<template>
    <article class="card blog-card h-100 border-0 shadow-sm overflow-hidden">
        <RouterLink class="blog-image-wrap bg-light" :to="`/blog/${blog.slug}`" :aria-label="`Read ${blog.title}`">
            <img
                v-if="blog.featured_image"
                class="blog-image"
                :src="blog.featured_image"
                :alt="blog.title"
                loading="lazy"
            >
            <div v-else class="blog-image-placeholder d-grid text-primary" aria-hidden="true">
                <span class="display-5 fw-bold">B</span>
            </div>
        </RouterLink>

        <div class="card-body d-flex flex-column p-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <BaseBadge v-if="blog.category" :text="blog.category.name" variant="light" />
                <BaseBadge v-if="blog.is_featured" text="Featured" variant="warning" />
                <small v-if="publishedDate" class="text-secondary ms-auto">{{ publishedDate }}</small>
            </div>

            <h3 class="h4 mb-3">
                <RouterLink class="blog-title-link" :to="`/blog/${blog.slug}`">{{ blog.title }}</RouterLink>
            </h3>
            <p v-if="blog.excerpt" class="text-secondary flex-grow-1">{{ blog.excerpt }}</p>

            <div class="d-flex justify-content-between align-items-center gap-3 mt-2 small text-secondary">
                <span>{{ blog.author || 'Editorial team' }}</span>
                <RouterLink class="fw-semibold text-primary text-decoration-none" :to="`/blog/${blog.slug}`">
                    Read article <span aria-hidden="true">&rarr;</span>
                </RouterLink>
            </div>
        </div>
    </article>
</template>

<style scoped>
.blog-card {
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.blog-card:hover {
    transform: translateY(-0.3rem);
    box-shadow: 0 1rem 2.5rem rgba(33, 37, 41, 0.12) !important;
}

.blog-image-wrap {
    display: block;
    aspect-ratio: 16 / 9;
    overflow: hidden;
}

.blog-image,
.blog-image-placeholder {
    width: 100%;
    height: 100%;
}

.blog-image {
    object-fit: cover;
    transition: transform 300ms ease;
}

.blog-card:hover .blog-image {
    transform: scale(1.035);
}

.blog-image-placeholder {
    place-items: center;
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.08), rgba(var(--bs-primary-rgb), 0.18));
}

.blog-title-link {
    color: inherit;
    text-decoration: none;
}

.blog-title-link:hover {
    color: var(--bs-primary);
}

@media (prefers-reduced-motion: reduce) {
    .blog-card,
    .blog-image {
        transition: none;
    }
}
</style>
