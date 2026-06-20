<script setup>
import DOMPurify from 'dompurify';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import BlogCard from '../components/BlogCard.vue';
import BaseLoader from '../components/base/BaseLoader.vue';

const route = useRoute();
const blog = ref(null);
const relatedBlogs = ref([]);
const loading = ref(true);
const notFound = ref(false);
const errorMessage = ref('');
const appName = import.meta.env.VITE_APP_NAME || 'Billsoft';
let requestController;

const originalDescription = document.querySelector('meta[name="description"]');
const originalDescriptionContent = originalDescription?.getAttribute('content') ?? null;
const dynamicElements = [];

const safeContent = computed(() => DOMPurify.sanitize(blog.value?.content || '', {
    USE_PROFILES: { html: true },
}));

const publishedDate = computed(() => {
    if (! blog.value?.published_at) return '';

    return new Intl.DateTimeFormat('en-IN', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(blog.value.published_at));
});

const ensureMeta = (selector, attributes) => {
    let element = document.querySelector(selector);

    if (! element) {
        element = document.createElement('meta');
        Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
        document.head.appendChild(element);
        dynamicElements.push(element);
    }

    return element;
};

const updateMetadata = (post) => {
    const title = post.meta_title || post.title;
    const description = post.meta_description || post.excerpt || '';
    document.title = `${title} | ${appName}`;

    ensureMeta('meta[name="description"]', { name: 'description' }).setAttribute('content', description);
    ensureMeta('meta[property="og:title"]', { property: 'og:title' }).setAttribute('content', title);
    ensureMeta('meta[property="og:description"]', { property: 'og:description' }).setAttribute('content', description);

    ensureMeta('meta[property="og:image"]', { property: 'og:image' })
        .setAttribute('content', post.og_image || post.featured_image || '');

    let canonical = document.querySelector('link[rel="canonical"]');
    if (! canonical) {
        canonical = document.createElement('link');
        canonical.setAttribute('rel', 'canonical');
        document.head.appendChild(canonical);
        dynamicElements.push(canonical);
    }
    canonical.setAttribute('href', post.canonical_url || window.location.href);
};

const loadBlog = async (slug) => {
    requestController?.abort();
    const controller = new AbortController();
    requestController = controller;
    loading.value = true;
    blog.value = null;
    relatedBlogs.value = [];
    notFound.value = false;
    errorMessage.value = '';

    try {
        const response = await fetch(`/api/blogs/${encodeURIComponent(slug)}`, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        });

        if (response.status === 404) {
            notFound.value = true;
            document.title = `Blog Post Not Found | ${appName}`;
            return;
        }

        if (! response.ok) {
            throw new Error('Unable to load this blog post right now.');
        }

        const payload = await response.json();
        blog.value = payload.data;
        relatedBlogs.value = Array.isArray(payload.related) ? payload.related : [];
        updateMetadata(blog.value);
    } catch (error) {
        if (error.name !== 'AbortError') {
            errorMessage.value = error.message || 'Unable to load this blog post right now.';
        }
    } finally {
        if (requestController === controller && ! controller.signal.aborted) {
            loading.value = false;
        }
    }
};

watch(() => route.params.slug, (slug) => loadBlog(String(slug)), { immediate: true });

onBeforeUnmount(() => {
    requestController?.abort();
    dynamicElements.forEach((element) => element.remove());

    if (originalDescription) {
        originalDescription.setAttribute('content', originalDescriptionContent || '');
    }
});
</script>

<template>
    <section class="section-padding bg-white">
        <div class="container">
            <BaseLoader v-if="loading" />

            <div v-else-if="notFound" class="text-center py-5">
                <p class="display-1 fw-bold text-primary mb-2">404</p>
                <h1 class="h2 mb-3">Blog post not found</h1>
                <p class="text-secondary mb-4">This article may have moved or is no longer published.</p>
                <RouterLink class="btn btn-primary" to="/blog">Back to blog</RouterLink>
            </div>

            <div v-else-if="errorMessage" class="alert alert-danger text-center" role="alert">{{ errorMessage }}</div>

            <template v-else-if="blog">
                <article>
                    <header class="article-width mx-auto text-center mb-5">
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 text-secondary mb-3">
                            <span v-if="blog.category" class="badge rounded-pill text-bg-light">{{ blog.category.name }}</span>
                            <span v-if="publishedDate">{{ publishedDate }}</span>
                            <span v-if="blog.author">By {{ blog.author }}</span>
                            <span>{{ blog.views }} views</span>
                        </div>
                        <h1 class="display-4 fw-bold mb-4">{{ blog.title }}</h1>
                        <p v-if="blog.excerpt" class="lead text-secondary mb-0">{{ blog.excerpt }}</p>
                    </header>

                    <img
                        v-if="blog.featured_image"
                        class="article-image img-fluid rounded-4 shadow-sm mb-5"
                        :src="blog.featured_image"
                        :alt="blog.title"
                    >

                    <div class="article-content article-width mx-auto" v-html="safeContent"></div>
                </article>

                <section v-if="relatedBlogs.length" class="border-top mt-5 pt-5" aria-labelledby="related-posts-title">
                    <div class="text-center mb-4">
                        <p class="text-primary text-uppercase fw-semibold mb-1">Keep reading</p>
                        <h2 id="related-posts-title" class="display-6 fw-bold">Related posts</h2>
                    </div>
                    <div class="row g-4">
                        <div v-for="related in relatedBlogs" :key="related.id" class="col-md-6 col-xl-4">
                            <BlogCard :blog="related" />
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </section>
</template>

<style scoped>
.article-width {
    max-width: 860px;
}

.article-image {
    display: block;
    width: 100%;
    max-height: 600px;
    object-fit: cover;
}

.article-content {
    color: #495057;
    font-size: 1.08rem;
    line-height: 1.85;
}

.article-content :deep(h2),
.article-content :deep(h3),
.article-content :deep(h4) {
    color: #212529;
    font-weight: 700;
    margin-bottom: 1rem;
    margin-top: 2.25rem;
}

.article-content :deep(img) {
    height: auto;
    max-width: 100%;
    border-radius: 0.75rem;
}

.article-content :deep(a) {
    color: var(--bs-primary);
}
</style>
