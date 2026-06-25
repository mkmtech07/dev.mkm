<script setup>
import { computed } from 'vue';
import { siteSettings } from '../siteSettings';

const props = defineProps({
    status: {
        type: Object,
        required: true,
    },
});

const title = computed(() => props.status.title || 'Website Under Maintenance');
const message = computed(() => props.status.message || 'We are currently improving our website. Please check back soon.');
const buttonText = computed(() => props.status.button_text || 'Contact Us');
const buttonUrl = computed(() => props.status.button_url || '/contact');
const image = computed(() => props.status.image || '');
const endTime = computed(() => {
    if (! props.status.end_at) return '';

    const date = new Date(props.status.end_at);
    if (Number.isNaN(date.getTime())) return '';

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
});
</script>

<template>
    <section class="maintenance-page d-flex align-items-center min-vh-100">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7 text-center">
                    <img
                        v-if="image"
                        class="maintenance-image mb-4"
                        :src="image"
                        :alt="title"
                    >

                    <div class="badge text-bg-primary mb-3">Maintenance</div>
                    <h1 class="display-5 fw-bold mb-3">{{ title }}</h1>
                    <p class="lead text-secondary maintenance-message mx-auto mb-4">{{ message }}</p>

                    <p v-if="endTime" class="small text-uppercase fw-semibold text-secondary mb-4">
                        Expected back by {{ endTime }}
                    </p>

                    <a class="btn btn-primary btn-lg px-4" :href="buttonUrl">
                        {{ buttonText }}
                    </a>

                    <p class="small text-secondary mt-5 mb-0">
                        {{ siteSettings.siteName }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.maintenance-page {
    background: #f8f9fa;
}

.maintenance-image {
    max-height: 14rem;
    max-width: min(100%, 26rem);
    object-fit: contain;
}

.maintenance-message {
    max-width: 44rem;
    white-space: pre-line;
}
</style>
