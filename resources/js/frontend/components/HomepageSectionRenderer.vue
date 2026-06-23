<script setup>
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref } from 'vue';
import BlogCard from './BlogCard.vue';
import ServiceCard from './ServiceCard.vue';
import TestimonialCard from './TestimonialCard.vue';

const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
});

const moduleItems = ref([]);
const isLoading = ref(false);
const instanceId = getCurrentInstance()?.uid ?? Math.random().toString(36).slice(2);
let requestController = null;

const moduleEndpoints = {
    services: '/api/services',
    gallery: '/api/gallery',
    testimonials: '/api/testimonials',
    blog: '/api/blogs',
    faq: '/api/faqs',
};

const endpoint = computed(() => moduleEndpoints[props.section.type] || null);
const hasButton = computed(() => Boolean(props.section.button_text && props.section.button_url));
const isInternalButton = computed(() => (
    hasButton.value
    && /^\/(?!\/)/.test(props.section.button_url)
));
const sectionId = computed(() => props.section.section_key || undefined);
const isFeatureSection = computed(() => ['services', 'gallery', 'testimonials', 'blog', 'faq'].includes(props.section.type));
const moduleData = computed(() => moduleItems.value.slice(0, props.section.type === 'faq' ? 8 : 6));

const sectionStyle = computed(() => {
    const style = {};

    if (props.section.background_color) {
        style.backgroundColor = props.section.background_color;
    }

    if (props.section.text_color) {
        style.color = props.section.text_color;
        style['--homepage-section-text-color'] = props.section.text_color;
    }

    if (props.section.background_image) {
        const overlay = props.section.type === 'hero' || props.section.type === 'cta'
            ? 'linear-gradient(rgba(13, 27, 42, 0.76), rgba(13, 27, 42, 0.76)), '
            : '';
        style.backgroundImage = `${overlay}url("${props.section.background_image}")`;
    }

    return style;
});

const sectionClasses = computed(() => ({
    'homepage-hero text-white': props.section.type === 'hero',
    'homepage-cta text-center': props.section.type === 'cta',
    'has-custom-text-color': Boolean(props.section.text_color),
    'text-white': props.section.type === 'cta' && !props.section.text_color,
    'bg-white': !props.section.background_color && !props.section.background_image && !['hero', 'cta'].includes(props.section.type),
    'bg-dark text-white': !props.section.background_color && !props.section.background_image && props.section.type === 'cta',
}));

