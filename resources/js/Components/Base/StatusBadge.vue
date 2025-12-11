<!-- Modern Status Badge Component -->
<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  showDot: {
    type: Boolean,
    default: true
  },
  customLabel: String,
});

// Status configuration with colors and labels
const statusConfig = {
  // Application statuses
  active: { color: 'green', label: 'Active', icon: '●' },
  inactive: { color: 'gray', label: 'Inactive', icon: '●' },
  pending: { color: 'yellow', label: 'Pending', icon: '●' },
  approved: { color: 'green', label: 'Approved', icon: '✓' },
  rejected: { color: 'red', label: 'Rejected', icon: '✗' },
  cancelled: { color: 'gray', label: 'Cancelled', icon: '✗' },
  
  // Publication statuses
  published: { color: 'green', label: 'Published', icon: '●' },
  draft: { color: 'gray', label: 'Draft', icon: '●' },
  archived: { color: 'gray', label: 'Archived', icon: '●' },
  scheduled: { color: 'blue', label: 'Scheduled', icon: '●' },
  
  // Processing statuses
  processing: { color: 'blue', label: 'Processing', icon: '●' },
  completed: { color: 'green', label: 'Completed', icon: '✓' },
  failed: { color: 'red', label: 'Failed', icon: '✗' },
  
  // Verification statuses
  verified: { color: 'green', label: 'Verified', icon: '✓' },
  unverified: { color: 'yellow', label: 'Unverified', icon: '●' },
  suspended: { color: 'red', label: 'Suspended', icon: '●' },
  
  // Payment statuses
  paid: { color: 'green', label: 'Paid', icon: '✓' },
  unpaid: { color: 'red', label: 'Unpaid', icon: '●' },
  refunded: { color: 'gray', label: 'Refunded', icon: '●' },
  
  // User roles
  admin: { color: 'purple', label: 'Admin', icon: '★' },
  user: { color: 'blue', label: 'User', icon: '●' },
  agency: { color: 'indigo', label: 'Agency', icon: '●' },
  consultant: { color: 'teal', label: 'Consultant', icon: '●' },
  
  // Visa statuses
  submitted: { color: 'blue', label: 'Submitted', icon: '●' },
  'under-review': { color: 'yellow', label: 'Under Review', icon: '●' },
  interview: { color: 'purple', label: 'Interview', icon: '●' },
  granted: { color: 'green', label: 'Granted', icon: '✓' },
  denied: { color: 'red', label: 'Denied', icon: '✗' },
  
  // General
  enabled: { color: 'green', label: 'Enabled', icon: '●' },
  disabled: { color: 'gray', label: 'Disabled', icon: '●' },
  true: { color: 'green', label: 'Yes', icon: '✓' },
  false: { color: 'red', label: 'No', icon: '✗' },
};

const config = computed(() => {
  const normalizedStatus = props.status.toLowerCase().replace(/_/g, '-');
  return statusConfig[normalizedStatus] || { 
    color: 'gray', 
    label: props.customLabel || props.status.replace(/_/g, ' ').replace(/-/g, ' '),
    icon: '●'
  };
});

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-2 py-0.5 text-xs gap-1',
    md: 'px-2.5 py-1 text-xs gap-1.5',
    lg: 'px-3 py-1.5 text-sm gap-2',
  };
  return sizes[props.size];
});

const dotSizeClasses = computed(() => {
  const sizes = {
    sm: 'w-1.5 h-1.5',
    md: 'w-2 h-2',
    lg: 'w-2.5 h-2.5',
  };
  return sizes[props.size];
});

const badgeClasses = computed(() => {
  const colorVariants = {
    green: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    yellow: 'bg-amber-50 text-amber-700 border-amber-200',
    red: 'bg-red-50 text-red-700 border-red-200',
    blue: 'bg-blue-50 text-blue-700 border-blue-200',
    purple: 'bg-purple-50 text-purple-700 border-purple-200',
    indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    teal: 'bg-teal-50 text-teal-700 border-teal-200',
    gray: 'bg-gray-100 text-gray-700 border-gray-200',
  };
  
  return [
    'inline-flex items-center font-medium rounded-full border',
    sizeClasses.value,
    colorVariants[config.value.color] || colorVariants.gray,
  ];
});

const dotClasses = computed(() => {
  const dotColors = {
    green: 'bg-emerald-500',
    yellow: 'bg-amber-500',
    red: 'bg-red-500',
    blue: 'bg-blue-500',
    purple: 'bg-purple-500',
    indigo: 'bg-indigo-500',
    teal: 'bg-teal-500',
    gray: 'bg-gray-500',
  };
  
  return [
    'rounded-full flex-shrink-0',
    dotSizeClasses.value,
    dotColors[config.value.color] || dotColors.gray,
  ];
});
</script>

<template>
  <span :class="badgeClasses">
    <span v-if="showDot" :class="dotClasses"></span>
    <span class="capitalize">{{ customLabel || config.label }}</span>
  </span>
</template>
