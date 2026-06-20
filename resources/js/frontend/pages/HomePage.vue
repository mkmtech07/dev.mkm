<script setup>
import HeroSection from '../components/HeroSection.vue';
import ServicesSection from '../components/ServicesSection.vue';
import BaseButton from '../components/base/BaseButton.vue';
import BaseCard from '../components/base/BaseCard.vue';

const testimonials = [
    { quote: 'Billsoft made our daily billing process faster and much easier to manage.', name: 'Aarav Mehta', role: 'Retail owner' },
    { quote: 'The team understood what we needed and delivered a wonderfully simple solution.', name: 'Neha Shah', role: 'Operations lead' },
];
</script>

<template>
    <HeroSection
        title="Simple digital tools for growing businesses"
        description="Modern solutions that help your team spend less time on routine work and more time moving forward."
    >
        <template #action>
            <RouterLink v-slot="{ navigate }" custom to="/services">
                <BaseButton size="lg" @click="navigate">Explore our services</BaseButton>
            </RouterLink>
        </template>
    </HeroSection>

    <ServicesSection />

    <section class="section-padding bg-white">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">What our customers say</h2>
            <div class="row g-4 justify-content-center">
                <div v-for="testimonial in testimonials" :key="testimonial.name" class="col-md-6">
                    <BaseCard>
                        <figure class="mb-0">
                            <blockquote class="blockquote fs-6">“{{ testimonial.quote }}”</blockquote>
                            <figcaption class="blockquote-footer mb-0 mt-3">
                                {{ testimonial.name }}, <cite>{{ testimonial.role }}</cite>
                            </figcaption>
                        </figure>
                    </BaseCard>
                </div>
            </div>
        </div>
    </section>
</template>
