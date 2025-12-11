# 🎨 BideshGomon Admin Panel - Complete UI/UX Redesign Plan

**Date:** December 3, 2025  
**Current Status:** Mixed design patterns, inconsistent spacing, varying color schemes  
**Goal:** Modern, clean, professional SaaS admin dashboard with consistent design system

---

## 📊 CURRENT STATE ANALYSIS

### 🔴 CRITICAL ISSUES IDENTIFIED

#### 1. **Inconsistent Spacing & Layout**
- **Problem:** Mixed spacing units (px-4, px-6, py-8, py-rhythm-xl, etc.)
- **Impact:** Uneven visual rhythm across pages
- **Examples:**
  ```vue
  <!-- Dashboard.vue -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-rhythm-xl space-y-rhythm-xl">
  
  <!-- Users/Index.vue -->
  <div class="min-h-screen bg-gray-50 pb-12">
  
  <!-- Faqs/Index.vue -->
  <div class="bg-white border-b border-gray-200 px-4 py-8 sm:px-6 lg:px-8">
  ```

#### 2. **Inconsistent Container Widths**
- Dashboard: `max-w-7xl`
- Users: No max-width constraint
- FAQs: `max-w-7xl`
- Some pages: Full width
- **Solution:** Standardize to `max-w-screen-2xl mx-auto` (1536px)

#### 3. **Mixed Color Schemes**
- Brand red: `bg-brand-red-600`, `bg-red-600`, `text-red-700`
- Blues: `bg-blue-500`, `bg-indigo-600`, `bg-ocean-500`
- Inconsistent hover states
- **Solution:** Unified color system with primary (red), secondary, and neutral palettes

#### 4. **Varying Card Designs**
```vue
<!-- Pattern 1: Shadow with border -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">

<!-- Pattern 2: Shadow only -->
<div class="bg-white rounded-lg shadow">

<!-- Pattern 3: Different padding -->
<div class="bg-white rounded-lg shadow p-6">
<div class="bg-white rounded-lg shadow-sm p-6">
```

#### 5. **Inconsistent Header Layouts**
- Some pages: Full-width gradient background
- Some pages: White background with border
- Some pages: No dedicated header section
- Varying title sizes (text-2xl, text-3xl, text-display-md)

#### 6. **Table Design Issues**
- Tight padding in cells (px-6 py-4)
- Inconsistent row hover effects
- No modern alternatives like cards for mobile
- Status badges vary in style

#### 7. **Empty State Variations**
```vue
<!-- Pattern 1: Center with icon -->
<div class="text-center py-12">
    <QuestionMarkCircleIcon class="mx-auto h-12 w-12 text-gray-400" />
    
<!-- Pattern 2: Larger padding -->
<div class="px-6 py-12 text-center">

<!-- Pattern 3: No icon -->
<div class="text-center py-16">
    <p class="text-gray-600 mb-3">No items found</p>
```

#### 8. **Search/Filter Bar Inconsistencies**
- Some pages: Filters always visible
- Some pages: Collapsible filter panel
- Different grid layouts (grid-cols-3, grid-cols-4)
- Varying button styles

#### 9. **Mobile Responsiveness Issues**
- Tables don't convert to cards on mobile
- Large stat grids don't collapse properly
- Action buttons wrap awkwardly
- Sidebar doesn't collapse smoothly

#### 10. **Button Style Chaos**
```vue
<!-- 5+ different button patterns -->
bg-brand-red-600 text-white
bg-red-600 text-white
bg-indigo-600 text-white
bg-ocean-500 text-white
bg-gray-100 text-gray-700
```

---

## 🎯 MODERN DESIGN SYSTEM

### **Color Palette**

