<script setup>
/**
 * FormSkeleton Component
 * Loading skeleton for forms in admin pages
 *
 * Usage:
 * <FormSkeleton :fields="6" :columns="2" />
 */

defineProps({
  fields: {
    type: Number,
    default: 6,
  },
  columns: {
    type: Number,
    default: 1,
  },
  showTitle: {
    type: Boolean,
    default: true,
  },
  showActions: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
    <!-- Title -->
    <div v-if="showTitle" class="mb-6">
      <div class="h-6 w-48 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-2"></div>
      <div class="h-4 w-72 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
    </div>

    <!-- Form fields -->
    <div
      class="gap-6"
      :class="{
        'grid': columns > 1,
        'grid-cols-1 md:grid-cols-2': columns === 2,
        'grid-cols-1 md:grid-cols-2 lg:grid-cols-3': columns === 3,
        'space-y-6': columns === 1,
      }"
    >
      <div
        v-for="i in fields"
        :key="'field-' + i"
        class="space-y-2"
        :class="columns === 1 ? '' : 'mb-0'"
      >
        <!-- Label -->
        <div
          class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
          :style="{ animationDelay: `${i * 75}ms` }"
        ></div>
        <!-- Input -->
        <div
          class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"
          :style="{ animationDelay: `${i * 75 + 25}ms` }"
        ></div>
      </div>
    </div>

    <!-- Actions -->
    <div v-if="showActions" class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
      <div class="h-10 w-24 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
      <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
    </div>
  </div>
</template>
