<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import BaseButton from './base/BaseButton.vue';
import { siteSettings } from '../siteSettings';

const props = defineProps({
    eyebrow: { type: String, default: `Welcome to ${siteSettings.siteName}` },
    title: { type: String, required: true },
    description: { type: String, required: true },
    buttonText: { type: String, default: 'Explore our services' },
    buttonLink: { type: String, default: '/services' },
});

const router = useRouter();
const sliders = ref([]);
const activeIndex = ref(0);
let autoplayTimer = null;
let requestController = null;

const fallbackSlide = computed(() => ({
    id: 'fallback',
    title: props.title,
    subtitle: props.description,
    button_text: props.buttonText,
    button_url: props.buttonLink,
    image_url: null,
    isFallback: true,
}));

const displayedSliders = computed(() => (
    sliders.value.length > 0 ? sliders.value : [fallbackSlide.value]
));

const currentSlide = computed(() => (
    displayedSliders.value[activeIndex.value] ?? fallbackSlide.value
));

const heroStyle = computed(() => {
    if (!currentSlide.value.image_url) {
        return {};
    }

    return {
        backgroundImage: `linear-gradient(100deg, rgba(13, 27, 42, 0.94) 0%, rgba(13, 27, 42, 0.68) 55%, rgba(var(--bs-primary-rgb), 0.35) 100%), url("${currentSlide.value.image_url}")`,
    };
});

const stopAutoplay = () => {
    if (autoplayTimer) {
        window.clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
};

const startAutoplay = () => {
    stopAutoplay();

    if (displayedSliders.value.length > 1) {
        autoplayTimer = window.setInterval(() => {
            activeIndex.value = (activeIndex.value + 1) % displayedSliders.value.length;
        }, 6000);
    }
};

const showSlide = (index) => {
    activeIndex.value = index;
    startAutoplay();
};

const showPrevious = () => {
    const total = displayedSliders.value.length;
    showSlide((activeIndex.value - 1 + total) % total);
};

const showNext = () => {
    showSlide((activeIndex.value + 1) % displayedSliders.value.length);
};

const followButton = (url) => {
    if (!url) {
        return;
    }

    if (/^(https?:)?\/\//i.test(url) || /^(mailto|tel):/i.test(url)) {
        window.location.assign(url);
        return;
    }

    router.push(url);
};

const loadSliders = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/hero-sliders', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Hero slider request failed with status ${response.status}`);
        }

        const payload = await response.json();
        sliders.value = Array.isArray(payload.data) ? payload.data : [];
        activeIndex.value = 0;
        startAutoplay();
    } catch (error) {
        if (error.name !== 'AbortError') {
            sliders.value = [];
            activeIndex.value = 0;
        }
    }
};

onMounted(loadSliders);

onBeforeUnmount(() => {
    requestController?.abort();
    stopAutoplay();
});
</script>

<template>
    <section
        class="hero-section position-relative overflow-hidden text-white"
        :style="heroStyle"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
        @focusin="stopAutoplay"
        @focusout="startAutoplay"
    >
        <div class="container position-relative z-1 py-5">
            <Transition name="hero-fade" mode="out-in">
                <div :key="currentSlide.id" class="col-lg-8 py-5 hero-content">
                    <p class="text-uppercase fw-semibold text-info mb-2">{{ eyebrow }}</p>
                    <h1 class="display-3 fw-bold">{{ currentSlide.title }}</h1>
                    <p class="lead my-4">{{ currentSlide.subtitle }}</p>

                    <template v-if="currentSlide.isFallback">
                        <slot name="action" :slide="currentSlide">
                            <BaseButton
                                v-if="currentSlide.button_text && currentSlide.button_url"
                                size="lg"
                                @click="followButton(currentSlide.button_url)"
                            >
                                {{ currentSlide.button_text }}
                            </BaseButton>
                        </slot>
                    </template>

                    <BaseButton
                        v-else-if="currentSlide.button_text && currentSlide.button_url"
                        size="lg"
                        @click="followButton(currentSlide.button_url)"
                    >
                        {{ currentSlide.button_text }}
                    </BaseButton>
                </div>
            </Transition>
        </div>

        <template v-if="displayedSliders.length > 1">
            <button
                class="carousel-control-prev hero-control"
                type="button"
                aria-label="Show previous slide"
                @click="showPrevious"
            >
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button
                class="carousel-control-next hero-control"
                type="button"
                aria-label="Show next slide"
                @click="showNext"
            >
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>

            <div class="hero-indicators position-absolute start-50 translate-middle-x d-flex gap-2">
                <button
                    v-for="(slide, index) in displayedSliders"
                    :key="slide.id"
                    class="hero-indicator border-0 rounded-pill"
                    :class="{ active: index === activeIndex }"
                    type="button"
                    :aria-label="`Show slide ${index + 1}`"
                    :aria-current="index === activeIndex ? 'true' : undefined"
                    @click="showSlide(index)"
                ></button>
            </div>
        </template>
    </section>
</template>

<style scoped>
.hero-section {
    min-height: 34rem;
    display: flex;
    align-items: center;
    background-color: #0d1b2a;
    background-image: linear-gradient(120deg, #0d1b2a, var(--site-primary-color));
    background-position: center;
    background-size: cover;
}

.hero-content {
    padding-inline: 3.5rem;
}

.hero-control {
    width: 5%;
    min-width: 3rem;
}

.hero-indicators {
    bottom: 1.5rem;
    z-index: 2;
}

.hero-indicator {
    width: 2rem;
    height: 0.25rem;
    padding: 0;
    background: rgba(255, 255, 255, 0.45);
    transition: width 180ms ease, background-color 180ms ease;
}

.hero-indicator.active {
    width: 3.25rem;
    background: #fff;
}

.hero-fade-enter-active,
.hero-fade-leave-active {
    transition: opacity 220ms ease, transform 220ms ease;
}

.hero-fade-enter-from,
.hero-fade-leave-to {
    opacity: 0;
    transform: translateY(0.75rem);
}

@media (max-width: 767.98px) {
    .hero-section {
        min-height: 30rem;
    }

    .hero-content {
        padding-inline: 2rem;
    }

    .hero-content h1 {
        font-size: calc(1.6rem + 2.5vw);
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-fade-enter-active,
    .hero-fade-leave-active,
    .hero-indicator {
        transition: none;
    }
}
</style>