```javascript
// Primary Brand Colors
const colors = {
  primary: {
    50: '#fef2f2',   // Lightest red backgrounds
    100: '#fee2e2',
    200: '#fecaca',
    300: '#fca5a5',
    400: '#f87171',
    500: '#ef4444',  // Main brand red
    600: '#dc2626',  // Primary buttons
    700: '#b91c1c',  // Hover states
    800: '#991b1b',
    900: '#7f1d1d',
  },
  
  // Neutral Gray (Professional)
  gray: {
    50: '#f9fafb',   // Page backgrounds
    100: '#f3f4f6',  // Card hover states
    200: '#e5e7eb',  // Borders
    300: '#d1d5db',  // Disabled states
    400: '#9ca3af',  // Placeholder text
    500: '#6b7280',  // Secondary text
    600: '#4b5563',  // Body text
    700: '#374151',  // Headings
    800: '#1f2937',
    900: '#111827',  // Dark headings
  },
  
  // Success (Emerald)
  success: {
    50: '#ecfdf5',
    500: '#10b981',
    600: '#059669',
    700: '#047857',
  },
  
  // Warning (Amber)
  warning: {
    50: '#fffbeb',
    500: '#f59e0b',
    600: '#d97706',
  },
  
  // Error (Red)
  error: {
    50: '#fef2f2',
    500: '#ef4444',
    600: '#dc2626',
  },
  
  // Info (Blue)
  info: {
    50: '#eff6ff',
    500: '#3b82f6',
    600: '#2563eb',
  },
};
```

### **Spacing Scale (8px base)**

```javascript
const spacing = {
  xs: '4px',    // 0.5 Tailwind
  sm: '8px',    // 2
  md: '12px',   // 3
  base: '16px', // 4
  lg: '24px',   // 6
  xl: '32px',   // 8
  '2xl': '48px', // 12
  '3xl': '64px', // 16
};
```

### **Typography Scale**

```javascript
const typography = {
  // Page Titles
  'display-2xl': 'text-4xl font-bold tracking-tight',  // 36px
  'display-xl': 'text-3xl font-bold tracking-tight',   // 30px
  'display-lg': 'text-2xl font-bold',                  // 24px
  
  // Section Headings
  'heading-lg': 'text-xl font-semibold',               // 20px
  'heading-md': 'text-lg font-semibold',               // 18px
  'heading-sm': 'text-base font-semibold',             // 16px
  
  // Body Text
  'body-lg': 'text-base',                              // 16px
  'body-md': 'text-sm',                                // 14px
  'body-sm': 'text-xs',                                // 12px
  
  // Labels
  'label-lg': 'text-sm font-medium',
  'label-md': 'text-xs font-medium uppercase tracking-wide',
};
```

### **Border Radius**

```javascript
const borderRadius = {
  'sm': '4px',     // rounded-sm
  'DEFAULT': '6px', // rounded
  'md': '8px',     // rounded-md
  'lg': '12px',    // rounded-lg
  'xl': '16px',    // rounded-xl
  'full': '9999px',// rounded-full
};
```

### **Shadows**

```javascript
const shadows = {
  'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',           // Subtle lift
  'DEFAULT': '0 1px 3px 0 rgba(0, 0, 0, 0.1)',       // Card default
  'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',         // Elevated cards
  'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1)',       // Modals
  'xl': '0 20px 25px -5px rgba(0, 0, 0, 0.1)',       // Dropdowns
};
```

---

## 🏗️ STANDARDIZED PAGE STRUCTURE

### **Template: All Admin List Pages**

