<template>
    <div :class="['relative', containerClass]">
        <!-- Label -->
        <label
            v-if="label"
            :for="inputId"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        
        <Popover class="relative" v-slot="{ open }">
            <PopoverButton
                :disabled="disabled"
                :class="[
                    'w-full flex items-center gap-3 px-3 py-2 rounded-lg border transition-all duration-200',
                    'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
                    error 
                        ? 'border-red-300 dark:border-red-600' 
                        : 'border-gray-300 dark:border-gray-600',
                    disabled 
                        ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' 
                        : 'bg-white dark:bg-gray-900 cursor-pointer hover:border-gray-400 dark:hover:border-gray-500',
                    open ? 'ring-2 ring-primary-500' : ''
                ]"
            >
                <!-- Color Preview -->
                <div
                    :style="{ backgroundColor: modelValue || '#ffffff' }"
                    :class="[
                        'w-8 h-8 rounded-md border border-gray-200 dark:border-gray-600',
                        'shadow-inner'
                    ]"
                />
                
                <!-- Color Value -->
                <span class="flex-1 text-left font-mono text-sm text-gray-700 dark:text-gray-300">
                    {{ modelValue || placeholder }}
                </span>
                
                <!-- Dropdown Icon -->
                <ChevronDownIcon
                    :class="[
                        'h-5 w-5 text-gray-400 transition-transform duration-200',
                        open ? 'rotate-180' : ''
                    ]"
                />
            </PopoverButton>
            
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-1 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-1 opacity-0"
            >
                <PopoverPanel
                    :class="[
                        'absolute z-50 mt-2 w-72 rounded-xl',
                        'bg-white dark:bg-gray-800 shadow-xl',
                        'ring-1 ring-black ring-opacity-5 dark:ring-gray-700',
                        'p-4',
                        position === 'left' ? 'left-0' : 'right-0'
                    ]"
                >
                    <!-- Preset Colors -->
                    <div v-if="showPresets" class="mb-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            Preset Colors
                        </p>
                        <div class="grid grid-cols-8 gap-1.5">
                            <button
                                v-for="color in presetColors"
                                :key="color"
                                @click="selectColor(color)"
                                :style="{ backgroundColor: color }"
                                :class="[
                                    'w-6 h-6 rounded-md border transition-all duration-150',
                                    'hover:scale-110 hover:shadow-md',
                                    'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
                                    modelValue === color 
                                        ? 'ring-2 ring-primary-500 ring-offset-1' 
                                        : 'border-gray-200 dark:border-gray-600'
                                ]"
                                :title="color"
                            />
                        </div>
                    </div>
                    
                    <!-- Color Picker -->
                    <div class="mb-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            Custom Color
                        </p>
                        <div class="flex items-center gap-3">
                            <input
                                type="color"
                                :value="modelValue || '#000000'"
                                @input="handleColorInput"
                                class="w-12 h-12 rounded-lg cursor-pointer border-0 p-0"
                            />
                            <input
                                type="text"
                                :value="modelValue"
                                @input="handleTextInput"
                                placeholder="#000000"
                                :class="[
                                    'flex-1 px-3 py-2 rounded-lg border font-mono text-sm',
                                    'border-gray-300 dark:border-gray-600',
                                    'bg-white dark:bg-gray-900',
                                    'text-gray-700 dark:text-gray-300',
                                    'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent'
                                ]"
                            />
                        </div>
                    </div>
                    
                    <!-- Alpha/Opacity Slider (optional) -->
                    <div v-if="showAlpha" class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Opacity
                            </p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ Math.round(alpha * 100) }}%
                            </span>
                        </div>
                        <input
                            type="range"
                            :value="alpha * 100"
                            @input="handleAlphaInput"
                            min="0"
                            max="100"
                            class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                        />
                    </div>
                    
                    <!-- Recent Colors -->
                    <div v-if="recentColors.length > 0" class="mb-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            Recent Colors
                        </p>
                        <div class="flex gap-1.5">
                            <button
                                v-for="color in recentColors"
                                :key="color"
                                @click="selectColor(color)"
                                :style="{ backgroundColor: color }"
                                :class="[
                                    'w-6 h-6 rounded-md border transition-all duration-150',
                                    'hover:scale-110',
                                    'focus:outline-none focus:ring-2 focus:ring-primary-500',
                                    'border-gray-200 dark:border-gray-600'
                                ]"
                                :title="color"
                            />
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button
                            v-if="clearable && modelValue"
                            @click="clearColor"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            Clear
                        </button>
                        <div v-else />
                        
                        <button
                            @click="copyColor"
                            :class="[
                                'inline-flex items-center gap-1 text-sm',
                                copied ? 'text-green-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                            ]"
                        >
                            <ClipboardDocumentIcon v-if="!copied" class="h-4 w-4" />
                            <CheckIcon v-else class="h-4 w-4" />
                            {{ copied ? 'Copied!' : 'Copy' }}
                        </button>
                    </div>
                </PopoverPanel>
            </transition>
        </Popover>
        
        <!-- Error Message -->
        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ error }}
        </p>
        
        <!-- Helper Text -->
        <p v-else-if="helperText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ helperText }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { 
    ChevronDownIcon, 
    ClipboardDocumentIcon,
    CheckIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: null
    },
    placeholder: {
        type: String,
        default: 'Select color'
    },
    helperText: {
        type: String,
        default: null
    },
    error: {
        type: String,
        default: null
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    clearable: {
        type: Boolean,
        default: true
    },
    showPresets: {
        type: Boolean,
        default: true
    },
    showAlpha: {
        type: Boolean,
        default: false
    },
    presets: {
        type: Array,
        default: null
    },
    position: {
        type: String,
        default: 'left',
        validator: (val) => ['left', 'right'].includes(val)
    },
    containerClass: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const inputId = `color-picker-${Math.random().toString(36).substr(2, 9)}`;
const alpha = ref(1);
const copied = ref(false);
const recentColors = ref([]);

const presetColors = computed(() => {
    if (props.presets) return props.presets;
    
    return [
        // Row 1: Reds
        '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#14b8a6', '#06b6d4',
        // Row 2: Blues & Purples
        '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e',
        // Row 3: Neutrals
        '#000000', '#1f2937', '#374151', '#6b7280', '#9ca3af', '#d1d5db', '#f3f4f6', '#ffffff'
    ];
});

function selectColor(color) {
    emit('update:modelValue', color);
    emit('change', color);
    addToRecent(color);
}

function handleColorInput(event) {
    const color = event.target.value;
    emit('update:modelValue', color);
    emit('change', color);
}

function handleTextInput(event) {
    let value = event.target.value.trim();
    
    // Auto-add # if missing
    if (value && !value.startsWith('#')) {
        value = '#' + value;
    }
    
    // Validate hex color
    if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(value) || value === '') {
        emit('update:modelValue', value);
        emit('change', value);
        if (value) addToRecent(value);
    }
}

function handleAlphaInput(event) {
    alpha.value = parseInt(event.target.value) / 100;
}

function clearColor() {
    emit('update:modelValue', '');
    emit('change', '');
}

async function copyColor() {
    if (!props.modelValue) return;
    
    try {
        await navigator.clipboard.writeText(props.modelValue);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
}

function addToRecent(color) {
    // Remove if already exists
    const index = recentColors.value.indexOf(color);
    if (index > -1) {
        recentColors.value.splice(index, 1);
    }
    
    // Add to front
    recentColors.value.unshift(color);
    
    // Keep only last 8
    if (recentColors.value.length > 8) {
        recentColors.value = recentColors.value.slice(0, 8);
    }
}
</script>

<style scoped>
/* Custom color input styling */
input[type="color"] {
    -webkit-appearance: none;
    border: none;
    width: 48px;
    height: 48px;
}

input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}

input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 8px;
}

/* Range input styling */
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: none;
}
</style>
