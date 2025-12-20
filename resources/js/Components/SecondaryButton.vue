<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-full border-2 border-upwork-green bg-white px-5 py-2.5 text-sm font-semibold text-upwork-green shadow-sm transition duration-150 ease-in-out hover:bg-upwork-lightest focus:outline-none focus:ring-2 focus:ring-upwork-green focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
