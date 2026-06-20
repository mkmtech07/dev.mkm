<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const faqs = ref([]);
let requestController;

const loadFaqs = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/faqs', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (! response.ok) {
            throw new Error(`FAQ request failed with status ${response.status}`);
        }

        const payload = await response.json();
        faqs.value = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            faqs.value = [];
        }
    }
};

onMounted(loadFaqs);

onBeforeUnmount(() => {
    requestController?.abort();
});
</script>

<template>
    <section v-if="faqs.length" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <p class="text-primary text-uppercase fw-semibold mb-2">FAQ</p>
                <h2 class="display-6 fw-bold">Frequently asked questions</h2>
                <p class="text-secondary mb-0">Quick answers to questions we hear most often.</p>
            </div>

            <div id="homepage-faq-accordion" class="accordion accordion-flush faq-accordion mx-auto shadow-sm rounded-4 overflow-hidden">
                <div v-for="(faq, index) in faqs" :key="faq.id" class="accordion-item">
                    <h3 :id="`faq-heading-${faq.id}`" class="accordion-header">
                        <button
                            class="accordion-button fw-semibold"
                            :class="{ collapsed: index !== 0 }"
                            type="button"
                            data-bs-toggle="collapse"
                            :data-bs-target="`#faq-collapse-${faq.id}`"
                            :aria-expanded="index === 0 ? 'true' : 'false'"
                            :aria-controls="`faq-collapse-${faq.id}`"
                        >
                            <span>{{ faq.question }}</span>
                            <span v-if="faq.category" class="badge rounded-pill text-bg-light ms-auto me-3">
                                {{ faq.category }}
                            </span>
                        </button>
                    </h3>
                    <div
                        :id="`faq-collapse-${faq.id}`"
                        class="accordion-collapse collapse"
                        :class="{ show: index === 0 }"
                        :aria-labelledby="`faq-heading-${faq.id}`"
                        data-bs-parent="#homepage-faq-accordion"
                    >
                        <div class="accordion-body text-secondary faq-answer">{{ faq.answer }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.faq-accordion {
    max-width: 860px;
}

.accordion-button {
    padding: 1.35rem 1.5rem;
}

.accordion-button:not(.collapsed) {
    color: var(--bs-primary);
    background: rgba(var(--bs-primary-rgb), 0.06);
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: inset 0 0 0 0.15rem rgba(var(--bs-primary-rgb), 0.18);
}

.faq-answer {
    padding: 1.25rem 1.5rem 1.5rem;
    line-height: 1.75;
    white-space: pre-line;
}
</style>
