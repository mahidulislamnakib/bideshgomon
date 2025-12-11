<template>
    <div class="nav-group">
        <!-- Group Header (Collapsible Trigger) -->
        <button
            @click="toggleOpen"
            :class="[
                'nav-group-header flex items-center gap-3 w-full px-3 py-2.5 rounded-lg transition-all duration-200 group',
                {
                    'text-primary-700 bg-primary-50': isOpen && !collapsed,
                    'text-neutral-700 hover:bg-neutral-100': !isOpen || collapsed,
                    'justify-center': collapsed
                }
            ]"
            :title="collapsed ? label : ''"
        >
            <!-- Icon -->
            <component
                :is="icon"
                :class="[
                    'flex-shrink-0 transition-colors',
                    isOpen && !collapsed ? 'text-primary-600' : 'text-neutral-500 group-hover:text-neutral-700',
                    collapsed ? 'h-6 w-6' : 'h-5 w-5'
                ]"
            />

            <!-- Label -->
            <span
                v-if="!collapsed"
                class="flex-1 text-sm font-medium text-left transition-colors"
            >
                {{ label }}
            </span>

            <!-- Chevron -->
            <ChevronDownIcon
                v-if="!collapsed"
                :class="[
                    'h-4 w-4 transition-transform duration-200',
                    isOpen ? 'transform rotate-180' : '',
                    isOpen ? 'text-primary-600' : 'text-neutral-400'
                ]"
            />
        </button>

        <!-- Group Items (Expandable) -->
        <div
            v-if="!collapsed"
            v-show="isOpen"
            class="nav-group-items mt-1 space-y-1"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    icon: {
        type: Object,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    collapsed: {
        type: Boolean,
        default: false
    },
    defaultOpen: {
        type: Boolean,
        default: false
    }
});

const isOpen = ref(props.defaultOpen);
let transitionInProgress = false;

const toggleOpen = () => {
    if (!props.collapsed && !transitionInProgress) {
        transitionInProgress = true;
        isOpen.value = !isOpen.value;
        setTimeout(() => {
            transitionInProgress = false;
        }, 250); // Match animation duration
    }
};

// When sidebar becomes collapsed, close all groups
watch(() => props.collapsed, (newValue) => {
    if (transitionInProgress) return;
    
    if (newValue) {
        isOpen.value = false;
    } else if (props.defaultOpen) {
        setTimeout(() => {
            isOpen.value = true;
        }, 50); // Small delay after sidebar expands
    }
});

// Re-open if defaultOpen changes (e.g., route change)
watch(() => props.defaultOpen, (newValue) => {
    if (newValue && !props.collapsed) {
        isOpen.value = true;
    }
});
</script>