```vue
<template>
  <Head :title="pageTitle" />

  <AdminLayout>
    <!-- 1. PAGE HEADER -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-screen-2xl mx-auto px-6 py-8">
        <div class="flex items-center justify-between">
          <!-- Title & Description -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
              {{ pageTitle }}
            </h1>
            <p class="text-gray-600 mt-2 text-sm">
              {{ pageDescription }}
            </p>
          </div>
          
          <!-- Primary Action -->
          <button class="btn-primary">
            <PlusIcon class="h-5 w-5 mr-2" />
            Create {{ resourceName }}
          </button>
        </div>
      </div>
    </div>

    <!-- 2. MAIN CONTENT AREA -->
    <div class="max-w-screen-2xl mx-auto px-6 py-8 space-y-6">
      
      <!-- 3. STATS CARDS (Optional) -->
      <div v-if="showStats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          v-for="stat in stats"
          :key="stat.label"
          :label="stat.label"
          :value="stat.value"
          :icon="stat.icon"
          :variant="stat.variant"
          :trend="stat.trend"
        />
      </div>

      <!-- 4. SEARCH & FILTERS CARD -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="space-y-4">
          <!-- Search Row -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
              <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search..."
                  class="input-base pl-10"
                />
              </div>
            </div>
            <select class="input-base">
              <option>Filter 1</option>
            </select>
            <select class="input-base">
              <option>Filter 2</option>
            </select>
          </div>

          <!-- Action Row -->
          <div class="flex items-center justify-between">
            <div class="flex gap-3">
              <button class="btn-primary">Search</button>
              <button class="btn-secondary">Reset</button>
            </div>
            <span v-if="hasFilters" class="text-sm text-gray-500">
              {{ filteredCount }} results
            </span>
          </div>
        </div>
      </div>

      <!-- 5. DATA LISTING CARD -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table -->
        <div v-if="hasData" class="overflow-x-auto">
          <table class="table-modern">
            <thead>
              <tr>
                <th>Column 1</th>
                <th>Column 2</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>{{ item.name }}</td>
                <td>
                  <StatusBadge :status="item.status" />
                </td>
                <td class="text-right">
                  <ActionButtons :item="item" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <EmptyState
          v-else
          icon="DocumentIcon"
          title="No items yet"
          description="Get started by creating your first item"
          :action="{
            label: 'Create Item',
            onClick: () => router.visit(route('admin.resource.create'))
          }"
        />

        <!-- Pagination -->
        <div v-if="hasData" class="border-t border-gray-200 px-6 py-4">
          <Pagination :links="items.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
```

---

## 🧩 REUSABLE COMPONENTS

### **1. Button System**

```vue
<!-- File: resources/js/Components/Base/Button.vue -->
<script setup>
const props = defineProps({
  variant: {
    type: String,
    default: 'primary', // primary, secondary, danger, ghost
    validator: (v) => ['primary', 'secondary', 'danger', 'ghost', 'outline'].includes(v)
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  loading: Boolean,
  disabled: Boolean,
});
</script>

<template>
  <button
    :class="[
      'btn-base',
      `btn-${variant}`,
      `btn-${size}`,
      {
        'opacity-50 cursor-not-allowed': disabled,
        'cursor-wait': loading,
      }
    ]"
    :disabled="disabled || loading"
  >
    <slot />
  </button>
</template>

<style scoped>
/* Base Button */
.btn-base {
  @apply inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
}

/* Sizes */
.btn-sm { @apply px-3 py-1.5 text-sm; }
.btn-md { @apply px-4 py-2.5 text-sm; }
.btn-lg { @apply px-6 py-3 text-base; }

/* Variants */
.btn-primary {
  @apply bg-red-600 text-white hover:bg-red-700 active:bg-red-800 focus:ring-red-500 shadow-sm;
}

.btn-secondary {
  @apply bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300 focus:ring-gray-400;
}

.btn-danger {
  @apply bg-red-100 text-red-700 hover:bg-red-200 active:bg-red-300 focus:ring-red-500;
}

.btn-ghost {
  @apply bg-transparent text-gray-700 hover:bg-gray-100 active:bg-gray-200;
}

.btn-outline {
  @apply bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-400;
}
</style>
```

### **2. Input System**

