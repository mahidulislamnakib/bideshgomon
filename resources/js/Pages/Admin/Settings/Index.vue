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
    // 1️⃣ ESSENTIAL SETTINGS (Site Identity & Contact)
    general: { icon: CogIcon, label: 'General', color: 'indigo' },
    branding: { icon: SparklesIcon, label: 'Branding', color: 'purple' },
    contact: { icon: ChatBubbleLeftRightIcon, label: 'Contact', color: 'green' },
    
    // 2️⃣ FEATURE MANAGEMENT (Control what's enabled)
    modules: { icon: CogIcon, label: 'Modules', color: 'blue' },
    features: { icon: FlagIcon, label: 'Features', color: 'orange' },
    homepage: { icon: SparklesIcon, label: 'Homepage Widgets', color: 'purple' },
    
    // 3️⃣ MODULE-SPECIFIC SETTINGS
    jobs: { icon: BriefcaseIcon, label: 'Jobs Settings', color: 'purple' },
    wallet: { icon: WalletIcon, label: 'Wallet Settings', color: 'green' },
    
    // 4️⃣ MARKETING & SEO
    seo: { icon: ShieldCheckIcon, label: 'SEO & Analytics', color: 'blue' },
    menus: { icon: MapIcon, label: 'Navigation Menus', color: 'cyan' },
    social: { icon: ShareIcon, label: 'Social Media', color: 'pink' },
    email: { icon: EnvelopeIcon, label: 'Email', color: 'blue' },
    
    // 5️⃣ ADVANCED (Technical Settings)
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
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-primary-500/30 to-primary-600/20 rounded-full blur-3xl"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-white/10 backdrop-blur-sm rounded-xl">
                                <CogIcon class="h-8 w-8 text-white" />
                            </div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                                Platform Settings
                            </h1>
                        </div>
                        <p class="text-gray-300 text-lg max-w-2xl">
                            Configure your platform settings and preferences with granular control
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('admin.settings.clear-cache')"
                            method="post"
                            as="button"
                            class="inline-flex items-center px-6 py-3.5 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white rounded-xl transition-all border border-white/20 font-semibold gap-2"
                        >
                            <ArrowPathIcon class="h-5 w-5" />
                            Clear Cache
                        </Link>
                        <Link
                            :href="route('admin.settings.seed')"
                            method="post"
                            as="button"
                            class="inline-flex items-center px-6 py-3.5 text-white rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold gap-2" style="background: #14a800;"
                        >
                            <ArrowPathIcon class="h-5 w-5" />
                            Reset to Defaults
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <!-- Modern Tabs -->
                    <div class="border-b border-neutral-200 dark:border-neutral-700 bg-gradient-to-r from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-800">
                        <nav class="flex space-x-2 px-6 py-3 overflow-x-auto" aria-label="Tabs">
                            <button
                                v-for="(label, key) in groups"
                                :key="key"
                                @click="switchTab(key)"
                                :class="[
                                    activeTab === key
                                        ? 'text-white shadow-lg'
                                        : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-700',
                                    'whitespace-nowrap py-3 px-4 font-semibold text-sm flex items-center gap-2 transition-all duration-200 rounded-xl'
                                ]"
                                :style="activeTab === key ? 'background: linear-gradient(135deg, #1f2937 0%, #111827 100%);' : ''"
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
                            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-6">
                                <div class="flex items-start gap-4">
                                    <MapIcon class="h-8 w-8 flex-shrink-0" style="color: #14a800;" />
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Navigation Menu Management</h3>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                                            Configure your site's navigation menus, headers, and footers. Manage menu items, ordering, and visibility.
                                        </p>
                                        <Link
                                            :href="route('menus.index')"
                                            class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-2xl transition-colors shadow-sm" style="background: #14a800;"
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
                            <div class="bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl p-6">
                                <div class="flex items-start gap-4">
                                    <ShieldCheckIcon class="h-8 w-8 flex-shrink-0" style="color: #3b82f6;" />
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">SEO & Meta Settings</h3>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                                            Configure page-specific SEO settings including meta titles, descriptions, keywords, Open Graph tags, and canonical URLs.
                                        </p>
                                        <Link
                                            :href="route('seo-settings.index')"
                                            class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-2xl transition-colors shadow-sm" style="background: #3b82f6;"
                                        >
                                            <ShieldCheckIcon class="h-5 w-5 mr-2" />
                                            Manage SEO Settings
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick SEO Settings (from general settings if any) -->
                            <div v-if="activeSettings.length > 0" class="space-y-6 mt-8">
                                <h4 class="text-sm font-semibold text-neutral-900 dark:text-white border-b border-neutral-200 dark:border-neutral-700 pb-2">General SEO Configuration</h4>
                                <div
                                    v-for="setting in activeSettings"
                                    :key="setting.key"
                                    class="pb-6 border-b border-neutral-100 dark:border-neutral-700 last:border-0"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <label :for="setting.key" class="block text-sm font-semibold text-neutral-900 dark:text-white mb-1">
                                                {{ setting.key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') }}
                                            </label>
                                            <p v-if="setting.description" class="text-xs text-neutral-500 dark:text-neutral-400 mb-3">
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
                                                    <div class="w-11 h-6 bg-neutral-200 dark:bg-neutral-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                                    <span class="ml-3 text-sm font-medium text-neutral-900 dark:text-white">
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
                                                class="mt-3 w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all sm:text-sm"
                                            ></textarea>

                                            <input
                                                v-else
                                                :id="setting.key"
                                                type="text"
                                                :value="form.settings.find(s => s.key === setting.key)?.value"
                                                @input="updateSetting(setting.key, $event.target.value)"
                                                class="mt-3 w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all sm:text-sm"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- API Keys Section - Enhanced with Collapsible Groups -->
                        <div v-if="activeTab === 'api'" class="space-y-6">
                            <!-- Warning Banner -->
                            <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-xl p-4 mb-6">
                                <div class="flex">
                                    <ExclamationTriangleIcon class="h-5 w-5 text-warning-500 flex-shrink-0" />
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-300">Security Notice</h3>
                                        <p class="mt-1 text-sm text-warning-700 dark:text-warning-400">
                                            API keys are sensitive credentials. Never share them publicly or commit them to version control.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Collapsible API Service Groups -->
                            <div
                                v-for="(groupConfig, groupKey) in apiServiceGroups"
                                :key="groupKey"
                                class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden"
                            >
                                <!-- Group Header (Collapsible) -->
                                <button
                                    type="button"
                                    @click="toggleApiGroup(groupKey)"
                                    class="w-full flex items-center justify-between p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <div :class="[
                                            'rounded-2xl p-2',
                                            `bg-${groupConfig.color}-100 dark:bg-${groupConfig.color}-900/30`
                                        ]">
                                            <component :is="groupConfig.icon" :class="[
                                                'h-5 w-5',
                                                `text-${groupConfig.color}-600 dark:text-${groupConfig.color}-400`
                                            ]" />
                                        </div>
                                        <div class="text-left">
                                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">
                                                {{ groupConfig.label }}
                                            </h3>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                {{ (groupedApiSettings[groupKey] || []).length }} service{{ (groupedApiSettings[groupKey] || []).length === 1 ? '' : 's' }}
                                                <span class="ml-1">•</span>
                                                <span class="ml-1">
                                                    {{ (groupedApiSettings[groupKey] || []).filter(s => form.settings.find(fs => fs.key === s.key)?.value).length }} configured
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <ChevronDownIcon 
                                        :class="[
                                            'h-5 w-5 text-neutral-400 transition-transform',
                                            expandedApiGroups[groupKey] ? 'rotate-180' : ''
                                        ]"
                                    />
                                </button>

                                <!-- Group Settings (Collapsible Content) -->
                                <div
                                    v-if="expandedApiGroups[groupKey] && groupedApiSettings[groupKey]"
                                    class="border-t border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-100 dark:divide-neutral-700"
                                >
                                    <div
                                        v-for="setting in groupedApiSettings[groupKey]"
                                        :key="setting.key"
                                        class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
                                    >
                                        <div class="flex items-start gap-3">
                                            <!-- Service Icon (Smaller) -->
                                            <div class="flex-shrink-0 rounded p-2 bg-neutral-100 dark:bg-neutral-700">
                                                <component 
                                                    :is="getApiServiceConfig(setting.key)?.icon || KeyIcon" 
                                                    class="h-4 w-4 text-neutral-600 dark:text-neutral-400"
                                                />
                                            </div>

                                            <!-- Setting Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <label :for="setting.key" class="text-sm font-medium text-neutral-900 dark:text-white">
                                                        {{ (setting.key || '').split('_').map(w => ((w || '').charAt(0).toUpperCase() || '') + (w || '').slice(1)).join(' ') }}
                                                    </label>
                                                    <span
                                                        v-if="form.settings.find(s => s.key === setting.key)?.value"
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300"
                                                    >
                                                        ?
                                                    </span>
                                                </div>
                                                
                                                <p v-if="setting.description" class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
                                                    {{ setting.description }}
                                                </p>

                                                <!-- Password Input with Toggle -->
                                                <div class="relative">
                                                    <input
                                                        :id="setting.key"
                                                        :type="visiblePasswords[setting.key] ? 'text' : 'password'"
                                                        :value="form.settings.find(s => s.key === setting.key)?.value"
                                                        @input="updateSetting(setting.key, $event.target.value)"
                                                        :placeholder="form.settings.find(s => s.key === setting.key)?.value ? '••••••••••••••••••••' : `Enter ${setting.key.split('_').pop()}`"
                                                        class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-sm pr-10 font-mono"
                                                    >
                                                    <button
                                                        type="button"
                                                        @click="togglePasswordVisibility(setting.key)"
                                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
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
                                    'pb-6 border-b border-neutral-100 dark:border-neutral-700 last:border-0',
                                    setting.type === 'textarea' ? 'col-span-2' : ''
                                ]"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <label :for="setting.key" class="block text-sm font-semibold text-neutral-900 dark:text-white mb-1">
                                            {{ setting.key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') }}
                                            <span
                                                v-if="setting.is_public"
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300"
                                            >
                                                Public
                                            </span>
                                        </label>
                                        <p v-if="setting.description" class="text-xs text-neutral-500 dark:text-neutral-400 mb-3">
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
                                                <div class="w-11 h-6 bg-neutral-200 dark:bg-neutral-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                                <span class="ml-3 text-sm font-medium text-neutral-900 dark:text-white">
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
                                                class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all sm:text-sm"
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
                                                    class="h-10 w-20 rounded-xl border-2 border-neutral-200 dark:border-neutral-600 cursor-pointer transition-all"
                                                >
                                                <input
                                                    type="text"
                                                    :value="form.settings.find(s => s.key === setting.key)?.value"
                                                    @input="updateSetting(setting.key, $event.target.value)"
                                                    placeholder="#3B82F6"
                                                    class="flex-1 px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all sm:text-sm font-mono"
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
                                                class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all sm:text-sm"
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
                                                    :placeholder="form.settings.find(s => s.key === setting.key)?.value ? '••••••••••••' : 'Enter secure value'"
                                                    class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all sm:text-sm pr-10"
                                                >
                                                <button
                                                    type="button"
                                                    @click="togglePasswordVisibility(setting.key)"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
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
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-2xl shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all" style="background: #14a800;"
                            >
                                <CheckIcon class="h-5 w-5 mr-2" />
                                {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Panel -->
                <div class="mt-6 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <CogIcon class="h-5 w-5" style="color: #3b82f6;" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-neutral-900 dark:text-white">Settings Information</h3>
                            <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
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
