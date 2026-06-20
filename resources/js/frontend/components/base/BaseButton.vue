<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'outline', 'danger'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const buttonClasses = computed(() => ({
    'btn-primary': props.variant === 'primary',
    'btn-secondary': props.variant === 'secondary',
    'btn-outline-primary': props.variant === 'outline',
    'btn-danger': props.variant === 'danger',
    'btn-sm': props.size === 'sm',
    'btn-lg': props.size === 'lg',
}));
</script>

<template>
    <button
        :type="type"
        class="btn"
        :class="buttonClasses"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
