<script setup>
/**
 * CardSkeleton Component
 * Loading skeleton for content cards
 *
 * Usage:
 * <CardSkeleton :count="3" layout="grid" />
 */

defineProps({
  count: {
    type: Number,
    default: 3,
  },
  layout: {
    type: String,
    default: 'grid',
    validator: (value) => ['grid', 'list'].includes(value),
  },
  columns: {
    type: Number,
    default: 3,
  },
  showImage: {
    type: Boolean,
    default: true,
  },
  showFooter: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <div
    :class="[
      layout === 'grid'
        ? {
            'grid gap-6': true,
            'grid-cols-1 sm:grid-cols-2': columns === 2,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': columns === 3,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4': columns === 4,
          }
        : 'space-y-4'
    ]"
  >
    <div
      v-for="i in count"
      :key="'card-' + i"
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden"
      :class="layout === 'list' ? 'flex' : ''"
    >
      <!-- Image placeholder -->
      <div
        v-if="showImage"
        class="bg-gray-200 dark:bg-gray-700 animate-pulse"
        :class="layout === 'list' ? 'w-48 h-32' : 'h-48 w-full'"
        :style="{ animationDelay: `${i * 100}ms` }"
      ></div>

      <!-- Content -->
      <div class="p-6 flex-1">
        <!-- Title -->
        <div
          class="h-5 w-3/4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-3"
          :style="{ animationDelay: `${i * 100 + 50}ms` }"
        ></div>

        <!-- Description lines -->
        <div class="space-y-2 mb-4">
          <div
            class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100 + 100}ms` }"
          ></div>
          <div
            class="h-3 w-5/6 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100 + 150}ms` }"
          ></div>
          <div
            class="h-3 w-2/3 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100 + 200}ms` }"
          ></div>
        </div>

        <!-- Footer -->
        <div v-if="showFooter" class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-2">
            <div
              class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"
              :style="{ animationDelay: `${i * 100 + 250}ms` }"
            ></div>
            <div
              class="h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
              :style="{ animationDelay: `${i * 100 + 275}ms` }"
            ></div>
          </div>
          <div
            class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
            :style="{ animationDelay: `${i * 100 + 300}ms` }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>
