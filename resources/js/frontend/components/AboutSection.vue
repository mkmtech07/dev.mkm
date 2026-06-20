<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BaseCard from './base/BaseCard.vue';

const defaultContent = {
    id: 'fallback',
    title: 'Technology with a practical purpose',
    subtitle: 'Approachable tools built around the way real teams work.',
    description: 'We build practical business software that reduces complexity, saves time, and helps every customer grow with confidence.',
    image_url: null,
    mission: 'To make useful business technology simple, dependable, and accessible to growing teams.',
    vision: 'A future where every business can use technology confidently without unnecessary complexity.',
    years_of_experience: 10,
    projects_completed: 250,
    clients_served: 180,
    team_members: 24,
};

const about = ref(defaultContent);
let requestController = null;

const statistics = computed(() => [
    { label: 'Years of experience', value: about.value.years_of_experience, suffix: '+' },
    { label: 'Projects completed', value: about.value.projects_completed, suffix: '+' },
    { label: 'Clients served', value: about.value.clients_served, suffix: '+' },
    { label: 'Team members', value: about.value.team_members, suffix: '' },
].filter((statistic) => statistic.value !== null && statistic.value !== undefined && statistic.value !== ''));

const loadAboutSection = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/about-section', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`About request failed with status ${response.status}`);
        }

        const payload = await response.json();
        about.value = payload.data || defaultContent;
    } catch (error) {
        if (error.name !== 'AbortError') {
            about.value = defaultContent;
        }
    }
};

onMounted(loadAboutSection);
onBeforeUnmount(() => requestController?.abort());
</script>

<template>
    <section class="section-padding bg-white">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <img
                        v-if="about.image_url"
                        :src="about.image_url"
                        class="about-image rounded-4 shadow-sm"
                        :alt="about.title"
                    >
                    <div v-else class="about-image-placeholder rounded-4 shadow-sm" aria-hidden="true">
                        <span class="display-4 fw-bold">About</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <p class="text-primary text-uppercase fw-semibold mb-2">About us</p>
                    <h1 class="display-5 fw-bold mb-3">{{ about.title }}</h1>
                    <p v-if="about.subtitle" class="lead text-secondary">{{ about.subtitle }}</p>
                    <p class="about-description text-secondary fs-5 mb-0">{{ about.description }}</p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="statistics.length" class="py-5 bg-light border-top border-bottom">
        <div class="container">
            <div class="row g-4 justify-content-center text-center">
                <div
                    v-for="statistic in statistics"
                    :key="statistic.label"
                    class="col-6 col-lg-3"
                >
                    <div class="display-6 fw-bold text-primary">
                        {{ statistic.value }}{{ statistic.suffix }}
                    </div>
                    <div class="text-secondary">{{ statistic.label }}</div>
                </div>
            </div>
        </div>
    </section>

    <section v-if="about.mission || about.vision" class="section-padding">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div v-if="about.mission" class="col-md-6">
                    <BaseCard title="Our mission">
                        <p class="about-description text-secondary mb-0">{{ about.mission }}</p>
                    </BaseCard>
                </div>
                <div v-if="about.vision" class="col-md-6">
                    <BaseCard title="Our vision">
                        <p class="about-description text-secondary mb-0">{{ about.vision }}</p>
                    </BaseCard>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.about-image,
.about-image-placeholder {
    width: 100%;
    min-height: 28rem;
}

.about-image {
    display: block;
    object-fit: cover;
}

.about-image-placeholder {
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #0d1b2a, #0d6efd);
    color: rgba(255, 255, 255, 0.9);
}

.about-description {
    white-space: pre-line;
}

@media (max-width: 767.98px) {
    .about-image,
    .about-image-placeholder {
        min-height: 20rem;
    }
}
</style>
