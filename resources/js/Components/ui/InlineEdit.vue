<template>
    <div
        :class="[
            'group relative inline-flex items-center gap-1',
            editing ? 'z-10' : ''
        ]"
    >
        <!-- Display Mode -->
        <template v-if="!editing">
            <span
                :class="[
                    'cursor-pointer transition-colors duration-150',
                    editable ? 'hover:text-primary-600 dark:hover:text-primary-400' : '',
                    displayClasses
                ]"
                @click="startEditing"
                @dblclick="startEditing"
            >
                <slot>{{ displayValue || placeholder }}</slot>
            </span>
            
            <!-- Edit icon (visible on hover) -->
            <button
                v-if="editable && showEditIcon"
                @click.stop="startEditing"
                :class="[
                    'opacity-0 group-hover:opacity-100 transition-opacity duration-150',
                    'p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                ]"
            >
                <PencilIcon class="h-3.5 w-3.5" />
            </button>
        </template>
        
        <!-- Edit Mode -->
        <template v-else>
            <div class="flex items-center gap-1">
                <!-- Text Input -->
                <input
                    v-if="type === 'text' || type === 'email' || type === 'number'"
                    ref="inputRef"
                    v-model="localValue"
                    :type="type"
                    :placeholder="placeholder"
                    :class="[
                        'px-2 py-1 rounded border transition-all duration-150',
                        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
                        error 
                            ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20' 
                            : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800',
                        'text-gray-900 dark:text-gray-100',
                        inputClasses
                    ]"
                    :style="{ width: inputWidth }"
                    @keydown.enter="save"
                    @keydown.escape="cancel"
                    @blur="handleBlur"
                />
                
                <!-- Textarea -->
                <textarea
                    v-else-if="type === 'textarea'"
                    ref="inputRef"
                    v-model="localValue"
                    :placeholder="placeholder"
                    :rows="rows"
                    :class="[
                        'px-2 py-1 rounded border transition-all duration-150',
                        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
                        error 
                            ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20' 
                            : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800',
                        'text-gray-900 dark:text-gray-100 resize-none',
                        inputClasses
                    ]"
                    :style="{ width: inputWidth }"
                    @keydown.escape="cancel"
                    @blur="handleBlur"
                />
                
                <!-- Select -->
                <select
                    v-else-if="type === 'select'"
                    ref="inputRef"
                    v-model="localValue"
                    :class="[
                        'px-2 py-1 rounded border transition-all duration-150',
                        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
                        'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800',
                        'text-gray-900 dark:text-gray-100',
                        inputClasses
                    ]"
                    @change="save"
                    @keydown.escape="cancel"
                    @blur="handleBlur"
                >
                    <option
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                
                <!-- Action Buttons -->
                <div v-if="showActions" class="flex items-center gap-0.5">
                    <!-- Save -->
                    <button
                        @click="save"
                        :disabled="saving"
                        class="p-1 rounded text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors"
                        title="Save (Enter)"
                    >
                        <CheckIcon v-if="!saving" class="h-4 w-4" />
                        <svg v-else class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                    </button>
                    
                    <!-- Cancel -->
                    <button
                        @click="cancel"
                        :disabled="saving"
                        class="p-1 rounded text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                        title="Cancel (Esc)"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
            
            <!-- Error Message -->
            <p v-if="error" class="absolute top-full left-0 mt-1 text-xs text-red-600 dark:text-red-400">
                {{ error }}
            </p>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { PencilIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    // Current value
    modelValue: {
        type: [String, Number],
        default: ''
    },
    // Input type
    type: {
        type: String,
        default: 'text',
        validator: (val) => ['text', 'email', 'number', 'textarea', 'select'].includes(val)
    },
    // Options for select type
    options: {
        type: Array,
        default: () => []
    },
    // Placeholder text
    placeholder: {
        type: String,
        default: 'Click to edit'
    },
    // Whether editing is allowed
    editable: {
        type: Boolean,
        default: true
    },
    // Show edit icon on hover
    showEditIcon: {
        type: Boolean,
        default: true
    },
    // Show save/cancel buttons
    showActions: {
        type: Boolean,
        default: true
    },
    // Save on blur (instead of cancel)
    saveOnBlur: {
        type: Boolean,
        default: false
    },
    // Input width
    inputWidth: {
        type: String,
        default: '200px'
    },
    // Textarea rows
    rows: {
        type: Number,
        default: 3
    },
    // Validation function
    validate: {
        type: Function,
        default: null
    },
    // Custom display classes
    displayClasses: {
        type: String,
        default: ''
    },
    // Custom input classes
    inputClasses: {
        type: String,
        default: 'text-sm'
    },
    // Required field
    required: {
        type: Boolean,
        default: false
    },
    // Async save function
    saveAsync: {
        type: Function,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'save', 'cancel', 'error']);

const inputRef = ref(null);
const editing = ref(false);
const saving = ref(false);
const localValue = ref(props.modelValue);
const error = ref(null);

// Formatted display value
const displayValue = computed(() => {
    if (props.type === 'select' && props.options.length) {
        const option = props.options.find(o => o.value === props.modelValue);
        return option ? option.label : props.modelValue;
    }
    return props.modelValue;
});

// Watch for external value changes
watch(() => props.modelValue, (newVal) => {
    if (!editing.value) {
        localValue.value = newVal;
    }
});

function startEditing() {
    if (!props.editable) return;
    
    localValue.value = props.modelValue;
    error.value = null;
    editing.value = true;
    
    nextTick(() => {
        inputRef.value?.focus();
        inputRef.value?.select?.();
    });
}

async function save() {
    error.value = null;
    
    // Validation
    if (props.required && !localValue.value) {
        error.value = 'This field is required';
        return;
    }
    
    if (props.validate) {
        const validationError = props.validate(localValue.value);
        if (validationError) {
            error.value = validationError;
            return;
        }
    }
    
    // No change
    if (localValue.value === props.modelValue) {
        editing.value = false;
        return;
    }
    
    // Async save
    if (props.saveAsync) {
        saving.value = true;
        try {
            await props.saveAsync(localValue.value);
            emit('update:modelValue', localValue.value);
            emit('save', localValue.value);
            editing.value = false;
        } catch (err) {
            error.value = err.message || 'Failed to save';
            emit('error', err);
        } finally {
            saving.value = false;
        }
    } else {
        emit('update:modelValue', localValue.value);
        emit('save', localValue.value);
        editing.value = false;
    }
}

function cancel() {
    localValue.value = props.modelValue;
    error.value = null;
    editing.value = false;
    emit('cancel');
}

function handleBlur(event) {
    // Don't blur if clicking save/cancel buttons
    const relatedTarget = event.relatedTarget;
    if (relatedTarget && event.currentTarget.parentNode.contains(relatedTarget)) {
        return;
    }
    
    // Small delay to allow button clicks
    setTimeout(() => {
        if (editing.value && !saving.value) {
            if (props.saveOnBlur) {
                save();
            } else {
                cancel();
            }
        }
    }, 150);
}

// Handle click outside to close
function handleClickOutside(event) {
    if (editing.value && inputRef.value && !inputRef.value.contains(event.target)) {
        // Check if clicked on action buttons
        const parent = inputRef.value.closest('.group');
        if (parent && !parent.contains(event.target)) {
            if (props.saveOnBlur) {
                save();
            } else {
                cancel();
            }
        }
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
