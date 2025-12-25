<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-4">
            <a href="/admin/settings" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Back to Admin">
              <ArrowLeftIcon class="w-5 h-5" />
            </a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
              🎨 Component Gallery
            </h1>
            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
              {{ totalComponents }} Components
            </span>
          </div>
          
          <!-- Search -->
          <div class="flex-1 max-w-md mx-8">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search all components..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <SunIcon v-if="isDark" class="w-5 h-5" />
            <MoonIcon v-else class="w-5 h-5" />
          </button>
        </div>
      </div>
    </header>

    <div class="flex">
      <!-- Sidebar -->
      <aside class="w-64 h-[calc(100vh-4rem)] sticky top-16 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
        <nav class="p-4 space-y-2">
          <!-- All Components option when searching -->
          <button
            v-if="searchQuery"
            @click="activeCategory = 'All'"
            :class="[
              'w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              activeCategory === 'All'
                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <span class="flex items-center gap-2">
              <span>🔍</span>
              <span>All Results</span>
            </span>
            <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full">
              {{ allSearchResults.length }}
            </span>
          </button>
          
          <button
            v-for="category in categories"
            :key="category.name"
            @click="activeCategory = category.name"
            :class="[
              'w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              activeCategory === category.name
                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <span class="flex items-center gap-2">
              <span>{{ category.icon }}</span>
              <span>{{ category.name }}</span>
            </span>
            <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full">
              {{ getCategoryResultCount(category) }}
            </span>
          </button>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-8">
        <!-- Category Title -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ activeCategory === 'All' ? '🔍 Search Results' : (currentCategory?.icon + ' ' + activeCategory) }}
          </h2>
          <p class="mt-1 text-gray-500 dark:text-gray-400">
            {{ activeCategory === 'All' ? `Found ${filteredComponents.length} components matching "${searchQuery}"` : currentCategory?.description }}
          </p>
        </div>

        <!-- Components Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="component in filteredComponents"
            :key="component.name"
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow"
          >
            <!-- Preview Area -->
            <div class="h-40 bg-gray-50 dark:bg-gray-900 p-4 flex items-center justify-center border-b border-gray-200 dark:border-gray-700 overflow-hidden">
              <Suspense>
                <template #default>
                  <component
                    :is="getComponent(component.name)"
                    v-bind="component.defaultProps"
                    class="max-w-full"
                  />
                </template>
                <template #fallback>
                  <div class="text-gray-400 text-sm flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Loading...
                  </div>
                </template>
              </Suspense>
            </div>
            
            <!-- Info -->
            <div class="p-4">
              <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                  {{ component.name }}
                </h3>
                <button
                  @click="copyImport(component.name)"
                  class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                  title="Copy import"
                >
                  <ClipboardDocumentIcon class="w-4 h-4" />
                </button>
              </div>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ component.description }}
              </p>
              
              <!-- Category Badge (only in search results) -->
              <div v-if="component.category && activeCategory === 'All'" class="mt-2">
                <span class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full">
                  {{ component.category }}
                </span>
              </div>
              
              <!-- Tags -->
              <div class="mt-3 flex flex-wrap gap-1">
                <span
                  v-for="tag in component.tags"
                  :key="tag"
                  class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-if="filteredComponents.length === 0"
          class="text-center py-12"
        >
          <MagnifyingGlassIcon class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" />
          <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No components found</h3>
          <p class="mt-1 text-gray-500 dark:text-gray-400">Try adjusting your search query</p>
        </div>
      </main>
    </div>

    <!-- Toast -->
    <Transition name="toast">
      <div
        v-if="showToast"
        class="fixed bottom-4 right-4 px-4 py-2 bg-gray-900 text-white rounded-lg shadow-lg"
      >
        ✓ Copied to clipboard
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, onMounted, watch } from 'vue'
import { MagnifyingGlassIcon, SunIcon, MoonIcon, ClipboardDocumentIcon, ArrowLeftIcon, XMarkIcon } from '@heroicons/vue/24/outline'

// Lazy load all components
const componentModules = import.meta.glob('@/Components/ui/*.vue')

const getComponent = (name) => {
  const path = `/resources/js/Components/ui/${name}.vue`
  if (componentModules[path]) {
    return defineAsyncComponent({
      loader: componentModules[path],
      delay: 200,
      timeout: 5000,
      errorComponent: {
        template: '<div class="text-red-400 text-xs text-center"><span class="block">⚠️</span>Error loading</div>'
      },
      loadingComponent: {
        template: '<div class="text-gray-400 text-xs flex items-center gap-1"><svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Loading...</div>'
      }
    })
  }
  return null
}

const searchQuery = ref('')
const activeCategory = ref('Form Inputs')
const previousCategory = ref('Form Inputs')
const isDark = ref(false)
const showToast = ref(false)

// Auto-select "All" when searching, restore when cleared
watch(searchQuery, (newVal, oldVal) => {
  if (newVal && !oldVal) {
    // Started searching - save current category and switch to All
    previousCategory.value = activeCategory.value
    activeCategory.value = 'All'
  } else if (!newVal && oldVal) {
    // Cleared search - restore previous category
    activeCategory.value = previousCategory.value
  }
})

// Check initial dark mode
onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark') || localStorage.getItem('darkMode') === 'true'
  if (isDark.value) {
    document.documentElement.classList.add('dark')
  }
})

