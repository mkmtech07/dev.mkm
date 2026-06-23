<script setup>
import { computed, reactive, ref } from 'vue';
import { siteSettings } from '../siteSettings';

const emptyForm = () => ({
    name: '',
    phone: '',
    email: '',
    subject: '',
    message: '',
});

const form = reactive(emptyForm());
const formElement = ref(null);
const errors = ref({});
const successMessage = ref('');
const errorMessage = ref('');
const submitting = ref(false);
const validated = ref(false);

const whatsappUrl = computed(() => {
    const number = String(siteSettings.whatsapp || '').replace(/[^0-9]/g, '');
    return number ? `https://wa.me/${number}` : '';
});

const hasContactDetails = computed(() => Boolean(
    siteSettings.phone
    || siteSettings.email
    || siteSettings.address
    || whatsappUrl.value
));

const mapUrl = computed(() => {
    if (! siteSettings.googleMapEmbed) return '';

    const documentFragment = new DOMParser().parseFromString(siteSettings.googleMapEmbed, 'text/html');
    const source = documentFragment.querySelector('iframe')?.getAttribute('src') || '';

    try {
        const url = new URL(source);
        const isGoogleMaps = ['google.com', 'www.google.com'].includes(url.hostname)
            && url.pathname === '/maps/embed';

        return url.protocol === 'https:' && isGoogleMaps ? url.toString() : '';
    } catch {
        return '';
    }
});

const fieldError = (field) => errors.value[field]?.[0] ?? '';

const submit = async () => {
    validated.value = true;
    errors.value = {};
    successMessage.value = '';
    errorMessage.value = '';

    if (!formElement.value.checkValidity()) {
        return;
    }

    submitting.value = true;

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
            body: JSON.stringify(form),
        });
        const data = await response.json().catch(() => ({}));

        if (response.status === 422) {
            errors.value = data.errors ?? {};
            errorMessage.value = 'Please check the highlighted fields and try again.';
            return;
        }

        if (!response.ok) {
            throw new Error(data.message || 'Unable to send your message right now.');
        }

        Object.assign(form, emptyForm());
        validated.value = false;
        successMessage.value = data.message;
    } catch (error) {
        errorMessage.value = error.message || 'Unable to send your message right now. Please try again.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <section class="section-padding">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <p class="text-primary text-uppercase fw-semibold">Contact</p>
                    <h1 class="display-5 fw-bold">Let's talk about what you need</h1>
                    <p class="lead text-secondary">Tell us a little about your business and we'll get back to you soon.</p>

                    <div v-if="hasContactDetails" class="contact-details card border-0 shadow-sm mt-4">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Contact information</h2>
                            <dl class="mb-0">
                                <div v-if="siteSettings.phone" class="mb-3">
                                    <dt class="small text-secondary">Phone</dt>
                                    <dd class="mb-0"><a :href="`tel:${siteSettings.phone}`">{{ siteSettings.phone }}</a></dd>
                                </div>
                                <div v-if="siteSettings.email" class="mb-3">
                                    <dt class="small text-secondary">Email</dt>
                                    <dd class="mb-0"><a :href="`mailto:${siteSettings.email}`">{{ siteSettings.email }}</a></dd>
                                </div>
                                <div v-if="whatsappUrl" class="mb-3">
                                    <dt class="small text-secondary">WhatsApp</dt>
                                    <dd class="mb-0"><a :href="whatsappUrl" target="_blank" rel="noopener noreferrer">{{ siteSettings.whatsapp }}</a></dd>
                                </div>
                                <div v-if="siteSettings.address">
                                    <dt class="small text-secondary">Address</dt>
                                    <dd class="mb-0 contact-address">{{ siteSettings.address }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <form
                        ref="formElement"
                        class="card border-0 shadow-sm needs-validation"
                        :class="{ 'was-validated': validated }"
                        novalidate
                        @submit.prevent="submit"
                    >
                        <div class="card-body p-4 p-md-5">
                            <div v-if="successMessage" class="alert alert-success" role="alert">
                                {{ successMessage }}
                            </div>
                            <div v-if="errorMessage" class="alert alert-danger" role="alert">
                                {{ errorMessage }}
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="contact-name" class="form-label">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="contact-name"
                                        v-model.trim="form.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': fieldError('name') }"
                                        maxlength="255"
                                        autocomplete="name"
                                        required
                                    >
                                    <div class="invalid-feedback">{{ fieldError('name') || 'Please enter your name.' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-phone" class="form-label">Phone</label>
                                    <input
                                        id="contact-phone"
                                        v-model.trim="form.phone"
                                        type="tel"
                                        class="form-control"
                                        :class="{ 'is-invalid': fieldError('phone') }"
                                        maxlength="50"
                                        autocomplete="tel"
                                    >
                                    <div class="invalid-feedback">{{ fieldError('phone') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-email" class="form-label">Email</label>
                                    <input
                                        id="contact-email"
                                        v-model.trim="form.email"
                                        type="email"
                                        class="form-control"
                                        :class="{ 'is-invalid': fieldError('email') }"
                                        maxlength="255"
                                        autocomplete="email"
                                    >
                                    <div class="invalid-feedback">{{ fieldError('email') || 'Please enter a valid email address.' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-subject" class="form-label">Subject</label>
                                    <input
                                        id="contact-subject"
                                        v-model.trim="form.subject"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': fieldError('subject') }"
                                        maxlength="255"
                                    >
                                    <div class="invalid-feedback">{{ fieldError('subject') }}</div>
                                </div>
                                <div class="col-12">
                                    <label for="contact-message" class="form-label">
                                        Message <span class="text-danger">*</span>
                                    </label>
                                    <textarea
                                        id="contact-message"
                                        v-model.trim="form.message"
                                        class="form-control"
                                        :class="{ 'is-invalid': fieldError('message') }"
                                        rows="5"
                                        maxlength="10000"
                                        required
                                    ></textarea>
                                    <div class="invalid-feedback">{{ fieldError('message') || 'Please enter your message.' }}</div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit" :disabled="submitting">
                                        <span
                                            v-if="submitting"
                                            class="spinner-border spinner-border-sm me-2"
                                            aria-hidden="true"
                                        ></span>
                                        {{ submitting ? 'Sending...' : 'Send message' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="mapUrl" class="map-wrapper ratio ratio-21x9 rounded-3 overflow-hidden shadow-sm mt-5">
                <iframe
                    :src="mapUrl"
                    title="Our location on Google Maps"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </section>
</template>

<style scoped>
.contact-details a {
    color: var(--site-primary-color);
    text-decoration: none;
}

.contact-details a:hover {
    text-decoration: underline;
}

.contact-address {
    white-space: pre-line;
}

.map-wrapper {
    background: #e9ecef;
    min-height: 20rem;
}

.map-wrapper iframe {
    border: 0;
}
</style>
