<script setup>
/**
 * StatsSkeleton Component
 * Loading skeleton for stats cards in admin dashboards
 *
 * Usage:
 * <StatsSkeleton :count="4" />
 */

defineProps({
  count: {
    type: Number,
    default: 4,
  },
  columns: {
    type: Number,
    default: 4,
  },
})
</script>

<template>
  <div
    class="grid gap-6"
    :class="{
      'grid-cols-1 sm:grid-cols-2': columns === 2,
      'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': columns === 3,
      'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4': columns === 4,
    }"
  >
    <div
      v-for="i in count"
      :key="'stat-' + i"
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6"
    >
      <div class="flex items-center justify-between">
        <div class="space-y-3 flex-1">
          <!-- Label -->
          <div
            class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100}ms` }"
          ></div>
          <!-- Value -->
          <div
            class="h-8 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100 + 50}ms` }"
          ></div>
          <!-- Trend -->
          <div
            class="h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100 + 100}ms` }"
          ></div>
        </div>
        <!-- Icon -->
        <div
          class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"
          :style="{ animationDelay: `${i * 100 + 75}ms` }"
        ></div>
      </div>
    </div>
  </div>
</template>
