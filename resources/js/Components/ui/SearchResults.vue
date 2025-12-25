<template>
  <div :class="['search-results', themeClasses]">
    <!-- Search Header -->
    <div v-if="showHeader" class="p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Search Results
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ totalResults }} results for "<span class="font-medium text-gray-900 dark:text-white">{{ query }}</span>"
            <span v-if="searchTime">in {{ searchTime }}ms</span>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <!-- View Toggle -->
          <div class="flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
            <button
              type="button"
              :class="[
                'p-2 rounded-md transition-colors',
                viewMode === 'list'
                  ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white'
                  : 'text-gray-500 dark:text-gray-400'
              ]"
              @click="viewMode = 'list'"
            >
              <Bars3Icon class="w-4 h-4" />
            </button>
            <button
              type="button"
              :class="[
                'p-2 rounded-md transition-colors',
                viewMode === 'grid'
                  ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white'
                  : 'text-gray-500 dark:text-gray-400'
              ]"
              @click="viewMode = 'grid'"
            >
              <Squares2X2Icon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Category Tabs -->
      <div v-if="categories.length > 0" class="flex gap-2 mt-4 overflow-x-auto pb-1">
        <button
          v-for="cat in allCategories"
          :key="cat.id"
          type="button"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
            activeCategory === cat.id
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
          @click="activeCategory = cat.id"
        >
          <component v-if="cat.icon" :is="cat.icon" class="w-4 h-4" />
          {{ cat.label }}
          <span :class="[
            'px-1.5 py-0.5 text-xs rounded-full',
            activeCategory === cat.id
              ? 'bg-blue-200 dark:bg-blue-800'
              : 'bg-gray-200 dark:bg-gray-700'
          ]">
            {{ cat.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- Results Content -->
    <div class="overflow-auto" :style="{ maxHeight: height }">
      <!-- Loading State -->
      <div v-if="loading" class="p-8">
        <div class="space-y-4">
          <div v-for="i in 5" :key="i" class="animate-pulse flex gap-4">
            <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg" />
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
              <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2" />
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else-if="viewMode === 'list'" class="divide-y divide-gray-100 dark:divide-gray-800">
        <div
          v-for="result in filteredResults"
          :key="result.id"
          class="flex gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors group"
          @click="$emit('select', result)"
        >
          <!-- Icon/Image -->
          <div class="flex-shrink-0">
            <img
              v-if="result.image"
              :src="result.image"
              :alt="result.title"
              class="w-12 h-12 rounded-lg object-cover"
            />
            <div
              v-else
              :class="[
                'w-12 h-12 rounded-lg flex items-center justify-center',
                getTypeColor(result.type)
              ]"
            >
              <component :is="getTypeIcon(result.type)" class="w-6 h-6 text-white" />
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  <span v-html="highlightQuery(result.title)" />
                </h4>
                <p v-if="result.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                  <span v-html="highlightQuery(result.description)" />
                </p>
              </div>
              <span
                v-if="result.type"
                :class="[
                  'px-2 py-0.5 text-xs font-medium rounded-full flex-shrink-0',
                  getTypeBadgeColor(result.type)
                ]"
              >
                {{ result.type }}
              </span>
            </div>

            <!-- Meta -->
            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400 dark:text-gray-500">
              <span v-if="result.path" class="flex items-center gap-1 truncate">
                <FolderIcon class="w-3 h-3" />
                {{ result.path }}
              </span>
              <span v-if="result.date" class="flex items-center gap-1">
                <CalendarIcon class="w-3 h-3" />
                {{ formatDate(result.date) }}
              </span>
              <span v-if="result.author" class="flex items-center gap-1">
                <UserIcon class="w-3 h-3" />
                {{ result.author }}
              </span>
            </div>

            <!-- Tags -->
            <div v-if="result.tags?.length" class="flex flex-wrap gap-1 mt-2">
              <span
                v-for="tag in result.tags"
                :key="tag"
                class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full"
              >
                {{ tag }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              type="button"
              class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
              title="Preview"
              @click.stop="$emit('preview', result)"
            >
              <EyeIcon class="w-4 h-4" />
            </button>
            <button
              type="button"
              class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
              title="Open"
              @click.stop="$emit('open', result)"
            >
              <ArrowTopRightOnSquareIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Grid View -->
      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
        <div
          v-for="result in filteredResults"
          :key="result.id"
          class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700 transition-all cursor-pointer"
          @click="$emit('select', result)"
        >
          <!-- Image/Icon -->
          <div class="aspect-video bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
            <img
              v-if="result.image"
              :src="result.image"
              :alt="result.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <component :is="getTypeIcon(result.type)" class="w-12 h-12 text-gray-400 dark:text-gray-500" />
            </div>
            <span
              v-if="result.type"
              :class="[
                'absolute top-2 right-2 px-2 py-0.5 text-xs font-medium rounded-full',
                getTypeBadgeColor(result.type)
              ]"
            >
              {{ result.type }}
            </span>
          </div>

          <!-- Content -->
          <div class="p-3">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
              {{ result.title }}
            </h4>
            <p v-if="result.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
              {{ result.description }}
            </p>
            <div v-if="result.date" class="text-xs text-gray-400 dark:text-gray-500 mt-2">
              {{ formatDate(result.date) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredResults.length === 0" class="py-16 text-center">
        <MagnifyingGlassIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No results found</h3>
        <p class="text-gray-500 dark:text-gray-400">
          Try adjusting your search or filter to find what you're looking for.
        </p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="showPagination && totalPages > 1" class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700">
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Showing {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, totalResults) }} of {{ totalResults }}
      </p>
      <div class="flex items-center gap-1">
        <button
          type="button"
          :disabled="currentPage === 1"
          class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50"
          @click="$emit('page', currentPage - 1)"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          type="button"
          :class="[
            'w-10 h-10 rounded-lg text-sm font-medium transition-colors',
            page === currentPage
              ? 'bg-blue-600 text-white'
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
          @click="$emit('page', page)"
        >
          {{ page }}
        </button>
        <button
          type="button"
          :disabled="currentPage === totalPages"
          class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50"
          @click="$emit('page', currentPage + 1)"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  MagnifyingGlassIcon,
  Bars3Icon,
  Squares2X2Icon,
  FolderIcon,
  CalendarIcon,
  UserIcon,
  EyeIcon,
  ArrowTopRightOnSquareIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  DocumentIcon,
  PhotoIcon,
  FilmIcon,
  MusicalNoteIcon,
  UserGroupIcon,
  TagIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  query: { type: String, default: '' },
  results: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  totalResults: { type: Number, default: 0 },
  currentPage: { type: Number, default: 1 },
  perPage: { type: Number, default: 10 },
  totalPages: { type: Number, default: 1 },
  searchTime: { type: Number, default: null },
  loading: { type: Boolean, default: false },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showPagination: { type: Boolean, default: true }
})

