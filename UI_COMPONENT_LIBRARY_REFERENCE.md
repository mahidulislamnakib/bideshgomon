# BideshGomon - World-Class UI Component Library
## Complete Reference Guide

---

## 🎨 DESIGN SYSTEM CONFIGURATION

### Files Created/Updated

1. **`tailwind.config.js`** - ✅ Complete redesign
   - Professional color palette
   - Mobile-first breakpoints
   - Typography system
   - Spacing & shadows
   - Animation system

2. **`resources/css/app.css`** - ✅ Complete redesign
   - Base layer (typography, focus states, form elements)
   - Components layer (buttons, inputs, cards, badges, alerts)
   - Utilities layer (text, layout, interaction, accessibility)
   - Print styles

3. **`resources/js/Config/design-system.js`** - ✅ New file
   - Centralized design tokens
   - Color definitions
   - Typography scales
   - Spacing system
   - Component variants

---

## 🧩 COMPONENT LIBRARY

### Core Components Created

#### 1. **Button** - `UI/Button.vue` ✅
**World-class button with 8 variants and full accessibility**

```vue
<Button 
  variant="primary|secondary|accent|ghost|outline|danger|success|warning"
  size="xs|sm|md|lg|xl"
  :leading-icon="IconComponent"
  :trailing-icon="IconComponent"
  :loading="boolean"
  :disabled="boolean"
  :full-width="boolean"
  :icon-only="boolean"
  href="/link"  <!-- Optional: makes it an Inertia Link -->
  @click="handleClick"
>
  Button Text
</Button>
```

**Features:**
- 8 color variants
- 5 size options
- Leading/trailing icons
- Loading state with spinner
- Full-width option
- Icon-only mode
- Inertia Link support
- Touch-optimized
- WCAG compliant

#### 2. **Input** - `UI/WorldClassInput.vue` ✅
**Feature-rich input with icons, validation, and character count**

```vue
<WorldClassInput
  v-model="value"
  label="Email Address"
  type="text|email|password|number|tel|url|search|date|time"
  placeholder="Enter email"
  :leading-icon="IconComponent"
  :trailing-icon="IconComponent"
  :leading-addon="'$'"
  helper-text="Helper message"
  helper-position="above|below"
  :error="errorMessage"
  :required="boolean"
  :disabled="boolean"
  :readonly="boolean"
  :loading="boolean"
  :clearable="boolean"
  :maxlength="100"
  size="sm|md|lg"
  @blur="handleBlur"
  @focus="handleFocus"
  @clear="handleClear"
/>
```

**Features:**
- All input types supported
- Password visibility toggle
- Character counter
- Clearable button
- Leading/trailing icons
- Input addons (like currency symbols)
- Loading state
- Error states
- Helper text positioning
- Mobile-optimized

#### 3. **Select** - `UI/WorldClassSelect.vue` ✅
**Styled select dropdown with groups support**

```vue
<WorldClassSelect
  v-model="selected"
  label="Country"
  placeholder="Select country"
  :options="countries"
  value-key="id"
  label-key="name"
  :grouped="boolean"
  :leading-icon="IconComponent"
  :error="errorMessage"
  helper-text="Helper message"
  :required="boolean"
  :disabled="boolean"
  size="sm|md|lg"
  @change="handleChange"
/>
```

**Options Format:**
```javascript
// Simple
const options = [
  { value: 1, label: 'Option 1' },
  { value: 2, label: 'Option 2' }
]

// Grouped
const options = [
  {
    label: 'Group 1',
    options: [
      { value: 1, label: 'Option 1.1' },
      { value: 2, label: 'Option 1.2' }
    ]
  }
]
```

**Features:**
- Styled dropdown
- Grouped options
- Icon support
- Custom value/label keys
- Error states
- Mobile-friendly

#### 4. **Textarea** - `UI/WorldClassTextarea.vue` ✅
**Auto-resize textarea with character count**

```vue
<WorldClassTextarea
  v-model="description"
  label="Description"
  placeholder="Enter description"
  :rows="4"
  :maxlength="500"
  :show-character-count="boolean"
  :auto-resize="boolean"
  helper-text="Helper message"
  :error="errorMessage"
  :required="boolean"
  :disabled="boolean"
  :readonly="boolean"
  size="sm|md|lg"
/>
```

