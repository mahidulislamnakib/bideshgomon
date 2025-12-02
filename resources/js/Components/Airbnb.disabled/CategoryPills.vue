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
<style scoped>
.category-pills-container {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 5rem;
    border-bottom: 1px solid rgb(229 231 235);
    background-color: white;
}

.categories-scroll {
    display: flex;
    gap: 2rem;
    overflow-x: auto;
    flex: 1;
    scroll-behavior: smooth;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.category-pill {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 200ms;
    white-space: nowrap;
    flex-shrink: 0;
    border: 2px solid transparent;
}

.category-pill-inactive {
    color: rgb(75 85 99);
}

.category-pill-inactive:hover {
    color: rgb(17 24 39);
    background-color: rgb(249 250 251);
}

.category-pill-active {
    color: rgb(17 24 39);
    border-color: rgb(17 24 39);
}

.scroll-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    border: 1px solid rgb(209 213 219);
    border-radius: 9999px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    transition: all 200ms;
    z-index: 10;
}

.scroll-button:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-50%) scale(1.1);
}

.scroll-button-left {
    left: 1rem;
}

.scroll-button-right {
    right: 6rem;
}

.filters-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
    transition: all 200ms;
    flex-shrink: 0;
}

.filters-button:hover {
    border-color: rgb(17 24 39);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .category-pills-container {
        padding: 1rem;
    }
    
    .scroll-button {
        display: none;
    }
    
    .categories-scroll {
        gap: 1rem;
    }
    
    .category-pill {
        padding: 0.375rem 0.75rem;
        gap: 0.25rem;
    }
}
</style>
-->
