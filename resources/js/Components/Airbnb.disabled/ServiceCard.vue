<template>
    <div class="service-card group cursor-pointer" @click="navigateToService">
        <!-- Card Header with Gradient -->\n        <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-emerald-50 to-emerald-100 group-hover:from-emerald-100 group-hover:to-emerald-200 transition-all duration-300">
            <!-- Large Initial Letter -->
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-8xl font-bold text-emerald-500/10">{{ service.title.charAt(0) }}</span>
            </div>
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/10 to-transparent"></div>

            <!-- Wishlist Button -->
            <button
                @click.stop="toggleWishlist"
                class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-white/90 hover:bg-white rounded-full shadow-md transition-all hover:scale-110"
            >
                <HeartIcon
                    :class="[
                        'h-5 w-5 transition-colors',
                        isWishlisted ? 'fill-red-500 text-red-500' : 'text-gray-700'
                    ]"
                />
            </button>

            <!-- Badge -->
            <div v-if="service.badge" class="absolute top-3 left-3">
                <span class="px-3 py-1 bg-emerald-500 text-white text-xs font-semibold rounded-full shadow-md">
                    {{ service.badge }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="mt-3 space-y-1">
            <!-- Location & Rating -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1 text-sm">
                    <MapPinIcon class="h-4 w-4 text-gray-500" />
                    <span class="font-semibold text-gray-900">{{ service.location }}</span>
                </div>
                <div v-if="service.rating" class="flex items-center gap-1">
                    <StarIcon class="h-4 w-4 fill-gray-900 text-gray-900" />
                    <span class="text-sm font-semibold text-gray-900">{{ service.rating }}</span>
                    <span class="text-sm text-gray-500">({{ service.reviews }})</span>
                </div>
            </div>

            <!-- Title -->
            <h3 class="text-base font-medium text-gray-700 line-clamp-2">
                {{ service.title }}
            </h3>

            <!-- Meta Info -->
            <p class="text-sm text-gray-500">
                {{ service.duration }} • {{ service.type }}
            </p>

            <!-- Price -->
            <div class="flex items-baseline gap-1 pt-1">
                <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(service.price) }}</span>
                <span class="text-sm text-gray-500">/ person</span>
            </div>

            <!-- Features Pills (Optional) -->
            <div v-if="service.features && service.features.length > 0" class="flex flex-wrap gap-1.5 pt-2">
                <span
                    v-for="feature in service.features.slice(0, 3)"
                    :key="feature"
                    class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full"
                >
                    {{ feature }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
    HeartIcon,
    StarIcon,
    MapPinIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    service: {
        type: Object,
        required: true,
        /**
         * Expected structure:
         * {
         *   id: number,
         *   title: string,
         *   location: string,
         *   images: string[],
         *   price: number,
         *   rating: number,
         *   reviews: number,
         *   duration: string,
         *   type: string,
         *   badge: string,
         *   features: string[],
         *   isWishlisted: boolean
         * }
         */
    }
})

const emit = defineEmits(['wishlist-toggle'])

const { formatCurrency } = useBangladeshFormat()

const currentImageIndex = ref(0)
const isWishlisted = ref(props.service.isWishlisted || false)

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--
    }
}

const nextImage = () => {
    if (currentImageIndex.value < props.service.images.length - 1) {
        currentImageIndex.value++
    }
}

const toggleWishlist = () => {
    isWishlisted.value = !isWishlisted.value
    emit('wishlist-toggle', props.service.id, isWishlisted.value)
}

const navigateToService = () => {
    router.visit(route('services.show', props.service.id))
}
</script>

<!-- Temporarily disabled for debugging
-->
