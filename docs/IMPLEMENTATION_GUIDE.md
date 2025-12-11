# Admin Dashboard Redesign - Implementation Guide

## 🚀 Quick Start

### Step 1: Install the New Base Components

Replace your existing components with the new Base component system:

```bash
# All new components are in:
# - resources/js/Components/Base/
# - resources/js/Components/Navigation/
```

### Step 2: Update Tailwind Config

Ensure your `tailwind.config.js` has the recommended color palette:

```javascript
// tailwind.config.js
colors: {
  primary: {
    50: '#EFF6FF',
    100: '#DBEAFE',
    200: '#BFDBFE',
    300: '#93C5FD',
    400: '#60A5FA',
    500: '#3B82F6',  // Main primary
    600: '#2563EB',
    700: '#1D4ED8',
    800: '#1E40AF',
    900: '#1E3A8A',
  },
}
```

### Step 3: Update AdminLayout

Replace your current sidebar navigation with the new structure:

```vue
<!-- resources/js/Layouts/AdminLayout.vue -->
<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <Sidebar :collapsed="sidebarCollapsed">
      <!-- Critical Actions -->
      <NavItem
        href="/admin/dashboard"
        label="Dashboard"
        :icon="HomeIcon"
        :active="route().current('admin.dashboard')"
      />

      <!-- Operations Section -->
      <NavItem
        label="Operations"
        :icon="ClipboardIcon"
        :defaultOpen="true"
        :children="[
          {
            href: '/admin/users',
            label: 'Users',
            icon: UsersIcon,
            badge: '243',
            active: route().current('admin.users.*'),
          },
          {
            href: '/admin/applications',
            label: 'Applications',
            icon: ClipboardDocumentListIcon,
            badge: '38',
            active: route().current('admin.applications.*'),
          },
        ]"
      />
    </Sidebar>

    <main :class="[sidebarCollapsed ? 'md:pl-20' : 'md:pl-64']">
      <slot />
    </main>
  </div>
</template>
```

### Step 4: Migrate Dashboard Stats

Replace old `RhythmicCard` with new `StatCard`:

```vue
<!-- Before -->
<RhythmicCard
  variant="ocean"
  size="md"
  title="Total Users"
  :description="`${stats.users.active} active`"
  :badge="`+${stats.users.today} today`"
>
  <template #icon>
    <UsersIcon class="h-8 w-8" />
  </template>
  <div class="text-display-md font-bold">
    {{ stats.users.total.toLocaleString() }}
  </div>
</RhythmicCard>

<!-- After -->
<StatCard
  :icon="UsersIcon"
  label="Total Users"
  :value="stats.users.total"
  :trend="{ direction: 'up', value: '+12.5%' }"
  description="243 active today"
  variant="blue"
/>
```

### Step 5: Migrate Data Tables

Replace custom tables with the new `DataTable` component:

```vue
<DataTable
  title="Users"
  description="Manage platform users"
  :columns="[
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role' },
    { key: 'created_at', label: 'Joined', sortable: true },
  ]"
  :data="users"
  :loading="loading"
  selectable
  clickable
  @row-click="viewUser"
>
  <!-- Custom cell templates -->
  <template #cell-role="{ value }">
    <Badge :variant="getRoleBadgeVariant(value)">
      {{ value }}
    </Badge>
  </template>

  <!-- Row actions -->
  <template #rowActions="{ row }">
    <Button
      size="sm"
      variant="ghost"
      :icon-left="PencilIcon"
      @click="editUser(row)"
    />
    <Button
      size="sm"
      variant="ghost"
      :icon-left="TrashIcon"
      @click="deleteUser(row)"
    />
  </template>

  <!-- Primary action -->
  <template #actions>
    <Button variant="primary" :icon-left="PlusIcon">
      Add User
    </Button>
  </template>
</DataTable>
```

---

## 📝 Component Usage Examples

### Button Component

```vue
<!-- Primary actions -->
<Button variant="primary" :icon-left="PlusIcon">
  Create New
</Button>

<!-- Secondary actions -->
<Button variant="secondary" :icon-right="ArrowRightIcon">
  View Details
</Button>

<!-- Loading state -->
<Button variant="primary" loading>
  Saving...
</Button>

<!-- With badge -->
<Button variant="ghost" badge="12">
  Notifications
</Button>

<!-- Icon only -->
<Button variant="ghost" :icon-left="CogIcon" size="sm" />

<!-- Full width (mobile) -->
<Button variant="primary" block>
  Submit
</Button>
```

