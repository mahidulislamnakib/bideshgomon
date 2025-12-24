<template>
    <div v-if="ad && !isDismissed" ref="adContainer" :class="containerClasses">
        <!-- Sidebar Ad -->
        <div v-if="slot === 'sidebar'" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase">Sponsored</span>
                <button
                    v-if="dismissible"
                    @click="dismiss"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    aria-label="Close ad"
                >
                    <XMarkIcon class="h-4 w-4" />
                </button>
            </div>
            <div class="p-4">
                <img
                    v-if="ad.image_url"
                    :src="getImageUrl(ad.image_url)"
                    :alt="ad.title"
                    class="w-full h-40 object-cover rounded-lg mb-4"
                    loading="lazy"
                />
                <h3 class="text-base font-bold text-gray-900 mb-2">{{ ad.title }}</h3>
                <p v-if="ad.body" class="text-sm text-gray-600 mb-4 line-clamp-3">{{ ad.body }}</p>
                <a
                    v-if="ad.cta_url"
                    :href="ad.cta_url"
                    @click="handleClick"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block w-full bg-red-600 hover:bg-growth-700 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    {{ ad.cta_text }}
                </a>
            </div>
        </div>

        <!-- Inline Ad -->
        <div v-else-if="slot === 'inline'" class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200 overflow-hidden">
            <div class="flex items-start gap-4 p-4">
                <img
                    v-if="ad.image_url"
                    :src="getImageUrl(ad.image_url)"
                    :alt="ad.title"
                    class="w-24 h-24 object-cover rounded-lg flex-shrink-0"
                    loading="lazy"
                />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-semibold text-growth-600 uppercase">Sponsored</span>
                        <button
                            v-if="dismissible"
                            @click="dismiss"
                            class="ml-auto text-gray-400 hover:text-gray-600 transition-colors"
                            aria-label="Close ad"
                        >
                            <XMarkIcon class="h-4 w-4" />
                        </button>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ ad.title }}</h3>
                    <p v-if="ad.body" class="text-sm text-gray-700 mb-3 line-clamp-2">{{ ad.body }}</p>
                    <a
                        v-if="ad.cta_url"
                        :href="ad.cta_url"
                        @click="handleClick"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 bg-growth-600 hover:bg-growth-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        {{ ad.cta_text }}
                        <ArrowRightIcon class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Empty State Ad (Large Card) -->
        <div v-else-if="slot === 'empty_state'" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="relative">
                <img
                    v-if="ad.image_url"
                    :src="getImageUrl(ad.image_url)"
                    :alt="ad.title"
                    class="w-full h-64 object-cover"
                    loading="lazy"
                />
                <div v-else class="w-full h-64 bg-gradient-to-br from-red-100 to-purple-100"></div>
                <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full shadow-md">
                    <span class="text-xs font-bold text-gray-700 uppercase">Sponsored</span>
                </div>
                <button
                    v-if="dismissible"
                    @click="dismiss"
                    class="absolute top-4 left-4 bg-white p-2 rounded-full shadow-md text-gray-600 hover:text-gray-900 transition-colors"
                    aria-label="Close ad"
                >
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>
            <div class="p-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ ad.title }}</h2>
                <p v-if="ad.body" class="text-lg text-gray-600 mb-6 max-w-2xl mx-auto">{{ ad.body }}</p>
                <a
                    v-if="ad.cta_url"
                    :href="ad.cta_url"
                    @click="handleClick"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-growth-700 text-white px-8 py-4 rounded-xl text-lg font-bold transition-colors shadow-lg"
                >
                    {{ ad.cta_text }}
                    <ArrowRightIcon class="h-6 w-6" />
                </a>
            </div>
        </div>

        <!-- Banner Ad -->
        <div v-else-if="slot === 'banner'" class="bg-gradient-to-r from-red-500 to-purple-600 text-white rounded-lg overflow-hidden shadow-lg">
            <div class="flex items-center justify-between gap-4 p-4">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <img
                        v-if="ad.image_url"
                        :src="getImageUrl(ad.image_url)"
                        :alt="ad.title"
                        class="w-16 h-16 object-cover rounded-lg flex-shrink-0"
                        loading="lazy"
                    />
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-semibold text-white/80 uppercase mb-1 block">Sponsored</span>
                        <h3 class="text-lg font-bold mb-1 truncate">{{ ad.title }}</h3>
                        <p v-if="ad.body" class="text-sm text-white/90 truncate">{{ ad.body }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a
                        v-if="ad.cta_url"
                        :href="ad.cta_url"
                        @click="handleClick"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="bg-white text-red-600 hover:bg-gray-100 px-6 py-2 rounded-lg font-bold transition-colors whitespace-nowrap"
                    >
                        {{ ad.cta_text }}
                    </a>
                    <button
                        v-if="dismissible"
                        @click="dismiss"
                        class="text-white/80 hover:text-white transition-colors p-2"
                        aria-label="Close ad"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Ad (Mobile) -->
        <div v-else-if="slot === 'sticky_bottom'" 
             class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-200 shadow-2xl z-50 animate-slide-up">
            <div class="max-w-7xl mx-auto px-4 py-3">
                <div class="flex items-center gap-3">
                    <img
                        v-if="ad.image_url"
                        :src="getImageUrl(ad.image_url)"
                        :alt="ad.title"
                        class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                        loading="lazy"
                    />
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-semibold text-gray-500 uppercase block mb-0.5">Sponsored</span>
                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ ad.title }}</h3>
                    </div>
                    <a
                        v-if="ad.cta_url"
                        :href="ad.cta_url"
                        @click="handleClick"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="bg-red-600 hover:bg-growth-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors whitespace-nowrap"
                    >
                        {{ ad.cta_text }}
                    </a>
                    <button
                        @click="dismiss"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1"
                        aria-label="Close ad"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { XMarkIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    slot: {
        type: String,
        required: true,
        validator: (value) => ['sidebar', 'inline', 'empty_state', 'banner', 'sticky_bottom', 'modal'].includes(value)
    },
    page: {
        type: String,
        required: true
    },
    dismissible: {
        type: Boolean,
        default: true
    }
});

