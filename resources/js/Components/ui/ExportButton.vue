<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton
            :class="[
                'inline-flex items-center justify-center gap-2 rounded-lg font-medium',
                'transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
                variantClasses,
                sizeClasses,
                disabled ? 'opacity-50 cursor-not-allowed' : ''
            ]"
            :disabled="disabled || isExporting"
        >
            <!-- Loading spinner -->
            <svg
                v-if="isExporting"
                class="animate-spin h-4 w-4"
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
            
            <!-- Icon -->
            <ArrowDownTrayIcon v-else class="h-4 w-4" />
            
            <!-- Label -->
            <span v-if="showLabel">{{ label }}</span>
            
            <!-- Dropdown chevron -->
            <ChevronDownIcon v-if="showDropdown" class="h-4 w-4 -mr-1" />
        </MenuButton>
        
        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems
                v-if="showDropdown"
                :class="[
                    'absolute z-50 mt-2 w-48 origin-top-right rounded-lg',
                    'bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5',
                    'focus:outline-none divide-y divide-gray-100 dark:divide-gray-700',
                    position === 'left' ? 'left-0' : 'right-0'
                ]"
            >
                <div class="py-1">
                    <!-- CSV Option -->
                    <MenuItem
                        v-if="formats.includes('csv')"
                        v-slot="{ active }"
                    >
                        <button
                            @click="handleExport('csv')"
                            :class="[
                                'flex items-center w-full px-4 py-2 text-sm gap-3',
                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <DocumentTextIcon class="h-5 w-5 text-green-500" />
                            <div class="text-left">
                                <div class="font-medium">CSV</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Comma-separated values</div>
                            </div>
                        </button>
                    </MenuItem>
                    
                    <!-- Excel Option -->
                    <MenuItem
                        v-if="formats.includes('excel')"
                        v-slot="{ active }"
                    >
                        <button
                            @click="handleExport('excel')"
                            :class="[
                                'flex items-center w-full px-4 py-2 text-sm gap-3',
                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <TableCellsIcon class="h-5 w-5 text-emerald-600" />
                            <div class="text-left">
                                <div class="font-medium">Excel</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Microsoft Excel format</div>
                            </div>
                        </button>
                    </MenuItem>
                    
                    <!-- JSON Option -->
                    <MenuItem
                        v-if="formats.includes('json')"
                        v-slot="{ active }"
                    >
                        <button
                            @click="handleExport('json')"
                            :class="[
                                'flex items-center w-full px-4 py-2 text-sm gap-3',
                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <CodeBracketIcon class="h-5 w-5 text-yellow-500" />
                            <div class="text-left">
                                <div class="font-medium">JSON</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">JavaScript Object Notation</div>
                            </div>
                        </button>
                    </MenuItem>
                </div>
                
                <!-- Print Option -->
                <div v-if="formats.includes('print')" class="py-1">
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="handleExport('print')"
                            :class="[
                                'flex items-center w-full px-4 py-2 text-sm gap-3',
                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <PrinterIcon class="h-5 w-5 text-gray-500" />
                            <div class="text-left">
                                <div class="font-medium">Print</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Print or save as PDF</div>
                            </div>
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import {
    ArrowDownTrayIcon,
    ChevronDownIcon,
    DocumentTextIcon,
    TableCellsIcon,
    CodeBracketIcon,
    PrinterIcon
} from '@heroicons/vue/24/outline';
import { useDataExport } from '@/Composables/useDataExport';

const props = defineProps({
    // Data to export
    data: {
        type: Array,
        required: true
    },
    // Column configuration
    columns: {
        type: Array,
        default: null
    },
    // Base filename (without extension)
    filename: {
        type: String,
        default: 'export'
    },
    // Available export formats
    formats: {
        type: Array,
        default: () => ['csv', 'excel', 'json', 'print'],
        validator: (val) => val.every(f => ['csv', 'excel', 'json', 'print'].includes(f))
    },
    // Button variant
    variant: {
        type: String,
        default: 'secondary',
        validator: (val) => ['primary', 'secondary', 'outline', 'ghost'].includes(val)
    },
    // Button size
    size: {
        type: String,
        default: 'md',
        validator: (val) => ['sm', 'md', 'lg'].includes(val)
    },
    // Button label
    label: {
        type: String,
        default: 'Export'
    },
    // Show label text
    showLabel: {
        type: Boolean,
        default: true
    },
    // Dropdown position
    position: {
        type: String,
        default: 'right',
        validator: (val) => ['left', 'right'].includes(val)
    },
    // Disabled state
    disabled: {
        type: Boolean,
        default: false
    },
    // Print title
    printTitle: {
        type: String,
        default: 'Data Export'
    }
});

const emit = defineEmits(['export', 'error']);

const { exportToCSV, exportToExcel, exportToJSON, printData } = useDataExport();

const isExporting = ref(false);

const showDropdown = computed(() => props.formats.length > 1);

const variantClasses = computed(() => {
    const variants = {
        primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
        secondary: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 focus:ring-gray-500',
        outline: 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:ring-gray-500',
        ghost: 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-gray-500'
    };
    return variants[props.variant] || variants.secondary;
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-2.5 py-1.5 text-xs',
        md: 'px-3 py-2 text-sm',
        lg: 'px-4 py-2.5 text-base'
    };
    return sizes[props.size] || sizes.md;
});

async function handleExport(format) {
    if (!props.data || props.data.length === 0) {
        emit('error', { format, message: 'No data to export' });
        return;
    }
    
    isExporting.value = true;
    
    try {
        const timestamp = new Date().toISOString().slice(0, 10);
        const filename = `${props.filename}_${timestamp}`;
        
        let success = false;
        
        switch (format) {
            case 'csv':
                success = exportToCSV(props.data, {
                    filename,
                    columns: props.columns
                });
                break;
                
            case 'excel':
                success = exportToExcel(props.data, {
                    filename,
                    columns: props.columns
                });
                break;
                
            case 'json':
                success = exportToJSON(props.data, {
                    filename,
                    columns: props.columns
                });
                break;
                
            case 'print':
                success = printData(props.data, {
                    title: props.printTitle,
                    columns: props.columns
                });
                break;
        }
        
        if (success) {
            emit('export', { format, filename, count: props.data.length });
        }
    } catch (error) {
        emit('error', { format, message: error.message });
    } finally {
        isExporting.value = false;
    }
}
</script>