```vue
<!-- File: resources/js/Components/Base/FormInput.vue -->
<script setup>
const props = defineProps({
  modelValue: [String, Number],
  type: { type: String, default: 'text' },
  placeholder: String,
  error: String,
  label: String,
  helpText: String,
  required: Boolean,
  icon: Object,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div class="form-group">
    <!-- Label -->
    <label v-if="label" class="form-label">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Input Field -->
    <div class="relative">
      <component
        v-if="icon"
        :is="icon"
        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
      />
      <input
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :class="[
          'input-base',
          { 'pl-10': icon },
          { 'border-red-300 focus:border-red-500 focus:ring-red-500': error }
        ]"
      />
    </div>

    <!-- Help Text -->
    <p v-if="helpText && !error" class="form-help">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="error" class="form-error">
      {{ error }}
    </p>
  </div>
</template>

<style scoped>
.form-group {
  @apply space-y-1.5;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.input-base {
  @apply w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
         focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent
         transition-colors duration-200 bg-white;
}

.form-help {
  @apply text-xs text-gray-500;
}

.form-error {
  @apply text-xs text-red-600;
}
</style>
```

### **3. Status Badge Component**

```vue
<!-- File: resources/js/Components/Base/StatusBadge.vue -->
<script setup>
const props = defineProps({
  status: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  }
});

const statusConfig = {
  active: { color: 'green', label: 'Active' },
  inactive: { color: 'gray', label: 'Inactive' },
  pending: { color: 'yellow', label: 'Pending' },
  approved: { color: 'green', label: 'Approved' },
  rejected: { color: 'red', label: 'Rejected' },
  published: { color: 'green', label: 'Published' },
  draft: { color: 'gray', label: 'Draft' },
  completed: { color: 'blue', label: 'Completed' },
};

const config = statusConfig[props.status] || { color: 'gray', label: props.status };
</script>

<template>
  <span
    :class="[
      'badge',
      `badge-${config.color}`,
      `badge-${size}`
    ]"
  >
    <span class="badge-dot"></span>
    {{ config.label }}
  </span>
</template>

<style scoped>
.badge {
  @apply inline-flex items-center gap-1.5 font-medium rounded-full;
}

.badge-sm { @apply px-2 py-0.5 text-xs; }
.badge-md { @apply px-2.5 py-1 text-xs; }
.badge-lg { @apply px-3 py-1.5 text-sm; }

.badge-dot {
  @apply w-1.5 h-1.5 rounded-full;
}

.badge-green {
  @apply bg-emerald-50 text-emerald-700 border border-emerald-200;
}
.badge-green .badge-dot { @apply bg-emerald-500; }

.badge-yellow {
  @apply bg-amber-50 text-amber-700 border border-amber-200;
}
.badge-yellow .badge-dot { @apply bg-amber-500; }

.badge-red {
  @apply bg-red-50 text-red-700 border border-red-200;
}
.badge-red .badge-dot { @apply bg-red-500; }

.badge-blue {
  @apply bg-blue-50 text-blue-700 border border-blue-200;
}
.badge-blue .badge-dot { @apply bg-blue-500; }

.badge-gray {
  @apply bg-gray-100 text-gray-700 border border-gray-200;
}
.badge-gray .badge-dot { @apply bg-gray-500; }
</style>
```

### **4. Empty State Component**

