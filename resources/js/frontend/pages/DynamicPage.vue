<script setup>
import DOMPurify from 'dompurify';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import BaseLoader from '../components/base/BaseLoader.vue';

const route = useRoute();
const page = ref(null);
const loading = ref(true);
const notFound = ref(false);
const errorMessage = ref('');
const appName = import.meta.env.VITE_APP_NAME || 'Billsoft';

const originalDescription = document.querySelector('meta[name="description"]');
const originalDescriptionContent = originalDescription?.getAttribute('content') ?? null;
let requestController;
let dynamicDescription;

const safeContent = computed(() => DOMPurify.sanitize(page.value?.content || '', {
    USE_PROFILES: { html: true },
}));

const updateMetadata = (title, description = '') => {
    document.title = `${title} | ${appName}`;

    let descriptionTag = document.querySelector('meta[name="description"]');

    if (! descriptionTag) {
        descriptionTag = document.createElement('meta');
        descriptionTag.setAttribute('name', 'description');
        descriptionTag.dataset.dynamicPageMeta = 'true';
        document.head.appendChild(descriptionTag);
        dynamicDescription = descriptionTag;
    }

    descriptionTag.setAttribute('content', description || '');
};

const loadPage = async (slug) => {
    requestController?.abort();
    const controller = new AbortController();
    requestController = controller;

    loading.value = true;
    page.value = null;
    notFound.value = false;
    errorMessage.value = '';

    try {
        const response = await fetch(`/api/pages/${encodeURIComponent(slug)}`, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        });

        if (response.status === 404) {
            notFound.value = true;
            updateMetadata('Page Not Found');
            return;
        }

        if (! response.ok) {
            throw new Error('Unable to load this page right now.');
        }

        const payload = await response.json();
        page.value = payload.data;
        updateMetadata(
            page.value.meta_title || page.value.title,
            page.value.meta_description || '',
        );
    } catch (error) {
        if (error.name !== 'AbortError') {
            errorMessage.value = error.message || 'Unable to load this page right now.';
            updateMetadata('Page Error');
        }
    } finally {
        if (requestController === controller && ! controller.signal.aborted) {
            loading.value = false;
        }
    }
};

watch(
    () => route.params.slug,
    (slug) => loadPage(String(slug)),
    { immediate: true },
);

onBeforeUnmount(() => {
    requestController?.abort();

    if (originalDescription) {
        originalDescription.setAttribute('content', originalDescriptionContent || '');
    } else {
        dynamicDescription?.remove();
    }
});
</script>

<template>
    <section class="dynamic-page section-padding bg-white">
        <div class="container">
            <BaseLoader v-if="loading" />

            <div v-else-if="notFound" class="text-center py-5">
                <p class="display-1 fw-bold text-primary mb-2">404</p>
                <h1 class="h2 mb-3">Page not found</h1>
                <p class="text-secondary mb-4">The page may have moved, been unpublished, or never existed.</p>
                <RouterLink class="btn btn-primary" to="/">Back to home</RouterLink>
            </div>

            <div v-else-if="errorMessage" class="alert alert-danger text-center" role="alert">
                {{ errorMessage }}
            </div>

            <article v-else-if="page">
                <header class="mx-auto mb-5 text-center page-header">
                    <h1 class="display-5 fw-bold mb-3">{{ page.title }}</h1>
                    <p v-if="page.meta_description" class="lead text-secondary mb-0">
                        {{ page.meta_description }}
                    </p>
                </header>

                <img
                    v-if="page.featured_image"
                    class="featured-image img-fluid rounded-4 shadow-sm mb-5"
                    :src="page.featured_image"
                    :alt="page.title"
                >

                <div class="page-content mx-auto" v-html="safeContent"></div>
            </article>
        </div>
    </section>
</template>

<style scoped>
.page-header,
.page-content {
    max-width: 860px;
}

.featured-image {
    display: block;
    width: 100%;
    max-height: 520px;
    object-fit: cover;
}

.page-content {
    color: #495057;
    font-size: 1.05rem;
    line-height: 1.8;
}

.page-content :deep(h2),
.page-content :deep(h3),
.page-content :deep(h4) {
    color: #212529;
    font-weight: 700;
    margin-bottom: 1rem;
    margin-top: 2rem;
}

.page-content :deep(img) {
    height: auto;
    max-width: 100%;
    border-radius: 0.75rem;
}

.page-content :deep(a) {
    color: var(--bs-primary);
}
</style>
