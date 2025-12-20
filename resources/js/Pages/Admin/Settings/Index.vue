<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    CogIcon, 
    EnvelopeIcon, 
    BriefcaseIcon, 
    WalletIcon, 
    FlagIcon, 
    ShareIcon,
    CheckIcon,
    XMarkIcon,
    PlusIcon,
    ArrowPathIcon,
    KeyIcon,
    EyeIcon,
    EyeSlashIcon,
    MapIcon,
    CreditCardIcon,
    CloudIcon,
    ShieldCheckIcon,
    ChatBubbleLeftRightIcon,
    SparklesIcon,
    ExclamationTriangleIcon,
    ChevronDownIcon,
    ChevronUpIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Array,          // Array of all settings
    currentGroup: String,      // Active group
    groups: Object,            // Available groups {key: label}
});

const activeTab = ref(props.currentGroup || 'general');
const visiblePasswords = ref({});
const expandedApiGroups = ref({
    authentication: true,
    payment: false,
    cloud: false,
    communication: false,
    ai: false,
    security: false
});

const togglePasswordVisibility = (key) => {
    visiblePasswords.value[key] = !visiblePasswords.value[key];
};

const toggleApiGroup = (group) => {
    expandedApiGroups.value[group] = !expandedApiGroups.value[group];
};

// Settings organized by category for better UX
const groupConfig = {
    // 1?? ESSENTIAL SETTINGS (Site Identity & Contact)
    general: { icon: CogIcon, label: 'General', color: 'indigo' },
    branding: { icon: SparklesIcon, label: 'Branding', color: 'purple' },
    contact: { icon: ChatBubbleLeftRightIcon, label: 'Contact', color: 'green' },
    
    // 2?? FEATURE MANAGEMENT (Control what's enabled)
    modules: { icon: CogIcon, label: 'Modules', color: 'blue' },
    features: { icon: FlagIcon, label: 'Features', color: 'orange' },
    homepage: { icon: SparklesIcon, label: 'Homepage Widgets', color: 'purple' },
    
    // 3?? MODULE-SPECIFIC SETTINGS
    jobs: { icon: BriefcaseIcon, label: 'Jobs Settings', color: 'purple' },
    wallet: { icon: WalletIcon, label: 'Wallet Settings', color: 'green' },
    
    // 4?? MARKETING & SEO
    seo: { icon: ShieldCheckIcon, label: 'SEO & Analytics', color: 'blue' },
    menus: { icon: MapIcon, label: 'Navigation Menus', color: 'cyan' },
    social: { icon: ShareIcon, label: 'Social Media', color: 'pink' },
    email: { icon: EnvelopeIcon, label: 'Email', color: 'blue' },
    
    // 5?? ADVANCED (Technical Settings)
    api: { icon: KeyIcon, label: 'API Keys', color: 'red' },
    advanced: { icon: CogIcon, label: 'Advanced', color: 'gray' },
};

// API service configuration with icons, colors, and grouping
const apiServiceGroups = {
    authentication: {
        label: 'Authentication & OAuth',
        icon: ShieldCheckIcon,
        color: 'blue',
        services: ['google_oauth', 'facebook']
    },
    payment: {
        label: 'Payment Gateways',
        icon: CreditCardIcon,
        color: 'green',
        services: ['stripe', 'paypal', 'sslcommerz', 'bkash', 'nagad']
    },
    cloud: {
        label: 'Cloud Services',
        icon: CloudIcon,
        color: 'orange',
        services: ['aws', 'google_maps']
    },
    communication: {
        label: 'Communication',
        icon: ChatBubbleLeftRightIcon,
        color: 'purple',
        services: ['pusher', 'mailgun', 'twilio']
    },
    ai: {
        label: 'AI & Machine Learning',
        icon: SparklesIcon,
        color: 'pink',
        services: ['openai']
    },
    security: {
        label: 'Security',
        icon: ShieldCheckIcon,
        color: 'red',
        services: ['recaptcha']
    }
};

