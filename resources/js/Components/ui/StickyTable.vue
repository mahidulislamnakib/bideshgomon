<template>
    <div :class="['overflow-hidden', containerClass]">
        <div 
            ref="scrollContainer" 
            :class="['overflow-auto', scrollContainerClass]"
            :style="maxHeightStyle"
            @scroll="handleScroll"
        >
            <table :class="['min-w-full divide-y divide-neutral-200 dark:divide-neutral-700', tableClass]">
                <!-- Sticky Header -->
                <thead 
                    :class="[
                        'bg-neutral-50 dark:bg-neutral-700',
                        stickyHeader ? 'sticky top-0 z-10' : '',
                        isScrolled && stickyHeader ? 'shadow-sm' : '',
                        headerClass
                    ]"
                >
                    <slot name="header" />
                </thead>
                
                <!-- Table Body -->
                <tbody 
                    :class="[
                        'bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700',
                        bodyClass
                    ]"
                >
                    <slot name="body" />
                    
                    <!-- Empty State -->
                    <tr v-if="isEmpty">
                        <td :colspan="columnCount" class="px-6 py-12 text-center">
                            <slot name="empty">
                                <div class="flex flex-col items-center justify-center">
                                    <component 
                                        v-if="emptyIcon" 
                                        :is="emptyIcon" 
                                        class="h-12 w-12 text-neutral-400 mb-4" 
                                    />
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ emptyMessage }}
                                    </p>
                                    <slot name="empty-action" />
                                </div>
                            </slot>
                        </td>
                    </tr>
                    
                    <!-- Loading State -->
                    <tr v-if="loading">
                        <td :colspan="columnCount" class="px-6 py-8 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <svg class="animate-spin h-5 w-5 text-neutral-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
                
                <!-- Optional Footer -->
                <tfoot v-if="$slots.footer">
                    <slot name="footer" />
                </tfoot>
            </table>
        </div>
        
        <!-- Scroll to Top Button -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <button
                v-if="showScrollTop && scrollTop > 200"
                @click="scrollToTop"
                class="absolute bottom-4 right-4 p-2 bg-neutral-800 dark:bg-neutral-600 text-white rounded-full shadow-lg hover:bg-neutral-700 dark:hover:bg-neutral-500 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500"
                title="Scroll to top"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </button>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    /** Enable sticky header */
    stickyHeader: {
        type: Boolean,
        default: true,
    },
    /** Max height for scrollable area (e.g., '500px', '70vh') */
    maxHeight: {
        type: String,
        default: null,
    },
    /** Number of columns (for empty/loading colspan) */
    columnCount: {
        type: Number,
        default: 5,
    },
    /** Show empty state */
    isEmpty: {
        type: Boolean,
        default: false,
    },
    /** Empty state message */
    emptyMessage: {
        type: String,
        default: 'No data available',
    },
    /** Empty state icon component */
    emptyIcon: {
        type: [Object, null],
        default: null,
    },
    /** Show loading state */
    loading: {
        type: Boolean,
        default: false,
    },
    /** Show scroll to top button */
    showScrollTop: {
        type: Boolean,
        default: true,
    },
    /** Container class */
    containerClass: {
        type: String,
        default: 'relative rounded-xl border border-neutral-200 dark:border-neutral-700',
    },
    /** Scroll container class */
    scrollContainerClass: {
        type: String,
        default: '',
    },
    /** Table class */
    tableClass: {
        type: String,
        default: '',
    },
    /** Header class */
    headerClass: {
        type: String,
        default: '',
    },
    /** Body class */
    bodyClass: {
        type: String,
        default: '',
    },
});

const scrollContainer = ref(null);
const scrollTop = ref(0);
const isScrolled = ref(false);

const maxHeightStyle = computed(() => {
    if (!props.maxHeight) return {};
    return { maxHeight: props.maxHeight };
});

function handleScroll(event) {
    scrollTop.value = event.target.scrollTop;
    isScrolled.value = scrollTop.value > 0;
}

function scrollToTop() {
    if (scrollContainer.value) {
        scrollContainer.value.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}
</script>
