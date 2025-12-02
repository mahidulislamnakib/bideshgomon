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
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Array,
    currentGroup: String,
    groups: Object,
});

// Track which accordion panels are expanded
const expandedGroups = ref({
    [props.currentGroup || 'general']: true, // Open current group by default
});

const visiblePasswords = ref({});
const expandedApiGroups = ref({
    authentication: true,
    payment: false,
    cloud: false,
    communication: false,
    ai: false,
    security: false
});

const toggleGroup = (groupKey) => {
    expandedGroups.value[groupKey] = !expandedGroups.value[groupKey];
};

const togglePasswordVisibility = (key) => {
    visiblePasswords.value[key] = !visiblePasswords.value[key];
};

const toggleApiGroup = (group) => {
    expandedApiGroups.value[group] = !expandedApiGroups.value[group];
};

// Settings organized by category with enhanced metadata
const groupConfig = {
    general: { icon: CogIcon, label: 'General', color: 'indigo', description: 'Site name, tagline, and basic information' },
    branding: { icon: SparklesIcon, label: 'Branding', color: 'purple', description: 'Logo, favicon, and visual identity' },
    contact: { icon: ChatBubbleLeftRightIcon, label: 'Contact', color: 'green', description: 'Contact information and support details' },
    modules: { icon: CogIcon, label: 'Modules', color: 'blue', description: 'Enable or disable platform modules' },
    features: { icon: FlagIcon, label: 'Features', color: 'orange', description: 'Toggle feature flags' },
    homepage: { icon: SparklesIcon, label: 'Homepage Widgets', color: 'purple', description: 'Configure homepage display widgets' },
    jobs: { icon: BriefcaseIcon, label: 'Jobs Settings', color: 'purple', description: 'Job board configuration' },
    wallet: { icon: WalletIcon, label: 'Wallet Settings', color: 'green', description: 'Digital wallet and transactions' },
    seo: { icon: ShieldCheckIcon, label: 'SEO & Analytics', color: 'blue', description: 'Search engine and analytics configuration' },
    social: { icon: ShareIcon, label: 'Social Media', color: 'pink', description: 'Social media links and integrations' },
    email: { icon: EnvelopeIcon, label: 'Email', color: 'blue', description: 'Email configuration and SMTP settings' },
    api: { icon: KeyIcon, label: 'API Keys', color: 'red', description: 'Third-party API keys and credentials' },
    advanced: { icon: CogIcon, label: 'Advanced', color: 'gray', description: 'Technical and advanced settings' },
};

// API service configuration
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

const getGroupedApiSettings = (groupKey) => {
    const settings = props.settings.filter(s => s.group === groupKey);
    const groups = {};
    settings.forEach(setting => {
        const apiGroupKey = getApiGroupForSetting(setting);
        if (!groups[apiGroupKey]) {
            groups[apiGroupKey] = [];
        }
        groups[apiGroupKey].push(setting);
    });
    return groups;
};

const form = useForm({
    settings: props.settings.map(setting => ({
        key: setting.key,
        value: setting.type === 'boolean' 
            ? setting.value === '1' || setting.value === true 
            : setting.value,
        type: setting.type,
    })),
});

const getGroupSettings = (groupKey) => {
    return props.settings.filter(s => s.group === groupKey);
};

const submitGroup = (groupKey) => {
    const groupSettings = form.settings.filter(s => {
        const originalSetting = props.settings.find(ps => ps.key === s.key);
        return originalSetting && originalSetting.group === groupKey;
    });
    
    const submitForm = useForm({
        settings: groupSettings
    });
    
    submitForm.post(route('admin.settings.update', { group: groupKey }), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Settings saved successfully');
        },
        onError: (errors) => {
            console.error('Save errors:', errors);
        },
    });
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

const getConfiguredCount = (groupKey) => {
    const settings = getGroupSettings(groupKey);
    return settings.filter(s => {
        const formSetting = form.settings.find(fs => fs.key === s.key);
        return formSetting?.value && formSetting.value !== '';
    }).length;
};
</script>