const apiServices = {
    google_maps: { icon: MapIcon, color: 'green', label: 'Google Maps' },
    google_oauth: { icon: ShieldCheckIcon, color: 'blue', label: 'Google OAuth' },
    facebook: { icon: ShareIcon, color: 'indigo', label: 'Facebook' },
    stripe: { icon: CreditCardIcon, color: 'purple', label: 'Stripe' },
    paypal: { icon: WalletIcon, color: 'blue', label: 'PayPal' },
    sslcommerz: { icon: CreditCardIcon, color: 'green', label: 'SSLCommerz' },
    bkash: { icon: WalletIcon, color: 'pink', label: 'bKash' },
    nagad: { icon: WalletIcon, color: 'orange', label: 'Nagad' },
    aws: { icon: CloudIcon, color: 'orange', label: 'AWS' },
    pusher: { icon: ChatBubbleLeftRightIcon, color: 'purple', label: 'Pusher' },
    mailgun: { icon: EnvelopeIcon, color: 'red', label: 'Mailgun' },
    twilio: { icon: ChatBubbleLeftRightIcon, color: 'red', label: 'Twilio' },
    openai: { icon: SparklesIcon, color: 'green', label: 'OpenAI' },
    recaptcha: { icon: ShieldCheckIcon, color: 'blue', label: 'reCAPTCHA' },
};

const getApiServiceConfig = (key) => {
    for (const [service, config] of Object.entries(apiServices)) {
        if (key.toLowerCase().includes(service.replace('_', ''))) {
            return config;
        }
    }
    return null;
};

const getApiGroupForSetting = (setting) => {
    const key = setting.key.toLowerCase();
    for (const [groupKey, group] of Object.entries(apiServiceGroups)) {
        for (const service of group.services) {
            if (key.includes(service.replace('_', ''))) {
                return groupKey;
            }
        }
    }
    return 'other';
};

const groupedApiSettings = computed(() => {
    if (activeTab.value !== 'api') return {};
    
    const groups = {};
    activeSettings.value.forEach(setting => {
        const groupKey = getApiGroupForSetting(setting);
        if (!groups[groupKey]) {
            groups[groupKey] = [];
        }
        groups[groupKey].push(setting);
    });
    return groups;
});

const form = useForm({
    settings: props.settings.map(setting => ({
        key: setting.key,
        value: setting.type === 'boolean' 
            ? setting.value === '1' || setting.value === true 
            : setting.value,
        type: setting.type,
    })),
    group: activeTab.value,
});

const activeSettings = computed(() => {
    return props.settings.filter(s => s.group === activeTab.value);
});

const submit = () => {
    // Only submit settings for the active tab
    const activeTabSettings = form.settings.filter(s => {
        const originalSetting = props.settings.find(ps => ps.key === s.key);
        return originalSetting && originalSetting.group === activeTab.value;
    });
    
    console.log('Submitting settings for group:', activeTab.value);
    console.log('Number of settings:', activeTabSettings.length);
    
    const submitForm = useForm({
        settings: activeTabSettings
    });
    
    submitForm.post(route('admin.settings.update', { group: activeTab.value }), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Settings saved successfully');
        },
        onError: (errors) => {
            console.error('Save errors:', errors);
        },
    });
};

const switchTab = (group) => {
    activeTab.value = group;
    window.history.pushState({}, '', route('admin.settings.index', { group }));
};

const getInputType = (type) => {
    const typeMap = {
        'text': 'text',
        'email': 'email',
        'url': 'url',
        'number': 'number',
        'boolean': 'checkbox',
        'password': 'password',
    };
    return typeMap[type] || 'text';
};

const updateSetting = (key, value) => {
    const index = form.settings.findIndex(s => s.key === key);
    if (index !== -1) {
        form.settings[index].value = value;
    }
};
</script>