**Features:**
- Auto-resize capability
- Character counter
- Max length validation
- Visual warning near limit
- All standard textarea features

#### 5. **Badge** - `UI/WorldClassBadge.vue` ✅
**Status indicators and labels**

```vue
<WorldClassBadge
  variant="primary|secondary|success|warning|error|info|neutral"
  size="sm|md|lg"
  :rounded="boolean"
  :leading-icon="IconComponent"
  :trailing-icon="IconComponent"
  :dismissible="boolean"
  @dismiss="handleDismiss"
>
  Active
</WorldClassBadge>
```

**Features:**
- 7 semantic variants
- 3 sizes
- Icon support (leading/trailing)
- Dismissible option
- Rounded or square

#### 6. **Alert** - `UI/WorldClassAlert.vue` ✅
**Notification messages with auto-dismiss**

```vue
<WorldClassAlert
  v-model="showAlert"
  variant="success|warning|error|info"
  title="Success!"
  message="Operation completed successfully"
  :dismissible="boolean"
  :auto-dismiss="5000"
  @dismiss="handleDismiss"
>
  <template #actions>
    <Button size="sm">Undo</Button>
  </template>
</WorldClassAlert>
```

**Features:**
- 4 semantic variants
- Auto icon selection
- Dismissible
- Auto-dismiss timer
- Action slot for buttons
- Smooth animations

#### 7. **Modal** - `UI/WorldClassModal.vue` ✅
**Accessible modal dialog**

```vue
<WorldClassModal
  v-model:show="isOpen"
  title="Confirm Action"
  subtitle="This action cannot be undone"
  size="sm|md|lg|xl|2xl|full"
  :closeable="boolean"
  :close-on-backdrop="boolean"
  :divided="boolean"
  @close="handleClose"
>
  <!-- Modal body content -->
  
  <template #footer>
    <Button variant="outline" @click="handleClose">Cancel</Button>
    <Button variant="primary" @click="handleConfirm">Confirm</Button>
  </template>
</WorldClassModal>
```

**Features:**
- 6 size options
- Backdrop click handling
- Escape key support
- Divided sections
- Body scroll lock
- Smooth animations
- Teleport to body
- Mobile responsive

#### 8. **Table** - `UI/WorldClassTable.vue` ✅
**Data table with sorting**

```vue
<WorldClassTable
  title="Users"
  description="Manage system users"
  :columns="columns"
  :data="users"
  :hoverable="boolean"
  default-sort-by="name"
  default-sort-order="asc|desc"
  empty-message="No users found"
  @row-click="handleRowClick"
  @sort="handleSort"
>
  <template #actions>
    <Button size="sm" :leading-icon="PlusIcon">Add User</Button>
  </template>
  
  <template #cell-status="{ row, value }">
    <WorldClassBadge :variant="value === 'active' ? 'success' : 'neutral'">
      {{ value }}
    </WorldClassBadge>
  </template>
  
  <template #footer>
    <Pagination :data="users" />
  </template>
</WorldClassTable>
```

**Columns Format:**
```javascript
const columns = [
  {
    key: 'name',
    label: 'Name',
    sortable: true,
    headerClass: '',
    cellClass: 'font-medium'
  },
  {
    key: 'email',
    label: 'Email',
    sortable: true
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false
  }
]
```

**Features:**
- Sortable columns
- Custom cell rendering
- Empty state
- Row click events
- Responsive
- Header actions
- Footer slot for pagination

---

## 🎨 CSS UTILITY CLASSES

### Buttons
```css
.btn              /* Base button */
.btn-primary      /* Primary action */
.btn-secondary    /* Secondary action */
.btn-accent       /* Accent action */
.btn-ghost        /* Ghost/transparent */
.btn-outline      /* Outlined */
.btn-danger       /* Destructive action */
.btn-sm           /* Small size */
.btn-lg           /* Large size */
.btn-xl           /* Extra large */
```

### Inputs
```css
.input            /* Base input */
.input-error      /* Error state */
.input-sm         /* Small size */
.input-lg         /* Large size */
```