const emit = defineEmits(['select', 'preview', 'open', 'page', 'category'])

const viewMode = ref('list')
const activeCategory = ref('all')

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 text-white' 
    : 'bg-white text-gray-900'
)

const defaultResults = [
  { id: 1, title: 'Getting Started Guide', description: 'Learn how to use the platform effectively', type: 'document', path: 'Docs/Guides', date: '2025-12-20', tags: ['guide', 'beginner'] },
  { id: 2, title: 'Visa Application Form', description: 'Complete visa application template', type: 'document', path: 'Forms', date: '2025-12-19' },
  { id: 3, title: 'Profile Photo', description: 'User profile picture requirements', type: 'image', path: 'Images', date: '2025-12-18' },
  { id: 4, title: 'Application Tutorial', description: 'Video guide for submitting applications', type: 'video', path: 'Videos', date: '2025-12-17' },
  { id: 5, title: 'User Management', description: 'Manage user accounts and permissions', type: 'page', path: 'Admin', date: '2025-12-16', author: 'Admin' }
]

const allResults = computed(() => 
  props.results.length > 0 ? props.results : defaultResults
)

const allCategories = computed(() => {
  const cats = [{ id: 'all', label: 'All', count: allResults.value.length }]
  if (props.categories.length > 0) {
    return [...cats, ...props.categories]
  }
  // Auto-generate from results
  const types = {}
  allResults.value.forEach(r => {
    if (r.type) {
      types[r.type] = (types[r.type] || 0) + 1
    }
  })
  Object.entries(types).forEach(([type, count]) => {
    cats.push({ id: type, label: type.charAt(0).toUpperCase() + type.slice(1), count })
  })
  return cats
})

const filteredResults = computed(() => {
  if (activeCategory.value === 'all') return allResults.value
  return allResults.value.filter(r => r.type === activeCategory.value)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, props.currentPage - 2)
  const end = Math.min(props.totalPages, props.currentPage + 2)
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const getTypeIcon = (type) => {
  const icons = {
    document: DocumentIcon,
    image: PhotoIcon,
    video: FilmIcon,
    audio: MusicalNoteIcon,
    user: UserGroupIcon,
    page: DocumentIcon,
    tag: TagIcon
  }
  return icons[type] || DocumentIcon
}

const getTypeColor = (type) => {
  const colors = {
    document: 'bg-blue-500',
    image: 'bg-green-500',
    video: 'bg-purple-500',
    audio: 'bg-pink-500',
    user: 'bg-indigo-500',
    page: 'bg-gray-500'
  }
  return colors[type] || 'bg-gray-500'
}

const getTypeBadgeColor = (type) => {
  const colors = {
    document: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    image: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    video: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
    audio: 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400',
    user: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400',
    page: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
  }
  return colors[type] || 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
}

const highlightQuery = (text) => {
  if (!props.query || !text) return text
  const regex = new RegExp(`(${props.query})`, 'gi')
  return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-500/30 rounded px-0.5">$1</mark>')
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
