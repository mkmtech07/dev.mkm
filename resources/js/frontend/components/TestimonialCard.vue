<script setup>
defineProps({
    quote: { type: String, required: true },
    name: { type: String, required: true },
    role: { type: String, default: 'Customer' },
});
</script>

<template>
    <figure class="card h-100 border-0 shadow-sm mb-0">
        <div class="card-body p-4">
            <blockquote class="blockquote fs-6">“{{ quote }}”</blockquote>
            <figcaption class="blockquote-footer mb-0 mt-3">
                {{ name }}, <cite>{{ role }}</cite>
            </figcaption>
        </div>
    </figure>
</template>
