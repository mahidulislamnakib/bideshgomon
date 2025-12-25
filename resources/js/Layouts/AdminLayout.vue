<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import RealtimeNotifications from '@/Components/RealtimeNotifications.vue'
import GlobalSearch from '@/Components/GlobalSearch.vue'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import ImpersonationBanner from '@/Components/ImpersonationBanner.vue'
import PWAInstallPrompt from '@/Components/PWAInstallPrompt.vue'
import NetworkStatus from '@/Components/NetworkStatus.vue'
import SlowConnectionWarning from '@/Components/SlowConnectionWarning.vue'
import ErrorBoundary from '@/Components/ErrorBoundary.vue'
import KeyboardShortcutsModal from '@/Components/ui/KeyboardShortcutsModal.vue'
import ConfirmModalProvider from '@/Components/ui/ConfirmModalProvider.vue'
import Breadcrumbs from '@/Components/ui/Breadcrumbs.vue'
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts'
import { useFlashMessages } from '@/Composables/useFlashMessages'
import {
  HomeIcon,
  Bars3Icon,
  XMarkIcon,
  AcademicCapIcon,
  GlobeAltIcon,
  LanguageIcon,
  MapIcon,
  MapPinIcon,
  PaperAirplaneIcon,
  UsersIcon,
  BriefcaseIcon,
  ClipboardDocumentListIcon,
  DocumentTextIcon,
  NewspaperIcon,
  ChartBarIcon,
  MegaphoneIcon,
  Cog6ToothIcon,
  BuildingLibraryIcon,
  UserGroupIcon,
  FolderIcon,
  TruckIcon,
  ShieldCheckIcon,
  GiftIcon,
  BanknotesIcon,
  RectangleStackIcon,
  CircleStackIcon,
  TableCellsIcon,
  TagIcon,
  EnvelopeIcon,
  ClipboardDocumentIcon,
  MagnifyingGlassCircleIcon,
  MagnifyingGlassIcon,
  ClockIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  ChatBubbleLeftRightIcon,
  CurrencyDollarIcon,
  SparklesIcon,
  ChatBubbleBottomCenterTextIcon,
  QuestionMarkCircleIcon,
  BellIcon,
  MoonIcon,
  SunIcon,
  CommandLineIcon,
  ArrowRightOnRectangleIcon,
  BookOpenIcon,
  BuildingOffice2Icon,
  LightBulbIcon,
  EllipsisHorizontalIcon,
  PlusCircleIcon,
  CubeIcon,
} from '@heroicons/vue/24/outline'

const showingNavigationDropdown = ref(false)
const showMobileSearch = ref(false)
const expandedGroups = ref({})
const sidebarCollapsed = ref(false)
const darkMode = ref(false)
const showCommandPalette = ref(false)
const menuSearch = ref('')
const page = usePage()
const user = computed(() => page.props.auth.user)

// Keyboard shortcuts
const { showHelp: showKeyboardShortcuts, globalShortcuts } = useKeyboardShortcuts()

// Flash messages to toast notifications
useFlashMessages()


