<script setup>
/**
 * PageSkeleton Component
 * Full page loading skeleton for admin pages
 *
 * Usage:
 * <PageSkeleton type="index" />
 * <PageSkeleton type="show" />
 * <PageSkeleton type="form" />
 * <PageSkeleton type="dashboard" />
 */

import TableSkeleton from './TableSkeleton.vue'
import StatsSkeleton from './StatsSkeleton.vue'
import CardSkeleton from './CardSkeleton.vue'
import FormSkeleton from './FormSkeleton.vue'

defineProps({
  type: {
    type: String,
    default: 'index',
    validator: (value) => ['index', 'show', 'form', 'dashboard', 'cards'].includes(value),
  },
  showHeader: {
    type: Boolean,
    default: true,
  },
  showFilters: {
    type: Boolean,
    default: true,
  },
  tableRows: {
    type: Number,
    default: 8,
  },
  tableColumns: {
    type: Number,
    default: 5,
  },
  statsCount: {
    type: Number,
    default: 4,
  },
  cardCount: {
    type: Number,
    default: 6,
  },
  formFields: {
    type: Number,
    default: 8,
  },
})
</script>

<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div v-if="showHeader" class="flex items-center justify-between">
      <div>
        <div class="h-8 w-48 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-2"></div>
        <div class="h-4 w-72 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
      </div>
      <div class="flex gap-3">
        <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
        <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
      </div>
    </div>

    <!-- Dashboard Layout -->
    <template v-if="type === 'dashboard'">
      <StatsSkeleton :count="statsCount" />
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
          <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-4"></div>
          <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
          <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-4"></div>
          <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
        </div>
      </div>
      <TableSkeleton :rows="5" :columns="4" />
    </template>

    <!-- Index/List Layout -->
    <template v-else-if="type === 'index'">
      <!-- Filters -->
      <div v-if="showFilters" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
          <div class="h-10 w-48 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
          <div class="h-10 w-36 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
          <div class="h-10 w-36 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
          <div class="ml-auto h-10 w-24 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
        </div>
      </div>
      <TableSkeleton :rows="tableRows" :columns="tableColumns" />
    </template>

    <!-- Show/Detail Layout -->
    <template v-else-if="type === 'show'">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <div class="flex items-start gap-4 mb-6">
              <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
              <div class="flex-1 space-y-3">
                <div class="h-6 w-48 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              </div>
            </div>
            <div class="space-y-4">
              <div v-for="i in 4" :key="'detail-' + i" class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-4"></div>
            <div class="space-y-3">
              <div v-for="i in 3" :key="'activity-' + i" class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
                <div class="flex-1 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Sidebar -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <div class="h-5 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-4"></div>
            <div class="space-y-3">
              <div v-for="i in 5" :key="'sidebar-' + i" class="flex justify-between">
                <div class="h-4 w-20 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <div class="h-5 w-20 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-4"></div>
            <div class="flex flex-wrap gap-2">
              <div v-for="i in 4" :key="'action-' + i" class="h-9 w-full bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Form Layout -->
    <template v-else-if="type === 'form'">
      <FormSkeleton :fields="formFields" :columns="2" />
    </template>

    <!-- Cards Layout -->
    <template v-else-if="type === 'cards'">
      <div v-if="showFilters" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
          <div class="h-10 w-48 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
          <div class="h-10 w-36 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
          <div class="ml-auto flex gap-2">
            <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
            <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
          </div>
        </div>
      </div>
      <CardSkeleton :count="cardCount" />
    </template>
  </div>
</template>
