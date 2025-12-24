<template>
    <div 
        class="bg-white dark:bg-neutral-800 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer border-l-4"
        :class="borderColorClass"
        @click="navigateTo"
    >
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <component :is="iconComponent" :class="iconColorClass" class="h-6 w-6 mr-2" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ description }}</p>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold" :class="textColorClass">{{ count }}</span>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">records</span>
                    </div>
                </div>
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    GlobeAltIcon, BuildingOffice2Icon, PaperAirplaneIcon, CurrencyDollarIcon,
    StarIcon, FolderIcon, BriefcaseIcon, AcademicCapIcon, LanguageIcon,
    ClipboardDocumentListIcon, Squares2X2Icon, BookOpenIcon, TagIcon,
    DocumentTextIcon, BuildingLibraryIcon, IdentificationIcon, UsersIcon,
    CogIcon, EnvelopeIcon, DocumentDuplicateIcon, MagnifyingGlassIcon,
    BellAlertIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    count: {
        type: Number,
        default: 0
    },
    icon: {
        type: String,
        default: 'folder'
    },
    description: {
        type: String,
        default: ''
    },
    route: {
        type: String,
        required: true
    },
    color: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'purple', 'yellow', 'red', 'indigo'].includes(value)
    }
});

// Icon mapping from string to component
const iconMap = {
    globe: GlobeAltIcon,
    building: BuildingOffice2Icon,
    plane: PaperAirplaneIcon,
    currency: CurrencyDollarIcon,
    star: StarIcon,
    folder: FolderIcon,
    briefcase: BriefcaseIcon,
    academic: AcademicCapIcon,
    language: LanguageIcon,
    clipboard: ClipboardDocumentListIcon,
    grid: Squares2X2Icon,
    book: BookOpenIcon,
    tag: TagIcon,
    document: DocumentTextIcon,
    bank: BuildingLibraryIcon,
    passport: IdentificationIcon,
    users: UsersIcon,
    cog: CogIcon,
    email: EnvelopeIcon,
    template: DocumentDuplicateIcon,
    search: MagnifyingGlassIcon,
    alert: BellAlertIcon
};

const iconComponent = computed(() => iconMap[props.icon] || FolderIcon);

const borderColorClass = computed(() => {
    const colors = {
        blue: 'border-blue-500',
        green: 'border-green-500',
        purple: 'border-purple-500',
        yellow: 'border-yellow-500',
        red: 'border-red-500',
        indigo: 'border-indigo-500'
    };
    return colors[props.color] || colors.blue;
});

const iconColorClass = computed(() => {
    const colors = {
        blue: 'text-growth-600',
        green: 'text-green-600',
        purple: 'text-purple-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        indigo: 'text-growth-600'
    };
    return colors[props.color] || colors.blue;
});

const textColorClass = computed(() => {
    const colors = {
        blue: 'text-growth-600',
        green: 'text-green-600',
        purple: 'text-purple-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        indigo: 'text-growth-600'
    };
    return colors[props.color] || colors.blue;
});

const navigateTo = () => {
    if (props.route && props.route !== '#') {
        router.visit(props.route);
    }
};
</script>
