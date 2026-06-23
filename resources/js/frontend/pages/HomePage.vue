<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import HeroSection from '../components/HeroSection.vue';
import HomepageSectionRenderer from '../components/HomepageSectionRenderer.vue';
import ServicesSection from '../components/ServicesSection.vue';
import TestimonialsSection from '../components/TestimonialsSection.vue';
import TeamSection from '../components/TeamSection.vue';
import FAQSection from '../components/FAQSection.vue';
import BaseButton from '../components/base/BaseButton.vue';
import { siteSettings } from '../siteSettings';

const homepageSections = ref(null);
let requestController = null;

const loadHomepageSections = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/homepage-sections', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Homepage sections request failed with status ${response.status}`);
        }

        const payload = await response.json();
        homepageSections.value = Array.isArray(payload.sections) ? payload.sections : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            homepageSections.value = [];
        }
    }
};

onMounted(loadHomepageSections);
onBeforeUnmount(() => requestController?.abort());
</script>

<template>
    <div v-if="homepageSections === null" class="homepage-loading d-flex align-items-center justify-content-center" role="status" aria-label="Loading homepage">
        <div class="spinner-border text-primary"></div>
    </div>

    <template v-else-if="homepageSections.length">
        <HomepageSectionRenderer
            v-for="(section, index) in homepageSections"
            :key="`${section.section_key || section.type}-${index}`"
            :section="section"
        />
    </template>

    <template v-else>
        <HeroSection
            :title="siteSettings.tagline || 'Transform Your Business with Technology'"
            :description="`${siteSettings.siteName} delivers modern websites, custom software, billing systems, POS solutions, eCommerce platforms, and mobile applications designed to help businesses streamline operations, enhance productivity, and achieve sustainable growth.`"
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
</template>

<style scoped>
.homepage-loading {
    min-height: 34rem;
}
</style>
