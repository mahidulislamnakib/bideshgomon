<script setup>
/**
 * TableSkeleton Component
 * Loading skeleton for data tables in admin pages
 *
 * Usage:
 * <TableSkeleton :rows="5" :columns="6" />
 */

defineProps({
  rows: {
    type: Number,
    default: 5,
  },
  columns: {
    type: Number,
    default: 5,
  },
  showHeader: {
    type: Boolean,
    default: true,
  },
  showCheckbox: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden">
    <!-- Header -->
    <div v-if="showHeader" class="border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-4 px-6 py-4">
        <div v-if="showCheckbox" class="w-5 h-5 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        <div
          v-for="col in columns"
          :key="'header-' + col"
          class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
          :class="col === 1 ? 'w-32' : 'flex-1'"
        ></div>
      </div>
    </div>

    <!-- Rows -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
      <div
        v-for="row in rows"
        :key="'row-' + row"
        class="flex items-center gap-4 px-6 py-4"
      >
        <div v-if="showCheckbox" class="w-5 h-5 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        <div
          v-for="col in columns"
          :key="'cell-' + row + '-' + col"
          class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
          :class="[
            col === 1 ? 'w-40' : 'flex-1',
            col === columns ? 'w-24' : ''
          ]"
          :style="{ animationDelay: `${(row * 50) + (col * 25)}ms` }"
        ></div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        <div class="flex gap-2">
          <div v-for="i in 5" :key="'page-' + i" class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        </div>
      </div>
    </div>
  </div>
</template>
