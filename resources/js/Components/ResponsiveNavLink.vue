<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    iconClass: {
        type: String,
        default: 'text-gray-400',
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-upwork-green text-start text-base font-medium text-upwork-green bg-upwork-lightest focus:outline-none transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-upwork-dark hover:bg-gray-50 hover:border-upwork-green/50 focus:outline-none focus:text-upwork-dark focus:bg-gray-50 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <div class="flex items-center gap-3">
            <span v-if="$slots.icon" :class="iconClass">
                <slot name="icon" />
            </span>
            <slot />
        </div>
    </Link>
</template>
