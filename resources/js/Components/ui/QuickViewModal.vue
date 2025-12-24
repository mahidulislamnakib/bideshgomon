<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="close" class="relative z-50">
            <!-- Backdrop -->
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
            </TransitionChild>

            <!-- Panel Container -->
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            :class="[
                                'w-full transform overflow-hidden rounded-2xl bg-white dark:bg-neutral-800 shadow-2xl transition-all',
                                sizeClass
                            ]"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <component 
                                        v-if="icon" 
                                        :is="icon" 
                                        :class="['h-6 w-6', iconClass]" 
                                    />
                                    <div>
                                        <DialogTitle as="h3" class="text-lg font-semibold text-neutral-900 dark:text-white">
                                            {{ title }}
                                        </DialogTitle>
                                        <p v-if="subtitle" class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ subtitle }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="close"
                                    class="rounded-lg p-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Loading State -->
                            <div v-if="loading" class="p-8 flex items-center justify-center">
                                <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Content -->
                            <div v-else :class="['overflow-y-auto', contentClass]" :style="{ maxHeight: maxContentHeight }">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div 
                                v-if="$slots.footer || showDefaultFooter" 
                                class="flex items-center justify-end gap-3 border-t border-neutral-200 dark:border-neutral-700 px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50"
                            >
                                <slot name="footer">
                                    <button
                                        @click="close"
                                        class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                                    >
                                        Close
                                    </button>
                                    <Link
                                        v-if="viewFullHref"
                                        :href="viewFullHref"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                    >
                                        View Full Details
                                    </Link>
                                </slot>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    /** Control modal visibility */
    isOpen: {
        type: Boolean,
        default: false,
    },
    /** Modal title */
    title: {
        type: String,
        default: 'Quick View',
    },
    /** Optional subtitle */
    subtitle: {
        type: String,
        default: '',
    },
    /** Icon component */
    icon: {
        type: [Object, null],
        default: null,
    },
    /** Icon class */
    iconClass: {
        type: String,
        default: 'text-blue-500',
    },
    /** Modal size: sm, md, lg, xl, 2xl, full */
    size: {
        type: String,
        default: 'lg',
        validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value),
    },
    /** Loading state */
    loading: {
        type: Boolean,
        default: false,
    },
    /** Content class */
    contentClass: {
        type: String,
        default: 'p-6',
    },
    /** Max content height */
    maxContentHeight: {
        type: String,
        default: '60vh',
    },
    /** Show default footer with close button */
    showDefaultFooter: {
        type: Boolean,
        default: true,
    },
    /** View full details link */
    viewFullHref: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close', 'update:isOpen']);

const sizeClass = computed(() => {
    const sizes = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
        full: 'max-w-4xl',
    };
    return sizes[props.size] || sizes.lg;
});

function close() {
    emit('close');
    emit('update:isOpen', false);
}
</script>
