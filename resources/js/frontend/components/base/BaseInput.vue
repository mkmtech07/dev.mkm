<script setup>
import { computed, useAttrs, useId } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);
const attrs = useAttrs();
const generatedId = useId();
const inputId = computed(() => attrs.id || `base-input-${generatedId}`);
const errorId = computed(() => `${inputId.value}-error`);

const updateValue = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div>
        <label v-if="label" class="form-label" :for="inputId">{{ label }}</label>
        <input
            v-bind="attrs"
            :id="inputId"
            :value="modelValue"
            :type="type"
            :placeholder="placeholder"
            class="form-control"
            :class="{ 'is-invalid': error }"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? errorId : undefined"
            @input="updateValue"
        >
        <div v-if="error" :id="errorId" class="invalid-feedback">
            {{ error }}
        </div>
    </div>
</template>
