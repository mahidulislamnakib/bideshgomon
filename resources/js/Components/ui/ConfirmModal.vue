<script setup>
import { ref, watch } from 'vue'
import {
    ExclamationTriangleIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    InformationCircleIcon,
    QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    /**
     * Whether the modal is visible
     */
    show: {
        type: Boolean,
        default: false,
    },
    /**
     * Modal type determines styling
     * @type {'danger' | 'warning' | 'info' | 'success'}
     */
    type: {
        type: String,
        default: 'danger',
    },
    /**
     * Modal title
     */
    title: {
        type: String,
        default: 'Confirm Action',
    },
    /**
     * Modal message/description
     */
    message: {
        type: String,
        default: 'Are you sure you want to proceed?',
    },
    /**
     * Confirm button text
     */
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    /**
     * Cancel button text
     */
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    /**
     * Whether the action is processing
     */
    processing: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['confirm', 'cancel', 'update:show'])

const close = () => {
    emit('update:show', false)
    emit('cancel')
}

const confirm = () => {
    emit('confirm')
}

// Handle escape key
const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.show) {
        close()
    }
}

watch(() => props.show, (show) => {
    if (show) {
        document.addEventListener('keydown', handleKeydown)
        document.body.style.overflow = 'hidden'
    } else {
        document.removeEventListener('keydown', handleKeydown)
        document.body.style.overflow = ''
    }
})

const getIcon = () => {
    const icons = {
        danger: TrashIcon,
        warning: ExclamationTriangleIcon,
        info: InformationCircleIcon,
        success: CheckCircleIcon,
    }
    return icons[props.type] || QuestionMarkCircleIcon
}

const getStyles = () => {
    const styles = {
        danger: {
            iconBg: 'bg-red-100 dark:bg-red-900/30',
            icon: 'text-red-600 dark:text-red-400',
            button: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        },
        warning: {
            iconBg: 'bg-yellow-100 dark:bg-yellow-900/30',
            icon: 'text-yellow-600 dark:text-yellow-400',
            button: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
        },
        info: {
            iconBg: 'bg-blue-100 dark:bg-blue-900/30',
            icon: 'text-blue-600 dark:text-blue-400',
            button: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        },
        success: {
            iconBg: 'bg-green-100 dark:bg-green-900/30',
            icon: 'text-green-600 dark:text-green-400',
            button: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        },
    }
    return styles[props.type] || styles.danger
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[9999] overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity"
                    @click="close"
                />

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-4"
                    >
                        <div
                            v-if="show"
                            class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-neutral-800 text-left shadow-xl transition-all w-full max-w-md"
                        >
                            <!-- Content -->
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <!-- Icon -->
                                    <div
                                        :class="[
                                            'flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl',
                                            getStyles().iconBg
                                        ]"
                                    >
                                        <component
                                            :is="getIcon()"
                                            :class="['h-6 w-6', getStyles().icon]"
                                        />
                                    </div>

                                    <!-- Text -->
                                    <div class="flex-1">
                                        <h3
                                            id="modal-title"
                                            class="text-lg font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ title }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ message }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="px-6 py-4 bg-gray-50 dark:bg-neutral-900/50 flex flex-row-reverse gap-3">
                                <button
                                    type="button"
                                    :disabled="processing"
                                    @click="confirm"
                                    :class="[
                                        'inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed',
                                        getStyles().button
                                    ]"
                                >
                                    <svg
                                        v-if="processing"
                                        class="animate-spin -ml-1 mr-2 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        />
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                    {{ confirmText }}
                                </button>
                                <button
                                    type="button"
                                    :disabled="processing"
                                    @click="close"
                                    class="inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all disabled:opacity-50"
                                >
                                    {{ cancelText }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