<template>
    <Head title="Settings" />

    <AdminLayout>
        <!-- Modern Hero Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                                <CogIcon class="h-8 w-8 text-white" />
                            </div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                                Platform Settings
                            </h1>
                        </div>
                        <p class="text-white/90 text-lg max-w-2xl">
                            Configure your platform settings and preferences with granular control
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('admin.settings.clear-cache')"
                            method="post"
                            as="button"
                            class="inline-flex items-center px-6 py-3.5 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-xl transition-all border border-white/30 font-semibold gap-2"
                        >
                            <ArrowPathIcon class="h-5 w-5" />
                            Clear Cache
                        </Link>
                        <Link
                            :href="route('admin.settings.seed')"
                            method="post"
                            as="button"
                            class="inline-flex items-center px-6 py-3.5 bg-white hover:bg-gray-50 text-indigo-600 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold gap-2"
                        >
                            <ArrowPathIcon class="h-5 w-5" />
                            Reset to Defaults
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">

                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Modern Tabs -->
                    <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <nav class="flex space-x-2 px-6 py-3 overflow-x-auto" aria-label="Tabs">
                            <button
                                v-for="(label, key) in groups"
                                :key="key"
                                @click="switchTab(key)"
                                :class="[
                                    activeTab === key
                                        ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg'
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100',
                                    'whitespace-nowrap py-3 px-4 font-semibold text-sm flex items-center gap-2 transition-all duration-200 rounded-xl'
                                ]"
                            >
                                <component :is="groupConfig[key]?.icon || CogIcon" class="h-5 w-5" />
                                {{ label }}
                            </button>
                        </nav>
                    </div>

                    <!-- Settings Form -->
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Navigation Menus Section -->
                        <div v-if="activeTab === 'menus'" class="space-y-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <div class="flex items-start gap-4">
                                    <MapIcon class="h-8 w-8 text-blue-600 flex-shrink-0" />
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Navigation Menu Management</h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            Configure your site's navigation menus, headers, and footers. Manage menu items, ordering, and visibility.
                                        </p>
                                        <Link
                                            :href="route('menus.index')"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                                        >
                                            <MapIcon class="h-5 w-5 mr-2" />
                                            Manage Navigation Menus
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Settings Section -->
                        <div v-else-if="activeTab === 'seo'" class="space-y-6">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                                <div class="flex items-start gap-4">
                                    <ShieldCheckIcon class="h-8 w-8 text-purple-600 flex-shrink-0" />
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">SEO & Meta Settings</h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            Configure page-specific SEO settings including meta titles, descriptions, keywords, Open Graph tags, and canonical URLs.
                                        </p>
                                        <Link
                                            :href="route('seo-settings.index')"
                                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors shadow-sm"
                                        >
                                            <ShieldCheckIcon class="h-5 w-5 mr-2" />
                                            Manage SEO Settings
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick SEO Settings (from general settings if any) -->
                            <div v-if="activeSettings.length > 0" class="space-y-6 mt-8">
                                <h4 class="text-sm font-semibold text-gray-900 border-b border-gray-200 pb-2">General SEO Configuration</h4>
                                <div
                                    v-for="setting in activeSettings"
                                    :key="setting.key"
                                    class="pb-6 border-b border-gray-100 last:border-0"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <label :for="setting.key" class="block text-sm font-semibold text-gray-900 mb-1">
                                                {{ setting.key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') }}
                                            </label>
                                            <p v-if="setting.description" class="text-xs text-gray-500 mb-3">
                                                {{ setting.description }}
                                            </p>

                                            <div v-if="setting.type === 'boolean'" class="mt-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input
                                                        :id="setting.key"
                                                        type="checkbox"
                                                        :checked="form.settings.find(s => s.key === setting.key)?.value === true || form.settings.find(s => s.key === setting.key)?.value === '1'"
                                                        @change="updateSetting(setting.key, $event.target.checked)"
                                                        class="sr-only peer"
                                                    >
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                                    <span class="ml-3 text-sm font-medium text-gray-900">
                                                        {{ form.settings.find(s => s.key === setting.key)?.value ? 'Enabled' : 'Disabled' }}
                                                    </span>
                                                </label>
                                            </div>

                                            <textarea
                                                v-else-if="setting.type === 'textarea'"
                                                :id="setting.key"
                                                :value="form.settings.find(s => s.key === setting.key)?.value"
                                                @input="updateSetting(setting.key, $event.target.value)"
                                                rows="3"
                                                class="mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all sm:text-sm"
                                            ></textarea>

                                            <input
                                                v-else
                                                :id="setting.key"
                                                type="text"
                                                :value="form.settings.find(s => s.key === setting.key)?.value"
                                                @input="updateSetting(setting.key, $event.target.value)"
                                                class="mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all sm:text-sm"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- API Keys Section - Enhanced with Collapsible Groups -->
                        <div v-if="activeTab === 'api'" class="space-y-6">
                            <!-- Warning Banner -->
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <ExclamationTriangleIcon class="h-5 w-5 text-amber-400 flex-shrink-0" />
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-amber-800">Security Notice</h3>
                                        <p class="mt-1 text-sm text-amber-700">
                                            API keys are sensitive credentials. Never share them publicly or commit them to version control.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Collapsible API Service Groups -->
                            <div
                                v-for="(groupConfig, groupKey) in apiServiceGroups"
                                :key="groupKey"
                                class="bg-white border border-gray-200 rounded-lg overflow-hidden"
                            >
                                <!-- Group Header (Collapsible) -->
                                <button
                                    type="button"
                                    @click="toggleApiGroup(groupKey)"
                                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <div :class="[
                                            'rounded-lg p-2',
                                            `bg-${groupConfig.color}-100`
                                        ]">
                                            <component :is="groupConfig.icon" :class="[
                                                'h-5 w-5',
                                                `text-${groupConfig.color}-600`
                                            ]" />
                                        </div>
                                        <div class="text-left">
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                {{ groupConfig.label }}
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                {{ (groupedApiSettings[groupKey] || []).length }} service{{ (groupedApiSettings[groupKey] || []).length === 1 ? '' : 's' }}
                                                <span class="ml-1">�</span>
                                                <span class="ml-1">
                                                    {{ (groupedApiSettings[groupKey] || []).filter(s => form.settings.find(fs => fs.key === s.key)?.value).length }} configured
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <ChevronDownIcon 
                                        :class="[
                                            'h-5 w-5 text-gray-400 transition-transform',
                                            expandedApiGroups[groupKey] ? 'rotate-180' : ''
                                        ]"
                                    />
                                </button>

                                <!-- Group Settings (Collapsible Content) -->
                                <div
                                    v-if="expandedApiGroups[groupKey] && groupedApiSettings[groupKey]"
                                    class="border-t border-gray-200 divide-y divide-gray-100"
                                >
                                    <div
                                        v-for="setting in groupedApiSettings[groupKey]"
                                        :key="setting.key"
                                        class="p-4 hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="flex items-start gap-3">
                                            <!-- Service Icon (Smaller) -->
                                            <div class="flex-shrink-0 rounded p-2 bg-gray-100">
                                                <component 
                                                    :is="getApiServiceConfig(setting.key)?.icon || KeyIcon" 
                                                    class="h-4 w-4 text-gray-600"
                                                />
                                            </div>

                                            <!-- Setting Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <label :for="setting.key" class="text-sm font-medium text-gray-900">
                                                        {{ (setting.key || '').split('_').map(w => ((w || '').charAt(0).toUpperCase() || '') + (w || '').slice(1)).join(' ') }}
                                                    </label>
                                                    <span
                                                        v-if="form.settings.find(s => s.key === setting.key)?.value"
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700"
                                                    >
                                                        ?
                                                    </span>
                                                </div>
                                                
                                                <p v-if="setting.description" class="text-xs text-gray-500 mb-2">
                                                    {{ setting.description }}
                                                </p>

                                                <!-- Password Input with Toggle -->
                                                <div class="relative">
                                                    <input
                                                        :id="setting.key"
                                                        :type="visiblePasswords[setting.key] ? 'text' : 'password'"
                                                        :value="form.settings.find(s => s.key === setting.key)?.value"
                                                        @input="updateSetting(setting.key, $event.target.value)"
                                                        :placeholder="form.settings.find(s => s.key === setting.key)?.value ? '��������������������' : `Enter ${setting.key.split('_').pop()}`"
                                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm pr-10 font-mono"
                                                    >
                                                    <button
                                                        type="button"
                                                        @click="togglePasswordVisibility(setting.key)"
                                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                                    >
                                                        <EyeIcon v-if="!visiblePasswords[setting.key]" class="h-4 w-4" />
                                                        <EyeSlashIcon v-else class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Standard Settings Layout for Other Tabs - Improved 2-Column Grid -->
                        <div v-else class="space-y-8">
                            <div
                                v-for="setting in activeSettings"
                                :key="setting.key"
                                :class="[
                                    'pb-6 border-b border-gray-100 last:border-0',
                                    setting.type === 'textarea' ? 'col-span-2' : ''
                                ]"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <label :for="setting.key" class="block text-sm font-semibold text-gray-900 mb-1">
                                            {{ setting.key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') }}
                                            <span
                                                v-if="setting.is_public"
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700"
                                            >
                                                Public
                                            </span>
                                        </label>
                                        <p v-if="setting.description" class="text-xs text-gray-500 mb-3">
                                            {{ setting.description }}
                                        </p>

                                        <!-- Boolean Input -->
                                        <div v-if="setting.type === 'boolean'" class="mt-3">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input
                                                    :id="setting.key"
                                                    type="checkbox"
                                                    :checked="form.settings.find(s => s.key === setting.key)?.value === true || form.settings.find(s => s.key === setting.key)?.value === '1'"
                                                    @change="updateSetting(setting.key, $event.target.checked)"
                                                    class="sr-only peer"
                                                >
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-red-600"></div>
                                                <span class="ml-3 text-sm font-medium text-gray-900">
                                                    {{ form.settings.find(s => s.key === setting.key)?.value ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Textarea for long content -->
                                        <div v-else-if="setting.type === 'textarea'" class="mt-3">
                                            <textarea
                                                :id="setting.key"
                                                :value="form.settings.find(s => s.key === setting.key)?.value"
                                                @input="updateSetting(setting.key, $event.target.value)"
                                                rows="4"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all sm:text-sm"
                                            ></textarea>
                                        </div>

                                        <!-- Color picker -->
                                        <div v-else-if="setting.type === 'color'" class="mt-3">
                                            <div class="flex items-center gap-3">
                                                <input
                                                    :id="setting.key"
                                                    type="color"
                                                    :value="form.settings.find(s => s.key === setting.key)?.value || '#3B82F6'"
                                                    @input="updateSetting(setting.key, $event.target.value)"
                                                    class="h-10 w-20 rounded-xl border-2 border-gray-200 cursor-pointer transition-all"
                                                >
                                                <input
                                                    type="text"
                                                    :value="form.settings.find(s => s.key === setting.key)?.value"
                                                    @input="updateSetting(setting.key, $event.target.value)"
                                                    placeholder="#3B82F6"
                                                    class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all sm:text-sm font-mono"
                                                >
                                            </div>
                                        </div>

                                        <!-- Text/Number/Email/URL Inputs -->
                                        <div v-else-if="setting.type !== 'password'" class="mt-3">
                                            <input
                                                :id="setting.key"
                                                :type="getInputType(setting.type)"
                                                :value="form.settings.find(s => s.key === setting.key)?.value"
                                                @input="updateSetting(setting.key, $event.target.value)"
                                                :step="setting.type === 'number' ? 'any' : undefined"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all sm:text-sm"
                                            >
                                        </div>

                                        <!-- Password Input with Toggle -->
                                        <div v-else class="mt-3">
                                            <div class="relative">
                                                <input
                                                    :id="setting.key"
                                                    :type="visiblePasswords[setting.key] ? 'text' : 'password'"
                                                    :value="form.settings.find(s => s.key === setting.key)?.value"
                                                    @input="updateSetting(setting.key, $event.target.value)"
                                                    :placeholder="form.settings.find(s => s.key === setting.key)?.value ? '������������' : 'Enter secure value'"
                                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all sm:text-sm pr-10"
                                                >
                                                <button
                                                    type="button"
                                                    @click="togglePasswordVisibility(setting.key)"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                                >
                                                    <EyeIcon v-if="!visiblePasswords[setting.key]" class="h-5 w-5" />
                                                    <EyeSlashIcon v-else class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            >
                                <CheckIcon class="h-5 w-5 mr-2" />
                                {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Panel -->
                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <CogIcon class="h-5 w-5 text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-brand-red-600">Settings Information</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Settings marked as "Public" can be accessed by the frontend application</li>
                                    <li>Changes are cached for performance - clear cache if needed</li>
                                    <li>Use "Reset to Defaults" to restore original platform settings</li>
                                    <li>Boolean settings use toggles for easier management</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