const ad = ref(null);
const adContainer = ref(null);
const isDismissed = ref(false);
const isVisible = ref(false);
const impressionRecorded = ref(false);

// Device detection
const deviceType = computed(() => {
    if (typeof window === 'undefined') return 'desktop';
    const width = window.innerWidth;
    if (width < 768) return 'mobile';
    if (width < 1024) return 'tablet';
    return 'desktop';
});

// Container classes based on slot type
const containerClasses = computed(() => {
    const classes = [];
    
    // Hide sidebar on mobile/tablet
    if (props.slot === 'sidebar' && ['mobile', 'tablet'].includes(deviceType.value)) {
        classes.push('hidden');
    }
    
    // Hide sticky bottom on desktop
    if (props.slot === 'sticky_bottom' && deviceType.value === 'desktop') {
        classes.push('hidden');
    }
    
    return classes.join(' ');
});

// Get full image URL
const getImageUrl = (url) => {
    if (url.startsWith('http')) return url;
    return `/storage/${url}`;
};

// Check if ad is dismissed
const checkDismissed = () => {
    const dismissed = localStorage.getItem(`ad_dismissed_${ad.value?.id}`);
    if (dismissed) {
        const dismissedAt = new Date(dismissed);
        const now = new Date();
        const hoursSinceDismissed = (now - dismissedAt) / 1000 / 60 / 60;
        
        // Show ad again after 24 hours
        if (hoursSinceDismissed < 24) {
            isDismissed.value = true;
            return true;
        } else {
            localStorage.removeItem(`ad_dismissed_${ad.value?.id}`);
        }
    }
    return false;
};

// Dismiss ad
const dismiss = () => {
    isDismissed.value = true;
    if (ad.value) {
        localStorage.setItem(`ad_dismissed_${ad.value.id}`, new Date().toISOString());
    }
};

// Fetch ad from API
const fetchAd = async () => {
    try {
        const response = await axios.post('/api/ads/fetch', {
            placement: props.slot,
            page: props.page,
            device_type: deviceType.value
        });
        
        if (response.data.ad) {
            ad.value = response.data.ad;
            checkDismissed();
        }
    } catch (error) {
        console.error('Failed to fetch ad:', error);
    }
};

// Record impression
const recordImpression = async () => {
    if (!ad.value || impressionRecorded.value) return;
    
    try {
        await axios.post('/api/ads/impression', {
            ad_id: ad.value.id,
            page: props.page,
            device_type: deviceType.value
        });
        impressionRecorded.value = true;
    } catch (error) {
        console.error('Failed to record impression:', error);
    }
};

// Handle click
const handleClick = async (event) => {
    if (!ad.value) return;
    
    try {
        await axios.post('/api/ads/click', {
            ad_id: ad.value.id,
            page: props.page,
            device_type: deviceType.value
        });
    } catch (error) {
        console.error('Failed to record click:', error);
    }
};

// Intersection Observer for lazy loading and impression tracking
let observer;

const setupObserver = () => {
    if (!adContainer.value || typeof IntersectionObserver === 'undefined') return;
    
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !isVisible.value) {
                    isVisible.value = true;
                    recordImpression();
                }
            });
        },
        {
            threshold: 0.5, // At least 50% visible
            rootMargin: '50px'
        }
    );
    
    observer.observe(adContainer.value);
};

// Lifecycle
onMounted(async () => {
    await fetchAd();
    if (ad.value && !isDismissed.value) {
        setupObserver();
    }
});

onUnmounted(() => {
    if (observer && adContainer.value) {
        observer.unobserve(adContainer.value);
    }
});
</script>

