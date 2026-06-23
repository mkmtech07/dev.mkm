<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import BaseEmptyState from './base/BaseEmptyState.vue';
import BaseLoader from './base/BaseLoader.vue';

const galleryItems = ref([]);
const selectedItem = ref(null);
const isLoading = ref(true);
let requestController = null;

const closeLightbox = () => {
    selectedItem.value = null;
};

const handleKeydown = (event) => {
    if (event.key === 'Escape' && selectedItem.value) {
        closeLightbox();
    }
};

const loadGallery = async () => {
    requestController = new AbortController();

    try {
        const response = await fetch('/api/gallery', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error(`Gallery request failed with status ${response.status}`);
        }

        const payload = await response.json();
        galleryItems.value = Array.isArray(payload.data) ? payload.data : [];
    } catch (error) {
        if (error.name !== 'AbortError') {
            galleryItems.value = [];
        }
    } finally {
        isLoading.value = false;
    }
};

watch(selectedItem, (item) => {
    document.body.classList.toggle('modal-open', Boolean(item));
});

onMounted(() => {
    loadGallery();
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    requestController?.abort();
    window.removeEventListener('keydown', handleKeydown);
    document.body.classList.remove('modal-open');
});
</script>

<template>
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <p class="text-primary text-uppercase fw-semibold mb-2">Gallery</p>
                <h1 class="display-5 fw-bold">A glimpse at the work</h1>
            </div>

            <BaseLoader v-if="isLoading" />

            <BaseEmptyState
                v-else-if="galleryItems.length === 0"
                title="No gallery images yet"
                message="Please check back soon for new work and updates."
            />

            <div v-else class="row g-4">
                <div
                    v-for="item in galleryItems"
                    :key="item.id"
                    class="col-sm-6 col-lg-4"
                >
                    <button
                        class="gallery-card card h-100 w-100 overflow-hidden border-0 p-0 text-start shadow-sm"
                        type="button"
                        :aria-label="`Open ${item.title} in lightbox`"
                        @click="selectedItem = item"
                    >
                        <img
                            :src="item.image_url"
                            class="card-img-top gallery-image"
                            :alt="item.alt_text || item.title"
                            loading="lazy"
                        >
                        <span class="card-body d-block">
                            <small v-if="item.category" class="text-primary text-uppercase fw-semibold">
                                {{ item.category }}
                            </small>
                            <span class="h5 d-block mt-1 mb-0">{{ item.title }}</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <Teleport to="body">
        <template v-if="selectedItem">
            <div
                class="modal fade show d-block"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
                :aria-label="selectedItem.title"
                @click.self="closeLightbox"
            >
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content overflow-hidden border-0 bg-dark shadow-lg">
                        <div class="modal-header border-0 text-white">
                            <div>
                                <small v-if="selectedItem.category" class="text-info text-uppercase fw-semibold">
                                    {{ selectedItem.category }}
                                </small>
                                <h2 class="h5 mb-0">{{ selectedItem.title }}</h2>
                            </div>
                            <button
                                class="btn-close btn-close-white"
                                type="button"
                                aria-label="Close lightbox"
                                @click="closeLightbox"
                            ></button>
                        </div>
                        <div class="modal-body p-0 text-center">
                            <img
                                :src="selectedItem.image_url"
                                class="lightbox-image"
                                :alt="selectedItem.alt_text || selectedItem.title"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        </template>
    </Teleport>
</template>

<style scoped>
.gallery-card {
    appearance: none;
    color: inherit;
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.gallery-card:hover,
.gallery-card:focus-visible {
    transform: translateY(-0.25rem);
    box-shadow: 0 1rem 2rem rgba(13, 27, 42, 0.14) !important;
}

.gallery-card:focus-visible {
    outline: 0.2rem solid rgba(var(--bs-primary-rgb), 0.35);
    outline-offset: 0.2rem;
}

.gallery-image {
    height: 16rem;
    object-fit: cover;
}

.lightbox-image {
    display: block;
    width: 100%;
    max-height: calc(100vh - 8rem);
    object-fit: contain;
}

@media (prefers-reduced-motion: reduce) {
    .gallery-card {
        transition: none;
    }
}
</style>
