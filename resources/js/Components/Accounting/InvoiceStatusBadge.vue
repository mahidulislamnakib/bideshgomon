<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full font-medium',
      sizeClasses,
      statusClasses[status] || statusClasses.default
    ]"
  >
    {{ statusLabels[status] || status }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: 'sm',
    validator: (v) => ['xs', 'sm', 'md'].includes(v),
  },
})

const statusClasses = {
  draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  cancelled: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 line-through',
  default: 'bg-gray-100 text-gray-800',
}

const statusLabels = {
  draft: 'Draft',
  sent: 'Sent',
  paid: 'Paid',
  partial: 'Partial',
  overdue: 'Overdue',
  cancelled: 'Cancelled',
}

const sizeClasses = computed(() => {
  return {
    xs: 'text-xs',
    sm: 'text-xs',
    md: 'text-sm',
  }[props.size]
})
</script>