```vue
<!-- File: resources/js/Components/Base/EmptyState.vue -->
<script setup>
import { computed } from 'vue';
import {
  DocumentIcon,
  UserGroupIcon,
  FolderIcon,
  InboxIcon,
  QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  icon: {
    type: String,
    default: 'InboxIcon'
  },
  title: {
    type: String,
    required: true
  },
  description: String,
  action: Object, // { label, onClick }
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  }
});

const iconComponents = {
  DocumentIcon,
  UserGroupIcon,
  FolderIcon,
  InboxIcon,
  QuestionMarkCircleIcon
};

const iconComponent = computed(() => iconComponents[props.icon] || InboxIcon);
</script>

<template>
  <div
    :class="[
      'empty-state',
      {
        'py-12': size === 'sm',
        'py-16': size === 'md',
        'py-24': size === 'lg'
      }
    ]"
  >
    <component
      :is="iconComponent"
      :class="[
        'empty-state-icon',
        {
          'h-12 w-12': size === 'sm',
          'h-16 w-16': size === 'md',
          'h-20 w-20': size === 'lg'
        }
      ]"
    />
    <h3
      :class="[
        'empty-state-title',
        {
          'text-base': size === 'sm',
          'text-lg': size === 'md',
          'text-xl': size === 'lg'
        }
      ]"
    >
      {{ title }}
    </h3>
    <p v-if="description" class="empty-state-description">
      {{ description }}
    </p>
    <button
      v-if="action"
      @click="action.onClick"
      class="btn-base btn-primary btn-md mt-6"
    >
      {{ action.label }}
    </button>
  </div>
</template>

<style scoped>
.empty-state {
  @apply text-center flex flex-col items-center justify-center;
}

.empty-state-icon {
  @apply text-gray-400 mb-4;
}

.empty-state-title {
  @apply font-semibold text-gray-900 mb-2;
}

.empty-state-description {
  @apply text-sm text-gray-500 max-w-sm;
}
</style>
```

### **5. Modern Table Component**

```vue
<!-- File: resources/js/Components/Base/DataTable.vue -->
<script setup>
const props = defineProps({
  columns: Array, // [{ key: 'name', label: 'Name', sortable: true }]
  data: Array,
  loading: Boolean,
  selectable: Boolean,
});

const emit = defineEmits(['sort', 'select', 'row-click']);
</script>

<template>
  <div class="overflow-x-auto">
    <table class="table-modern">
      <thead>
        <tr>
          <th v-if="selectable" class="w-12">
            <input type="checkbox" class="rounded" />
          </th>
          <th
            v-for="column in columns"
            :key="column.key"
            :class="column.align === 'right' ? 'text-right' : 'text-left'"
          >
            <button
              v-if="column.sortable"
              @click="$emit('sort', column.key)"
              class="flex items-center gap-1 hover:text-gray-900"
            >
              {{ column.label }}
              <ArrowsUpDownIcon class="h-4 w-4" />
            </button>
            <span v-else>{{ column.label }}</span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="row in data"
          :key="row.id"
          @click="$emit('row-click', row)"
          class="cursor-pointer"
        >
          <td v-if="selectable">
            <input
              type="checkbox"
              class="rounded"
              @click.stop
            />
          </td>
          <td
            v-for="column in columns"
            :key="column.key"
            :class="column.align === 'right' ? 'text-right' : 'text-left'"
          >
            <slot :name="`cell-${column.key}`" :row="row">
              {{ row[column.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-modern {
  @apply min-w-full divide-y divide-gray-200;
}

.table-modern thead {
  @apply bg-gray-50;
}

.table-modern thead th {
  @apply px-6 py-4 text-xs font-semibold text-gray-700 uppercase tracking-wider;
}

.table-modern tbody {
  @apply bg-white divide-y divide-gray-100;
}

.table-modern tbody tr {
  @apply hover:bg-gray-50 transition-colors duration-150;
}

.table-modern tbody td {
  @apply px-6 py-4 text-sm text-gray-900 whitespace-nowrap;
}
</style>
```

---

## 📄 PAGE REDESIGN EXAMPLES

### **Example 1: Users Management (Complete)**

