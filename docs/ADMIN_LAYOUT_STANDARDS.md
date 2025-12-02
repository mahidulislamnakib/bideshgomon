# Admin Layout Design Standards
**BideshGomon Platform - Consistent Admin Interface**

## Problem Statement
Currently, admin pages use inconsistent:
- Button colors (teal, blue, cyan, indigo, purple)
- Header layouts (varying button positions)
- Filter arrangements
- Typography styles
- Card designs

## Solution: Unified Design System

### 1. Color Palette (Brand-First)

**Primary Actions:**
- All CTA buttons: `bg-brand-red-600 hover:bg-red-700`
- Primary links: `text-brand-red-600 hover:text-red-900`
- Focus rings: `focus:ring-brand-red-600`
- Active states: `bg-brand-red-600 text-white`

**Status Colors:**
- Success: `bg-green-100 text-green-800` (approved, active, completed)
- Warning: `bg-yellow-100 text-yellow-800` (pending, draft)
- Danger: `bg-red-100 text-red-800` (rejected, inactive, cancelled)
- Info: `bg-blue-100 text-blue-800` (processing, in-review)
- Feature: `bg-purple-100 text-purple-800` (featured items)

**Neutral Colors:**
- Gray scale: 50-900 for borders, backgrounds, text

### 2. Standard Page Layout Structure

```vue
<template>
  <AuthenticatedLayout>
    <!-- PAGE HEADER -->
    <div class="bg-white shadow">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            {{ pageTitle }}
          </h1>
          <p class="mt-1 text-sm text-gray-500">{{ pageDescription }}</p>
        </div>
        
        <!-- PRIMARY ACTION (always brand-red) -->
        <Link 
          :href="route('admin.resource.create')" 
          class="inline-flex items-center px-4 py-2 bg-brand-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
        >
          <PlusIcon class="h-5 w-5 mr-2" />
          Add {{ resourceName }}
        </Link>
      </div>
    </div>

    <!-- FILTERS SECTION -->
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <!-- Search Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600 focus:border-transparent"
            />
          </div>
          
          <!-- Additional filters... -->
          
          <!-- Reset Button (always on far right) -->
          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Reset Filters
            </button>
          </div>
        </div>
      </div>

      <!-- CONTENT AREA -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Table or Card Grid -->
      </div>

      <!-- PAGINATION -->
      <div v-if="items.data.length > 0" class="mt-6 flex justify-center">
        <Link
          v-for="link in items.links"
          :key="link.label"
          :href="link.url"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-lg',
            link.active
              ? 'bg-brand-red-600 text-white'
              : link.url
              ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              : 'bg-gray-100 text-gray-400 cursor-not-allowed'
          ]"
        />
      </div>

      <!-- EMPTY STATE -->
      <div v-else class="text-center py-12">
        <ResourceIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ resourceName }} found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new {{ resourceName }}.</p>
        <Link
          :href="route('admin.resource.create')"
          class="mt-4 inline-flex items-center px-4 py-2 bg-brand-red-600 hover:bg-red-700 text-white font-medium rounded-lg"
        >
          <PlusIcon class="h-5 w-5 mr-2" />
          Add {{ resourceName }}
        </Link>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
```

### 3. Button Hierarchy

**Primary Action Buttons:**
```vue
<!-- Create/Add/Submit (brand red) -->
<button class="bg-brand-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
  Primary Action
</button>
```

**Secondary Action Buttons:**
```vue
<!-- Edit/View/Manage (white with gray border) -->
<button class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-lg">
  Secondary Action
</button>
```

**Danger Action Buttons:**
```vue
<!-- Delete/Cancel (red outline) -->
<button class="bg-white hover:bg-red-50 text-red-600 border border-red-300 px-4 py-2 rounded-lg">
  Danger Action
</button>
```

**Text Links:**
```vue
<!-- Inline actions -->
<Link class="text-brand-red-600 hover:text-red-900 font-medium">
  Edit
</Link>
```

### 4. Form Input Standards

**All text inputs:**
```vue
<input
  type="text"
  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600 focus:border-transparent"
/>
```

**All select dropdowns:**
```vue
<select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-red-600 focus:border-transparent">
  <option>Option</option>
</select>
```