// Toggle dark mode
const toggleDarkMode = () => {
  darkMode.value = !darkMode.value
  localStorage.setItem('darkMode', darkMode.value.toString())
  if (darkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

// Toggle menu group
const toggleGroup = (groupKey) => {
  expandedGroups.value[groupKey] = !expandedGroups.value[groupKey]
  localStorage.setItem('expandedGroups', JSON.stringify(expandedGroups.value))
}

// Load preferences and setup event listeners
onMounted(() => {
  // Load dark mode preference
  const savedDarkMode = localStorage.getItem('darkMode')
  if (savedDarkMode !== null) {
    darkMode.value = savedDarkMode === 'true'
    if (darkMode.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  // Load sidebar collapsed state
  const savedSidebarState = localStorage.getItem('sidebarCollapsed')
  if (savedSidebarState !== null) {
    sidebarCollapsed.value = savedSidebarState === 'true'
  }

  // Load expanded groups
  const savedGroups = localStorage.getItem('expandedGroups')
  if (savedGroups) {
    try {
      expandedGroups.value = JSON.parse(savedGroups)
    } catch (e) {
      // Default: expand primary groups
      expandedGroups.value = { services: true, jobs: true, travel: true }
    }
  } else {
    // Default: expand primary groups
    expandedGroups.value = { services: true, jobs: true, travel: true }
  }

  // Command Palette keyboard shortcut (Cmd+K / Ctrl+K)
  const handleKeyPress = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault()
      showCommandPalette.value = !showCommandPalette.value
    }
    // ESC to close command palette
    if (e.key === 'Escape' && showCommandPalette.value) {
      showCommandPalette.value = false
    }
  }
  window.addEventListener('keydown', handleKeyPress)

  // Cleanup
  return () => {
    window.removeEventListener('keydown', handleKeyPress)
  }
})

// Auto-collapse other groups when one is expanded (accordion behavior)
watch(expandedGroups, (newVal) => {
  const expandedCount = Object.values(newVal).filter(Boolean).length
  // Keep only services, jobs, travel expanded by default, collapse others on mobile
  if (window.innerWidth < 768 && expandedCount > 2) {
    // Auto-collapse behavior on mobile
  }
}, { deep: true })

// Dynamic profile route based on user role
const profileRoute = computed(() => {
  const roleSlug = user.value?.role?.slug
  if (roleSlug === 'agency') {
    return 'agency.profile.show'
  }
  if (roleSlug === 'consultant') {
    return 'consultant.profile.show'
  }
  return 'profile.edit'
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString())
}

// ========================================
// 3-LAYER ADMIN NAVIGATION STRUCTURE
// Layer 1: Primary Actions (Top)
// Layer 2: Collapsible Module Groups (Middle)
// Layer 3: More Section (Bottom)
// ========================================

// Layer 1: Primary Actions - Always visible
const primaryActions = [
  {
    name: 'Dashboard',
    href: route('admin.dashboard'),
    icon: HomeIcon,
    current: route().current('admin.dashboard'),
    badge: null,
  },
  {
    name: 'Analytics',
    href: route('admin.analytics.index'),
    icon: ChartBarIcon,
    current: route().current('admin.analytics.*'),
  },
  {
    name: 'Users & Roles',
    href: route('admin.users.index'),
    icon: UsersIcon,
    current: route().current('admin.users.*'),
  },
  {
    name: 'Settings',
    href: route('admin.settings.index'),
    icon: Cog6ToothIcon,
    current: route().current('admin.settings.*') || route().current('menus.*') || route().current('seo-settings.*'),
  },
]

// Layer 2: Collapsible Module Groups
const moduleGroups = [
  {
    key: 'services',
    name: 'Services',
    icon: RectangleStackIcon,
    badge: '38',
    items: [
      {
        name: 'Applications',
        href: route('service-applications.index'),
        icon: ClipboardDocumentListIcon,
        current: route().current('service-applications.*'),
        badge: '38',
      },
      {
        name: 'Quotes',
        href: route('admin.service-quotes.index'),
        icon: CurrencyDollarIcon,
        current: route().current('admin.service-quotes.*'),
      },
      {
        name: 'Modules',
        href: route('admin.service-modules.index'),
        icon: CubeIcon,
        current: route().current('admin.service-modules.*'),
      },
    ],
  },
  {
    key: 'jobs',
    name: 'Jobs',
    icon: BriefcaseIcon,
    items: [
      {
        name: 'Postings',
        href: route('admin.jobs.index'),
        icon: BriefcaseIcon,
        current: route().current('admin.jobs.*'),
      },
      {
        name: 'Applications',
        href: route('admin.job-applications.index'),
        icon: ClipboardDocumentListIcon,
        current: route().current('admin.job-applications.*') || route().current('admin.applications.*'),
      },
    ],
  },
  {
    key: 'travel',
    name: 'Travel',
    icon: GlobeAltIcon,
    items: [
      {
        name: 'Visa Apps',
        href: route('admin.visa-applications.index'),
        icon: DocumentTextIcon,
        current: route().current('admin.visa-applications.*'),
      },
      {
        name: 'Requirements',
        href: route('admin.visa-requirements.index'),
        icon: ClipboardDocumentListIcon,
        current: route().current('admin.visa-requirements.*'),
      },
      {
        name: 'Hotels',
        href: route('admin.hotels.index'),
        icon: BuildingLibraryIcon,
        current: route().current('admin.hotels.*'),
      },
      {
        name: 'Bookings',
        href: route('admin.hotel-bookings.index'),
        icon: ClockIcon,
        current: route().current('admin.hotel-bookings.*'),
      },
      {
        name: 'Flights',
        href: route('admin.flight-requests.index'),
        icon: TruckIcon,
        current: route().current('admin.flight-requests.*'),
      },
    ],
  },
  {
    key: 'agencies',
    name: 'Agencies',
    icon: UserGroupIcon,
    items: [
      {
        name: 'Assignments',
        href: route('admin.agency-assignments.index'),
        icon: UserGroupIcon,
        current: route().current('admin.agency-assignments.*'),
      },
      {
        name: 'Resources',
        href: route('admin.agency-resources.index'),
        icon: FolderIcon,
        current: route().current('admin.agency-resources.*'),
      },
    ],
  },
  {
    key: 'financial',
    name: 'Financial',
    icon: BanknotesIcon,
    items: [
      {
        name: 'Accounting',
        href: route('admin.accounting.dashboard'),
        icon: CurrencyDollarIcon,
        current: route().current('admin.accounting.*'),
      },
      {
        name: 'Wallets',
        href: route('admin.wallets.index'),
        icon: BanknotesIcon,
        current: route().current('admin.wallets.*'),
      },
      {
        name: 'Rewards',
        href: route('admin.rewards.index'),
        icon: GiftIcon,
        current: route().current('admin.rewards.*'),
      },
    ],
  },
  {
    key: 'content',
    name: 'Content',
    icon: NewspaperIcon,
    items: [
      {
        name: 'Blog',
        href: route('admin.blog.posts.index'),
        icon: NewspaperIcon,
        current: route().current('admin.blog.posts.*'),
      },
      {
        name: 'Pages',
        href: route('admin.pages.index'),
        icon: DocumentTextIcon,
        current: route().current('admin.pages.*'),
      },
      {
        name: 'Events',
        href: route('admin.events.index'),
        icon: ClockIcon,
        current: route().current('admin.events.*'),
      },
      {
        name: 'FAQs',
        href: route('admin.faqs.index'),
        icon: QuestionMarkCircleIcon,
        current: route().current('admin.faqs.*'),
      },
      {
        name: 'Testimonials',
        href: route('admin.testimonials.index'),
        icon: ChatBubbleBottomCenterTextIcon,
        current: route().current('admin.testimonials.*'),
      },
    ],
  },
  {
    key: 'support',
    name: 'Support',
    icon: ChatBubbleLeftRightIcon,
    items: [
      {
        name: 'Tickets',
        href: route('admin.support-tickets.index'),
        icon: ChatBubbleLeftRightIcon,
        current: route().current('admin.support-tickets.*'),
      },
      {
        name: 'Appointments',
        href: route('admin.appointments.index'),
        icon: ClockIcon,
        current: route().current('admin.appointments.*'),
      },
    ],
  },
]

// Layer 3: More Section - Rarely used pages
const moreItems = [
  {
    name: 'Partners',
    href: route('admin.partners.index'),
    icon: UserGroupIcon,
    current: route().current('admin.partners.*'),
  },
  {
    name: 'Directories',
    href: route('admin.directories.index'),
    icon: BookOpenIcon,
    current: route().current('admin.directories.*'),
  },
  {
    name: 'Marketing',
    href: route('admin.marketing-campaigns.index'),
    icon: MegaphoneIcon,
    current: route().current('admin.marketing-campaigns.*'),
  },
  {
    name: 'Ads',
    href: route('admin.ads.index'),
    icon: RectangleStackIcon,
    current: route().current('admin.ads.*'),
  },
  {
    name: 'Documents',
    href: route('admin.documents.verify.index'),
    icon: ShieldCheckIcon,
    current: route().current('admin.documents.verify.*'),
  },
  {
    name: 'Data Hub',
    href: route('admin.data.index'),
    icon: CircleStackIcon,
    current: route().current('admin.data.*'),
  },
  {
    name: 'Notifications',
    href: route('admin.notifications.index'),
    icon: BellIcon,
    current: route().current('admin.notifications.*'),
  },
  {
    name: 'Impersonations',
    href: route('admin.impersonations.index'),
    icon: ClockIcon,
    current: route().current('admin.impersonations.*'),
  },
  {
    name: 'Sitemap',
    href: route('admin.sitemap'),
    icon: MapIcon,
    current: route().current('admin.sitemap'),
  },
  {
    name: 'UI Components',
    href: route('component.gallery'),
    icon: SparklesIcon,
    current: route().current('component.gallery'),
  },
]

// Smart search filter
const filteredNavigation = computed(() => {
  if (!menuSearch.value) return null
  
  const searchTerm = menuSearch.value.toLowerCase()
  const results = []
  
  // Search primary actions
  primaryActions.forEach(item => {
    if (item.name.toLowerCase().includes(searchTerm)) {
      results.push({ ...item, section: 'Primary' })
    }
  })
  
  // Search module groups
  moduleGroups.forEach(group => {
    group.items.forEach(item => {
      if (item.name.toLowerCase().includes(searchTerm) || group.name.toLowerCase().includes(searchTerm)) {
        results.push({ ...item, section: group.name })
      }
    })
  })
  
  // Search more items
  moreItems.forEach(item => {
    if (item.name.toLowerCase().includes(searchTerm)) {
      results.push({ ...item, section: 'More' })
    }
  })
  
  return results
})

// Check if user has permission (role-based visibility)
const hasPermission = (item) => {
  // Add role-based logic here
  // For now, all items are visible to admin
  return true
}
</script>

<template>
  <div class="dark:bg-gray-900 min-h-screen">
    <!-- Impersonation Banner -->
    <ImpersonationBanner />

    <!-- Command Palette (Cmd+K) -->
    <Teleport to="body">
      <div
        v-if="showCommandPalette"
        class="fixed inset-0 z-[100] overflow-y-auto"
        @click="showCommandPalette = false"
      >
        <div class="flex min-h-screen items-start justify-center px-4 pt-20">
          <div
            class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"
            aria-hidden="true"
          ></div>

          <div
            class="relative w-full max-w-2xl transform rounded-2xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/5 transition-all"
            @click.stop
          >
            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
              <div class="flex items-center gap-3">
                <CommandLineIcon class="w-6 h-6 text-growth-600 dark:text-indigo-400" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Quick Navigation
                </h3>
                <kbd
                  class="ml-auto px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded"
                >
                  ESC
                </kbd>
              </div>
            </div>

            <!-- Search -->
            <div class="p-4">
              <GlobalSearch />
            </div>

            <!-- Quick Links -->
            <div class="px-4 pb-4">
              <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Quick Access
              </h4>
              <div class="grid grid-cols-2 gap-2">
                <Link
                  v-for="item in primaryActions"
                  :key="item.name"
                  :href="item.href"
                  class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group"
                  @click="showCommandPalette = false"
                >
                  <component
                    :is="item.icon"
                    class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-growth-600 dark:group-hover:text-indigo-400"
                  />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">
                    {{ item.name }}
                  </span>
                </Link>
              </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
              <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Press <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded">⌘K</kbd> or
                <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded">Ctrl+K</kbd> anytime to open
              </p>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <!-- Sidebar -->
      <nav
        :class="[
          'fixed inset-y-0 left-0 z-50 hidden md:flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out overflow-hidden',
          sidebarCollapsed ? 'w-20' : 'w-64',
        ]"
      >
        <!-- Logo Section - Fixed at Top -->
        <div
          class="flex flex-shrink-0 items-center justify-between px-4 h-16 border-b border-gray-200 bg-white"
        >
          <Link :href="route('admin.dashboard')" class="flex items-center">
            <ApplicationLogo class="h-8 w-auto" v-if="!sidebarCollapsed" />
            <div v-else class="w-8 h-8 rounded-lg bg-growth-600 flex items-center justify-center">
              <SparklesIcon class="w-4 h-4 text-white" />
            </div>
          </Link>
          
          <!-- Collapse Toggle Button -->
          <button
            v-if="!sidebarCollapsed"
            @click="toggleSidebar"
            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors"
            title="Collapse Sidebar"
          >
            <ChevronDownIcon class="w-4 h-4 rotate-90" />
          </button>
        </div>

        <!-- Expand Button (When Collapsed) - Fixed Position -->
        <div v-if="sidebarCollapsed" class="flex-shrink-0 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
          <button
            @click="toggleSidebar"
            class="w-full p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
            title="Expand Sidebar"
          >
            <ChevronRightIcon class="w-5 h-5 mx-auto" />
          </button>
        </div>

        <!-- Navigation Sections - Scrollable Area -->
        <div class="flex-1 overflow-y-auto px-3 py-4">
          <!-- Smart Search (when expanded) -->
          <div v-if="!sidebarCollapsed" class="mb-4">
            <input
              v-model="menuSearch"
              type="text"
              placeholder="Search menu..."
              class="w-full px-3 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-xl text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all"
            />
          </div>

          <!-- Search Results (when searching) -->
          <ul v-if="filteredNavigation && filteredNavigation.length > 0" role="list" class="space-y-1">
            <li v-for="item in filteredNavigation" :key="item.name">
              <Link
                :href="item.href"
                :class="[
                  item.current
                    ? 'bg-growth-600 text-white shadow-sm'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                  sidebarCollapsed ? 'justify-center px-2' : 'px-3',
                  'group flex items-center gap-x-3 rounded-xl py-2.5 text-sm font-medium transition-all',
                ]"
                @click="menuSearch = ''"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'h-5 w-5 shrink-0',
                    item.current ? 'text-white' : 'text-gray-500 group-hover:text-gray-700',
                  ]"
                />
                <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <span class="truncate">{{ item.name }}</span>
                    <span v-if="item.badge" class="px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-full ml-2">
                      {{ item.badge }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-400 truncate">{{ item.section }}</p>
                </div>
              </Link>
            </li>
          </ul>

          <!-- No Results -->
          <div v-else-if="filteredNavigation && filteredNavigation.length === 0" class="text-center py-8">
            <MagnifyingGlassIcon class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">No menu items found</p>
          </div>

          <!-- Normal Navigation (when not searching) -->
          <ul v-else role="list" class="space-y-1">
            <!-- ===== LAYER 1: PRIMARY ACTIONS ===== -->
            <li v-for="item in primaryActions" :key="item.name">
              <Link
                :href="item.href"
                :class="[
                  item.current
                    ? 'bg-growth-600 text-white shadow-sm'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                  sidebarCollapsed ? 'justify-center px-2' : 'px-3',
                  'group flex items-center gap-x-3 rounded-xl py-2.5 text-sm font-semibold transition-all',
                ]"
                :title="sidebarCollapsed ? item.name : ''"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'h-5 w-5 shrink-0',
                    item.current ? 'text-white' : 'text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-400',
                  ]"
                />
                <span v-if="!sidebarCollapsed">{{ item.name }}</span>
              </Link>
            </li>

            <!-- Divider -->
            <li class="my-3">
              <div class="h-px bg-gray-200 dark:bg-gray-700"></div>
            </li>

            <!-- ===== LAYER 2: MODULE GROUPS ===== -->
            <li v-for="group in moduleGroups" :key="group.key" class="mb-2">
              <!-- Group Header -->
              <button
                v-if="!sidebarCollapsed"
                @click="toggleGroup(group.key)"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-all"
              >
                <div class="flex items-center gap-2">
                  <component :is="group.icon" class="w-4 h-4" />
                  <span class="uppercase tracking-wider">{{ group.name }}</span>
                  <span v-if="group.badge" class="px-1.5 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-full">
                    {{ group.badge }}
                  </span>
                </div>
                <ChevronDownIcon
                  :class="[
                    'w-4 h-4 transition-transform duration-200',
                    expandedGroups[group.key] ? 'rotate-0' : '-rotate-90',
                  ]"
                />
              </button>

              <!-- Divider for Collapsed Sidebar -->
              <div v-if="sidebarCollapsed" class="h-px bg-gray-200 dark:bg-gray-700 my-2"></div>

              <!-- Group Items -->
              <ul v-show="expandedGroups[group.key] || sidebarCollapsed" role="list" class="space-y-1 mt-1">
                <li v-for="item in group.items" :key="item.name">
                  <Link
                    :href="item.href"
                    :class="[
                      item.current
                        ? 'bg-growth-600 text-white shadow-sm'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                      sidebarCollapsed ? 'justify-center px-2' : 'px-3 ml-4',
                      'group flex items-center gap-x-3 rounded-xl py-2.5 text-sm font-medium transition-all',
                    ]"
                    :title="sidebarCollapsed ? item.name : ''"
                  >
                    <component
                      :is="item.icon"
                      :class="[
                        'h-5 w-5 shrink-0',
                        item.current ? 'text-white' : 'text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-400',
                      ]"
                    />
                    <span v-if="!sidebarCollapsed" class="flex-1 truncate">{{ item.name }}</span>
                    <span
                      v-if="item.badge && !sidebarCollapsed"
                      class="px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-full"
                    >
                      {{ item.badge }}
                    </span>
                  </Link>
                </li>
              </ul>
            </li>

            <!-- Divider -->
            <li class="my-3">
              <div class="h-px bg-gray-200 dark:bg-gray-700"></div>
            </li>

            <!-- ===== LAYER 3: MORE SECTION ===== -->
            <li>
              <!-- More Header -->
              <button
                v-if="!sidebarCollapsed"
                @click="toggleGroup('more')"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-all"
              >
                <div class="flex items-center gap-2">
                  <EllipsisHorizontalIcon class="w-4 h-4" />
                  <span class="uppercase tracking-wider">More</span>
                </div>
                <ChevronDownIcon
                  :class="[
                    'w-4 h-4 transition-transform duration-200',
                    expandedGroups.more ? 'rotate-0' : '-rotate-90',
                  ]"
                />
              </button>

              <!-- More Items -->
              <ul v-show="expandedGroups.more || sidebarCollapsed" role="list" class="space-y-1 mt-1">
                <li v-for="item in moreItems" :key="item.name">
                  <Link
                    :href="item.href"
                    :class="[
                      item.current
                        ? 'bg-growth-600 text-white shadow-sm'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                      sidebarCollapsed ? 'justify-center px-2' : 'px-3 ml-4',
                      'group flex items-center gap-x-3 rounded-xl py-2.5 text-sm font-medium transition-all',
                    ]"
                    :title="sidebarCollapsed ? item.name : ''"
                  >
                    <component
                      :is="item.icon"
                      :class="[
                        'h-5 w-5 shrink-0',
                        item.current ? 'text-white' : 'text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-400',
                      ]"
                    />
                    <span v-if="!sidebarCollapsed" class="flex-1 truncate">{{ item.name }}</span>
                  </Link>
                </li>
              </ul>
            </li>
          </ul>
        </div>

        <!-- Footer - Fixed at Bottom -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
          <div v-if="!sidebarCollapsed" class="space-y-2">
            <!-- User Info -->
            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</p>
            </div>
            
            <!-- Dark Mode Toggle & Logout -->
            <div class="flex items-center gap-2">
              <button
                @click="toggleDarkMode"
                class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors font-medium"
                :title="darkMode ? 'Light Mode' : 'Dark Mode'"
              >
                <SunIcon v-if="darkMode" class="w-4 h-4" />
                <MoonIcon v-else class="w-4 h-4" />
                <span class="text-xs">{{ darkMode ? 'Light' : 'Dark' }}</span>
              </button>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors font-medium"
                title="Logout"
              >
                <ArrowRightOnRectangleIcon class="w-4 h-4" />
                <span class="text-xs">Logout</span>
              </Link>
            </div>
          </div>
          
          <!-- Collapsed View - Icons Only -->
          <div v-else class="flex flex-col items-center gap-3">
            <button
              @click="toggleDarkMode"
              class="p-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
              :title="darkMode ? 'Light Mode' : 'Dark Mode'"
            >
              <SunIcon v-if="darkMode" class="w-5 h-5" />
              <MoonIcon v-else class="w-5 h-5" />
            </button>
            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="p-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              title="Logout"
            >
              <ArrowRightOnRectangleIcon class="w-5 h-5" />
            </Link>
          </div>
        </div>
      </nav>

      <!-- Main Content Area -->
      <div
        :class="[
          'flex flex-col flex-1 transition-all duration-300',
          sidebarCollapsed ? 'md:pl-20' : 'md:pl-64',
        ]"
      >
        <!-- Top Header Bar - Simplified -->
        <div
          class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 lg:px-8"
        >
          <!-- Mobile Menu Button -->
          <button
            type="button"
            class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors md:hidden"
            @click="showingNavigationDropdown = !showingNavigationDropdown"
          >
            <span class="sr-only">Open sidebar</span>
            <Bars3Icon class="h-6 w-6" aria-hidden="true" />
          </button>

          <!-- Quick Search -->
          <button
            @click="showCommandPalette = true"
            class="hidden md:flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors group w-96"
          >
            <CommandLineIcon class="w-4 h-4 text-gray-400" />
            <span class="text-sm text-gray-500">Quick search...</span>
            <kbd class="ml-auto px-2 py-0.5 text-xs font-semibold text-gray-500 bg-white border border-gray-300 rounded">
              ⌘K
            </kbd>
          </button>

          <!-- Spacer to push right items to the end -->
          <div class="flex-1"></div>

          <!-- Right Actions -->
          <div class="flex items-center gap-2">
            <!-- Mobile Search Icon -->
            <button
              class="lg:hidden p-2.5 text-gray-600 dark:text-gray-400 hover:text-growth-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all transform hover:scale-105"
              title="Search"
              @click="showMobileSearch = !showMobileSearch"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </button>

            <!-- Real-time Notifications -->
            <div class="relative">
              <RealtimeNotifications />
            </div>

            <!-- Keyboard Shortcuts Button -->
            <button
              @click="showKeyboardShortcuts = true"
              class="hidden md:flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 transition-all"
              title="Keyboard shortcuts (?)"
            >
              <CommandLineIcon class="w-5 h-5" />
            </button>

            <!-- Language Switcher -->
            <LanguageSwitcher />

            <!-- User Menu - Premium Design -->
            <Dropdown align="right" width="64">
              <template #trigger>
                <button
                  type="button"
                  class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group"
                >
                  <div class="relative">
                    <div
                      class="w-10 h-10 rounded-xl bg-growth-600 flex items-center justify-center text-white font-bold text-sm shadow-lg transform group-hover:scale-105 transition-transform border-2 border-blue-700"
                    >
                      {{ (user.name || '').charAt(0).toUpperCase() }}
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-gray-900 rounded-full"></div>
                  </div>
                  <div class="hidden lg:flex flex-col items-start min-w-0">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[140px]">
                      {{ user.name }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ user.role?.name || 'Admin' }}</span>
                  </div>
                  <ChevronDownIcon
                    class="hidden lg:block h-4 w-4 text-gray-400 group-hover:text-growth-600 dark:group-hover:text-indigo-400 transition-all"
                  />
                </button>
              </template>

              <template #content>
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
                  <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                    {{ user.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                    {{ user.email }}
                  </p>
                  <div class="mt-2 inline-flex items-center px-2 py-1 text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full">
                    {{ user.role?.name || 'Administrator' }}
                  </div>
                </div>
                <div class="py-1">
                  <DropdownLink
                    :href="route(profileRoute)"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                  >
                    <UsersIcon class="w-5 h-5 text-growth-600 dark:text-indigo-400" />
                    <div class="flex flex-col">
                      <span class="font-medium">My Profile</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">View and edit your profile</span>
                    </div>
                  </DropdownLink>
                  <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                  <DropdownLink
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 w-full transition-colors"
                  >
                    <XMarkIcon class="w-5 h-5" />
                    <div class="flex flex-col items-start">
                      <span class="font-medium">Log Out</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">Sign out of your account</span>
                    </div>
                  </DropdownLink>
                </div>
              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Mobile Search Modal - Enhanced Design -->
        <div
          v-if="showMobileSearch"
          class="fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm lg:hidden"
          @click="showMobileSearch = false"
        >
          <div
            class="fixed inset-x-0 top-0 bg-white p-4 sm:p-6 shadow-2xl border-b-2 border-blue-500"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-lg bg-growth-600 flex items-center justify-center shadow-md border-2 border-blue-700"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Search Admin Panel</h3>
              </div>
              <button
                class="p-2 text-gray-500 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-all"
                title="Close"
                @click="showMobileSearch = false"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <!-- Search Component -->
            <GlobalSearch />
          </div>
        </div>

        <header v-if="$slots.header" class="bg-white shadow">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot name="header"></slot>
          </div>
        </header>

        <!-- Breadcrumbs -->
        <div v-if="$slots.breadcrumbs" class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <slot name="breadcrumbs"></slot>
          </div>
        </div>

        <main class="flex-1">
          <ErrorBoundary>
            <slot></slot>
          </ErrorBoundary>
        </main>
      </div>

      <!-- Mobile Sidebar -->
      <div
        v-if="showingNavigationDropdown"
        class="relative z-50 md:hidden"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity duration-300"
          :class="showingNavigationDropdown ? 'opacity-100' : 'opacity-0'"
          @click="showingNavigationDropdown = false"
        ></div>

        <div class="fixed inset-0 flex">
          <div
            class="relative mr-16 flex w-full max-w-xs flex-1 transform transition ease-in-out duration-300"
            :class="showingNavigationDropdown ? 'translate-x-0' : '-translate-x-full'"
            @click.stop
          >
            <!-- Close Button -->
            <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
              <button
                type="button"
                class="p-3 min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full bg-growth-600 hover:bg-growth-700 transition-all duration-150 active:scale-95 shadow-lg"
                @click="showingNavigationDropdown = false"
              >
                <span class="sr-only">Close sidebar</span>
                <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </button>
            </div>

            <!-- Mobile Menu Content -->
            <div class="flex grow flex-col gap-y-3 overflow-y-auto bg-white pb-4">
              <!-- Mobile Logo -->
              <div
                class="flex h-16 shrink-0 items-center justify-center bg-growth-600 px-4 border-b-2 border-blue-700"
              >
                <Link :href="route('admin.dashboard')">
                  <ApplicationLogo class="block h-10 w-auto brightness-0 invert" />
                </Link>
              </div>

              <!-- Mobile Navigation -->
              <nav class="flex flex-1 flex-col px-2">
                <ul role="list" class="flex flex-1 flex-col gap-y-1">
                  <li v-for="(items, sectionName) in navigationSections" :key="sectionName">
                    <div v-if="items.length > 0" class="mb-2">
                      <!-- Section Header -->
                      <button
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                        @click="toggleSection(sectionName)"
                      >
                        <span class="uppercase tracking-wide">{{ sectionName }}</span>
                        <ChevronDownIcon
                          :class="[
                            'w-4 h-4 transition-transform duration-200',
                            collapsedSections[sectionName] ? '-rotate-90' : '',
                          ]"
                        />
                      </button>

                      <!-- Section Items -->
                      <ul
                        v-show="!collapsedSections[sectionName]"
                        role="list"
                        class="space-y-1 mt-1"
                      >
                        <li v-for="item in items" :key="item.name">
                          <Link
                            :href="item.disabled ? '#' : item.href"
                            :class="[
                              item.current
                                ? 'bg-growth-50 text-growth-700 border-l-4 border-growth-600'
                                : 'text-gray-700 hover:text-growth-600 hover:bg-gray-50 border-l-4 border-transparent',
                              'group flex items-center px-3 py-2.5 text-sm font-medium rounded-r-lg transition-all duration-150',
                              item.disabled ? 'opacity-50 cursor-not-allowed' : '',
                            ]"
                            :aria-disabled="item.disabled"
                            :tabindex="item.disabled ? -1 : undefined"
                            @click="!item.disabled && (showingNavigationDropdown = false)"
                          >
                            <component
                              :is="item.icon"
                              :class="[
                                'h-6 w-6 shrink-0',
                                item.current ? 'text-growth-600' : '',
                              ]"
                              aria-hidden="true"
                            />
                            <span class="flex-1">{{ item.name }}</span>
                            <ChevronRightIcon
                              v-if="item.current && !item.disabled"
                              class="w-4 h-4 text-growth-600"
                            />
                          </Link>
                        </li>
                      </ul>
                    </div>
                  </li>
                </ul>
              </nav>

              <!-- Mobile Footer -->
              <div class="flex-shrink-0 border-t border-gray-200 p-3 bg-gray-50">
                <div class="text-xs text-gray-500 text-center">
                  <p class="font-semibold">BideshGomon Admin</p>
                  <p class="mt-1">v1.0.0</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PWA Components -->
    <NetworkStatus />
    <PWAInstallPrompt />
    <SlowConnectionWarning />
    
    <!-- Keyboard Shortcuts Modal -->
    <KeyboardShortcutsModal
      v-model:show="showKeyboardShortcuts"
      :global-shortcuts="globalShortcuts"
    />
    
    <!-- Toast Notifications -->
    <Toaster position="top-right" :rich-colors="true" />
    
    <!-- Confirmation Modal Provider -->
    <ConfirmModalProvider />
  </div>
</template>
