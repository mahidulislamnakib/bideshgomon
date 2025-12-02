<template>
    <div 
        class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer border-l-4"
        :class="borderColorClass"
        @click="navigateTo"
    >
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <component :is="icon" :class="iconColorClass" class="h-6 w-6 mr-2" />
                        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ description }}</p>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold" :class="textColorClass">{{ count }}</span>
                        <span class="ml-2 text-sm text-gray-500">records</span>
                    </div>
                </div>
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { FolderIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    count: {
        type: Number,
        default: 0
    },
    icon: {
        type: Object,
        default: () => FolderIcon
    },
    description: {
        type: String,
        default: ''
    },
    route: {
        type: String,
        required: true
    },
    color: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'purple', 'yellow', 'red', 'indigo'].includes(value)
    }
});

const borderColorClass = computed(() => {
    const colors = {
        blue: 'border-blue-500',
        green: 'border-green-500',
        purple: 'border-purple-500',
        yellow: 'border-yellow-500',
        red: 'border-red-500',
        indigo: 'border-indigo-500'
    };
    return colors[props.color] || colors.blue;
});

const iconColorClass = computed(() => {
    const colors = {
        blue: 'text-brand-red-600',
        green: 'text-green-600',
        purple: 'text-purple-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        indigo: 'text-brand-red-600'
    };
    return colors[props.color] || colors.blue;
});

const textColorClass = computed(() => {
    const colors = {
        blue: 'text-brand-red-600',
        green: 'text-green-600',
        purple: 'text-purple-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        indigo: 'text-brand-red-600'
    };
    return colors[props.color] || colors.blue;
});

const navigateTo = () => {
    if (props.route && props.route !== '#') {
        router.visit(props.route);
    }
};
</script>