```vue
<!-- File: resources/js/Pages/Admin/Users/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
  UsersIcon, PlusIcon, MagnifyingGlassIcon,
  FunnelIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';
import Button from '@/Components/Base/Button.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import DataTable from '@/Components/Base/DataTable.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  users: Object,
  stats: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');
const status = ref(props.filters.status || '');

const performSearch = () => {
  router.get(route('admin.users.index'), {
    search: search.value,
    role: role.value,
    status: status.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  search.value = '';
  role.value = '';
  status.value = '';
  router.get(route('admin.users.index'));
};

const hasFilters = computed(() => {
  return search.value || role.value || status.value;
});

const columns = [
  { key: 'name', label: 'User', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Role' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Joined', sortable: true },
  { key: 'actions', label: 'Actions', align: 'right' },
];
</script>

<template>
  <Head title="Users Management" />

  <AdminLayout>
    <!-- PAGE HEADER -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-screen-2xl mx-auto px-6 py-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
              Users Management
            </h1>
            <p class="text-gray-600 mt-2 text-sm">
              Manage all platform users, roles, and permissions
            </p>
          </div>
          <div class="flex gap-3">
            <Button variant="outline" @click="exportUsers">
              <ArrowDownTrayIcon class="h-5 w-5 mr-2" />
              Export
            </Button>
            <Button variant="primary" as="link" :href="route('admin.users.create')">
              <PlusIcon class="h-5 w-5 mr-2" />
              Add User
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-screen-2xl mx-auto px-6 py-8 space-y-6">
      
      <!-- STATS CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="stat-card">
          <div class="stat-card-icon bg-blue-100">
            <UsersIcon class="h-6 w-6 text-blue-600" />
          </div>
          <div>
            <p class="stat-card-label">Total Users</p>
            <p class="stat-card-value">{{ stats.total }}</p>
            <p class="stat-card-change text-green-600">
              +{{ stats.new_today }} today
            </p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-card-icon bg-green-100">
            <UsersIcon class="h-6 w-6 text-green-600" />
          </div>
          <div>
            <p class="stat-card-label">Active Users</p>
            <p class="stat-card-value">{{ stats.active }}</p>
            <p class="stat-card-change text-gray-500">
              {{ Math.round((stats.active / stats.total) * 100) }}% of total
            </p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-card-icon bg-yellow-100">
            <UsersIcon class="h-6 w-6 text-yellow-600" />
          </div>
          <div>
            <p class="stat-card-label">Unverified</p>
            <p class="stat-card-value">{{ stats.unverified }}</p>
            <p class="stat-card-change text-yellow-600">
              Pending verification
            </p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-card-icon bg-red-100">
            <UsersIcon class="h-6 w-6 text-red-600" />
          </div>
          <div>
            <p class="stat-card-label">Suspended</p>
            <p class="stat-card-value">{{ stats.suspended }}</p>
            <p class="stat-card-change text-red-600">
              Requires action
            </p>
          </div>
        </div>
      </div>

      <!-- SEARCH & FILTERS -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
              <FormInput
                v-model="search"
                placeholder="Search by name, email, or phone..."
                :icon="MagnifyingGlassIcon"
                @keyup.enter="performSearch"
              />
            </div>
            <select v-model="role" class="input-base">
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="user">User</option>
              <option value="agency">Agency</option>
              <option value="consultant">Consultant</option>
            </select>
            <select v-model="status" class="input-base">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="unverified">Unverified</option>
            </select>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex gap-3">
              <Button variant="primary" @click="performSearch">
                Search
              </Button>
              <Button variant="secondary" @click="clearFilters" v-if="hasFilters">
                Reset
              </Button>
            </div>
            <span v-if="hasFilters" class="text-sm text-gray-500">
              {{ users.total }} results found
            </span>
          </div>
        </div>
      </div>

      <!-- DATA TABLE -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <DataTable
          v-if="users.data.length > 0"
          :columns="columns"
          :data="users.data"
          selectable
        >
          <!-- Custom Cells -->
          <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                {{ row.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ row.name }}</p>
                <p class="text-xs text-gray-500">ID: {{ row.id }}</p>
              </div>
            </div>
          </template>

          <template #cell-role="{ row }">
            <StatusBadge :status="row.role.slug" />
          </template>

          <template #cell-status="{ row }">
            <StatusBadge :status="row.suspended_at ? 'suspended' : 'active'" />
          </template>

          <template #cell-created_at="{ row }">
            <span class="text-sm text-gray-600">
              {{ formatDate(row.created_at) }}
            </span>
          </template>

          <template #cell-actions="{ row }">
            <div class="flex items-center justify-end gap-2">
              <Link
                :href="route('admin.users.show', row.id)"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium"
              >
                View
              </Link>
              <Link
                :href="route('admin.users.edit', row.id)"
                class="text-gray-600 hover:text-gray-700 text-sm font-medium"
              >
                Edit
              </Link>
            </div>
          </template>
        </DataTable>

        <EmptyState
          v-else
          icon="UserGroupIcon"
          title="No users found"
          description="Try adjusting your search criteria or create a new user"
          :action="{
            label: 'Create User',
            onClick: () => router.visit(route('admin.users.create'))
          }"
        />

        <!-- Pagination -->
        <div v-if="users.data.length > 0" class="border-t border-gray-200 px-6 py-4">
          <Pagination :links="users.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Stat Cards */
.stat-card {
  @apply bg-white rounded-lg border border-gray-200 p-6 flex items-start gap-4 hover:shadow-md transition-shadow duration-200;
}

.stat-card-icon {
  @apply p-3 rounded-lg shrink-0;
}

.stat-card-label {
  @apply text-sm font-medium text-gray-600;
}

.stat-card-value {
  @apply text-2xl font-bold text-gray-900 mt-1;
}

.stat-card-change {
  @apply text-xs font-medium mt-1;
}

/* Input Base */
.input-base {
  @apply w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
         focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent
         transition-colors duration-200 bg-white;
}
</style>
```

