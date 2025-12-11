<template>
    <div :class="[utilityClasses.card, 'settings-section overflow-hidden']">
        <!-- Section Header (Clickable) -->
        <button
            type="button"
            @click="$emit('toggle')"
            class="w-full flex items-center justify-between p-6 hover:bg-neutral-50 transition-colors"
        >
            <div class="flex items-center gap-4">
                <!-- Icon -->
                <div :class="[
                    'flex-shrink-0 p-3 rounded-lg',
                    iconBackgroundColor
                ]">
                    <component :is="icon" :class="['h-6 w-6', iconColor]" />
                </div>

                <!-- Title & Description -->
                <div class="text-left">
                    <h3 class="text-lg font-semibold text-neutral-900">{{ title }}</h3>
                    <p v-if="description" class="text-sm text-neutral-500 mt-1">{{ description }}</p>
                </div>
            </div>

            <!-- Expand/Collapse Chevron -->
            <ChevronDownIcon
                :class="[
                    'h-5 w-5 text-neutral-400 transition-transform duration-200',
                    expanded ? 'rotate-180' : ''
                ]"
            />
        </button>

        <!-- Section Content (Collapsible) -->
        <Transition
            name="slide-down"
            @enter="onEnter"
            @after-enter="onAfterEnter"
            @leave="onLeave"
        >
            <div v-show="expanded" class="border-t border-neutral-200">
                <div class="p-6">
                    <slot></slot>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';

const props = defineProps({
    icon: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    expanded: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: 'primary', // primary, success, warning, danger, neutral
        validator: (value) => ['primary', 'success', 'warning', 'danger', 'neutral'].includes(value)
    }
});

defineEmits(['toggle']);

const iconBackgroundColor = computed(() => {
    const colors = {
        primary: 'bg-primary-100',
        success: 'bg-success-100',
        warning: 'bg-yellow-100',
        danger: 'bg-red-100',
        neutral: 'bg-neutral-100'
    };
    return colors[props.color];
});

const iconColor = computed(() => {
    const colors = {
        primary: 'text-primary-600',
        success: 'text-success-600',
        warning: 'text-yellow-600',
        danger: 'text-red-600',
        neutral: 'text-neutral-600'
    };
    return colors[props.color];
});

// Transition handlers
const onEnter = (el) => {
    el.style.height = '0';
    el.offsetHeight; // Force reflow
    el.style.height = el.scrollHeight + 'px';
};

const onAfterEnter = (el) => {
    el.style.height = 'auto';
};

const onLeave = (el) => {
    el.style.height = el.scrollHeight + 'px';
    el.offsetHeight; // Force reflow
    el.style.height = '0';
};
</script>

