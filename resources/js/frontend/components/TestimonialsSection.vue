<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import TestimonialCard from './TestimonialCard.vue';

const demoTestimonials = [
    {
        id: 'demo-1',
        client_name: 'Aarav Mehta',
        company: 'Mehta Retail',
        designation: 'Owner',
        review: 'Billsoft made our daily billing process faster and much easier to manage.',
        rating: 5,
        image_url: null,
        featured: true,
    },
    {
        id: 'demo-2',
        client_name: 'Neha Shah',
        company: 'Northstar Operations',
        designation: 'Operations Lead',
        review: 'The team understood what we needed and delivered a wonderfully simple solution.',
        rating: 5,
        image_url: null,
        featured: false,
    },
];

const testimonials = ref([]);
const activeIndex = ref(0);
let autoplayTimer = null;
let requestController = null;

const displayedTestimonials = computed(() => (
    testimonials.value.length > 0 ? testimonials.value : demoTestimonials
));

const currentTestimonial = computed(() => (
    displayedTestimonials.value[activeIndex.value] ?? demoTestimonials[0]
));

const stopAutoplay = () => {
    if (autoplayTimer) {
        window.clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
};

const startAutoplay = () => {
    stopAutoplay();

    if (displayedTestimonials.value.length > 1) {
        autoplayTimer = window.setInterval(() => {
            activeIndex.value = (activeIndex.value + 1) % displayedTestimonials.value.length;
        }, 7000);
    }
};

const showSlide = (index) => {
    activeIndex.value = index;
    startAutoplay();
};

const showPrevious = () => {
    const total = displayedTestimonials.value.length;
    showSlide((activeIndex.value - 1 + total) % total);
};

const showNext = () => {
    showSlide((activeIndex.value + 1) % displayedTestimonials.value.length);
};

const loadTestimonials = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/testimonials', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Testimonials request failed with status ${response.status}`);
        }

        const payload = await response.json();
        testimonials.value = Array.isArray(payload.data) ? payload.data : [];
        activeIndex.value = 0;
        startAutoplay();
    } catch (error) {
        if (error.name !== 'AbortError') {
            testimonials.value = [];
            activeIndex.value = 0;
            startAutoplay();
        }
    }
};

onMounted(() => {
    loadTestimonials();
    startAutoplay();
});

onBeforeUnmount(() => {
    requestController?.abort();
    stopAutoplay();
});
</script>

<template>
    <section
        class="section-padding bg-white"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
        @focusin="stopAutoplay"
        @focusout="startAutoplay"
    >
        <div class="container">
            <div class="text-center mb-5">
                <p class="text-primary text-uppercase fw-semibold mb-2">Testimonials</p>
                <h2 class="display-6 fw-bold">What our customers say</h2>
            </div>

            <div class="testimonial-slider position-relative mx-auto px-md-5">
                <Transition name="testimonial-fade" mode="out-in">
                    <TestimonialCard
                        :key="currentTestimonial.id"
                        :client-name="currentTestimonial.client_name"
                        :company="currentTestimonial.company || ''"
                        :designation="currentTestimonial.designation || ''"
                        :review="currentTestimonial.review"
                        :rating="currentTestimonial.rating"
                        :image="currentTestimonial.image_url || ''"
                        :featured="Boolean(currentTestimonial.featured)"
                    />
                </Transition>

                <template v-if="displayedTestimonials.length > 1">
                    <button
                        class="slider-control slider-control-prev btn btn-light rounded-circle shadow-sm"
                        type="button"
                        aria-label="Show previous testimonial"
                        @click="showPrevious"
                    >
                        <span aria-hidden="true">&lsaquo;</span>
                    </button>
                    <button
                        class="slider-control slider-control-next btn btn-light rounded-circle shadow-sm"
                        type="button"
                        aria-label="Show next testimonial"
                        @click="showNext"
                    >
                        <span aria-hidden="true">&rsaquo;</span>
                    </button>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <button
                            v-for="(testimonial, index) in displayedTestimonials"
                            :key="testimonial.id"
                            class="slider-indicator border-0 rounded-circle"
                            :class="{ active: index === activeIndex }"
                            type="button"
                            :aria-label="`Show testimonial ${index + 1}`"
                            :aria-current="index === activeIndex ? 'true' : undefined"
                            @click="showSlide(index)"
                        ></button>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<style scoped>
.testimonial-slider {
    max-width: 58rem;
}

.slider-control {
    position: absolute;
    top: 50%;
    z-index: 2;
    display: grid;
    width: 3rem;
    height: 3rem;
    place-items: center;
    color: #0d6efd;
    font-size: 2rem;
    line-height: 1;
    transform: translateY(-50%);
}

.slider-control-prev {
    left: -1rem;
}

.slider-control-next {
    right: -1rem;
}

.slider-indicator {
    width: 0.65rem;
    height: 0.65rem;
    padding: 0;
    background: #ced4da;
}

.slider-indicator.active {
    background: #0d6efd;
}

.testimonial-fade-enter-active,
.testimonial-fade-leave-active {
    transition: opacity 200ms ease, transform 200ms ease;
}

.testimonial-fade-enter-from,
.testimonial-fade-leave-to {
    opacity: 0;
    transform: translateY(0.5rem);
}

@media (max-width: 767.98px) {
    .slider-control {
        top: auto;
        bottom: -0.45rem;
        width: 2.5rem;
        height: 2.5rem;
    }

    .slider-control-prev {
        left: 0;
    }

    .slider-control-next {
        right: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .testimonial-fade-enter-active,
    .testimonial-fade-leave-active {
        transition: none;
    }
}
</style>
