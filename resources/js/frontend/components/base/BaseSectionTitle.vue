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
    headingTag: {
        type: String,
        default: 'h2',
        validator: (value) => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(value),
    },
});
</script>

<template>
    <header>
        <p v-if="eyebrow" class="text-primary text-uppercase fw-semibold mb-2">
            {{ eyebrow }}
        </p>
        <component :is="headingTag" class="display-6 fw-bold mb-3">{{ title }}</component>
        <p v-if="description" class="lead text-body-secondary mb-0">
            {{ description }}
        </p>
    </header>
</template>