### 5. Status Badge Standards

```vue
<!-- Approved/Active/Completed -->
<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
  Approved
</span>

<!-- Pending/Draft -->
<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
  Pending
</span>

<!-- Rejected/Inactive -->
<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
  Rejected
</span>

<!-- Featured/Special -->
<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
  Featured
</span>
```

### 6. Card Design Standards

**Stats Card:**
```vue
<div class="bg-white shadow rounded-lg p-6">
  <div class="flex items-center">
    <div class="flex-shrink-0 bg-brand-red-600 rounded-md p-3">
      <Icon class="h-6 w-6 text-white" />
    </div>
    <div class="ml-5 w-0 flex-1">
      <dl>
        <dt class="text-sm font-medium text-gray-500 truncate">Label</dt>
        <dd class="text-lg font-medium text-gray-900">Value</dd>
      </dl>
    </div>
  </div>
</div>
```

**List Card:**
```vue
<div class="bg-white shadow rounded-lg overflow-hidden">
  <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
    <h3 class="text-lg font-medium text-gray-900">Card Title</h3>
  </div>
  <div class="px-4 py-5 sm:p-6">
    <!-- Card content -->
  </div>
</div>
```

### 7. Typography Standards

**Page Title:**
```vue
<h1 class="text-3xl font-bold tracking-tight text-gray-900">Page Title</h1>
```

**Section Heading:**
```vue
<h2 class="text-xl font-semibold text-gray-900">Section Title</h2>
```

**Card Heading:**
```vue
<h3 class="text-lg font-medium text-gray-900">Card Title</h3>
```

**Label:**
```vue
<label class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
```

**Description:**
```vue
<p class="mt-1 text-sm text-gray-500">Description text</p>
```

### 8. Migration Checklist

For each admin page:

- [ ] Replace all `bg-teal-*`, `bg-blue-*`, `bg-indigo-*`, `bg-cyan-*` with `bg-brand-red-600`
- [ ] Ensure all primary action buttons use brand red
- [ ] Standardize header layout (title + description + action button)
- [ ] Consistent filter section layout
- [ ] Update all focus rings to `focus:ring-brand-red-600`
- [ ] Standardize pagination active state to brand red
- [ ] Consistent empty state design
- [ ] Proper status badge colors
- [ ] Uniform card designs
- [ ] Consistent typography hierarchy

### 9. Pages to Update

**High Priority (User-Facing Content):**
- ✅ Pages/Index.vue
- ✅ Blog/Posts/Index.vue
- ✅ Events/Index.vue
- ✅ Faqs/Index.vue
- ✅ Testimonials/Index.vue
- ✅ Partners/Index.vue
- ✅ Directories/Index.vue
- ✅ MarketingCampaigns/Index.vue

**Medium Priority (Admin Tools):**
- [ ] Users/Index.vue
- [ ] ServiceApplications/Index.vue
- [ ] ServiceModules/Index.vue
- [ ] ServiceQuotes/Index.vue
- [ ] Visa/Index.vue
- [ ] VisaRequirements/Index.vue
- [ ] Support/Index.vue
- [ ] SupportTickets/Index.vue
- [ ] Wallets/Index.vue
- [ ] Rewards/Index.vue

**Low Priority (Settings/Data Management):**
- [ ] Settings/Index.vue
- [ ] SeoSettings/Index.vue
- [ ] Sitemap/Index.vue
- [ ] DataManagement/**/Index.vue (multiple pages)
- [ ] AgencyVerification/Index.vue
- [ ] Notifications/Index.vue

### 10. Implementation Strategy

**Phase 1: Core Pages (8 pages - COMPLETE ✅)**
All user-facing content management pages now use brand-red-600.

**Phase 2: System Pages (12 pages - NEXT)**
Update all system management pages (users, services, support, wallets).

**Phase 3: Settings Pages (10+ pages - FINAL)**
Update configuration and data management pages.

---

**Last Updated:** December 2, 2025  
**Status:** Phase 1 Complete (8/8 pages), Phase 2 Pending (0/12 pages)  
**Color System:** Brand Red (#e4282b) as primary action color across all pages