### Cards
```css
.card             /* Base card */
.card-hover       /* Hover effect */
.card-sm          /* Small padding */
.card-md          /* Medium padding */
.card-lg          /* Large padding */
```

### Badges
```css
.badge            /* Base badge */
.badge-success    /* Success variant */
.badge-warning    /* Warning variant */
.badge-error      /* Error variant */
.badge-info       /* Info variant */
.badge-neutral    /* Neutral variant */
```

### Alerts
```css
.alert            /* Base alert */
.alert-success    /* Success variant */
.alert-warning    /* Warning variant */
.alert-error      /* Error variant */
.alert-info       /* Info variant */
```

### Containers
```css
.container-responsive  /* 7xl max-width */
.container-narrow      /* 4xl max-width */
.container-wide        /* 9xl max-width */
```

### Loading States
```css
.skeleton         /* Loading skeleton */
.spinner          /* Loading spinner */
```

### Dividers
```css
.divider          /* Thin divider */
.divider-strong   /* Thick divider */
```

---

## 🎯 USAGE EXAMPLES

### Complete Form
```vue
<template>
  <div class="container-responsive py-8">
    <Card title="User Profile" padding="lg" divided>
      <form @submit.prevent="handleSubmit">
        <div class="space-y-6">
          <WorldClassInput
            v-model="form.name"
            label="Full Name"
            :leading-icon="UserIcon"
            :error="form.errors.name"
            required
          />
          
          <WorldClassInput
            v-model="form.email"
            label="Email"
            type="email"
            :leading-icon="EnvelopeIcon"
            :error="form.errors.email"
            required
          />
          
          <WorldClassSelect
            v-model="form.country"
            label="Country"
            :options="countries"
            :error="form.errors.country"
            required
          />
          
          <WorldClassTextarea
            v-model="form.bio"
            label="Bio"
            :maxlength="500"
            :auto-resize="true"
            :error="form.errors.bio"
          />
        </div>
        
        <template #footer>
          <div class="flex justify-end gap-3">
            <Button variant="outline" @click="handleCancel">
              Cancel
            </Button>
            <Button variant="primary" type="submit" :loading="form.processing">
              Save Profile
            </Button>
          </div>
        </template>
      </form>
    </Card>
  </div>
</template>
```

### Data Table with Actions
```vue
<template>
  <WorldClassTable
    title="Team Members"
    :columns="columns"
    :data="members"
    @row-click="handleViewMember"
  >
    <template #actions>
      <Button size="sm" :leading-icon="PlusIcon">
        Add Member
      </Button>
    </template>
    
    <template #cell-role="{ value }">
      <WorldClassBadge :variant="getRoleBadgeVariant(value)">
        {{ value }}
      </WorldClassBadge>
    </template>
    
    <template #cell-actions="{ row }">
      <div class="flex gap-2">
        <Button 
          size="xs" 
          variant="ghost" 
          :leading-icon="PencilIcon"
          @click.stop="handleEdit(row)"
        />
        <Button 
          size="xs" 
          variant="ghost" 
          :leading-icon="TrashIcon"
          @click.stop="handleDelete(row)"
        />
      </div>
    </template>
  </WorldClassTable>
</template>
```

### Modal Confirmation
```vue
<template>
  <div>
    <Button variant="danger" @click="showConfirm = true">
      Delete Account
    </Button>
    
    <WorldClassModal
      v-model:show="showConfirm"
      title="Confirm Deletion"
      subtitle="This action cannot be undone"
      size="sm"
    >
      <p class="text-neutral-700">
        Are you sure you want to delete your account? 
        All your data will be permanently removed.
      </p>
      
      <template #footer>
        <Button variant="outline" @click="showConfirm = false">
          Cancel
        </Button>
        <Button 
          variant="danger" 
          :loading="isDeleting"
          @click="handleDelete"
        >
          Delete Account
        </Button>
      </template>
    </WorldClassModal>
  </div>
</template>
```

---

## 📱 MOBILE-FIRST RESPONSIVE PATTERNS

### Stack on Mobile, Row on Desktop
```vue
<div class="flex flex-col md:flex-row gap-4">
  <div class="flex-1">Content 1</div>
  <div class="flex-1">Content 2</div>
</div>
```

