<template>
    <div :class="[utilityClasses.card, 'chart-card']">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900">{{ title }}</h3>
                <p v-if="subtitle" class="text-sm text-neutral-500 mt-1">{{ subtitle }}</p>
            </div>

            <!-- Actions Dropdown -->
            <div v-if="actions && actions.length > 0" class="relative">
                <button
                    @click="showActions = !showActions"
                    class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                >
                    <EllipsisVerticalIcon class="h-5 w-5 text-neutral-500" />
                </button>

                <div
                    v-if="showActions"
                    v-click-outside="() => showActions = false"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-neutral-200 py-2 z-20"
                >
                    <button
                        v-for="action in actions"
                        :key="action.label"
                        @click="handleAction(action)"
                        class="w-full text-left px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-50 transition-colors"
                    >
                        {{ action.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Chart Area (Slot for flexibility) -->
        <div class="chart-container" :style="{ height: `${height}px` }">
            <slot name="chart">
                <!-- Default empty state if no chart provided -->
                <div class="flex items-center justify-center h-full text-neutral-400">
                    <ChartBarIcon class="h-16 w-16" />
                </div>
            </slot>
        </div>

        <!-- Legend (if provided) -->
        <div v-if="legend && legend.length > 0" class="mt-4 pt-4 border-t border-neutral-200">
            <div class="flex flex-wrap gap-4">
                <div
                    v-for="item in legend"
                    :key="item.label"
                    class="flex items-center gap-2"
                >
                    <div
                        class="w-3 h-3 rounded-full"
                        :style="{ backgroundColor: item.color }"
                    ></div>
                    <span class="text-sm text-neutral-600">{{ item.label }}</span>
                    <span v-if="item.value" class="text-sm font-semibold text-neutral-900">
                        {{ item.value }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer Stats (optional) -->
        <div v-if="footerStats && footerStats.length > 0" class="mt-4 pt-4 border-t border-neutral-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                    v-for="stat in footerStats"
                    :key="stat.label"
                    class="text-center"
                >
                    <p class="text-2xl font-bold text-neutral-900">{{ stat.value }}</p>
                    <p class="text-xs text-neutral-500 mt-1">{{ stat.label }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { EllipsisVerticalIcon, ChartBarIcon } from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    height: {
        type: Number,
        default: 300 // Chart height in pixels
    },
    legend: {
        type: Array,
        default: () => [] // [{ label: 'Series 1', color: '#e4282b', value: '123' }]
    },
    footerStats: {
        type: Array,
        default: () => [] // [{ label: 'Total', value: '1,234' }]
    },
    actions: {
        type: Array,
        default: () => [] // [{ label: 'Download', value: 'download' }]
    }
});

const emit = defineEmits(['action']);

const showActions = ref(false);

const handleAction = (action) => {
    emit('action', action.value);
    showActions.value = false;
};

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    }
};
</script>

