<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <header>
        <p v-if="eyebrow" class="text-primary text-uppercase fw-semibold mb-2">
            {{ eyebrow }}
        </p>
        <h2 class="display-6 fw-bold mb-3">{{ title }}</h2>
        <p v-if="description" class="lead text-body-secondary mb-0">
            {{ description }}
        </p>
    </header>
</template>
