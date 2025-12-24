<!-- Modern Page Header Component -->
<script setup>
import { PlusIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: String,
  primaryAction: Object, // { label, onClick, icon, href }
  secondaryActions: Array, // [{ label, onClick, icon, variant }]
  breadcrumbs: Array, // [{ label, href }]
  stats: Array, // Quick stats to show in header
});
</script>

<template>
  <div class="bg-white border-b border-gray-200">
    <div class="max-w-screen-2xl mx-auto px-6 py-8">
      <!-- Breadcrumbs -->
      <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="mb-4">
        <ol class="flex items-center space-x-2 text-sm">
          <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
            <component
              :is="crumb.href ? 'a' : 'span'"
              :href="crumb.href"
              :class="[
                index === breadcrumbs.length - 1 
                  ? 'text-gray-900 font-medium' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              {{ crumb.label }}
            </component>
            <svg
              v-if="index < breadcrumbs.length - 1"
              class="h-4 w-4 text-gray-400 mx-2"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </li>
        </ol>
      </nav>

      <!-- Main Header Content -->
      <div class="flex items-start justify-between gap-6">
        <!-- Title & Description -->
        <div class="flex-1 min-w-0">
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            {{ title }}
          </h1>
          <p v-if="description" class="text-gray-600 mt-2 text-sm max-w-3xl">
            {{ description }}
          </p>
          
          <!-- Quick Stats in Header -->
          <div v-if="stats && stats.length > 0" class="flex items-center gap-6 mt-4">
            <div v-for="(stat, index) in stats" :key="index" class="flex items-center gap-2">
              <component
                v-if="stat.icon"
                :is="stat.icon"
                class="h-5 w-5 text-gray-400"
              />
              <div>
                <p class="text-xs text-gray-500">{{ stat.label }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ stat.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 flex-shrink-0">
          <!-- Secondary Actions -->
          <button
            v-for="(action, index) in secondaryActions"
            :key="index"
            @click="action.onClick"
            :class="[
              'inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
              'focus:outline-none focus:ring-2 focus:ring-offset-2',
              action.variant === 'danger'
                ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 focus:ring-red-500'
                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500'
            ]"
          >
            <component
              v-if="action.icon"
              :is="action.icon"
              class="h-5 w-5 mr-2"
            />
            {{ action.label }}
          </button>

          <!-- Primary Action -->
          <component
            v-if="primaryAction"
            :is="primaryAction.href ? 'a' : 'button'"
            :href="primaryAction.href"
            @click="primaryAction.onClick"
            class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-growth-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition-all duration-200"
          >
            <component
              v-if="primaryAction.icon"
              :is="primaryAction.icon"
              class="h-5 w-5 mr-2"
            />
            {{ primaryAction.label }}
          </component>
        </div>
      </div>

      <!-- Additional Slot for Tabs or Extra Content -->
      <div v-if="$slots.default" class="mt-6">
        <slot />
      </div>
    </div>
  </div>
</template>