### Card Component

```vue
<!-- Simple card -->
<Card title="Recent Activity" description="Last 7 days">
  <ActivityList :items="recentActivity" />
</Card>

<!-- Card with icon -->
<Card
  title="Statistics"
  :icon="ChartBarIcon"
  icon-variant="blue"
>
  <StatsGrid />
</Card>

<!-- Card with actions -->
<Card title="Users">
  <template #actions>
    <Button variant="ghost" :icon-left="FunnelIcon">
      Filter
    </Button>
    <Button variant="secondary" :icon-left="ArrowDownTrayIcon">
      Export
    </Button>
  </template>

  <UserTable :users="users" />

  <template #footer>
    <Pagination :total="100" :per-page="20" />
  </template>
</Card>

<!-- Loading state -->
<Card title="Dashboard" loading />

<!-- Error state -->
<Card
  title="Revenue Chart"
  error="Failed to load chart data"
  error-title="Connection Error"
  :on-retry="fetchData"
/>
```

### Navigation Component

```vue
<!-- Simple nav item -->
<NavItem
  href="/admin/dashboard"
  label="Dashboard"
  :icon="HomeIcon"
  :active="true"
/>

<!-- With badge -->
<NavItem
  href="/admin/notifications"
  label="Notifications"
  :icon="BellIcon"
  badge="12"
  badge-label="unread"
/>

<!-- With keyboard shortcut -->
<NavItem
  href="/admin/search"
  label="Search"
  :icon="MagnifyingGlassIcon"
  shortcut="⌘K"
/>

<!-- Collapsible section -->
<NavItem
  label="Management"
  :icon="CogIcon"
  :default-open="true"
  :children="[
    {
      href: '/admin/users',
      label: 'Users',
      icon: UsersIcon,
      active: route().current('admin.users.*'),
    },
    {
      href: '/admin/settings',
      label: 'Settings',
      icon: Cog6ToothIcon,
    },
  ]"
/>

<!-- Disabled item -->
<NavItem
  href="/admin/coming-soon"
  label="Analytics"
  :icon="ChartBarIcon"
  disabled
/>
```

---

## 🎨 Styling Guidelines

### Colors

```vue
<!-- Primary actions -->
bg-blue-600 hover:bg-blue-700

<!-- Success states -->
bg-emerald-600 hover:bg-emerald-700

<!-- Warning states -->
bg-amber-500 hover:bg-amber-600

<!-- Error/destructive actions -->
bg-red-600 hover:bg-red-700

<!-- Neutral/secondary -->
bg-gray-100 hover:bg-gray-200
```

### Typography

```vue
<!-- Page titles -->
<h1 class="text-3xl font-bold text-gray-900 dark:text-white">

<!-- Section headers -->
<h2 class="text-2xl font-semibold text-gray-900 dark:text-white">

<!-- Card titles -->
<h3 class="text-lg font-semibold text-gray-900 dark:text-white">

<!-- Body text -->
<p class="text-base text-gray-700 dark:text-gray-300">

<!-- Secondary text -->
<p class="text-sm text-gray-500 dark:text-gray-400">

<!-- Captions -->
<p class="text-xs text-gray-500 dark:text-gray-400">
```

### Spacing

```vue
<!-- Consistent gaps -->
<div class="space-y-6">  <!-- Vertical spacing -->
<div class="space-x-4">  <!-- Horizontal spacing -->
<div class="gap-6">      <!-- Grid/flex gaps -->

<!-- Consistent padding -->
<div class="p-6">        <!-- All sides -->
<div class="px-6 py-4">  <!-- Horizontal + vertical -->
```

---

## ♿ Accessibility Checklist

When implementing new pages, ensure:

- [ ] All interactive elements have proper focus states
- [ ] Keyboard navigation works (Tab, Enter, Escape, Arrow keys)
- [ ] Color contrast meets WCAG AA (4.5:1 for text)
- [ ] Images have alt text
- [ ] Forms have proper labels and error messages
- [ ] Loading states are announced to screen readers
- [ ] Buttons have clear, descriptive labels
- [ ] Modal dialogs trap focus
- [ ] Skip navigation links provided

