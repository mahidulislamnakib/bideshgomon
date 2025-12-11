<!-- Modern Empty State Component -->
<script setup>
import { computed } from 'vue';
import {
  DocumentIcon,
  UserGroupIcon,
  FolderIcon,
  InboxIcon,
  QuestionMarkCircleIcon,
  ArchiveBoxIcon,
  ClipboardDocumentListIcon,
  MagnifyingGlassIcon,
  ExclamationTriangleIcon,
  BriefcaseIcon,
  GlobeAltIcon,
  BuildingOfficeIcon,
  NewspaperIcon,
  ChatBubbleLeftRightIcon,
  TicketIcon,
  CreditCardIcon,
  CalendarIcon,
  MapPinIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  icon: {
    type: [String, Object],
    default: 'InboxIcon'
  },
  title: {
    type: String,
    required: true
  },
  description: String,
  action: Object, // { label, onClick, variant, icon }
  secondaryAction: Object, // Secondary button
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'search', 'error', 'success'].includes(v)
  },
});

// Icon mapping
const iconComponents = {
  DocumentIcon,
  UserGroupIcon,
  FolderIcon,
  InboxIcon,
  QuestionMarkCircleIcon,
  ArchiveBoxIcon,
  ClipboardDocumentListIcon,
  MagnifyingGlassIcon,
  ExclamationTriangleIcon,
  BriefcaseIcon,
  GlobeAltIcon,
  BuildingOfficeIcon,
  NewspaperIcon,
  ChatBubbleLeftRightIcon,
  TicketIcon,
  CreditCardIcon,
  CalendarIcon,
  MapPinIcon,
};

const iconComponent = computed(() => {
  if (typeof props.icon === 'object') {
    return props.icon;
  }
  return iconComponents[props.icon] || InboxIcon;
});

const containerPadding = computed(() => {
  const paddings = {
    sm: 'py-12 px-6',
    md: 'py-16 px-8',
    lg: 'py-24 px-12',
  };
  return paddings[props.size];
});

const iconSizeClass = computed(() => {
  const sizes = {
    sm: 'h-12 w-12',
    md: 'h-16 w-16',
    lg: 'h-20 w-20',
  };
  return sizes[props.size];
});

const titleSizeClass = computed(() => {
  const sizes = {
    sm: 'text-base',
    md: 'text-lg',
    lg: 'text-xl',
  };
  return sizes[props.size];
});

const iconColorClass = computed(() => {
  const colors = {
    default: 'text-gray-400',
    search: 'text-blue-400',
    error: 'text-red-400',
    success: 'text-green-400',
  };
  return colors[props.variant];
});
</script>

<template>
  <div :class="['text-center flex flex-col items-center justify-center', containerPadding]">
    <!-- Icon -->
    <div :class="['rounded-full p-4 mb-4', variant === 'error' ? 'bg-red-50' : variant === 'success' ? 'bg-green-50' : 'bg-gray-50']">
      <component
        :is="iconComponent"
        :class="[iconSizeClass, iconColorClass]"
      />
    </div>

    <!-- Title -->
    <h3 :class="['font-semibold text-gray-900 mb-2', titleSizeClass]">
      {{ title }}
    </h3>

    <!-- Description -->
    <p v-if="description" class="text-sm text-gray-500 max-w-md mb-6">
      {{ description }}
    </p>

    <!-- Actions -->
    <div v-if="action || secondaryAction" class="flex items-center gap-3">
      <!-- Primary Action -->
      <button
        v-if="action"
        @click="action.onClick"
        :class="[
          'inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
          'focus:outline-none focus:ring-2 focus:ring-offset-2',
          action.variant === 'secondary' 
            ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500'
            : 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm'
        ]"
      >
        <component
          v-if="action.icon"
          :is="action.icon"
          class="h-5 w-5 mr-2"
        />
        {{ action.label }}
      </button>

      <!-- Secondary Action -->
      <button
        v-if="secondaryAction"
        @click="secondaryAction.onClick"
        class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
      >
        <component
          v-if="secondaryAction.icon"
          :is="secondaryAction.icon"
          class="h-5 w-5 mr-2"
        />
        {{ secondaryAction.label }}
      </button>
    </div>

    <!-- Additional Slot -->
    <div v-if="$slots.default" class="mt-6">
      <slot />
    </div>
  </div>
</template>