### Hide on Mobile
```vue
<div class="hidden md:block">Desktop only</div>
```

### Show on Mobile Only
```vue
<div class="block md:hidden">Mobile only</div>
```

### Responsive Padding
```vue
<div class="px-4 sm:px-6 lg:px-8">
  Content with responsive padding
</div>
```

### Responsive Grid
```vue
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <!-- Grid items -->
</div>
```

---

## ♿ ACCESSIBILITY BEST PRACTICES

### Always Include Labels
```vue
<WorldClassInput
  label="Email Address"  <!-- ✅ Always provide -->
  required
/>
```

### Use Semantic Variants
```vue
<WorldClassAlert variant="error">  <!-- ✅ Use semantic colors -->
  Error message
</WorldClassAlert>
```

### Provide Error Messages
```vue
<WorldClassInput
  :error="form.errors.email"  <!-- ✅ Clear error feedback -->
/>
```

### Use Button Types
```vue
<Button type="submit">  <!-- ✅ Semantic button types -->
  Submit Form
</Button>
```

### Modal Titles
```vue
<WorldClassModal title="User Settings">  <!-- ✅ Descriptive titles -->
  <!-- Content -->
</WorldClassModal>
```

---

## 🚀 MIGRATION GUIDE

### Old → New Components

```vue
<!-- OLD -->
<PrimaryButton @click="handleClick">Save</PrimaryButton>

<!-- NEW -->
<Button variant="primary" @click="handleClick">Save</Button>
```

```vue
<!-- OLD -->
<TextInput v-model="form.email" />

<!-- NEW -->
<WorldClassInput v-model="form.email" label="Email" />
```

```vue
<!-- OLD -->
<div class="bg-green-100 text-green-800 px-2 py-1 rounded">Active</div>

<!-- NEW -->
<WorldClassBadge variant="success">Active</WorldClassBadge>
```

---

## 📊 COMPONENT CHECKLIST

### ✅ Created Components
- [x] Button (8 variants, 5 sizes, full features)
- [x] Input (password toggle, clear, icons, counter)
- [x] Select (grouped options, styled)
- [x] Textarea (auto-resize, counter)
- [x] Badge (7 variants, dismissible)
- [x] Alert (4 variants, auto-dismiss)
- [x] Modal (6 sizes, accessible)
- [x] Table (sortable, custom cells)

### 🔄 To Be Updated
- [ ] Existing Input.vue (merge or replace)
- [ ] Existing Card.vue (enhance)
- [ ] Existing Modal.vue (replace)
- [ ] Existing Badge.vue (replace)
- [ ] Dropdown component
- [ ] Tabs component
- [ ] Accordion component
- [ ] Toast notifications

---

## 🎨 COLOR USAGE GUIDE

### When to Use Each Color

**Primary (Green #64ac44)**
- Main CTAs (Save, Submit, Confirm)
- Primary navigation items
- Success states

**Secondary (Red #e4282b)**
- Destructive actions (Delete, Remove)
- Error states
- Urgent notifications

**Accent (Blue #3b82f6)**
- Links
- Info states
- Secondary actions

**Success (Green)**
- Success messages
- Completed states
- Positive indicators

**Warning (Yellow)**
- Warning messages
- Pending states
- Caution indicators

**Error (Red)**
- Error messages
- Validation errors
- Failed states

**Info (Blue)**
- Info messages
- Help text
- Neutral notifications

**Neutral (Gray)**
- Text content
- Borders
- Backgrounds
- Inactive states

---

## 📚 NEXT STEPS

1. **Update Existing Components** - Replace old components with new ones
2. **Layout Redesign** - Apply new design system to all layouts
3. **Page Updates** - Update all pages to use new components
4. **Mobile Testing** - Verify all components on mobile devices
5. **Accessibility Audit** - Run WCAG AA compliance checks
6. **Performance Testing** - Optimize animations and transitions
7. **Documentation** - Create Storybook or component showcase

---

**Created:** December 20, 2025  
**Standard:** International World-Class UI/UX  
**Framework:** Vue 3 + Tailwind CSS + Inertia.js
