<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-gray-300 shadow-sm focus:border-upwork-green focus:ring-upwork-green transition-colors text-gray-900 placeholder-gray-400"
        v-model="model"
        ref="input"
    />
</template>
