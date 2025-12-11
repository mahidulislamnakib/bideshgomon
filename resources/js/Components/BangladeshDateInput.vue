<script setup>
import { ref, computed, watch } from 'vue';
import { CalendarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: String,  // YYYY-MM-DD format
    label: String,
    placeholder: {
        type: String,
        default: 'DD MM YYYY'
    },
    error: String,
    required: Boolean,
    disabled: Boolean,
    minDate: String,  // YYYY-MM-DD
    maxDate: String,  // YYYY-MM-DD
});

const emit = defineEmits(['update:modelValue']);

const displayValue = ref('');
const isFocused = ref(false);

// Month names for Bangladesh format
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const monthNamesLower = monthNames.map(m => m.toLowerCase());

// Format YYYY-MM-DD to DD Mon YYYY
const formatDisplayDate = (isoDate) => {
    if (!isoDate) return '';
    
    const match = isoDate.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) return '';
    
    const [, year, month, day] = match;
    const monthName = monthNames[parseInt(month) - 1];
    const dayNum = parseInt(day);
    
    return `${dayNum} ${monthName} ${year}`;
};

// Parse DD MM YYYY or DD/MM/YYYY to YYYY-MM-DD
const parseInputDate = (input) => {
    if (!input) return '';
    
    // Try space-separated: DD Mon YYYY or DD MM YYYY
    const spaceMatch = input.match(/^(\d{1,2})\s+(\w+|\d{1,2})\s+(\d{4})$/);
    if (spaceMatch) {
        const [, day, monthStr, year] = spaceMatch;
        const dayPad = day.padStart(2, '0');
        
        let monthPad;
        if (isNaN(monthStr)) {
            // Month name
            const monthIndex = monthNamesLower.indexOf(monthStr.toLowerCase().substring(0, 3));
            if (monthIndex === -1) return '';
            monthPad = String(monthIndex + 1).padStart(2, '0');
        } else {
            // Numeric month
            monthPad = monthStr.padStart(2, '0');
        }
        
        return `${year}-${monthPad}-${dayPad}`;
    }
    
    // Try slash-separated: DD/MM/YYYY
    const slashMatch = input.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (slashMatch) {
        const [, day, month, year] = slashMatch;
        const dayPad = day.padStart(2, '0');
        const monthPad = month.padStart(2, '0');
        return `${year}-${monthPad}-${dayPad}`;
    }
    
    // Already in YYYY-MM-DD format
    if (input.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return input;
    }
    
    return '';
};

// Validate date
const isValidDate = (dateStr) => {
    if (!dateStr) return true; // Empty is valid if not required
    
    const isoDate = parseInputDate(dateStr);
    if (!isoDate) return false;
    
    const date = new Date(isoDate);
    if (isNaN(date.getTime())) return false;
    
    // Check min/max constraints
    if (props.minDate) {
        const minDate = new Date(props.minDate);
        if (date < minDate) return false;
    }
    
    if (props.maxDate) {
        const maxDate = new Date(props.maxDate);
        if (date > maxDate) return false;
    }
    
    return true;
};

// Initialize display value from modelValue
watch(() => props.modelValue, (newValue) => {
    if (!isFocused.value) {
        displayValue.value = formatDisplayDate(newValue);
    }
}, { immediate: true });

// Handle input changes
const handleInput = (event) => {
    const input = event.target.value;
    displayValue.value = input;
    
    // Try to parse and emit
    const isoDate = parseInputDate(input);
    if (isoDate && isValidDate(input)) {
        emit('update:modelValue', isoDate);
    }
};

// Handle blur - format display value
const handleBlur = () => {
    isFocused.value = false;
    
    const isoDate = parseInputDate(displayValue.value);
    if (isoDate && isValidDate(displayValue.value)) {
        displayValue.value = formatDisplayDate(isoDate);
        emit('update:modelValue', isoDate);
    } else if (!displayValue.value) {
        // Empty input
        emit('update:modelValue', '');
    }
    // If invalid, keep user's input so they can correct it
};

// Handle focus
const handleFocus = () => {
    isFocused.value = true;
};

// Input class binding
const inputClasses = computed(() => {
    return [
        'w-full pl-10 pr-4 py-2 border rounded-lg transition-colors',
        'focus:ring-2 focus:ring-brand-red-600 focus:border-indigo-500',
        'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
        props.error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
    ];
});

// Helper text
const helperText = computed(() => {
    if (props.error) return props.error;
    return 'Format: DD Mon YYYY (e.g., 15 Dec 2025) or DD MM YYYY';
});
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-medium text-gray-900 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Input Container -->
        <div class="relative">
            <!-- Calendar Icon -->
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <CalendarIcon class="h-5 w-5 text-gray-400" />
            </div>

            <!-- Input Field -->
            <input
                type="text"
                :value="displayValue"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :class="inputClasses"
                autocomplete="off"
            />

            <!-- Native Date Picker Fallback (Hidden) -->
            <input
                type="date"
                :value="modelValue"
                @input="emit('update:modelValue', $event.target.value)"
                class="sr-only"
                :min="minDate"
                :max="maxDate"
                :disabled="disabled"
                :required="required"
                tabindex="-1"
            />
        </div>

        <!-- Helper/Error Text -->
        <p 
            v-if="helperText && !isFocused"
            :class="[
                'mt-1 text-sm',
                error ? 'text-red-600' : 'text-gray-500'
            ]"
        >
            {{ helperText }}
        </p>

        <!-- Format Hint (on focus) -->
        <p 
            v-if="isFocused && !error"
            class="mt-1 text-sm text-brand-red-600"
        >
            💡 Type: 15 Dec 2025 or 15 12 2025 or 15/12/2025
        </p>
    </div>
</template>

