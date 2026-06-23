<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BaseSectionTitle from './base/BaseSectionTitle.vue';

const demoTeamMembers = [
    {
        id: 'demo-1',
        name: 'Riya Sharma',
        designation: 'Product Lead',
        bio: 'Riya turns complex business needs into focused, approachable products.',
        image_url: null,
        facebook_url: null,
        linkedin_url: 'https://www.linkedin.com',
        twitter_url: null,
    },
    {
        id: 'demo-2',
        name: 'Arjun Patel',
        designation: 'Engineering Lead',
        bio: 'Arjun builds dependable systems that help teams work confidently at scale.',
        image_url: null,
        facebook_url: null,
        linkedin_url: 'https://www.linkedin.com',
        twitter_url: 'https://x.com',
    },
    {
        id: 'demo-3',
        name: 'Meera Joshi',
        designation: 'Customer Success Lead',
        bio: 'Meera helps customers get lasting value from every part of the platform.',
        image_url: null,
        facebook_url: 'https://www.facebook.com',
        linkedin_url: null,
        twitter_url: null,
    },
];

const teamMembers = ref([]);
let requestController = null;

const displayedMembers = computed(() => (
    teamMembers.value.length > 0 ? teamMembers.value : demoTeamMembers
));

const initials = (name) => name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');

const socialLinks = (member) => [
    { label: 'Facebook', shortLabel: 'f', url: member.facebook_url },
    { label: 'LinkedIn', shortLabel: 'in', url: member.linkedin_url },
    { label: 'X / Twitter', shortLabel: 'x', url: member.twitter_url },
].filter((social) => social.url);

const loadTeamMembers = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/team-members', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Team request failed with status ${response.status}`);
        }

        const payload = await response.json();
        teamMembers.value = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            teamMembers.value = [];
        }
    }
};

onMounted(loadTeamMembers);
onBeforeUnmount(() => requestController?.abort());
</script>

<template>
    <section class="section-padding bg-light">
        <div class="container">
            <BaseSectionTitle
                class="team-title text-center mx-auto mb-5"
                eyebrow="Our team"
                title="Meet the people behind the work"
                description="A thoughtful group of builders, problem-solvers, and customer advocates."
            />

            <div class="row g-4 justify-content-center">
                <div
                    v-for="member in displayedMembers"
                    :key="member.id"
                    class="col-sm-6 col-lg-4"
                >
                    <article class="card h-100 overflow-hidden border-0 shadow-sm text-center">
                        <img
                            v-if="member.image_url"
                            :src="member.image_url"
                            class="card-img-top team-image"
                            :alt="member.name"
                            loading="lazy"
                        >
                        <div v-else class="team-image team-placeholder" aria-hidden="true">
                            {{ initials(member.name) }}
                        </div>

                        <div class="card-body d-flex flex-column p-4">
                            <h2 class="h5 card-title mb-1">{{ member.name }}</h2>
                            <p v-if="member.designation" class="text-primary fw-semibold small mb-3">
                                {{ member.designation }}
                            </p>
                            <p v-if="member.bio" class="card-text text-secondary flex-grow-1">
                                {{ member.bio }}
                            </p>

                            <div v-if="socialLinks(member).length" class="d-flex justify-content-center gap-2 mt-3">
                                <a
                                    v-for="social in socialLinks(member)"
                                    :key="social.label"
                                    class="social-link btn btn-sm btn-outline-primary rounded-circle"
                                    :href="social.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :aria-label="`${member.name} on ${social.label}`"
                                >
                                    {{ social.shortLabel }}
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.team-title {
    max-width: 44rem;
}

.team-image {
    display: grid;
    width: 100%;
    height: 19rem;
    place-items: center;
    object-fit: cover;
}

.team-placeholder {
    background: linear-gradient(135deg, #0d1b2a, var(--site-primary-color));
    color: rgba(255, 255, 255, 0.92);
    font-size: 3rem;
    font-weight: 700;
}

.social-link {
    display: grid;
    width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    place-items: center;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}
</style>