const toggleDarkMode = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('darkMode', isDark.value.toString())
}

const copyImport = async (name) => {
  const importStatement = `import ${name} from '@/Components/ui/${name}.vue'`
  await navigator.clipboard.writeText(importStatement)
  showToast.value = true
  setTimeout(() => showToast.value = false, 2000)
}

// All 229 Components organized by category
const categories = ref([
  {
    name: 'Form Inputs',
    icon: '📝',
    description: 'Input components for forms and data entry',
    components: [
      { name: 'Input', description: 'Basic text input field', tags: ['form', 'text'], defaultProps: { modelValue: '', placeholder: 'Enter text...' } },
      { name: 'Textarea', description: 'Multi-line text input', tags: ['form', 'text'], defaultProps: { modelValue: 'Sample text content', placeholder: 'Enter text...', rows: 3 } },
      { name: 'Select', description: 'Dropdown select input', tags: ['form', 'dropdown'], defaultProps: { modelValue: '1', options: [{ value: '1', label: 'Option 1' }, { value: '2', label: 'Option 2' }] } },
      { name: 'SelectBox', description: 'Enhanced select with search', tags: ['form', 'dropdown'], defaultProps: { modelValue: '', options: [{ value: '1', label: 'Option 1' }], placeholder: 'Select...' } },
      { name: 'Checkbox', description: 'Single checkbox input', tags: ['form', 'boolean'], defaultProps: { modelValue: true, label: 'Check me' } },
      { name: 'CheckboxGroup', description: 'Multiple checkbox selection', tags: ['form', 'group'], defaultProps: { modelValue: ['opt1'], options: [{ value: 'opt1', label: 'Option 1' }, { value: 'opt2', label: 'Option 2' }] } },
      { name: 'Radio', description: 'Radio button input', tags: ['form', 'selection'], defaultProps: { modelValue: 'opt1', name: 'demo', value: 'opt1', label: 'Radio option' } },
      { name: 'RadioGroup', description: 'Radio button group', tags: ['form', 'group'], defaultProps: { modelValue: 'opt1', name: 'radio-demo', options: [{ value: 'opt1', label: 'Option 1' }, { value: 'opt2', label: 'Option 2' }] } },
      { name: 'ToggleSwitch', description: 'On/off toggle switch', tags: ['form', 'boolean'], defaultProps: { modelValue: true } },
      { name: 'NumberInput', description: 'Numeric input with controls', tags: ['form', 'number'], defaultProps: { modelValue: 42 } },
      { name: 'CurrencyInput', description: 'Currency formatted input', tags: ['form', 'money'], defaultProps: { modelValue: 1000, currency: 'BDT' } },
      { name: 'PhoneInput', description: 'Phone number input', tags: ['form', 'phone'], defaultProps: { modelValue: '01712345678' } },
      { name: 'OTPInput', description: 'One-time password input', tags: ['form', 'security'], defaultProps: { length: 4 } },
      { name: 'PinInput', description: 'PIN code input', tags: ['form', 'security'], defaultProps: { length: 4 } },
      { name: 'DatePicker', description: 'Date selection input', tags: ['form', 'date'], defaultProps: { modelValue: new Date().toISOString().split('T')[0] } },
      { name: 'TimePicker', description: 'Time selection input', tags: ['form', 'time'], defaultProps: { modelValue: '10:30' } },
      { name: 'ColorPicker', description: 'Color selection input', tags: ['form', 'color'], defaultProps: { modelValue: '#3B82F6' } },
      { name: 'RangeSlider', description: 'Range slider input', tags: ['form', 'range'], defaultProps: { modelValue: 50, min: 0, max: 100 } },
      { name: 'Rating', description: 'Star rating display', tags: ['form', 'rating'], defaultProps: { modelValue: 4, max: 5 } },
      { name: 'RatingInput', description: 'Interactive rating input', tags: ['form', 'rating'], defaultProps: { modelValue: 3 } },
      { name: 'StarRating', description: 'Star-based rating', tags: ['form', 'rating'], defaultProps: { modelValue: 4 } },
      { name: 'SearchInput', description: 'Search input field', tags: ['form', 'search'], defaultProps: { modelValue: '', placeholder: 'Search...' } },
      { name: 'TagInput', description: 'Tag/chip input field', tags: ['form', 'tags'], defaultProps: { modelValue: ['Vue', 'Laravel'] } },
      { name: 'MentionInput', description: 'Input with @mentions', tags: ['form', 'mentions'], defaultProps: { modelValue: 'Hello @user', users: [{ id: 1, name: 'John' }] } },
      { name: 'FloatingLabelInput', description: 'Input with floating label', tags: ['form', 'label'], defaultProps: { modelValue: 'Sample text', label: 'Label' } },
      { name: 'PasswordStrength', description: 'Password with strength meter', tags: ['form', 'password'], defaultProps: { modelValue: 'Pass123!' } },
      { name: 'InlineEdit', description: 'Click to edit text', tags: ['form', 'edit'], defaultProps: { modelValue: 'Click to edit' } },
      { name: 'Signature', description: 'Signature drawing pad', tags: ['form', 'signature'], defaultProps: {} },
      { name: 'SignaturePad', description: 'Full signature pad', tags: ['form', 'signature'], defaultProps: {} },
      { name: 'Cascader', description: 'Cascading selection', tags: ['form', 'nested'], defaultProps: { options: [{ value: 'a', label: 'Option A', children: [{ value: 'a1', label: 'A-1' }] }] } },
      { name: 'Combobox', description: 'Searchable dropdown', tags: ['form', 'search'], defaultProps: { modelValue: '', options: [{ value: '1', label: 'Item 1' }], placeholder: 'Select...' } },
      { name: 'TransferList', description: 'Dual list transfer', tags: ['form', 'list'], defaultProps: { sourceItems: [{ id: 1, label: 'Item 1' }], targetItems: [] } },
      { name: 'LocationPicker', description: 'Location selection', tags: ['form', 'location'], defaultProps: {} },
      { name: 'EmojiPicker', description: 'Emoji selection', tags: ['form', 'emoji'], defaultProps: {} },
      { name: 'IconPicker', description: 'Icon selection', tags: ['form', 'icon'], defaultProps: {} },
      { name: 'CreditCardInput', description: 'Credit card input', tags: ['form', 'payment'], defaultProps: {} },
      { name: 'TreeSelect', description: 'Tree dropdown select', tags: ['form', 'tree'], defaultProps: { options: [{ value: '1', label: 'Node 1', children: [] }] } },
      { name: 'BideshInput', description: 'Branded input field', tags: ['form', 'branded'], defaultProps: { modelValue: '', placeholder: 'Bidesh input...' } },
      { name: 'WorldClassInput', description: 'Premium input field', tags: ['form', 'premium'], defaultProps: { modelValue: '', label: 'Label' } },
      { name: 'WorldClassSelect', description: 'Premium select field', tags: ['form', 'premium'], defaultProps: { modelValue: '', options: [{ value: '1', label: 'Option' }] } },
      { name: 'WorldClassTextarea', description: 'Premium textarea', tags: ['form', 'premium'], defaultProps: { modelValue: 'Content' } },
    ]
  },
  {
    name: 'Buttons',
    icon: '🔘',
    description: 'Button and action components',
    components: [
      { name: 'Button', description: 'Standard button', tags: ['action', 'click'], defaultProps: { default: 'Click Me' } },
      { name: 'ActionButton', description: 'Action button with icon', tags: ['action', 'icon'], defaultProps: { label: 'Action' } },
      { name: 'BideshButton', description: 'Branded button', tags: ['action', 'branded'], defaultProps: { default: 'Bidesh' } },
      { name: 'SplitButton', description: 'Button with dropdown', tags: ['action', 'dropdown'], defaultProps: { label: 'Save', options: [{ label: 'Save as...' }] } },
      { name: 'FloatingActionButton', description: 'FAB button', tags: ['action', 'floating'], defaultProps: {} },
      { name: 'SpeedDial', description: 'Speed dial menu', tags: ['action', 'menu'], defaultProps: { actions: [{ icon: '📧', label: 'Email' }] } },
      { name: 'CopyButton', description: 'Copy to clipboard button', tags: ['action', 'copy'], defaultProps: { text: 'Copy this' } },
      { name: 'CopyToClipboard', description: 'Copy wrapper component', tags: ['action', 'copy'], defaultProps: { text: 'Copied!' } },
      { name: 'ExportButton', description: 'Export data button', tags: ['action', 'export'], defaultProps: { data: [], filename: 'export' } },
      { name: 'BulkActionsBar', description: 'Bulk action toolbar', tags: ['action', 'bulk'], defaultProps: { selectedCount: 3, actions: [] } },
      { name: 'RowActionsDropdown', description: 'Row actions menu', tags: ['action', 'menu'], defaultProps: { actions: [{ label: 'Edit' }, { label: 'Delete' }] } },
      { name: 'QuickActions', description: 'Quick action panel', tags: ['action', 'panel'], defaultProps: { actions: [{ icon: '➕', label: 'Add' }] } },
    ]
  },
  {
    name: 'Data Display',
    icon: '📊',
    description: 'Components for displaying data',
    components: [
      { name: 'Table', description: 'Basic data table', tags: ['data', 'table'], defaultProps: { columns: [{ key: 'name', label: 'Name' }], data: [{ name: 'John' }] } },
      { name: 'DataTable', description: 'Advanced data table', tags: ['data', 'table'], defaultProps: { columns: [{ key: 'id', label: 'ID' }], rows: [{ id: 1 }] } },
      { name: 'DataGrid', description: 'Data grid component', tags: ['data', 'grid'], defaultProps: { columns: [], rows: [] } },
      { name: 'StickyTable', description: 'Table with sticky header', tags: ['data', 'table'], defaultProps: { columns: [], data: [] } },
      { name: 'TableWrapper', description: 'Table container wrapper', tags: ['data', 'wrapper'], defaultProps: {} },
      { name: 'WorldClassTable', description: 'Premium data table', tags: ['data', 'premium'], defaultProps: { columns: [], data: [] } },
      { name: 'Card', description: 'Content card', tags: ['layout', 'container'], defaultProps: { title: 'Card Title' } },
      { name: 'BideshCard', description: 'Branded card', tags: ['layout', 'branded'], defaultProps: { title: 'Bidesh Card' } },
      { name: 'StatCard', description: 'Statistics card', tags: ['data', 'stats'], defaultProps: { title: 'Users', value: '1,234' } },
      { name: 'StatsCard', description: 'Stats display card', tags: ['data', 'stats'], defaultProps: { title: 'Revenue', value: '৳50,000' } },
      { name: 'MetricCard', description: 'Metric display card', tags: ['data', 'metric'], defaultProps: { label: 'Visitors', value: 1234 } },
      { name: 'ChartCard', description: 'Card with chart', tags: ['data', 'chart'], defaultProps: { title: 'Chart' } },
      { name: 'Badge', description: 'Status badge', tags: ['display', 'status'], defaultProps: { default: 'New' } },
      { name: 'BideshBadge', description: 'Branded badge', tags: ['display', 'branded'], defaultProps: { label: 'Pro' } },
      { name: 'WorldClassBadge', description: 'Premium badge', tags: ['display', 'premium'], defaultProps: { label: 'Premium' } },
      { name: 'Chip', description: 'Info chip', tags: ['display', 'tag'], defaultProps: { label: 'Vue.js' } },
      { name: 'Avatar', description: 'User avatar', tags: ['display', 'user'], defaultProps: { name: 'John Doe' } },
      { name: 'AvatarGroup', description: 'Avatar group', tags: ['display', 'users'], defaultProps: { users: [{ name: 'A' }, { name: 'B' }] } },
      { name: 'AvatarUpload', description: 'Avatar with upload', tags: ['display', 'upload'], defaultProps: {} },
      { name: 'UserAvatar', description: 'User profile avatar', tags: ['display', 'profile'], defaultProps: { user: { name: 'User' } } },
      { name: 'Timeline', description: 'Timeline display', tags: ['data', 'history'], defaultProps: { items: [{ title: 'Event 1', date: 'Today' }] } },
      { name: 'ActivityFeed', description: 'Activity feed list', tags: ['data', 'feed'], defaultProps: { activities: [{ text: 'User logged in', time: '2m ago' }] } },
      { name: 'TreeView', description: 'Tree structure view', tags: ['data', 'tree'], defaultProps: { items: [{ label: 'Root', children: [] }] } },
      { name: 'FileTree', description: 'File tree display', tags: ['data', 'files'], defaultProps: { items: [{ name: 'src', type: 'folder' }] } },
      { name: 'JsonViewer', description: 'JSON data viewer', tags: ['data', 'json'], defaultProps: { data: { key: 'value' } } },
      { name: 'CodeBlock', description: 'Syntax highlighted code', tags: ['display', 'code'], defaultProps: { code: 'const x = 1;', language: 'javascript' } },
      { name: 'CodeDiff', description: 'Code diff viewer', tags: ['display', 'diff'], defaultProps: { oldCode: 'old', newCode: 'new' } },
      { name: 'DiffViewer', description: 'Text diff viewer', tags: ['display', 'diff'], defaultProps: { oldText: 'before', newText: 'after' } },
      { name: 'QRCode', description: 'QR code generator', tags: ['display', 'qr'], defaultProps: { value: 'https://bideshgomon.com' } },
      { name: 'Barcode', description: 'Barcode generator', tags: ['display', 'barcode'], defaultProps: { value: '123456789' } },
      { name: 'CreditCard', description: 'Credit card display', tags: ['display', 'payment'], defaultProps: { number: '4242424242424242', name: 'John Doe' } },
      { name: 'RelativeTime', description: 'Relative time display', tags: ['display', 'time'], defaultProps: { date: new Date() } },
      { name: 'Countdown', description: 'Countdown timer', tags: ['display', 'timer'], defaultProps: { targetDate: new Date(Date.now() + 86400000) } },
      { name: 'AnimatedCounter', description: 'Animated number counter', tags: ['display', 'animation'], defaultProps: { value: 1234 } },
      { name: 'AnimatedStat', description: 'Animated stat display', tags: ['display', 'animation'], defaultProps: { value: 5000, label: 'Users' } },
      { name: 'Kbd', description: 'Keyboard key display', tags: ['display', 'keyboard'], defaultProps: { default: 'Ctrl' } },
    ]
  },
  {
    name: 'Charts',
    icon: '📈',
    description: 'Data visualization charts',
    components: [
      { name: 'GaugeChart', description: 'Gauge/speedometer chart', tags: ['chart', 'gauge'], defaultProps: { value: 75, max: 100 } },
      { name: 'HeatMap', description: 'Heat map visualization', tags: ['chart', 'heatmap'], defaultProps: { data: [[1,2],[3,4]] } },
      { name: 'TreeMap', description: 'Treemap chart', tags: ['chart', 'treemap'], defaultProps: { data: [{ name: 'A', value: 100 }, { name: 'B', value: 50 }] } },
      { name: 'SunburstChart', description: 'Sunburst chart', tags: ['chart', 'sunburst'], defaultProps: { data: { name: 'Root', children: [{ name: 'A', value: 10 }] } } },
      { name: 'RadarChart', description: 'Radar/spider chart', tags: ['chart', 'radar'], defaultProps: { data: [{ label: 'A', value: 80 }, { label: 'B', value: 60 }, { label: 'C', value: 70 }] } },
      { name: 'CircularProgress', description: 'Circular progress chart', tags: ['chart', 'progress'], defaultProps: { value: 75, size: 80 } },
      { name: 'NetworkGraph', description: 'Network graph visualization', tags: ['chart', 'network'], defaultProps: { nodes: [], edges: [] } },
      { name: 'FlowChart', description: 'Flow chart diagram', tags: ['chart', 'flow'], defaultProps: { nodes: [], edges: [] } },
      { name: 'GanttChart', description: 'Gantt chart timeline', tags: ['chart', 'gantt'], defaultProps: { tasks: [] } },
      { name: 'MindMap', description: 'Mind map diagram', tags: ['chart', 'mindmap'], defaultProps: { data: { text: 'Center' } } },
    ]
  },
  {
    name: 'Feedback',
    icon: '⚡',
    description: 'User feedback and notification components',
    components: [
      { name: 'Alert', description: 'Alert message', tags: ['feedback', 'message'], defaultProps: { type: 'info', message: 'This is an alert' } },
      { name: 'WorldClassAlert', description: 'Premium alert', tags: ['feedback', 'premium'], defaultProps: { type: 'success', title: 'Success!' } },
      { name: 'Notification', description: 'Notification message', tags: ['feedback', 'notify'], defaultProps: { title: 'Notification', message: 'You have a message' } },
      { name: 'NotificationBell', description: 'Notification bell icon', tags: ['feedback', 'bell'], defaultProps: { count: 5 } },
      { name: 'NotificationCenter', description: 'Notification panel', tags: ['feedback', 'center'], defaultProps: { notifications: [] } },
      { name: 'ToastContainer', description: 'Toast notifications', tags: ['feedback', 'toast'], defaultProps: {} },
      { name: 'Modal', description: 'Modal dialog', tags: ['feedback', 'dialog'], defaultProps: { show: false, title: 'Modal' } },
      { name: 'WorldClassModal', description: 'Premium modal', tags: ['feedback', 'premium'], defaultProps: { show: false } },
      { name: 'ConfirmModal', description: 'Confirmation dialog', tags: ['feedback', 'confirm'], defaultProps: { show: false, title: 'Confirm', message: 'Are you sure?' } },
      { name: 'ConfirmModalProvider', description: 'Confirm modal provider', tags: ['feedback', 'provider'], defaultProps: {} },
      { name: 'Drawer', description: 'Slide-out drawer', tags: ['feedback', 'panel'], defaultProps: { show: false, title: 'Drawer' } },
      { name: 'BottomSheet', description: 'Bottom sheet panel', tags: ['feedback', 'mobile'], defaultProps: { show: false } },
      { name: 'Tooltip', description: 'Hover tooltip', tags: ['feedback', 'hover'], defaultProps: { content: 'Tooltip text' } },
      { name: 'Popover', description: 'Popover panel', tags: ['feedback', 'popup'], defaultProps: { content: 'Popover content' } },
      { name: 'Tour', description: 'Guided tour', tags: ['feedback', 'onboarding'], defaultProps: { steps: [] } },
      { name: 'Spotlight', description: 'Spotlight highlight', tags: ['feedback', 'focus'], defaultProps: {} },
      { name: 'Banner', description: 'Banner notification', tags: ['feedback', 'banner'], defaultProps: { message: 'Banner message' } },
      { name: 'EmptyState', description: 'Empty state display', tags: ['feedback', 'empty'], defaultProps: { title: 'No data', description: 'Nothing to show' } },
      { name: 'ErrorBoundary', description: 'Error boundary wrapper', tags: ['feedback', 'error'], defaultProps: {} },
      { name: 'AutoSaveIndicator', description: 'Auto-save status', tags: ['feedback', 'status'], defaultProps: { status: 'saved' } },
    ]
  },
  {
    name: 'Navigation',
    icon: '🧭',
    description: 'Navigation and menu components',
    components: [
      { name: 'Tabs', description: 'Tab navigation', tags: ['nav', 'tabs'], defaultProps: { tabs: [{ label: 'Tab 1', value: 'tab1' }, { label: 'Tab 2', value: 'tab2' }], modelValue: 'tab1' } },
      { name: 'Breadcrumb', description: 'Breadcrumb navigation', tags: ['nav', 'path'], defaultProps: { items: [{ label: 'Home', href: '/' }, { label: 'Page' }] } },
      { name: 'Breadcrumbs', description: 'Breadcrumbs trail', tags: ['nav', 'path'], defaultProps: { items: [{ label: 'Home' }] } },
      { name: 'Pagination', description: 'Page navigation', tags: ['nav', 'pages'], defaultProps: { currentPage: 1, totalPages: 10 } },
      { name: 'Stepper', description: 'Step indicator', tags: ['nav', 'steps'], defaultProps: { steps: [{ label: 'Step 1' }, { label: 'Step 2' }], currentStep: 1 } },
      { name: 'StepWizard', description: 'Wizard steps', tags: ['nav', 'wizard'], defaultProps: { steps: [{ title: 'Step 1' }], currentStep: 0 } },
      { name: 'FormWizard', description: 'Form wizard', tags: ['nav', 'form'], defaultProps: { steps: [{ title: 'Info' }] } },
      { name: 'Dropdown', description: 'Dropdown menu', tags: ['nav', 'menu'], defaultProps: { label: 'Menu', items: [{ label: 'Item 1' }] } },
      { name: 'SortMenu', description: 'Sort options menu', tags: ['nav', 'sort'], defaultProps: { options: [{ label: 'Name', value: 'name' }] } },
      { name: 'FilterPanel', description: 'Filter panel', tags: ['nav', 'filter'], defaultProps: { filters: [] } },
      { name: 'FilterPills', description: 'Filter pill buttons', tags: ['nav', 'filter'], defaultProps: { filters: [{ label: 'All', value: 'all' }], modelValue: 'all' } },
      { name: 'CommandPalette', description: 'Command palette', tags: ['nav', 'command'], defaultProps: { commands: [{ name: 'Search' }] } },
      { name: 'CommandMenu', description: 'Command menu', tags: ['nav', 'command'], defaultProps: { items: [{ label: 'Command' }] } },
      { name: 'SearchSpotlight', description: 'Search spotlight', tags: ['nav', 'search'], defaultProps: {} },
      { name: 'SearchResults', description: 'Search results display', tags: ['nav', 'search'], defaultProps: { results: [] } },
      { name: 'FloatingDock', description: 'Floating dock menu', tags: ['nav', 'dock'], defaultProps: { items: [{ icon: '🏠', label: 'Home' }] } },
      { name: 'SegmentedControl', description: 'Segmented control', tags: ['nav', 'segment'], defaultProps: { options: [{ value: 'a', label: 'A' }, { value: 'b', label: 'B' }], modelValue: 'a' } },
      { name: 'QuickViewModal', description: 'Quick view modal', tags: ['nav', 'preview'], defaultProps: { show: false } },
    ]
  },
  {
    name: 'Layout',
    icon: '📐',
    description: 'Layout and container components',
    components: [
      { name: 'Accordion', description: 'Accordion panels', tags: ['layout', 'collapse'], defaultProps: { items: [{ title: 'Section 1', content: 'Content here' }] } },
      { name: 'Collapse', description: 'Collapsible panel', tags: ['layout', 'collapse'], defaultProps: { title: 'Click to expand' } },
      { name: 'Collapsible', description: 'Collapsible container', tags: ['layout', 'collapse'], defaultProps: { title: 'Collapsible' } },
      { name: 'Divider', description: 'Content divider', tags: ['layout', 'separator'], defaultProps: {} },
      { name: 'SplitPane', description: 'Split pane layout', tags: ['layout', 'split'], defaultProps: {} },
      { name: 'Masonry', description: 'Masonry grid layout', tags: ['layout', 'grid'], defaultProps: { columns: 3 } },
      { name: 'VirtualList', description: 'Virtualized list', tags: ['layout', 'performance'], defaultProps: { items: [{id:1},{id:2},{id:3}], itemHeight: 40 } },
      { name: 'InfiniteScroll', description: 'Infinite scroll container', tags: ['layout', 'scroll'], defaultProps: {} },
      { name: 'Carousel', description: 'Image carousel', tags: ['layout', 'slider'], defaultProps: { slides: [] } },
      { name: 'ImageGallery', description: 'Image gallery grid', tags: ['layout', 'images'], defaultProps: { images: [] } },
      { name: 'SortableList', description: 'Drag sortable list', tags: ['layout', 'drag'], defaultProps: { items: [{ id: 1, text: 'Item 1' }] } },
      { name: 'Kanban', description: 'Kanban board', tags: ['layout', 'board'], defaultProps: { columns: [{ title: 'Todo', items: [] }] } },
      { name: 'KanbanBoard', description: 'Full kanban board', tags: ['layout', 'board'], defaultProps: { columns: [] } },
      { name: 'Calendar', description: 'Calendar display', tags: ['layout', 'date'], defaultProps: {} },
    ]
  },
  {
    name: 'Visual',
    icon: '✨',
    description: 'Visual enhancement components',
    components: [
      { name: 'ProgressBar', description: 'Progress indicator bar', tags: ['visual', 'progress'], defaultProps: { value: 60, max: 100 } },
      { name: 'Spinner', description: 'Loading spinner', tags: ['visual', 'loading'], defaultProps: { size: 'md' } },
      { name: 'Watermark', description: 'Watermark overlay', tags: ['visual', 'overlay'], defaultProps: { text: 'Watermark' } },
      { name: 'Highlight', description: 'Text highlight', tags: ['visual', 'text'], defaultProps: { text: 'Highlighted text', highlight: 'light' } },
      { name: 'GradientText', description: 'Gradient colored text', tags: ['visual', 'text'], defaultProps: { text: 'Gradient' } },
      { name: 'NeonText', description: 'Neon glow text', tags: ['visual', 'text'], defaultProps: { text: 'Neon', color: '#00ff00' } },
      { name: 'GlitchText', description: 'Glitch effect text', tags: ['visual', 'text'], defaultProps: { text: 'Glitch' } },
      { name: 'WaveText', description: 'Wave animated text', tags: ['visual', 'text'], defaultProps: { text: 'Wave' } },
      { name: 'ParticleText', description: 'Particle effect text', tags: ['visual', 'text'], defaultProps: { text: 'Particles' } },
      { name: 'TypingEffect', description: 'Typewriter effect', tags: ['visual', 'animation'], defaultProps: { text: 'Typing...' } },
      { name: 'Typewriter', description: 'Typewriter animation', tags: ['visual', 'animation'], defaultProps: { text: 'Hello World' } },
      { name: 'TextReveal', description: 'Text reveal animation', tags: ['visual', 'animation'], defaultProps: { text: 'Reveal' } },
      { name: 'TextScramble', description: 'Text scramble effect', tags: ['visual', 'animation'], defaultProps: { text: 'Scramble' } },
      { name: 'Marquee', description: 'Scrolling marquee text', tags: ['visual', 'scroll'], defaultProps: { default: 'Scrolling text content' } },
      { name: 'ImageCompare', description: 'Before/after compare', tags: ['visual', 'image'], defaultProps: {} },
      { name: 'ImageCropper', description: 'Image cropper tool', tags: ['visual', 'image'], defaultProps: {} },
      { name: 'ImageEditor', description: 'Image editor', tags: ['visual', 'image'], defaultProps: {} },
      { name: 'ImageAnnotation', description: 'Image annotation tool', tags: ['visual', 'image'], defaultProps: {} },
      { name: 'Confetti', description: 'Confetti celebration', tags: ['visual', 'effect'], defaultProps: { active: false } },
      { name: 'Fireworks', description: 'Fireworks effect', tags: ['visual', 'effect'], defaultProps: { active: false } },
      { name: 'Snow', description: 'Snow effect', tags: ['visual', 'effect'], defaultProps: { active: false } },
      { name: 'MatrixRain', description: 'Matrix rain effect', tags: ['visual', 'effect'], defaultProps: {} },
      { name: 'Noise', description: 'Noise texture', tags: ['visual', 'texture'], defaultProps: {} },
      { name: 'ScratchCard', description: 'Scratch card reveal', tags: ['visual', 'interactive'], defaultProps: {} },
      { name: 'Cursor', description: 'Custom cursor', tags: ['visual', 'cursor'], defaultProps: {} },
      { name: 'ThemeCustomizer', description: 'Theme customizer', tags: ['visual', 'theme'], defaultProps: {} },
    ]
  },
  {
    name: 'Skeletons',
    icon: '💀',
    description: 'Loading skeleton placeholders',
    components: [
      { name: 'Skeleton', description: 'Basic skeleton loader', tags: ['skeleton', 'loading'], defaultProps: { width: '100%', height: '20px' } },
      { name: 'CardSkeleton', description: 'Card loading skeleton', tags: ['skeleton', 'card'], defaultProps: {} },
      { name: 'TableSkeleton', description: 'Table loading skeleton', tags: ['skeleton', 'table'], defaultProps: { rows: 3 } },
      { name: 'FormSkeleton', description: 'Form loading skeleton', tags: ['skeleton', 'form'], defaultProps: {} },
      { name: 'StatsSkeleton', description: 'Stats loading skeleton', tags: ['skeleton', 'stats'], defaultProps: {} },
      { name: 'PageSkeleton', description: 'Full page skeleton', tags: ['skeleton', 'page'], defaultProps: {} },
    ]
  },
  {
    name: 'Animations',
    icon: '🎬',
    description: 'Animation wrapper components',
    components: [
      { name: 'Fade', description: 'Fade in/out animation', tags: ['animation', 'opacity'], defaultProps: { show: true } },
      { name: 'Slide', description: 'Slide animation', tags: ['animation', 'movement'], defaultProps: { show: true, direction: 'left' } },
      { name: 'Scale', description: 'Scale animation', tags: ['animation', 'transform'], defaultProps: { show: true } },
      { name: 'Rotate', description: 'Rotate animation', tags: ['animation', 'transform'], defaultProps: { show: true } },
      { name: 'Bounce', description: 'Bounce animation', tags: ['animation', 'movement'], defaultProps: { show: true } },
      { name: 'Shake', description: 'Shake animation', tags: ['animation', 'movement'], defaultProps: { show: true } },
      { name: 'Pulse', description: 'Pulse animation', tags: ['animation', 'scale'], defaultProps: { show: true } },
      { name: 'Glow', description: 'Glow effect animation', tags: ['animation', 'effect'], defaultProps: {} },
      { name: 'Flip', description: 'Flip animation', tags: ['animation', 'transform'], defaultProps: { show: true } },
      { name: 'Zoom', description: 'Zoom animation', tags: ['animation', 'scale'], defaultProps: { show: true } },
      { name: 'Expand', description: 'Expand animation', tags: ['animation', 'size'], defaultProps: { show: true } },
      { name: 'Reveal', description: 'Reveal animation', tags: ['animation', 'visibility'], defaultProps: { show: true } },
      { name: 'Stagger', description: 'Staggered animation', tags: ['animation', 'sequence'], defaultProps: {} },
      { name: 'Morph', description: 'Morph transition', tags: ['animation', 'transform'], defaultProps: {} },
      { name: 'Morphing', description: 'Morphing shapes', tags: ['animation', 'svg'], defaultProps: {} },
      { name: 'Parallax', description: 'Parallax scroll', tags: ['animation', 'scroll'], defaultProps: {} },
      { name: 'Tilt', description: 'Tilt on hover', tags: ['animation', 'hover'], defaultProps: {} },
      { name: 'Magnetic', description: 'Magnetic cursor effect', tags: ['animation', 'cursor'], defaultProps: {} },
    ]
  },
  {
    name: 'Editors',
    icon: '✏️',
    description: 'Rich text and code editors',
    components: [
      { name: 'RichTextEditor', description: 'WYSIWYG editor', tags: ['editor', 'text'], defaultProps: { modelValue: '<p>Rich text</p>' } },
      { name: 'MarkdownEditor', description: 'Markdown editor', tags: ['editor', 'markdown'], defaultProps: { modelValue: '# Hello' } },
      { name: 'MarkdownPreview', description: 'Markdown preview', tags: ['editor', 'preview'], defaultProps: { content: '**Bold**' } },
      { name: 'CodeEditor', description: 'Code editor', tags: ['editor', 'code'], defaultProps: { modelValue: 'const x = 1;', language: 'javascript' } },
      { name: 'JsonEditor', description: 'JSON editor', tags: ['editor', 'json'], defaultProps: { modelValue: { key: 'value' } } },
      { name: 'SqlQueryBuilder', description: 'SQL query builder', tags: ['editor', 'sql'], defaultProps: {} },
      { name: 'CronBuilder', description: 'Cron expression builder', tags: ['editor', 'cron'], defaultProps: { modelValue: '* * * * *' } },
      { name: 'FormBuilder', description: 'Form builder', tags: ['editor', 'form'], defaultProps: { fields: [] } },
      { name: 'Terminal', description: 'Terminal emulator', tags: ['editor', 'terminal'], defaultProps: {} },
      { name: 'TerminalEmulator', description: 'Full terminal', tags: ['editor', 'terminal'], defaultProps: {} },
      { name: 'Whiteboard', description: 'Drawing whiteboard', tags: ['editor', 'draw'], defaultProps: {} },
      { name: 'SpreadSheet', description: 'Spreadsheet editor', tags: ['editor', 'data'], defaultProps: { data: [[1, 2], [3, 4]] } },
    ]
  },
  {
    name: 'Files',
    icon: '📁',
    description: 'File handling components',
    components: [
      { name: 'FileUpload', description: 'File upload input', tags: ['file', 'upload'], defaultProps: {} },
      { name: 'FileDropzone', description: 'Drag & drop zone', tags: ['file', 'drag'], defaultProps: {} },
      { name: 'FileManager', description: 'File manager', tags: ['file', 'manager'], defaultProps: { files: [] } },
      { name: 'PdfViewer', description: 'PDF viewer', tags: ['file', 'pdf'], defaultProps: {} },
      { name: 'VideoPlayer', description: 'Video player', tags: ['file', 'video'], defaultProps: { src: '' } },
      { name: 'AudioPlayer', description: 'Audio player', tags: ['file', 'audio'], defaultProps: { src: '' } },
    ]
  },
  {
    name: 'Specialty',
    icon: '🎯',
    description: 'Specialized utility components',
    components: [
      { name: 'KeyboardShortcuts', description: 'Keyboard shortcuts handler', tags: ['utility', 'keyboard'], defaultProps: { shortcuts: [] } },
      { name: 'KeyboardShortcutsModal', description: 'Shortcuts help modal', tags: ['utility', 'help'], defaultProps: { show: false } },
      { name: 'SortableHeader', description: 'Sortable table header', tags: ['utility', 'table'], defaultProps: { label: 'Column' } },
      { name: 'PullToRefresh', description: 'Pull to refresh', tags: ['utility', 'mobile'], defaultProps: {} },
      { name: 'SwipeActions', description: 'Swipe action buttons', tags: ['utility', 'mobile'], defaultProps: {} },
    ]
  }
])