<template>
    <Head title="Settings" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Platform Settings</h1>
                            <p class="mt-2 text-sm text-gray-600">
                                Configure your platform settings and preferences
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('admin.settings.clear-cache')"
                                method="post"
                                as="button"
                                class="inline-flex items-center px-4 py-2 border border-emerald-300 rounded-lg shadow-sm text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors"
                            >
                                <ArrowPathIcon class="h-5 w-5 mr-2" />
                                Clear Cache
                            </Link>
                            <Link
                                :href="route('admin.settings.seed')"
                                method="post"
                                as="button"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                            >
                                <ArrowPathIcon class="h-5 w-5 mr-2" />
                                Reset to Defaults
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Accordion Container -->
                <div class="space-y-3">
                    <div 
                        v-for="(label, groupKey) in groups"
                        :key="groupKey"
                        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
                    >
                        <!-- Accordion Header -->
                        <button
                            type="button"
                            @click="toggleGroup(groupKey)"
                            class="w-full flex items-center justify-between p-5 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <!-- Icon with Color Badge -->
                                <div :class="[
                                    'rounded-lg p-3 transition-colors',
                                    expandedGroups[groupKey] ? `bg-${groupConfig[groupKey]?.color || 'gray'}-100` : 'bg-gray-100'
                                ]">
                                    <component 
                                        :is="groupConfig[groupKey]?.icon || CogIcon" 
                                        :class="[
                                            'h-6 w-6 transition-colors',
                                            expandedGroups[groupKey] ? `text-${groupConfig[groupKey]?.color || 'gray'}-600` : 'text-gray-500'
                                        ]" 
                                    />
                                </div>
                                
                                <!-- Label and Description -->
                                <div class="text-left">
                                    <h3 class="text-base font-semibold text-gray-900">
                                        {{ label }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ groupConfig[groupKey]?.description || '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <!-- Settings Count Badge -->
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ getConfiguredCount(groupKey) }}/{{ getGroupSettings(groupKey).length }}
                                </span>
                                
                                <!-- Chevron Icon -->
                                <ChevronDownIcon 
                                    :class="[
                                        'h-5 w-5 text-gray-400 transition-transform duration-200',
                                        expandedGroups[groupKey] ? 'rotate-180' : ''
                                    ]"
                                />
                            </div>
                        </button>

                        <!-- Accordion Content -->
                        <div 
                            v-show="expandedGroups[groupKey]"
                            class="border-t border-gray-200 bg-gray-50"
                        >
                            <form @submit.prevent="submitGroup(groupKey)" class="p-6">
                                <!-- API Keys Special Handling -->
                                <div v-if="groupKey === 'api'" class="space-y-6">
                                    <!-- Warning Banner -->
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                        <div class="flex">
                                            <ExclamationTriangleIcon class="h-5 w-5 text-amber-400 flex-shrink-0" />
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-amber-800">Security Notice</h3>
                                                <p class="mt-1 text-sm text-amber-700">
                                                    API keys are sensitive credentials. Never share them publicly.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nested API Service Groups -->
                                    <div
                                        v-for="(apiGroupConfig, apiGroupKey) in apiServiceGroups"
                                        :key="apiGroupKey"
                                        class="bg-white border border-gray-200 rounded-lg overflow-hidden"
                                    >
                                        <button
                                            type="button"
                                            @click="toggleApiGroup(apiGroupKey)"
                                            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div :class="`rounded-lg p-2 bg-${apiGroupConfig.color}-100`">
                                                    <component 
                                                        :is="apiGroupConfig.icon" 
                                                        :class="`h-5 w-5 text-${apiGroupConfig.color}-600`" 
                                                    />
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900">
                                                    {{ apiGroupConfig.label }}
                                                </span>
                                            </div>
                                            <ChevronDownIcon 
                                                :class="[
                                                    'h-5 w-5 text-gray-400 transition-transform',
                                                    expandedApiGroups[apiGroupKey] ? 'rotate-180' : ''
                                                ]"
                                            />
                                        </button>

                                        <div
                                            v-if="expandedApiGroups[apiGroupKey]"
                                            class="border-t border-gray-200 divide-y divide-gray-100"
                                        >
                                            <div
                                                v-for="setting in getGroupedApiSettings(groupKey)[apiGroupKey] || []"
                                                :key="setting.key"
                                                class="p-4"
                                            >
                                                <label :for="setting.key" class="block text-sm font-medium text-gray-900 mb-2">
                                                    {{ (setting.key || '').split('_').map(w => ((w || '').charAt(0).toUpperCase() || '') + (w || '').slice(1)).join(' ') }}
                                                </label>
                                                
                                                <div class="relative">
                                                    <input
                                                        :id="setting.key"
                                                        :type="setting.type === 'password' && !visiblePasswords[setting.key] ? 'password' : 'text'"
                                                        :value="form.settings.find(s => s.key === setting.key)?.value"
                                                        @input="updateSetting(setting.key, $event.target.value)"
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600 focus:border-brand-red-600"
                                                        :placeholder="setting.description || ''"
                                                    />
                                                    <button
                                                        v-if="setting.type === 'password'"
                                                        type="button"
                                                        @click="togglePasswordVisibility(setting.key)"
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                                    >
                                                        <EyeIcon v-if="!visiblePasswords[setting.key]" class="h-5 w-5" />
                                                        <EyeSlashIcon v-else class="h-5 w-5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Regular Settings -->
                                <div v-else class="space-y-6">
                                    <div 
                                        v-for="setting in getGroupSettings(groupKey)"
                                        :key="setting.key"
                                        class="bg-white p-4 rounded-lg border border-gray-200"
                                    >
                                        <div v-if="setting.type === 'boolean'" class="flex items-center justify-between">
                                            <div>
                                                <label :for="setting.key" class="text-sm font-medium text-gray-900">
                                                    {{ (setting.key || '').split('_').map(w => ((w || '').charAt(0).toUpperCase() || '') + (w || '').slice(1)).join(' ') }}
                                                </label>
                                                <p v-if="setting.description" class="text-sm text-gray-500 mt-1">
                                                    {{ setting.description }}
                                                </p>
                                            </div>
                                            <input
                                                :id="setting.key"
                                                type="checkbox"
                                                :checked="form.settings.find(s => s.key === setting.key)?.value"
                                                @change="updateSetting(setting.key, $event.target.checked)"
                                                class="h-5 w-5 text-brand-red-600 border-gray-300 rounded focus:ring-brand-red-600"
                                            />
                                        </div>
                                        <div v-else>
                                            <label :for="setting.key" class="block text-sm font-medium text-gray-900 mb-2">
                                                {{ (setting.key || '').split('_').map(w => ((w || '').charAt(0).toUpperCase() || '') + (w || '').slice(1)).join(' ') }}
                                            </label>
                                            <p v-if="setting.description" class="text-sm text-gray-500 mb-2">
                                                {{ setting.description }}
                                            </p>
                                            <input
                                                :id="setting.key"
                                                :type="getInputType(setting.type)"
                                                :value="form.settings.find(s => s.key === setting.key)?.value"
                                                @input="updateSetting(setting.key, $event.target.value)"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600 focus:border-brand-red-600"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button -->
                                <div class="flex justify-end pt-6 border-t border-gray-200 mt-6">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <CheckIcon class="h-5 w-5 mr-2" />
                                        {{ form.processing ? 'Saving...' : 'Save ' + label }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
