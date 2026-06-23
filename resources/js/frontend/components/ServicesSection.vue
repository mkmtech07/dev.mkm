<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BaseSectionTitle from './base/BaseSectionTitle.vue';
import ServiceCard from './ServiceCard.vue';

defineProps({
    eyebrow: {
        type: String,
        default: 'What we do',
    },
    title: {
        type: String,
        default: 'Everything you need to work smarter',
    },
    description: {
        type: String,
        default: '',
    },
    headingTag: {
        type: String,
        default: 'h2',
    },
});

const fallbackServices = [
    {
        id: 'fallback-1',
        icon: '01',
        title: 'Smart Billing',
        short_description: 'Create clear, professional invoices with less manual work.',
        image_url: null,
    },
    {
        id: 'fallback-2',
        icon: '02',
        title: 'Business Insights',
        short_description: 'See the numbers that matter and make confident decisions.',
        image_url: null,
    },
    {
        id: 'fallback-3',
        icon: '03',
        title: 'Reliable Support',
        short_description: 'Get friendly help whenever your team needs it.',
        image_url: null,
    },
];

const services = ref([]);
let requestController = null;

const displayedServices = computed(() => {
    const source = services.value.length > 0 ? services.value : fallbackServices;

    return source.map((service, index) => ({
        id: service.id ?? service.slug ?? `service-${index}`,
        title: service.title,
        description: service.short_description || service.description || '',
        icon: service.icon || String(index + 1).padStart(2, '0'),
        image: service.image_url || '',
    }));
});

const loadServices = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/services', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Services request failed with status ${response.status}`);
        }

        const payload = await response.json();
        services.value = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            services.value = [];
        }
    }
};

onMounted(loadServices);
onBeforeUnmount(() => requestController?.abort());
</script>

<template>
    <section class="section-padding">
        <div class="container">
            <BaseSectionTitle
                class="services-title text-center mx-auto mb-5"
                :eyebrow="eyebrow"
                :title="title"
                :description="description"
                :heading-tag="headingTag"
            />

            <div class="row g-4 justify-content-center">
                <div
                    v-for="service in displayedServices"
                    :key="service.id"
                    class="col-md-6 col-lg-4"
                >
                    <ServiceCard v-bind="service" />
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.services-title {
    max-width: 42rem;
}

.services-title :deep(.display-6) {
    margin-bottom: 0.5rem !important;
}
</style>