---

## 📐 GLOBAL STYLES (Tailwind Config)

```javascript
// File: tailwind.config.js
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#fef2f2',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
        },
        gray: {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
        },
      },
      spacing: {
        'rhythm-xs': '4px',
        'rhythm-sm': '8px',
        'rhythm-md': '12px',
        'rhythm-base': '16px',
        'rhythm-lg': '24px',
        'rhythm-xl': '32px',
        'rhythm-2xl': '48px',
        'rhythm-3xl': '64px',
      },
      maxWidth: {
        'screen-2xl': '1536px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
};
```

---

## ✅ IMPLEMENTATION CHECKLIST

### Phase 1: Foundation (Week 1)
- [ ] Create all base components (Button, Input, StatusBadge, EmptyState, DataTable)
- [ ] Update Tailwind config with design tokens
- [ ] Create global CSS utilities file
- [ ] Update AdminLayout header spacing

### Phase 2: High-Traffic Pages (Week 2)
- [ ] Redesign: Admin Dashboard
- [ ] Redesign: Users Management
- [ ] Redesign: Service Applications
- [ ] Redesign: Visa Applications
- [ ] Test responsive behavior

### Phase 3: Data Management (Week 3)
- [ ] Redesign: FAQs Index
- [ ] Redesign: Pages Management
- [ ] Redesign: Blog Posts
- [ ] Redesign: Job Postings
- [ ] Redesign: All data master tables

### Phase 4: Settings & Support (Week 4)
- [ ] Redesign: Settings pages
- [ ] Redesign: Support tickets
- [ ] Redesign: Analytics
- [ ] Redesign: Reports
- [ ] Final QA and polish

---

## 🎬 BEFORE/AFTER PREVIEW

### Current Issues Visual:
```
❌ Inconsistent spacing (py-8 vs py-rhythm-xl)
❌ Mixed widths (max-w-7xl vs full-width)
❌ 5+ button styles
❌ Varying card designs
❌ Inconsistent header layouts
❌ Tables don't adapt to mobile
❌ No unified empty states
❌ Mixed color schemes
```

### After Redesign:
```
✅ Consistent 8px spacing rhythm
✅ Standardized max-w-screen-2xl
✅ Unified button system (5 variants)
✅ Single card design
✅ Consistent page headers
✅ Responsive tables & cards
✅ Beautiful empty states
✅ Cohesive red + gray palette
✅ Professional SaaS look
```

---

**Ready to implement? Start with Phase 1 components and I'll help you redesign each page systematically!**
