<script setup>
import DOMPurify from 'dompurify';
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import BlogCard from './BlogCard.vue';
import ServiceCard from './ServiceCard.vue';
import TestimonialCard from './TestimonialCard.vue';
import { siteSettings } from '../siteSettings';

const props = defineProps({
    block: {
        type: Object,
        required: true,
    },
});

const moduleItems = ref([]);
const moduleLoading = ref(false);
const contactFormElement = ref(null);
const contactErrors = ref({});
const contactSuccess = ref('');
const contactError = ref('');
const contactSubmitting = ref(false);
const contactValidated = ref(false);
const newsletterEmail = ref('');
const newsletterSuccess = ref('');
const newsletterError = ref('');
const newsletterValidation = ref({});
const newsletterSubmitting = ref(false);
const instanceId = getCurrentInstance()?.uid ?? Math.random().toString(36).slice(2);
let requestController = null;
let contactController = null;
let newsletterController = null;

const contactForm = reactive({
    name: '',
    phone: '',
    email: '',
    subject: '',
    message: '',
});

const moduleEndpoints = {
    services: '/api/services',
    gallery: '/api/gallery',
    testimonials: '/api/testimonials',
    faq: '/api/faqs',
    blog: '/api/blogs',
};

const endpoint = computed(() => moduleEndpoints[props.block.type] || null);
const blockId = computed(() => props.block.block_key || undefined);
const isInternalUrl = (url) => typeof url === 'string' && /^\/(?!\/)/.test(url);
const hasPrimaryButton = computed(() => Boolean(props.block.button_text && props.block.button_url));
const hasSecondaryButton = computed(() => Boolean(props.block.secondary_button_text && props.block.secondary_button_url));
const featureItems = computed(() => Array.isArray(props.block.settings?.items) ? props.block.settings.items : []);
const pricingPlans = computed(() => Array.isArray(props.block.settings?.plans) ? props.block.settings.plans : []);
const moduleData = computed(() => moduleItems.value.slice(0, props.block.type === 'faq' ? 8 : 6));
const safeHtml = computed(() => DOMPurify.sanitize(props.block.content || '', {
    USE_PROFILES: { html: true },
}));
const sectionStyle = computed(() => {
    const style = {};

    if (props.block.background_color) {
        style.backgroundColor = props.block.background_color;
    }

    if (props.block.text_color) {
        style.color = props.block.text_color;
        style['--page-block-text-color'] = props.block.text_color;
    }

    if (props.block.background_image) {
        const overlay = ['hero', 'cta'].includes(props.block.type)
            ? 'linear-gradient(rgba(13, 27, 42, 0.76), rgba(13, 27, 42, 0.76)), '
            : '';
        style.backgroundImage = `${overlay}url("${props.block.background_image}")`;
    }

    return style;
});
const sectionClasses = computed(() => ({
    'page-block-hero text-white': props.block.type === 'hero',
    'page-block-cta text-center': props.block.type === 'cta',
    'has-custom-text-color': Boolean(props.block.text_color),
    'text-white': props.block.type === 'cta' && !props.block.text_color,
    'bg-light': !props.block.background_color && !props.block.background_image && ['features', 'pricing', 'contact_form', 'newsletter'].includes(props.block.type),
    'bg-white': !props.block.background_color && !props.block.background_image && !['hero', 'cta', 'features', 'pricing', 'contact_form', 'newsletter'].includes(props.block.type),
    'bg-dark text-white': !props.block.background_color && !props.block.background_image && props.block.type === 'cta',
}));
const hasContactDetails = computed(() => Boolean(siteSettings.phone || siteSettings.email || siteSettings.address));

const fieldError = (field) => contactErrors.value[field]?.[0] ?? '';
const resetContactForm = () => {
    contactForm.name = '';
    contactForm.phone = '';
    contactForm.email = '';
    contactForm.subject = '';
    contactForm.message = '';
};

