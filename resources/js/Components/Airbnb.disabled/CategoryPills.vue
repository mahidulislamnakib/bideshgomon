<template>
    <div class="category-pills-container">
        <!-- Scroll Left Button -->
        <button
            v-if="showLeftButton"
            @click="scrollLeft"
            class="scroll-button scroll-button-left"
        >
            <ChevronLeftIcon class="h-4 w-4" />
        </button>

        <!-- Categories Pills -->
        <div
            ref="scrollContainer"
            class="categories-scroll"
            @scroll="handleScroll"
        >
            <button
                v-for="category in categories"
                :key="category.id"
                @click="selectCategory(category.id)"
                :class="[
                    'category-pill',
                    selectedCategory === category.id ? 'category-pill-active' : 'category-pill-inactive'
                ]"
            >
                <span class="text-xl">{{ category.icon }}</span>
                <span class="text-xs font-medium">{{ category.name }}</span>
            </button>
        </div>

        <!-- Scroll Right Button -->
        <button
            v-if="showRightButton"
            @click="scrollRight"
            class="scroll-button scroll-button-right"
        >
            <ChevronRightIcon class="h-4 w-4" />
        </button>

        <!-- Filters Button -->
        <button
            @click="openFilters"
            class="filters-button"
        >
            <AdjustmentsHorizontalIcon class="h-4 w-4" />
            <span class="text-xs font-medium">Filters</span>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    AdjustmentsHorizontalIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    categories: {
        type: Array,
        required: true,
        /**
         * Expected structure:
         * [
         *   { id: string, name: string, icon: string }
         * ]
         */
    },
    initialCategory: {
        type: String,
        default: 'all'
    }
})

const emit = defineEmits(['category-change', 'filters-open'])

const scrollContainer = ref(null)
const showLeftButton = ref(false)
const showRightButton = ref(true)
const selectedCategory = ref(props.initialCategory)

const handleScroll = () => {
    if (!scrollContainer.value) return
    
    const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value
    
    showLeftButton.value = scrollLeft > 0
    showRightButton.value = scrollLeft < scrollWidth - clientWidth - 10
}

const scrollLeft = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({
            left: -300,
            behavior: 'smooth'
        })
    }
}

const scrollRight = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({
            left: 300,
            behavior: 'smooth'
        })
    }
}

const selectCategory = (categoryId) => {
    selectedCategory.value = categoryId
    emit('category-change', categoryId)
}

const openFilters = () => {
    emit('filters-open')
}

onMounted(() => {
    handleScroll()
    window.addEventListener('resize', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleScroll)
})
</script>

<!-- Temporarily disabled for debugging
-->
