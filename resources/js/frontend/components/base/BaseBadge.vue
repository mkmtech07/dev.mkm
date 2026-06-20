<script setup>
import { computed } from 'vue';

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'success', 'warning', 'danger', 'light'].includes(value),
    },
});

const badgeClasses = computed(() => ({
    'text-bg-primary': props.variant === 'primary',
    'text-bg-success': props.variant === 'success',
    'text-bg-warning': props.variant === 'warning',
    'text-bg-danger': props.variant === 'danger',
    'text-bg-light': props.variant === 'light',
}));
</script>

<template>
    <span class="badge rounded-pill" :class="badgeClasses">{{ text }}</span>
</template>