const loadModuleData = async () => {
    if (!endpoint.value) {
        return;
    }

    requestController = new AbortController();
    moduleLoading.value = true;

    try {
        const response = await fetch(endpoint.value, {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Block data request failed with status ${response.status}`);
        }

        const payload = await response.json();
        moduleItems.value = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            moduleItems.value = [];
        }
    } finally {
        moduleLoading.value = false;
    }
};

const submitContact = async () => {
    contactValidated.value = true;
    contactErrors.value = {};
    contactSuccess.value = '';
    contactError.value = '';

    if (!contactFormElement.value?.checkValidity()) {
        return;
    }

    contactController?.abort();
    contactController = new AbortController();
    contactSubmitting.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/contact-messages', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(contactForm),
            signal: contactController.signal,
        });
        const payload = await response.json().catch(() => ({}));

        if (response.status === 422) {
            contactErrors.value = payload.errors ?? {};
            throw new Error('Please check the highlighted fields and try again.');
        }

        if (!response.ok) {
            throw new Error(payload.message || 'Unable to send your message right now.');
        }

        resetContactForm();
        contactValidated.value = false;
        contactSuccess.value = payload.message || 'Your message has been sent.';
    } catch (error) {
        if (error.name !== 'AbortError') {
            contactError.value = error.message || 'Unable to send your message right now.';
        }
    } finally {
        contactSubmitting.value = false;
    }
};

const subscribeNewsletter = async () => {
    newsletterSuccess.value = '';
    newsletterError.value = '';
    newsletterValidation.value = {};

    newsletterController?.abort();
    newsletterController = new AbortController();
    newsletterSubmitting.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/api/newsletter/subscribe', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ email: newsletterEmail.value, source: 'page_block', website: '' }),
            signal: newsletterController.signal,
        });
        const payload = await response.json().catch(() => ({}));

        if (response.status === 422) {
            newsletterValidation.value = payload.errors || {};
            throw new Error(newsletterValidation.value.email?.[0] || 'Please enter a valid email address.');
        }

        if (!response.ok) {
            throw new Error(payload.message || 'Subscription is temporarily unavailable.');
        }

        newsletterSuccess.value = payload.message || 'Thank you for subscribing.';
        newsletterEmail.value = '';
    } catch (error) {
        if (error.name !== 'AbortError') {
            newsletterError.value = error.message || 'Subscription is temporarily unavailable.';
        }
    } finally {
        newsletterSubmitting.value = false;
    }
};

onMounted(loadModuleData);
onBeforeUnmount(() => {
    requestController?.abort();
    contactController?.abort();
    newsletterController?.abort();
});
</script>

<template>
    <section
        :id="blockId"
        class="page-builder-block section-padding"
        :class="sectionClasses"
        :style="sectionStyle"
        :data-block-type="block.type"
    >
        <div class="container position-relative">
            <template v-if="block.type === 'hero'">
                <div class="row align-items-center g-5 py-lg-5">
                    <div class="col-lg-7">
                        <p v-if="block.subtitle" class="text-uppercase fw-semibold text-info mb-2">{{ block.subtitle }}</p>
                        <h1 v-if="block.title" class="display-3 fw-bold mb-3">{{ block.title }}</h1>
                        <p v-if="block.content" class="lead block-copy mb-4">{{ block.content }}</p>
                        <div v-if="hasPrimaryButton || hasSecondaryButton" class="d-flex flex-wrap gap-2">
                            <RouterLink v-if="hasPrimaryButton && isInternalUrl(block.button_url)" class="btn btn-primary btn-lg" :to="block.button_url">{{ block.button_text }}</RouterLink>
                            <a v-else-if="hasPrimaryButton" class="btn btn-primary btn-lg" :href="block.button_url">{{ block.button_text }}</a>
                            <RouterLink v-if="hasSecondaryButton && isInternalUrl(block.secondary_button_url)" class="btn btn-outline-light btn-lg" :to="block.secondary_button_url">{{ block.secondary_button_text }}</RouterLink>
                            <a v-else-if="hasSecondaryButton" class="btn btn-outline-light btn-lg" :href="block.secondary_button_url">{{ block.secondary_button_text }}</a>
                        </div>
                    </div>
                    <div v-if="block.image" class="col-lg-5">
                        <img class="block-feature-image rounded-4 shadow-lg" :src="block.image" :alt="block.title || 'Page block image'">
                    </div>
                </div>
            </template>

            <template v-else-if="block.type === 'cta'">
                <div class="page-block-cta-content mx-auto">
                    <p v-if="block.subtitle" class="text-uppercase fw-semibold opacity-75 mb-2">{{ block.subtitle }}</p>
                    <h2 v-if="block.title" class="display-5 fw-bold mb-3">{{ block.title }}</h2>
                    <p v-if="block.content" class="lead block-copy mb-4">{{ block.content }}</p>
                    <div v-if="hasPrimaryButton || hasSecondaryButton" class="d-flex flex-wrap justify-content-center gap-2">
                        <RouterLink v-if="hasPrimaryButton && isInternalUrl(block.button_url)" class="btn btn-primary btn-lg" :to="block.button_url">{{ block.button_text }}</RouterLink>
                        <a v-else-if="hasPrimaryButton" class="btn btn-primary btn-lg" :href="block.button_url">{{ block.button_text }}</a>
                        <RouterLink v-if="hasSecondaryButton && isInternalUrl(block.secondary_button_url)" class="btn btn-outline-light btn-lg" :to="block.secondary_button_url">{{ block.secondary_button_text }}</RouterLink>
                        <a v-else-if="hasSecondaryButton" class="btn btn-outline-light btn-lg" :href="block.secondary_button_url">{{ block.secondary_button_text }}</a>
                    </div>
                </div>
            </template>

            <template v-else-if="block.type === 'text'">
                <div class="section-heading text-center mx-auto">
                    <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                    <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                    <p v-if="block.content" class="block-copy text-body-secondary fs-5 mb-0">{{ block.content }}</p>
                </div>
            </template>

            <template v-else-if="block.type === 'image'">
                <figure class="block-figure mx-auto mb-0 text-center">
                    <img v-if="block.image" class="block-feature-image rounded-4 shadow-sm" :src="block.image" :alt="block.title || block.subtitle || 'Page block image'" loading="lazy">
                    <figcaption v-if="block.title || block.subtitle || block.content" class="mt-4">
                        <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                        <h2 v-if="block.title" class="h3 fw-bold mb-2">{{ block.title }}</h2>
                        <p v-if="block.content" class="block-copy text-body-secondary mb-0">{{ block.content }}</p>
                    </figcaption>
                </figure>
            </template>

            <template v-else-if="block.type === 'text_image'">
                <div class="row align-items-center g-5" :class="{ 'flex-lg-row-reverse': block.settings?.image_position === 'right' }">
                    <div v-if="block.image" class="col-lg-6">
                        <img class="block-feature-image rounded-4 shadow-sm" :src="block.image" :alt="block.title || 'Page block image'" loading="lazy">
                    </div>
                    <div :class="block.image ? 'col-lg-6' : 'col-lg-9 mx-auto text-center'">
                        <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                        <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                        <p v-if="block.content" class="block-copy text-body-secondary fs-5 mb-4">{{ block.content }}</p>
                        <RouterLink v-if="hasPrimaryButton && isInternalUrl(block.button_url)" class="btn btn-primary" :to="block.button_url">{{ block.button_text }}</RouterLink>
                        <a v-else-if="hasPrimaryButton" class="btn btn-primary" :href="block.button_url">{{ block.button_text }}</a>
                    </div>
                </div>
            </template>

            <template v-else-if="['services', 'gallery', 'testimonials', 'faq', 'blog'].includes(block.type)">
                <div class="section-heading text-center mx-auto mb-5">
                    <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                    <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                    <p v-if="block.content" class="block-copy text-body-secondary mb-0">{{ block.content }}</p>
                </div>

                <div v-if="moduleLoading" class="d-flex justify-content-center py-5" role="status" aria-label="Loading block content">
                    <div class="spinner-border text-primary"></div>
                </div>

                <div v-else-if="block.type === 'services' && moduleData.length" class="row g-4 justify-content-center">
                    <div v-for="(service, index) in moduleData" :key="service.id || service.slug || index" class="col-md-6 col-lg-4">
                        <ServiceCard
                            :title="service.title"
                            :description="service.short_description || service.description || ''"
                            :icon="service.icon || String(index + 1).padStart(2, '0')"
                            :image="service.image_url || ''"
                        />
                    </div>
                </div>

                <div v-else-if="block.type === 'gallery' && moduleData.length" class="row g-4">
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

                <div v-else-if="block.type === 'testimonials' && moduleData.length" class="row g-4 justify-content-center">
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

                <div v-else-if="block.type === 'blog' && moduleData.length" class="row g-4 justify-content-center">
                    <div v-for="blog in moduleData.slice(0, 3)" :key="blog.slug" class="col-md-6 col-lg-4">
                        <BlogCard :blog="blog" />
                    </div>
                </div>

                <div v-else-if="block.type === 'faq' && moduleData.length" :id="`page-block-faq-${instanceId}`" class="accordion accordion-flush faq-list mx-auto rounded-4 overflow-hidden shadow-sm">
                    <div v-for="(faq, index) in moduleData" :key="faq.id || index" class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button fw-semibold" :class="{ collapsed: index !== 0 }" type="button" data-bs-toggle="collapse" :data-bs-target="`#page-block-faq-${instanceId}-${index}`" :aria-expanded="index === 0">
                                {{ faq.question }}
                            </button>
                        </h3>
                        <div :id="`page-block-faq-${instanceId}-${index}`" class="accordion-collapse collapse" :class="{ show: index === 0 }" :data-bs-parent="`#page-block-faq-${instanceId}`">
                            <div class="accordion-body block-copy text-body-secondary">{{ faq.answer }}</div>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else-if="block.type === 'features'">
                <div class="section-heading text-center mx-auto mb-5">
                    <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                    <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                    <p v-if="block.content" class="block-copy text-body-secondary mb-0">{{ block.content }}</p>
                </div>
                <div v-if="featureItems.length" class="row g-4">
                    <div v-for="(feature, index) in featureItems" :key="feature.title || index" class="col-md-6 col-lg-4">
                        <article class="feature-card h-100 bg-white border shadow-sm">
                            <i v-if="feature.icon" :class="[feature.icon, 'feature-icon text-primary']" aria-hidden="true"></i>
                            <h3 class="h5 fw-bold mb-2">{{ feature.title }}</h3>
                            <p class="text-body-secondary mb-0">{{ feature.description }}</p>
                        </article>
                    </div>
                </div>
            </template>

            <template v-else-if="block.type === 'pricing'">
                <div class="section-heading text-center mx-auto mb-5">
                    <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                    <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                    <p v-if="block.content" class="block-copy text-body-secondary mb-0">{{ block.content }}</p>
                </div>
                <div v-if="pricingPlans.length" class="row g-4 justify-content-center">
                    <div v-for="(plan, index) in pricingPlans" :key="plan.name || index" class="col-md-6 col-xl-4">
                        <article class="pricing-card h-100 bg-white border shadow-sm">
                            <h3 class="h4 fw-bold mb-2">{{ plan.name }}</h3>
                            <p v-if="plan.price" class="display-6 fw-bold text-primary mb-3">{{ plan.price }}</p>
                            <ul v-if="Array.isArray(plan.features)" class="list-unstyled text-body-secondary mb-4">
                                <li v-for="feature in plan.features" :key="feature" class="mb-2">
                                    <i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>{{ feature }}
                                </li>
                            </ul>
                            <RouterLink v-if="plan.button_text && isInternalUrl(plan.button_url)" class="btn btn-primary w-100" :to="plan.button_url">{{ plan.button_text }}</RouterLink>
                            <a v-else-if="plan.button_text && plan.button_url" class="btn btn-primary w-100" :href="plan.button_url">{{ plan.button_text }}</a>
                        </article>
                    </div>
                </div>
            </template>

            <template v-else-if="block.type === 'contact_form'">
                <div class="row g-5 align-items-start">
                    <div class="col-lg-5">
                        <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                        <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                        <p v-if="block.content" class="block-copy text-body-secondary fs-5">{{ block.content }}</p>
                        <dl v-if="hasContactDetails" class="contact-details mt-4 mb-0">
                            <div v-if="siteSettings.phone" class="mb-3">
                                <dt class="small text-secondary">Phone</dt>
                                <dd><a :href="`tel:${siteSettings.phone}`">{{ siteSettings.phone }}</a></dd>
                            </div>
                            <div v-if="siteSettings.email" class="mb-3">
                                <dt class="small text-secondary">Email</dt>
                                <dd><a :href="`mailto:${siteSettings.email}`">{{ siteSettings.email }}</a></dd>
                            </div>
                            <div v-if="siteSettings.address">
                                <dt class="small text-secondary">Address</dt>
                                <dd class="block-copy">{{ siteSettings.address }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="col-lg-7">
                        <form ref="contactFormElement" class="contact-form bg-white border shadow-sm" :class="{ 'was-validated': contactValidated }" novalidate @submit.prevent="submitContact">
                            <div v-if="contactSuccess" class="alert alert-success" role="alert">{{ contactSuccess }}</div>
                            <div v-if="contactError" class="alert alert-danger" role="alert">{{ contactError }}</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" :for="`block-contact-name-${instanceId}`">Name <span class="text-danger">*</span></label>
                                    <input :id="`block-contact-name-${instanceId}`" v-model.trim="contactForm.name" class="form-control" :class="{ 'is-invalid': fieldError('name') }" type="text" maxlength="255" autocomplete="name" required>
                                    <div class="invalid-feedback">{{ fieldError('name') || 'Please enter your name.' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" :for="`block-contact-phone-${instanceId}`">Phone</label>
                                    <input :id="`block-contact-phone-${instanceId}`" v-model.trim="contactForm.phone" class="form-control" :class="{ 'is-invalid': fieldError('phone') }" type="tel" maxlength="50" autocomplete="tel">
                                    <div class="invalid-feedback">{{ fieldError('phone') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" :for="`block-contact-email-${instanceId}`">Email</label>
                                    <input :id="`block-contact-email-${instanceId}`" v-model.trim="contactForm.email" class="form-control" :class="{ 'is-invalid': fieldError('email') }" type="email" maxlength="255" autocomplete="email">
                                    <div class="invalid-feedback">{{ fieldError('email') || 'Please enter a valid email address.' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" :for="`block-contact-subject-${instanceId}`">Subject</label>
                                    <input :id="`block-contact-subject-${instanceId}`" v-model.trim="contactForm.subject" class="form-control" :class="{ 'is-invalid': fieldError('subject') }" type="text" maxlength="255">
                                    <div class="invalid-feedback">{{ fieldError('subject') }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" :for="`block-contact-message-${instanceId}`">Message <span class="text-danger">*</span></label>
                                    <textarea :id="`block-contact-message-${instanceId}`" v-model.trim="contactForm.message" class="form-control" :class="{ 'is-invalid': fieldError('message') }" rows="5" maxlength="10000" required></textarea>
                                    <div class="invalid-feedback">{{ fieldError('message') || 'Please enter your message.' }}</div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit" :disabled="contactSubmitting">
                                        <span v-if="contactSubmitting" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                                        {{ contactSubmitting ? 'Sending...' : 'Send message' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </template>

            <template v-else-if="block.type === 'newsletter'">
                <div class="newsletter-block mx-auto text-center">
                    <p v-if="block.subtitle" class="text-primary text-uppercase fw-semibold mb-2">{{ block.subtitle }}</p>
                    <h2 v-if="block.title" class="display-6 fw-bold mb-3">{{ block.title }}</h2>
                    <p v-if="block.content" class="block-copy text-body-secondary fs-5 mb-4">{{ block.content }}</p>
                    <form class="newsletter-form mx-auto" @submit.prevent="subscribeNewsletter">
                        <label class="visually-hidden" :for="`block-newsletter-${instanceId}`">Email address</label>
                        <div class="input-group input-group-lg">
                            <input :id="`block-newsletter-${instanceId}`" v-model.trim="newsletterEmail" class="form-control" :class="{ 'is-invalid': newsletterValidation.email }" type="email" autocomplete="email" placeholder="Your email address" required :disabled="newsletterSubmitting">
                            <button class="btn btn-primary" type="submit" :disabled="newsletterSubmitting">
                                <span v-if="newsletterSubmitting" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <span v-else>Subscribe</span>
                            </button>
                        </div>
                        <div v-if="newsletterSuccess" class="small text-success-emphasis bg-success-subtle rounded px-2 py-1 mt-2" role="status">{{ newsletterSuccess }}</div>
                        <div v-if="newsletterError" class="small text-danger-emphasis bg-danger-subtle rounded px-2 py-1 mt-2" role="alert">{{ newsletterError }}</div>
                    </form>
                </div>
            </template>

            <template v-else-if="block.type === 'custom_html'">
                <div class="custom-html mx-auto" v-html="safeHtml"></div>
            </template>
        </div>
    </section>
</template>

<style scoped>
.page-builder-block {
    background-position: center;
    background-size: cover;
}

.has-custom-text-color .text-primary,
.has-custom-text-color .text-info,
.has-custom-text-color .text-body-secondary {
    color: var(--page-block-text-color) !important;
}

.page-block-hero {
    display: flex;
    min-height: 34rem;
    align-items: center;
    background-color: #0d1b2a;
    background-image: linear-gradient(120deg, #0d1b2a, var(--site-primary-color));
}

.page-block-cta-content,
.section-heading,
.newsletter-block,
.custom-html {
    max-width: 54rem;
}

.block-copy {
    white-space: pre-line;
}

.block-feature-image {
    display: block;
    width: 100%;
    max-height: 34rem;
    object-fit: cover;
}

.block-figure {
    max-width: 58rem;
}

.gallery-image {
    height: 16rem;
    object-fit: cover;
}

.faq-list {
    max-width: 54rem;
}

.feature-card,
.pricing-card,
.contact-form {
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.feature-icon {
    display: inline-block;
    font-size: 2rem;
    margin-bottom: 1rem;
}

.newsletter-form {
    max-width: 40rem;
}

.contact-details a {
    color: var(--site-primary-color);
    text-decoration: none;
}

.contact-details a:hover {
    text-decoration: underline;
}

.custom-html :deep(img) {
    height: auto;
    max-width: 100%;
    border-radius: 0.5rem;
}

.custom-html :deep(a) {
    color: var(--site-primary-color);
}

@media (max-width: 767.98px) {
    .page-block-hero {
        min-height: 30rem;
    }
}
</style>
