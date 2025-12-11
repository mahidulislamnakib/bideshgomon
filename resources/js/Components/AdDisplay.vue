<template>
    <div v-if="ad && shouldShow" :class="containerClass">
        <!-- Ad Container -->
        <div 
            :class="[
                'relative overflow-hidden transition-all duration-300',
                adClass
            ]"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
        >
            <!-- Ad Content -->
            <div class="flex flex-col h-full">
                <!-- Image (if provided) -->
                <div v-if="ad.image_url" class="relative overflow-hidden">
                    <img 
                        :src="ad.image_url" 
                        :alt="ad.title"
                        class="w-full h-auto object-cover"
                        :class="imageClass"
                        @load="recordImpression"
                    />
                    <!-- Sponsored Badge -->
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 bg-black/50 backdrop-blur-sm text-white text-xs font-medium rounded">
                            Sponsored
                        </span>
                    </div>
                </div>

                <!-- Text Content -->
                <div :class="contentClass">
                    <h3 v-if="ad.title" class="font-semibold text-gray-900 mb-2" :class="titleClass">
                        {{ ad.title }}
                    </h3>
                    <p v-if="ad.body" class="text-gray-600 text-sm mb-4">
                        {{ ad.body }}
                    </p>

                    <!-- CTA Button -->
                    <a
                        v-if="ad.cta_url"
                        :href="ad.cta_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="recordClick"
                        class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md hover:shadow-lg"
                    >
                        {{ ad.cta_text || 'Learn More' }}
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Ad Label -->
            <div v-if="!ad.image_url" class="absolute top-2 right-2">
                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded">
                    Ad
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    placement: {
        type: String,
        required: true,
        validator: (value) => ['sidebar', 'inline', 'empty_state', 'banner', 'sticky_bottom', 'modal'].includes(value)
    },
    page: {
        type: String,
        required: true
    },
    deviceType: {
        type: String,
        default: null,
        validator: (value) => !value || ['desktop', 'tablet', 'mobile'].includes(value)
    }
});

const ad = ref(null);
const shouldShow = ref(false);
const isHovered = ref(false);
const impressionRecorded = ref(false);

// Styling based on placement
const containerClass = computed(() => {
    const baseClass = 'ad-container';
    switch (props.placement) {
        case 'sidebar':
            return `${baseClass} mb-6`;
        case 'banner':
            return `${baseClass} w-full mb-4`;
        case 'inline':
            return `${baseClass} my-6`;
        case 'sticky_bottom':
            return `${baseClass} fixed bottom-0 left-0 right-0 z-50 shadow-2xl`;
        case 'modal':
            return `${baseClass} max-w-md mx-auto`;
        default:
            return baseClass;
    }
});

const adClass = computed(() => {
    switch (props.placement) {
        case 'sidebar':
            return 'bg-white rounded-xl shadow-lg border-2 border-gray-200 hover:shadow-xl';
        case 'banner':
            return 'bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg shadow-md p-6';
        case 'inline':
            return 'bg-white rounded-xl shadow-md border border-gray-200 p-6';
        case 'sticky_bottom':
            return 'bg-white border-t-2 border-gray-300 p-4';
        case 'modal':
            return 'bg-white rounded-2xl shadow-2xl border-2 border-indigo-200 p-6';
        default:
            return 'bg-white rounded-lg shadow p-4';
    }
});

const contentClass = computed(() => {
    if (props.placement === 'banner') {
        return 'flex items-center justify-between';
    }
    return ad.value?.image_url ? 'p-4' : 'p-6';
});

const imageClass = computed(() => {
    switch (props.placement) {
        case 'sidebar':
            return 'h-48 rounded-t-xl';
        case 'banner':
            return 'h-32 rounded-lg mr-4 w-32 flex-shrink-0';
        case 'inline':
            return 'h-64 rounded-t-xl';
        default:
            return 'h-auto';
    }
});

const titleClass = computed(() => {
    switch (props.placement) {
        case 'banner':
            return 'text-lg';
        case 'sticky_bottom':
            return 'text-base';
        default:
            return 'text-lg';
    }
});

// Fetch ad from API
const fetchAd = async () => {
    try {
        const response = await axios.post('/api/ads/fetch', {
            placement: props.placement,
            page: props.page,
            device_type: props.deviceType || detectDeviceType()
        });

        if (response.data.ad) {
            ad.value = response.data.ad;
            shouldShow.value = true;
        }
    } catch (error) {
        console.error('Failed to fetch ad:', error);
        shouldShow.value = false;
    }
};

// Record impression (when ad becomes visible)
const recordImpression = async () => {
    if (!ad.value || impressionRecorded.value) return;

    try {
        await axios.post('/api/ads/impression', {
            ad_id: ad.value.id,
            page: props.page,
            device_type: props.deviceType || detectDeviceType()
        });
        impressionRecorded.value = true;
    } catch (error) {
        console.error('Failed to record impression:', error);
    }
};

// Record click
const recordClick = async () => {
    if (!ad.value) return;

    try {
        await axios.post('/api/ads/click', {
            ad_id: ad.value.id,
            page: props.page,
            device_type: props.deviceType || detectDeviceType()
        });
    } catch (error) {
        console.error('Failed to record click:', error);
    }
};

// Detect device type
const detectDeviceType = () => {
    const width = window.innerWidth;
    if (width < 768) return 'mobile';
    if (width < 1024) return 'tablet';
    return 'desktop';
};

// Lifecycle
onMounted(() => {
    fetchAd();
});

// Watch for page changes
watch(() => props.page, () => {
    fetchAd();
    impressionRecorded.value = false;
});
</script>


