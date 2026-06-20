<script setup>
const posts = [
    { date: 'June 12, 2026', title: 'Five small ways to simplify daily billing', excerpt: 'Practical changes that can save your team time every week.' },
    { date: 'May 28, 2026', title: 'What useful business reporting looks like', excerpt: 'Start with a few clear signals instead of a wall of numbers.' },
    { date: 'May 10, 2026', title: 'A calmer approach to inventory management', excerpt: 'Build a reliable stock routine without adding unnecessary process.' },
];
</script>

<template>
    <section class="section-padding bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <p class="text-primary text-uppercase fw-semibold">Blog</p>
                <h1 class="display-5 fw-bold">Ideas for better business</h1>
            </div>
            <div class="row g-4">
                <div v-for="post in posts" :key="post.title" class="col-md-4">
                    <article class="card h-100 border-0 bg-light">
                        <div class="card-body p-4">
                            <small class="text-secondary">{{ post.date }}</small>
                            <h2 class="h4 mt-3">{{ post.title }}</h2>
                            <p class="text-secondary mb-0">{{ post.excerpt }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>
