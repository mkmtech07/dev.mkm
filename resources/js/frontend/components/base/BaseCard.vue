<script setup>
defineProps({
    title: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <article class="card h-100 border-0 shadow-sm">
        <div class="card-body p-4">
            <header v-if="title || subtitle" class="mb-3">
                <h2 v-if="title" class="card-title h5 mb-1">{{ title }}</h2>
                <p v-if="subtitle" class="card-subtitle text-body-secondary">{{ subtitle }}</p>
            </header>

            <slot />
        </div>
    </article>
</template>
