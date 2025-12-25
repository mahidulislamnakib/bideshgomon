<template>
  <nav
    class="flex items-center justify-between"
    :class="containerClasses"
    aria-label="Pagination"
  >
    <!-- Results info (left side) -->
    <div v-if="showInfo" class="hidden sm:block">
      <p class="text-sm" :class="infoTextClasses">
        Showing
        <span class="font-medium">{{ startItem }}</span>
        to
        <span class="font-medium">{{ endItem }}</span>
        of
        <span class="font-medium">{{ total }}</span>
        results
      </p>
    </div>

    <!-- Pagination controls -->
    <div class="flex items-center" :class="showInfo ? '' : 'w-full justify-center'">
      <!-- Per page selector -->
      <div v-if="showPerPage" class="mr-4 flex items-center gap-2">
        <label :for="`per-page-${uid}`" class="text-sm" :class="infoTextClasses">Show</label>
        <select
          :id="`per-page-${uid}`"
          :value="perPage"
          class="rounded-md border-gray-300 py-1 pl-2 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500"
          @change="$emit('update:perPage', Number($event.target.value))"
        >
          <option v-for="option in perPageOptions" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </div>

      <!-- Page buttons -->
      <div class="flex items-center gap-1">
        <!-- First page -->
        <button
          v-if="showFirstLast"
          type="button"
          :disabled="currentPage === 1"
          class="relative inline-flex items-center rounded-md p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="navButtonClasses"
          @click="goToPage(1)"
        >
          <span class="sr-only">First page</span>
          <ChevronDoubleLeftIcon class="h-4 w-4" />
        </button>

        <!-- Previous page -->
        <button
          type="button"
          :disabled="currentPage === 1"
          class="relative inline-flex items-center rounded-md p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="navButtonClasses"
          @click="goToPage(currentPage - 1)"
        >
          <span class="sr-only">Previous</span>
          <ChevronLeftIcon class="h-4 w-4" />
        </button>

        <!-- Page numbers -->
        <template v-for="page in visiblePages" :key="page">
          <span
            v-if="page === '...'"
            class="px-2 text-gray-500"
          >
            ...
          </span>
          <button
            v-else
            type="button"
            class="relative inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
            :class="page === currentPage ? activePageClasses : pageButtonClasses"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </template>

        <!-- Next page -->
        <button
          type="button"
          :disabled="currentPage === totalPages"
          class="relative inline-flex items-center rounded-md p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="navButtonClasses"
          @click="goToPage(currentPage + 1)"
        >
          <span class="sr-only">Next</span>
          <ChevronRightIcon class="h-4 w-4" />
        </button>

        <!-- Last page -->
        <button
          v-if="showFirstLast"
          type="button"
          :disabled="currentPage === totalPages"
          class="relative inline-flex items-center rounded-md p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="navButtonClasses"
          @click="goToPage(totalPages)"
        >
          <span class="sr-only">Last page</span>
          <ChevronDoubleRightIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Jump to page -->
      <div v-if="showJumpTo" class="ml-4 flex items-center gap-2">
        <label :for="`jump-to-${uid}`" class="text-sm" :class="infoTextClasses">Go to</label>
        <input
          :id="`jump-to-${uid}`"
          type="number"
          min="1"
          :max="totalPages"
          :value="currentPage"
          class="w-16 rounded-md border-gray-300 py-1 px-2 text-sm focus:border-primary-500 focus:ring-primary-500"
          @keyup.enter="goToPage(Number($event.target.value))"
          @blur="goToPage(Number($event.target.value))"
        />
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { 
  ChevronLeftIcon, 
  ChevronRightIcon, 
  ChevronDoubleLeftIcon, 
  ChevronDoubleRightIcon 
} from '@heroicons/vue/20/solid'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 20
  },
  perPageOptions: {
    type: Array,
    default: () => [10, 20, 50, 100]
  },
  maxVisiblePages: {
    type: Number,
    default: 5
  },
  showInfo: {
    type: Boolean,
    default: true
  },
  showPerPage: {
    type: Boolean,
    default: false
  },
  showFirstLast: {
    type: Boolean,
    default: true
  },
  showJumpTo: {
    type: Boolean,
    default: false
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'simple', 'bordered'].includes(value)
  }
})

const emit = defineEmits(['update:currentPage', 'update:perPage', 'page-change'])

// Unique ID for form elements
const uid = Math.random().toString(36).substring(2, 9)

// Computed values
const totalPages = computed(() => Math.ceil(props.total / props.perPage))
const startItem = computed(() => (props.currentPage - 1) * props.perPage + 1)
const endItem = computed(() => Math.min(props.currentPage * props.perPage, props.total))

// Generate visible page numbers with ellipsis
const visiblePages = computed(() => {
  const pages = []
  const maxVisible = props.maxVisiblePages
  const current = props.currentPage
  const total = totalPages.value

  if (total <= maxVisible) {
    // Show all pages
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)

    // Calculate range around current page
    let start = Math.max(2, current - Math.floor((maxVisible - 3) / 2))
    let end = Math.min(total - 1, start + maxVisible - 3)

    // Adjust if near the end
    if (end === total - 1) {
      start = Math.max(2, end - (maxVisible - 3))
    }

    // Add ellipsis before range if needed
    if (start > 2) {
      pages.push('...')
    }

    // Add page range
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    // Add ellipsis after range if needed
    if (end < total - 1) {
      pages.push('...')
    }

    // Always show last page
    if (total > 1) {
      pages.push(total)
    }
  }

  return pages
})

// Navigation
function goToPage(page) {
  const validPage = Math.max(1, Math.min(page, totalPages.value))
  if (validPage !== props.currentPage) {
    emit('update:currentPage', validPage)
    emit('page-change', validPage)
  }
}

// Styling
const containerClasses = computed(() => {
  const variants = {
    default: 'py-3',
    simple: 'py-2',
    bordered: 'py-3 px-4 border border-gray-200 rounded-lg bg-white'
  }
  return variants[props.variant]
})

const infoTextClasses = 'text-gray-700'

const navButtonClasses = computed(() => {
  return 'text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed focus:ring-primary-500'
})

const pageButtonClasses = computed(() => {
  return 'text-gray-700 hover:bg-gray-100 focus:ring-primary-500'
})

const activePageClasses = computed(() => {
  return 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500'
})
</script>