### Example: Accessible Form

```vue
<form @submit.prevent="handleSubmit">
  <div class="form-field">
    <label for="email" class="required">
      Email Address
    </label>
    <input
      id="email"
      v-model="form.email"
      type="email"
      aria-required="true"
      aria-invalid="form.errors.email ? true : false"
      aria-describedby="email-help email-error"
    />
    <p id="email-help" class="help-text">
      We'll never share your email
    </p>
    <p
      v-if="form.errors.email"
      id="email-error"
      role="alert"
      class="error-text"
    >
      {{ form.errors.email }}
    </p>
  </div>

  <Button
    type="submit"
    variant="primary"
    :loading="form.processing"
    :disabled="form.processing"
  >
    <span v-if="form.processing">Submitting...</span>
    <span v-else>Submit</span>
  </Button>
</form>
```

---

## 📱 Mobile Responsiveness

### Breakpoint Usage

```vue
<!-- Hide on mobile, show on desktop -->
<div class="hidden md:block">Desktop only</div>

<!-- Show on mobile, hide on desktop -->
<div class="block md:hidden">Mobile only</div>

<!-- Responsive grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <!-- Automatically adjusts columns -->
</div>

<!-- Responsive text sizes -->
<h1 class="text-2xl sm:text-3xl lg:text-4xl">
  Responsive Heading
</h1>

<!-- Responsive padding -->
<div class="p-4 sm:p-6 lg:p-8">
  <!-- More padding on larger screens -->
</div>
```

### Touch-Friendly Sizing

```vue
<!-- Minimum 44x44px touch targets -->
<button class="min-h-[44px] min-w-[44px] px-4 py-3">
  Mobile Friendly
</button>

<!-- Larger padding on mobile -->
<div class="p-6 sm:p-4">
  <!-- 24px mobile, 16px desktop -->
</div>
```

---

## 🚀 Performance Tips

### 1. Lazy Load Heavy Components

```vue
<script setup>
import { defineAsyncComponent } from 'vue'

const HeavyChart = defineAsyncComponent(() =>
  import('@/Components/Charts/HeavyChart.vue')
)
</script>

<template>
  <Suspense>
    <HeavyChart :data="data" />
    <template #fallback>
      <ChartSkeleton />
    </template>
  </Suspense>
</template>
```

### 2. Use v-memo for Expensive Lists

```vue
<div
  v-for="item in items"
  :key="item.id"
  v-memo="[item.id, item.status]"
>
  <!-- Only re-renders when id or status changes -->
  <ExpensiveRow :item="item" />
</div>
```

### 3. Optimize Images

```vue
<img
  :src="image.url"
  loading="lazy"
  decoding="async"
  :alt="image.alt"
  :width="image.width"
  :height="image.height"
/>
```

---

## 📚 Migration Checklist

### Phase 1: Foundation
- [ ] Install new base components
- [ ] Update Tailwind config
- [ ] Test dark mode

### Phase 2: Layout
- [ ] Migrate AdminLayout
- [ ] Update navigation structure
- [ ] Test mobile responsiveness

### Phase 3: Dashboard
- [ ] Replace stat cards
- [ ] Update charts
- [ ] Migrate activity feeds

### Phase 4: Data Tables
- [ ] Replace table components
- [ ] Add pagination
- [ ] Test sorting/filtering

### Phase 5: Forms
- [ ] Update form components
- [ ] Add validation
- [ ] Test accessibility

### Phase 6: Polish
- [ ] Add loading states
- [ ] Add empty states
- [ ] Add error boundaries
- [ ] Performance audit
- [ ] Accessibility audit

---

## 🆘 Troubleshooting

### Colors not updating?
```bash
# Clear Tailwind cache
npm run build
# or
npx tailwindcss -i ./resources/css/app.css -o ./public/css/app.css --watch
```

### Components not rendering?
Check import paths and ensure components are properly registered.

### Dark mode not working?
Ensure `dark:` classes are used and `dark` class is added to `<html>` element.

### Icons not showing?
Verify Heroicons are installed:
```bash
npm install @heroicons/vue
```

---

**Questions?** Refer to the main redesign document: `docs/ADMIN_DASHBOARD_REDESIGN.md`