const loadModuleData = async () => {
    if (!endpoint.value) {
        return;
    }

    requestController = new AbortController();
    isLoading.value = true;

    try {
        const response = await fetch(endpoint.value, {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Section data request failed with status ${response.status}`);
        }

        const payload = await response.json();
        moduleItems.value = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            moduleItems.value = [];
        }
    } finally {
        isLoading.value = false;
    }
};

onMounted(loadModuleData);
onBeforeUnmount(() => requestController?.abort());
</script>

<template>
    <section
        :id="sectionId"
        class="homepage-builder-section section-padding"
        :class="sectionClasses"
        :style="sectionStyle"
        :data-section-type="section.type"
    >
        <div class="container position-relative">
            <template v-if="section.type === 'hero'">
                <div class="row align-items-center g-5 py-lg-5">
                    <div class="col-lg-7">
                        <p v-if="section.subtitle" class="text-uppercase fw-semibold text-info mb-2">{{ section.subtitle }}</p>
                        <h1 v-if="section.title" class="display-3 fw-bold mb-3">{{ section.title }}</h1>
                        <p v-if="section.content" class="lead section-copy mb-4">{{ section.content }}</p>
                        <RouterLink v-if="hasButton && isInternalButton" class="btn btn-primary btn-lg" :to="section.button_url">{{ section.button_text }}</RouterLink>
                        <a v-else-if="hasButton" class="btn btn-primary btn-lg" :href="section.button_url">{{ section.button_text }}</a>
                    </div>
                    <div v-if="section.image" class="col-lg-5">
                        <img class="section-feature-image rounded-4 shadow-lg" :src="section.image" :alt="section.title || 'Homepage section image'">
                    </div>
                </div>
            </template>

            <template v-else-if="section.type === 'cta'">
                <div class="homepage-cta-content mx-auto">
                    <p v-if="section.subtitle" class="text-uppercase fw-semibold opacity-75 mb-2">{{ section.subtitle }}</p>
                    <h2 v-if="section.title" class="display-5 fw-bold mb-3">{{ section.title }}</h2>
                    <p v-if="section.content" class="lead section-copy mb-4">{{ section.content }}</p>
                    <RouterLink v-if="hasButton && isInternalButton" class="btn btn-primary btn-lg" :to="section.button_url">{{ section.button_text }}</RouterLink>
                    <a v-else-if="hasButton" class="btn btn-primary btn-lg" :href="section.button_url">{{ section.button_text }}</a>
                </div>
            </template>

            <template v-else-if="!isFeatureSection">
                <div class="row align-items-center g-5" :class="{ 'flex-lg-row-reverse': section.settings?.image_position === 'right' }">
                    <div v-if="section.image" class="col-lg-6">
                        <img class="section-feature-image rounded-4 shadow-sm" :src="section.image" :alt="section.title || 'Homepage section image'" loading="lazy">
                    </div>
                    <div :class="section.image ? 'col-lg-6' : 'col-lg-9 mx-auto text-center'">
                        <p v-if="section.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ section.subtitle }}</p>
                        <h2 v-if="section.title" class="display-5 fw-bold mb-3">{{ section.title }}</h2>
                        <p v-if="section.content" class="section-copy text-body-secondary fs-5 mb-4">{{ section.content }}</p>
                        <RouterLink v-if="hasButton && isInternalButton" class="btn btn-primary" :to="section.button_url">{{ section.button_text }}</RouterLink>
                        <a v-else-if="hasButton" class="btn btn-primary" :href="section.button_url">{{ section.button_text }}</a>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="section-heading text-center mx-auto mb-5">
                    <p v-if="section.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ section.subtitle }}</p>
                    <h2 v-if="section.title" class="display-6 fw-bold mb-3">{{ section.title }}</h2>
                    <p v-if="section.content" class="section-copy text-body-secondary mb-0">{{ section.content }}</p>
                </div>

                <div v-if="isLoading" class="d-flex justify-content-center py-5" role="status" aria-label="Loading section content">
                    <div class="spinner-border text-primary"></div>
                </div>

                <div v-else-if="section.type === 'services' && moduleData.length" class="row g-4 justify-content-center">
                    <div v-for="(service, index) in moduleData" :key="service.id || service.slug || index" class="col-md-6 col-lg-4">
                        <ServiceCard
                            :title="service.title"
                            :description="service.short_description || service.description || ''"
                            :icon="service.icon || String(index + 1).padStart(2, '0')"
                            :image="service.image_url || ''"
                        />
                    </div>
                </div>

                <div v-else-if="section.type === 'gallery' && moduleData.length" class="row g-4">
                    <div v-for="(item, index) in moduleData" :key="item.id || index" class="col-sm-6 col-lg-4">
                        <article class="card h-100 overflow-hidden border-0 shadow-sm">
                            <img class="gallery-image card-img-top" :src="item.image_url" :alt="item.alt_text || item.title" loading="lazy">
                            <div class="card-body">
                                <small v-if="item.category" class="text-primary text-uppercase fw-semibold">{{ item.category }}</small>
                                <h3 class="h5 mt-1 mb-0">{{ item.title }}</h3>
                            </div>
                        </article>
                    </div>
                </div>

                <div v-else-if="section.type === 'testimonials' && moduleData.length" class="row g-4 justify-content-center">
                    <div v-for="(testimonial, index) in moduleData" :key="testimonial.id || index" class="col-lg-6">
                        <TestimonialCard
                            :client-name="testimonial.client_name"
                            :company="testimonial.company || ''"
                            :designation="testimonial.designation || ''"
                            :review="testimonial.review"
                            :rating="Number(testimonial.rating) || 5"
                            :image="testimonial.image_url || ''"
                            :featured="Boolean(testimonial.featured)"
                        />
                    </div>
                </div>

                <div v-else-if="section.type === 'blog' && moduleData.length" class="row g-4 justify-content-center">
                    <div v-for="blog in moduleData.slice(0, 3)" :key="blog.slug" class="col-md-6 col-lg-4">
                        <BlogCard :blog="blog" />
                    </div>
                </div>

                <div v-else-if="section.type === 'faq' && moduleData.length" :id="`homepage-builder-faq-${instanceId}`" class="accordion accordion-flush faq-list mx-auto rounded-4 overflow-hidden shadow-sm">
                    <div v-for="(faq, index) in moduleData" :key="faq.id || index" class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button fw-semibold" :class="{ collapsed: index !== 0 }" type="button" data-bs-toggle="collapse" :data-bs-target="`#homepage-builder-faq-${instanceId}-${index}`" :aria-expanded="index === 0">
                                {{ faq.question }}
                            </button>
                        </h3>
                        <div :id="`homepage-builder-faq-${instanceId}-${index}`" class="accordion-collapse collapse" :class="{ show: index === 0 }" :data-bs-parent="`#homepage-builder-faq-${instanceId}`">
                            <div class="accordion-body section-copy text-body-secondary">{{ faq.answer }}</div>
                        </div>
                    </div>
                </div>

                <p v-else class="text-center text-body-secondary mb-0">No active {{ section.type }} content is available yet.</p>

                <div v-if="hasButton" class="text-center mt-5">
                    <RouterLink v-if="isInternalButton" class="btn btn-primary" :to="section.button_url">{{ section.button_text }}</RouterLink>
                    <a v-else class="btn btn-primary" :href="section.button_url">{{ section.button_text }}</a>
                </div>
            </template>
        </div>
    </section>
</template>

<style scoped>
.homepage-builder-section {
    background-position: center;
    background-size: cover;
}

.has-custom-text-color .text-primary,
.has-custom-text-color .text-info,
.has-custom-text-color .text-body-secondary {
    color: var(--homepage-section-text-color) !important;
}

.homepage-hero {
    display: flex;
    min-height: 34rem;
    align-items: center;
    background-color: #0d1b2a;
    background-image: linear-gradient(120deg, #0d1b2a, var(--site-primary-color));
}

.homepage-cta-content,
.section-heading {
    max-width: 48rem;
}

.section-copy {
    white-space: pre-line;
}

.section-feature-image {
    display: block;
    width: 100%;
    max-height: 34rem;
    object-fit: cover;
}

.gallery-image {
    height: 16rem;
    object-fit: cover;
}

.faq-list {
    max-width: 54rem;
}

@media (max-width: 767.98px) {
    .homepage-hero {
        min-height: 30rem;
    }
}
</style>
