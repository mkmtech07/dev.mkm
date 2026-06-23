<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BlogCard from '../components/BlogCard.vue';
import BaseEmptyState from '../components/base/BaseEmptyState.vue';
import BaseLoader from '../components/base/BaseLoader.vue';
import { siteSettings } from '../siteSettings';

const route = useRoute();
const router = useRouter();
const blogs = ref([]);
const featuredBlogs = ref([]);
const categories = ref([]);
const loading = ref(true);
const errorMessage = ref('');
const searchInput = ref('');
const selectedCategory = ref('');
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
let requestController;

const loadBlogs = async () => {
    requestController?.abort();
    const controller = new AbortController();
    requestController = controller;
    loading.value = true;
    errorMessage.value = '';

    const params = new URLSearchParams();
    const search = String(route.query.search || '').trim();
    const category = String(route.query.category || '').trim();
    const page = Number(route.query.page || 1);

    searchInput.value = search;
    selectedCategory.value = category;

    if (search) params.set('search', search);
    if (category) params.set('category', category);
    if (page > 1) params.set('page', String(page));

    try {
        const response = await fetch(`/api/blogs?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        });

        if (! response.ok) {
            throw new Error('Unable to load blog posts right now.');
        }

        const payload = await response.json();
        blogs.value = Array.isArray(payload.data) ? payload.data : [];
        featuredBlogs.value = Array.isArray(payload.featured) ? payload.featured : [];
        categories.value = Array.isArray(payload.categories) ? payload.categories : [];
        pagination.value = payload.meta || { current_page: 1, last_page: 1, total: blogs.value.length };
    } catch (error) {
        if (error.name !== 'AbortError') {
            blogs.value = [];
            featuredBlogs.value = [];
            errorMessage.value = error.message || 'Unable to load blog posts right now.';
        }
    } finally {
        if (requestController === controller && ! controller.signal.aborted) {
            loading.value = false;
        }
    }
};

const applyFilters = () => {
    router.push({
        name: 'blog',
        query: {
            ...(searchInput.value.trim() ? { search: searchInput.value.trim() } : {}),
            ...(selectedCategory.value ? { category: selectedCategory.value } : {}),
        },
    });
};

const clearFilters = () => {
    searchInput.value = '';
    selectedCategory.value = '';
    router.push({ name: 'blog' });
};

const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) {
        return;
    }

    router.push({
        name: 'blog',
        query: {
            ...route.query,
            ...(page > 1 ? { page } : { page: undefined }),
        },
    });
};

watch(() => route.query, loadBlogs, { immediate: true });

onBeforeUnmount(() => requestController?.abort());
</script>

<template>
    <div>
        <section class="blog-hero py-5 text-white">
            <div class="container py-lg-4 text-center">
                <p class="text-uppercase fw-semibold opacity-75 mb-2">Insights &amp; Ideas</p>
                <h1 class="display-4 fw-bold mb-3">The {{ siteSettings.siteName }} Blog</h1>
                <p class="lead mx-auto mb-0 blog-intro">Practical ideas for simpler operations and smarter business growth.</p>
            </div>
        </section>

        <section class="section-padding bg-white">
            <div class="container">
                <form class="row g-3 justify-content-center mb-5" @submit.prevent="applyFilters">
                    <div class="col-lg-6">
                        <label class="visually-hidden" for="blog-search">Search articles</label>
                        <input
                            id="blog-search"
                            v-model="searchInput"
                            class="form-control form-control-lg"
                            type="search"
                            placeholder="Search articles"
                        >
                    </div>
                    <div class="col-sm-7 col-lg-3">
                        <label class="visually-hidden" for="blog-category">Category</label>
                        <select id="blog-category" v-model="selectedCategory" class="form-select form-select-lg">
                            <option value="">All categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.slug">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-lg" type="submit">Search</button>
                    </div>
                </form>

                <BaseLoader v-if="loading" />

                <div v-else-if="errorMessage" class="alert alert-danger text-center" role="alert">
                    {{ errorMessage }}
                </div>

                <template v-else>
                    <section v-if="featuredBlogs.length" class="mb-5 pb-lg-4" aria-labelledby="featured-posts-title">
                        <div class="d-flex align-items-end justify-content-between mb-4">
                            <div>
                                <p class="text-primary text-uppercase fw-semibold mb-1">Editor's picks</p>
                                <h2 id="featured-posts-title" class="display-6 fw-bold mb-0">Featured posts</h2>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div v-for="blog in featuredBlogs" :key="`featured-${blog.id}`" class="col-md-6 col-xl-4">
                                <BlogCard :blog="blog" />
                            </div>
                        </div>
                    </section>

                    <section aria-labelledby="latest-posts-title">
                        <div class="d-flex align-items-end justify-content-between mb-4">
                            <div>
                                <p class="text-primary text-uppercase fw-semibold mb-1">Fresh thinking</p>
                                <h2 id="latest-posts-title" class="display-6 fw-bold mb-0">Latest posts</h2>
                            </div>
                            <span v-if="pagination.total" class="text-secondary">{{ pagination.total }} articles</span>
                        </div>

                        <div v-if="blogs.length" class="row g-4">
                            <div v-for="blog in blogs" :key="blog.id" class="col-md-6 col-xl-4">
                                <BlogCard :blog="blog" />
                            </div>
                        </div>

                        <BaseEmptyState
                            v-else
                            title="No articles found"
                            message="Try another keyword or category."
                            button-text="Clear filters"
                            @action="clearFilters"
                        />

                        <nav v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-5" aria-label="Blog pagination">
                            <ul class="pagination mb-0">
                                <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                    <button class="page-link" type="button" @click="changePage(pagination.current_page - 1)">Previous</button>
                                </li>
                                <li
                                    v-for="page in pagination.last_page"
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: page === pagination.current_page }"
                                >
                                    <button class="page-link" type="button" @click="changePage(page)">{{ page }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                    <button class="page-link" type="button" @click="changePage(pagination.current_page + 1)">Next</button>
                                </li>
                            </ul>
                        </nav>
                    </section>
                </template>
            </div>
        </section>
    </div>
</template>

<style scoped>
.blog-hero {
    background: linear-gradient(135deg, var(--site-primary-color) 0%, #0d1b2a 100%);
}

.blog-intro {
    max-width: 44rem;
}
</style>
