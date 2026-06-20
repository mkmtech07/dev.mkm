<script setup>
import BaseButton from './BaseButton.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
    buttonText: {
        type: String,
        default: '',
    },
});

defineEmits(['action']);
</script>

<template>
    <div class="rounded-3 border bg-light p-5 text-center">
        <h2 class="h4 mb-2">{{ title }}</h2>
        <p v-if="message" class="text-body-secondary mb-4">{{ message }}</p>
        <BaseButton v-if="buttonText" @click="$emit('action')">
            {{ buttonText }}
        </BaseButton>
    </div>
</template>