const currentCategory = computed(() => 
  categories.value.find(c => c.name === activeCategory.value)
)

// Search across all categories
const allSearchResults = computed(() => {
  if (!searchQuery.value) return []
  const query = searchQuery.value.toLowerCase()
  const results = []
  categories.value.forEach(cat => {
    cat.components.forEach(c => {
      if (
        c.name.toLowerCase().includes(query) ||
        c.description.toLowerCase().includes(query) ||
        c.tags.some(t => t.toLowerCase().includes(query))
      ) {
        results.push({ ...c, category: cat.name })
      }
    })
  })
  return results
})

// Get result count for a category when searching
const getCategoryResultCount = (category) => {
  if (!searchQuery.value) return category.components.length
  const query = searchQuery.value.toLowerCase()
  return category.components.filter(c => 
    c.name.toLowerCase().includes(query) ||
    c.description.toLowerCase().includes(query) ||
    c.tags.some(t => t.toLowerCase().includes(query))
  ).length
}

const filteredComponents = computed(() => {
  // If searching and "All" is selected, return all search results
  if (searchQuery.value && activeCategory.value === 'All') {
    return allSearchResults.value
  }
  
  const current = currentCategory.value
  if (!current) return allSearchResults.value
  
  if (!searchQuery.value) return current.components
  
  const query = searchQuery.value.toLowerCase()
  return current.components.filter(c => 
    c.name.toLowerCase().includes(query) ||
    c.description.toLowerCase().includes(query) ||
    c.tags.some(t => t.toLowerCase().includes(query))
  )
})

const totalComponents = computed(() => 
  categories.value.reduce((acc, cat) => acc + cat.components.length, 0)
)
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(1rem);
}
</style>
