<script setup>
import HeroSection from '../components/HeroSection.vue';
import ServicesSection from '../components/ServicesSection.vue';
import TestimonialsSection from '../components/TestimonialsSection.vue';
import TeamSection from '../components/TeamSection.vue';
import FAQSection from '../components/FAQSection.vue';
import BaseButton from '../components/base/BaseButton.vue';
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
    <TestimonialsSection />
    <TeamSection />
    <FAQSection />
</template>
