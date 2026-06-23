<script setup>
import { computed } from 'vue';
import { siteSettings } from '../siteSettings';

const props = defineProps({
    phone: { type: String, default: '' },
    message: { type: String, default: 'Hello, I would like to know more.' },
});

const resolvedPhone = computed(() => (
    props.phone || siteSettings.whatsapp || ''
).replace(/[^0-9]/g, ''));
</script>

<template>
    <a
        v-if="resolvedPhone"
        class="whatsapp-button btn btn-success rounded-circle shadow"
        :href="`https://wa.me/${resolvedPhone}?text=${encodeURIComponent(message)}`"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Chat with us on WhatsApp"
    >
        <svg
            class="whatsapp-icon"
            viewBox="0 0 16 16"
            fill="currentColor"
            aria-hidden="true"
        >
            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93a7.898 7.898 0 0 0-2.327-5.607M7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.25a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.591-6.592 6.591m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.115.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.982-.59-.525-.986-1.175-1.102-1.372-.114-.198-.011-.305.088-.404.087-.087.197-.232.296-.346.1-.116.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.611-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.15.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
        </svg>
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
    padding: 0;
    color: #fff;
    background-color: #25d366;
    border-color: #25d366;
    transition: background-color 160ms ease, border-color 160ms ease, transform 160ms ease;
}

.whatsapp-button:hover,
.whatsapp-button:focus-visible {
    color: #fff;
    background-color: #1ebe5d;
    border-color: #1ebe5d;
    transform: translateY(-2px);
}

.whatsapp-icon {
    width: 1.75rem;
    height: 1.75rem;
}
</style>
