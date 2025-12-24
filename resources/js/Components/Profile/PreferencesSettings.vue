<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg">
                    <CogIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Preferences & Settings</h3>
                    <p class="text-sm text-gray-500">Customize your experience and manage preferences</p>
                </div>
            </div>
        </div>

        <!-- Preferred Destinations -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <GlobeAltIcon class="w-6 h-6 text-growth-600" />
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Preferred Destinations</h4>
                    <p class="text-sm text-gray-600">Countries you're interested in visiting or working</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- Popular Countries (Quick Select) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">Popular Destinations</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="destination in popularDestinations"
                            :key="destination"
                            @click="toggleDestination(destination)"
                            :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-all',
                                form.preferred_destinations?.includes(destination)
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            {{ destination }}
                        </button>
                    </div>
                </div>

                <!-- All Countries (Searchable Dropdown) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">All Countries</label>
                    <div class="relative">
                        <input
                            v-model="countrySearch"
                            @focus="showCountryDropdown = true"
                            @blur="hideCountryDropdown"
                            type="text"
                            placeholder="Search and select countries..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                        />
                        <div
                            v-if="showCountryDropdown && filteredCountries.length > 0"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                        >
                            <button
                                v-for="country in filteredCountries"
                                :key="country.name"
                                @mousedown.prevent="toggleDestination(country.name)"
                                :class="[
                                    'w-full text-left px-4 py-2 hover:bg-gray-50 transition-colors flex items-center justify-between',
                                    form.preferred_destinations?.includes(country.name) ? 'bg-blue-50' : ''
                                ]"
                            >
                                <span class="text-sm">{{ country.name }}</span>
                                <span v-if="form.preferred_destinations?.includes(country.name)" class="text-growth-600 text-xs">✓ Selected</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selected Destinations -->
                <div v-if="form.preferred_destinations && form.preferred_destinations.length > 0">
                    <label class="block text-xs font-medium text-gray-700 mb-2">Selected Destinations ({{ form.preferred_destinations.length }})</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="destination in form.preferred_destinations"
                            :key="destination"
                            @click="toggleDestination(destination)"
                            class="px-3 py-1 bg-growth-600 text-white text-sm rounded-full hover:bg-growth-700 transition-all flex items-center gap-1"
                        >
                            {{ destination }}
                            <span class="text-xs">×</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Interests -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <SparklesIcon class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Service Interests</h4>
                    <p class="text-sm text-gray-600">Services you're interested in</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <label
                    v-for="service in availableServices"
                    :key="service.value"
                    class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all hover:bg-gray-50"
                    :class="form.service_interests?.includes(service.value) ? 'border-purple-500 bg-purple-50' : 'border-gray-200'"
                >
                    <input
                        type="checkbox"
                        :value="service.value"
                        v-model="form.service_interests"
                        class="rounded text-purple-600 focus:ring-purple-500"
                    />
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ service.emoji }} {{ service.label }}</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Communication Preferences -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-green-100 rounded-lg">
                    <EnvelopeIcon class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Communication Preferences</h4>
                    <p class="text-sm text-gray-600">How you'd like to be contacted</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <label
                    v-for="method in communicationMethods"
                    :key="method.value"
                    class="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                >
                    <div class="flex items-center gap-3">
                        <component :is="method.icon" class="w-5 h-5 text-gray-500" />
                        <div>
                            <div class="font-medium text-gray-900">{{ method.label }}</div>
                            <div class="text-sm text-gray-600">{{ method.description }}</div>
                        </div>
                    </div>
                    <input
                        type="checkbox"
                        :value="method.value"
                        v-model="form.communication_preferences"
                        class="rounded text-green-600 focus:ring-green-500 w-5 h-5"
                    />
                </label>
            </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <BellIcon class="w-6 h-6 text-yellow-600" />
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Notification Channels</h4>
                    <p class="text-sm text-gray-600">Choose how you receive notifications</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <div
                    v-for="channel in notificationChannels"
                    :key="channel.key"
                    class="flex items-center justify-between p-3 border rounded-lg"
                >
                    <div class="flex items-center gap-3">
                        <component :is="channel.icon" :class="`w-5 h-5 ${channel.color}`" />
                        <div>
                            <div class="font-medium text-gray-900">{{ channel.label }}</div>
                            <div class="text-sm text-gray-600">{{ channel.description }}</div>
                        </div>
                    </div>
                    <button
                        @click="toggleNotification(channel.key)"
                        :class="[
                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                            form.notifications[channel.key] ? 'bg-yellow-600' : 'bg-gray-200'
                        ]"
                    >
                        <span
                            :class="[
                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                form.notifications[channel.key] ? 'translate-x-6' : 'translate-x-1'
                            ]"
                        />
                    </button>
                </div>
            </div>
        </div>

        <!-- Regional Settings -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <GlobeAltIcon class="w-6 h-6 text-growth-600" />
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Regional Settings</h4>
                    <p class="text-sm text-gray-600">Language, timezone, and currency preferences</p>
                </div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Language
                    </label>
                    <select
                        v-model="form.language"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                    >
                        <option v-for="language in languages" :key="language.id" :value="language.id">
                            {{ language.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Timezone
                    </label>
                    <select
                        v-model="form.timezone"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                    >
                        <option value="Asia/Dhaka">Asia/Dhaka (GMT+6)</option>
                        <option value="Asia/Dubai">Asia/Dubai (GMT+4)</option>
                        <option value="Asia/Riyadh">Asia/Riyadh (GMT+3)</option>
                        <option value="Europe/London">Europe/London (GMT+0)</option>
                        <option value="America/New_York">America/New York (GMT-5)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Currency
                    </label>
                    <select
                        v-model="form.currency"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                    >
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.code">
                            {{ currency.symbol }} {{ currency.code }} - {{ currency.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Appearance -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-pink-100 rounded-lg">
                    <SparklesIcon class="w-6 h-6 text-pink-600" />
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Appearance</h4>
                    <p class="text-sm text-gray-600">Customize how the platform looks</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Theme</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button
                            v-for="theme in themes"
                            :key="theme.value"
                            @click="form.theme = theme.value"
                            :class="[
                                'p-4 border-2 rounded-lg text-center transition-all',
                                form.theme === theme.value
                                    ? 'border-pink-500 bg-pink-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <div class="text-2xl mb-2">{{ theme.icon }}</div>
                            <div class="font-medium text-gray-900">{{ theme.label }}</div>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Font Size</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button
                            v-for="size in fontSizes"
                            :key="size.value"
                            @click="form.font_size = size.value"
                            :class="[
                                'p-4 border-2 rounded-lg text-center transition-all',
                                form.font_size === size.value
                                    ? 'border-pink-500 bg-pink-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <div :class="`font-bold mb-1 ${size.class}`">Aa</div>
                            <div class="text-sm text-gray-700">{{ size.label }}</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button
                @click="savePreferences"
                :disabled="form.processing"
                class="px-6 py-3 bg-growth-600 text-white rounded-lg hover:bg-growth-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
            >
                <span v-if="!form.processing">Save Preferences</span>
                <span v-else>Saving...</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    GlobeAltIcon,
    SparklesIcon,
    EnvelopeIcon,
    BellIcon,
    PhoneIcon,
    ChatBubbleLeftIcon,
    DevicePhoneMobileIcon,
    CogIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    userProfile: {
        type: Object,
        required: true
    },
    countries: {
        type: Array,
        default: () => []
    },
    serviceCategories: {
        type: Array,
        default: () => []
    },
    currencies: {
        type: Array,
        default: () => []
    },
    languages: {
        type: Array,
        default: () => []
    }
});

const customDestination = ref('');
const countrySearch = ref('');
const showCountryDropdown = ref(false);

// Use countries from database
const popularDestinations = computed(() => {
    if (!props.countries || props.countries.length === 0) return [];
    
    // Get top popular countries for migration/work
    const popularCountryNames = [
        'Saudi Arabia', 'United Arab Emirates', 'UAE', 'Qatar', 'Oman', 
        'Kuwait', 'Bahrain', 'Malaysia', 'Singapore', 
        'United Kingdom', 'UK', 'United States', 'USA', 'Canada', 'Australia'
    ];
    
    return props.countries
        .filter(c => popularCountryNames.some(name => 
            c.name.toLowerCase().includes(name.toLowerCase()) || 
            name.toLowerCase().includes(c.name.toLowerCase())
        ))
        .map(c => c.name)
        .slice(0, 12);
});

// Filtered countries based on search
const filteredCountries = computed(() => {
    if (!props.countries || props.countries.length === 0) return [];
    
    const searchTerm = countrySearch.value.toLowerCase().trim();
    
    if (!searchTerm) {
        // Show all countries when no search term
        return props.countries;
    }
    
    // Filter by name
    return props.countries.filter(c => 
        c.name.toLowerCase().includes(searchTerm)
    );
});

// Hide dropdown with delay to allow click events
const hideCountryDropdown = () => {
    setTimeout(() => {
        showCountryDropdown.value = false;
    }, 200);
};

// Map Font Awesome icons to emojis
const iconToEmoji = {
    'fa-passport': '✈️',
    'fa-plane': '🎫',
    'fa-graduation-cap': '🎓',
    'fa-briefcase': '💼',
    'fa-file-alt': '📋',
    'fa-ellipsis-h': '⚙️',
    'fa-hotel': '🏨',
    'fa-car': '🚗',
    'fa-medkit': '🏥'
};

// Use service categories from database
const availableServices = computed(() => {
    if (!props.serviceCategories || props.serviceCategories.length === 0) return [];
    
    return props.serviceCategories.map(service => ({
        emoji: iconToEmoji[service.icon] || '📋',
        label: service.name,
        value: service.id.toString()
    }));
});

const communicationMethods = [
    {
        icon: EnvelopeIcon,
        label: 'Email',
        description: 'Receive updates via email',
        value: 'email'
    },
    {
        icon: PhoneIcon,
        label: 'Phone Call',
        description: 'Contact via phone calls',
        value: 'phone'
    },
    {
        icon: DevicePhoneMobileIcon,
        label: 'SMS',
        description: 'Text message notifications',
        value: 'sms'
    },
    {
        icon: ChatBubbleLeftIcon,
        label: 'WhatsApp',
        description: 'WhatsApp messaging',
        value: 'whatsapp'
    }
];

const notificationChannels = [
    {
        key: 'email',
        icon: EnvelopeIcon,
        label: 'Email Notifications',
        description: 'Receive notifications via email',
        color: 'text-growth-600'
    },
    {
        key: 'sms',
        icon: DevicePhoneMobileIcon,
        label: 'SMS Notifications',
        description: 'Get SMS alerts for important updates',
        color: 'text-green-600'
    },
    {
        key: 'push',
        icon: BellIcon,
        label: 'Push Notifications',
        description: 'Browser push notifications',
        color: 'text-purple-600'
    },
    {
        key: 'whatsapp',
        icon: ChatBubbleLeftIcon,
        label: 'WhatsApp Notifications',
        description: 'Receive updates on WhatsApp',
        color: 'text-green-500'
    }
];

const themes = [
    { value: 'light', label: 'Light', icon: '☀️' },
    { value: 'dark', label: 'Dark', icon: '🌙' },
    { value: 'system', label: 'System', icon: '💻' }
];

const fontSizes = [
    { value: 'small', label: 'Small', class: 'text-sm' },
    { value: 'medium', label: 'Medium', class: 'text-base' },
    { value: 'large', label: 'Large', class: 'text-lg' }
];

const form = useForm({
    preferences: {
        preferred_destinations: props.userProfile.preferences?.preferred_destinations || [],
        service_interests: props.userProfile.preferences?.service_interests || [],
        communication_preferences: props.userProfile.preferences?.communication_preferences || [],
        language: props.userProfile.preferences?.language || 'en',
        timezone: props.userProfile.preferences?.timezone || 'Asia/Dhaka',
        currency: props.userProfile.preferences?.currency || 'BDT',
        notifications: {
            email: props.userProfile.preferences?.notifications?.email ?? true,
            sms: props.userProfile.preferences?.notifications?.sms ?? false,
            push: props.userProfile.preferences?.notifications?.push ?? true,
            whatsapp: props.userProfile.preferences?.notifications?.whatsapp ?? false
        },
        theme: props.userProfile.preferences?.theme || 'system',
        font_size: props.userProfile.preferences?.font_size || 'medium'
    }
});

// Create reactive proxies for easier binding
const form_data = reactive({
    preferred_destinations: form.preferences.preferred_destinations,
    service_interests: form.preferences.service_interests,
    communication_preferences: form.preferences.communication_preferences,
    language: form.preferences.language,
    timezone: form.preferences.timezone,
    currency: form.preferences.currency,
    notifications: form.preferences.notifications,
    theme: form.preferences.theme,
    font_size: form.preferences.font_size
});

// Sync reactive data back to form
Object.keys(form_data).forEach(key => {
    Object.defineProperty(form, key, {
        get() { return form_data[key]; },
        set(value) { form_data[key] = value; }
    });
});

const toggleDestination = (destination) => {
    const index = form.preferred_destinations.indexOf(destination);
    if (index > -1) {
        form.preferred_destinations.splice(index, 1);
    } else {
        form.preferred_destinations.push(destination);
    }
    // Clear search after selection
    countrySearch.value = '';
};

const addCustomDestination = () => {
    if (customDestination.value.trim() && !form.preferred_destinations.includes(customDestination.value.trim())) {
        form.preferred_destinations.push(customDestination.value.trim());
        customDestination.value = '';
    }
};

const toggleNotification = (key) => {
    form.notifications[key] = !form.notifications[key];
};

const savePreferences = () => {
    form.post(route('profile.preferences.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        }
    });
};
</script>
