<script setup>
defineProps({
    phone: { type: String, default: '919999999999' },
    message: { type: String, default: 'Hello, I would like to know more.' },
});
</script>

<template>
    <a
        class="whatsapp-button btn btn-success rounded-circle shadow"
        :href="`https://wa.me/${phone}?text=${encodeURIComponent(message)}`"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Chat with us on WhatsApp"
    >
        <span aria-hidden="true">WA</span>
    </a>
</template>

<style scoped>
.whatsapp-button {
    position: fixed;
    right: 1.5rem;
    bottom: 1.5rem;
    z-index: 1030;
    display: grid;
    width: 3.5rem;
    height: 3.5rem;
    place-items: center;
    font-size: 0.8rem;
    font-weight: 800;
}
</style>
