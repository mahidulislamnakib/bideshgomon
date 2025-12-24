<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
    >
        <div 
            v-if="visible"
            :class="[
                'inline-flex items-center gap-2 text-sm',
                isSaving ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-500 dark:text-neutral-400'
            ]"
        >
            <!-- Saving indicator -->
            <template v-if="isSaving">
                <svg 
                    class="w-4 h-4 animate-spin" 
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
                <span>Saving...</span>
            </template>
            
            <!-- Saved indicator -->
            <template v-else-if="lastSaved">
                <svg 
                    class="w-4 h-4 text-green-500 dark:text-green-400" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M5 13l4 4L19 7" 
                    />
                </svg>
                <span>{{ displayText }}</span>
            </template>
            
            <!-- Restored data banner -->
            <div 
                v-if="showRestoreBanner && hasRestoredData"
                class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg border border-blue-200 dark:border-blue-800"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Previous draft restored</span>
                <button 
                    @click="$emit('discard')"
                    class="ml-2 text-xs underline hover:no-underline focus:outline-none"
                >
                    Discard
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** Is currently saving */
    isSaving: {
        type: Boolean,
        default: false,
    },
    /** Last saved timestamp */
    lastSaved: {
        type: [Date, null],
        default: null,
    },
    /** Time since last save text */
    timeSinceLastSave: {
        type: [String, null],
        default: null,
    },
    /** Has restored data from previous session */
    hasRestoredData: {
        type: Boolean,
        default: false,
    },
    /** Show the restore banner */
    showRestoreBanner: {
        type: Boolean,
        default: true,
    },
    /** Custom saved text */
    savedText: {
        type: String,
        default: 'Draft saved',
    },
});

defineEmits(['discard']);

const visible = computed(() => props.isSaving || props.lastSaved || props.hasRestoredData);

const displayText = computed(() => {
    if (props.timeSinceLastSave) {
        return `${props.savedText} ${props.timeSinceLastSave}`;
    }
    return props.savedText;
});
</script>
