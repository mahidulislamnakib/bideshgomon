<script setup>
import { computed } from 'vue'
import {
    XMarkIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowDownTrayIcon,
    SparklesIcon,
    NoSymbolIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    /**
     * Number of selected items
     */
    count: {
        type: Number,
        required: true,
    },
    /**
     * Label for the items (e.g., 'user', 'job', 'hotel')
     */
    itemLabel: {
        type: String,
        default: 'item',
    },
    /**
     * Available actions for bulk operations
     * @type {Array<{key: string, label: string, icon?: string, variant?: 'primary'|'success'|'warning'|'danger', handler: Function}>}
     */
    actions: {
        type: Array,
        default: () => [],
    },
    /**
     * Show the export button
     */
    showExport: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['clear', 'export'])

const itemText = computed(() => {
    return props.count === 1 ? props.itemLabel : `${props.itemLabel}s`
})

const getVariantClasses = (variant) => {
    const variants = {
        primary: 'bg-blue-600 hover:bg-blue-700 text-white',
        success: 'bg-green-600 hover:bg-green-700 text-white',
        warning: 'bg-yellow-600 hover:bg-yellow-700 text-white',
        danger: 'bg-red-600 hover:bg-red-700 text-white',
        default: 'bg-gray-600 hover:bg-gray-700 text-white',
    }
    return variants[variant] || variants.default
}

const iconComponents = {
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowDownTrayIcon,
    SparklesIcon,
    NoSymbolIcon,
}

const getIcon = (iconName) => {
    return iconComponents[iconName] || null
}
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="count > 0"
            class="sticky top-4 z-30 bg-indigo-50 dark:bg-indigo-900/40 backdrop-blur-sm border border-indigo-200 dark:border-indigo-700 rounded-2xl p-4 mb-6 shadow-lg"
        >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Selection Info -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-xl text-white font-bold text-sm">
                        {{ count }}
                    </div>
                    <span class="text-sm font-medium text-indigo-900 dark:text-indigo-100">
                        {{ count }} {{ itemText }} selected
                    </span>
                    <button
                        @click="emit('clear')"
                        class="text-indigo-600 dark:text-indigo-300 hover:text-indigo-800 dark:hover:text-indigo-100 text-sm font-medium underline underline-offset-2"
                    >
                        Clear
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Custom Actions -->
                    <button
                        v-for="action in actions"
                        :key="action.key"
                        @click="action.handler"
                        :class="[
                            'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow',
                            getVariantClasses(action.variant)
                        ]"
                    >
                        <component
                            v-if="action.icon && getIcon(action.icon)"
                            :is="getIcon(action.icon)"
                            class="h-4 w-4"
                        />
                        {{ action.label }}
                    </button>

                    <!-- Export Button -->
                    <button
                        v-if="showExport"
                        @click="emit('export')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4" />
                        Export
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
