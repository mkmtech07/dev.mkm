<script setup>
import { computed } from 'vue';

defineOptions({ name: 'MenuNavItem' });

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    root: {
        type: Boolean,
        default: false,
    },
});

const children = computed(() => Array.isArray(props.item.children) ? props.item.children : []);
const hasChildren = computed(() => children.value.length > 0);
const isInternal = computed(() => (
    typeof props.item.url === 'string'
    && props.item.url.startsWith('/')
    && ! props.item.url.startsWith('//')
));
const linkClass = computed(() => props.root ? 'nav-link' : 'dropdown-item');
</script>

<template>
    <li :class="[root ? 'nav-item' : '', hasChildren ? (root ? 'dropdown' : 'dropend') : '']">
        <a
            v-if="hasChildren"
            :class="[linkClass, 'dropdown-toggle']"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i v-if="item.icon" :class="[item.icon, 'me-2']" aria-hidden="true"></i>
            {{ item.title }}
        </a>

        <RouterLink
            v-else-if="isInternal && item.target !== '_blank'"
            :class="linkClass"
            :to="item.url"
        >
            <i v-if="item.icon" :class="[item.icon, 'me-2']" aria-hidden="true"></i>
            {{ item.title }}
        </RouterLink>

        <a
            v-else
            :class="linkClass"
            :href="item.url"
            :target="item.target || '_self'"
            :rel="item.target === '_blank' ? 'noopener noreferrer' : null"
        >
            <i v-if="item.icon" :class="[item.icon, 'me-2']" aria-hidden="true"></i>
            {{ item.title }}
        </a>

        <ul v-if="hasChildren" class="dropdown-menu shadow-sm">
            <MenuNavItem
                v-for="child in children"
                :key="child.id || `${child.title}-${child.url}`"
                :item="child"
            />
        </ul>
    </li>
</template>

<style scoped>
.dropend > .dropdown-menu {
    left: 100%;
    margin-left: 0.125rem;
    margin-top: -0.5rem;
    top: 0;
}

.dropdown-item.router-link-active {
    background-color: var(--bs-primary-bg-subtle);
    color: var(--bs-primary-text-emphasis);
}
</style>
