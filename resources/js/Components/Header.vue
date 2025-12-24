<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { 
  Bars3Icon, 
  XMarkIcon, 
  MagnifyingGlassIcon,
  ChevronDownIcon 
} from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'

const props = defineProps({
  canLogin: {
    type: Boolean,
    default: false
  },
  canRegister: {
    type: Boolean,
    default: false
  }
})

const page = usePage()
const mobileMenuOpen = ref(false)
const searchOpen = ref(false)
const searchQuery = ref('')

// Check if user is authenticated
const isAuthenticated = computed(() => !!page.props.auth?.user)

// Navigation items - Upwork style categories
const navItems = [
  { label: 'Find Work', href: '/jobs', children: [
    { label: 'Browse Jobs', href: '/jobs' },
    { label: 'Saved Jobs', href: '/jobs/saved' },
    { label: 'My Applications', href: '/user/applications' },
  ]},
  { label: 'Services', href: '/services', children: [
    { label: 'Visa Services', href: '/services?category=visa' },
    { label: 'Job Placement', href: '/services?category=jobs' },
    { label: 'Document Services', href: '/services?category=documents' },
    { label: 'Travel Services', href: '/services?category=travel' },
  ]},
  { label: 'Why Bidesh Gomon', href: '/about' },
]

// Helper function to safely get route URL
const getMenuUrl = (item) => {
  if (item.is_external) return item.url
  if (item.route_name) {
    try {
      return route(item.route_name)
    } catch (error) {
      console.warn(`Route "${item.route_name}" not found for menu item "${item.label}"`)
      return '#'
    }
  }
  return item.url || '#'
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    window.location.href = `/search?q=${encodeURIComponent(searchQuery.value)}`
  }
}
</script>

<template>
  <header class="nav-header">
    <nav class="upwork-container h-full">
      <div class="flex h-full items-center justify-between gap-4">
        <!-- Logo -->
        <div class="flex items-center shrink-0">
          <Link :href="route('welcome')" class="flex items-center hover:opacity-80 transition-opacity">
            <ApplicationLogo class="h-8 w-auto" />
          </Link>
        </div>

        <!-- Search Bar (Desktop) - Upwork Style -->
        <div class="hidden lg:flex flex-1 max-w-xl mx-8">
          <div class="w-full">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search for jobs, services..."
              class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-full bg-gray-50 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all duration-200"
              @keyup.enter="handleSearch"
            />
          </div>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex md:items-center md:gap-1">
          <!-- Dynamic Menu Items -->
          <template v-for="item in $page.props.menus?.header_main || navItems" :key="item.id || item.label">
            <div class="relative group">
              <Link 
                v-if="!item.children"
                :href="item.href || getMenuUrl(item)"
                :target="item.target || '_self'"
                class="nav-link"
              >
                {{ item.label }}
              </Link>
              
              <!-- Dropdown for items with children -->
              <button 
                v-else
                class="nav-link inline-flex items-center"
              >
                {{ item.label }}
                <ChevronDownIcon class="w-4 h-4 ml-1" />
              </button>
              
              <!-- Dropdown Menu -->
              <div 
                v-if="item.children"
                class="absolute top-full left-0 mt-1 w-56 bg-white rounded-2xl shadow-lg border border-[var(--border-light)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
              >
                <div class="py-2">
                  <Link
                    v-for="child in item.children"
                    :key="child.label"
                    :href="child.href"
                    class="block px-4 py-2.5 text-sm text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] transition-colors"
                  >
                    {{ child.label }}
                  </Link>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Auth Buttons - Upwork Style -->
        <div class="flex items-center gap-3">
          <!-- Mobile Search Toggle -->
          <button
            @click="searchOpen = !searchOpen"
            class="lg:hidden p-2 rounded-full hover:bg-[var(--bg-tertiary)] transition-colors"
          >
            <MagnifyingGlassIcon class="w-5 h-5 text-[var(--text-primary)]" />
          </button>

          <template v-if="!isAuthenticated && canLogin">
            <Link
              :href="route('login')"
              class="hidden sm:inline-flex nav-link"
            >
              Log In
            </Link>
            <Link
              v-if="canRegister"
              :href="route('register')"
              class="btn-primary btn-sm"
            >
              Sign Up
            </Link>
          </template>
          
          <template v-else-if="isAuthenticated">
            <Link
              :href="route('dashboard')"
              class="btn-primary btn-sm"
            >
              Dashboard
            </Link>
          </template>
          
          <!-- Mobile menu button -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden p-2 rounded-full hover:bg-[var(--bg-tertiary)] transition-colors"
          >
            <Bars3Icon v-if="!mobileMenuOpen" class="w-6 h-6 text-[var(--text-primary)]" />
            <XMarkIcon v-else class="w-6 h-6 text-[var(--text-primary)]" />
          </button>
        </div>
      </div>

      <!-- Mobile Search Bar -->
      <div
        v-show="searchOpen"
        class="lg:hidden py-3 border-t border-[var(--border-light)]"
      >
        <div class="search-input-wrapper">
          <MagnifyingGlassIcon class="search-icon" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search for jobs, services..."
            class="search-input"
            @keyup.enter="handleSearch"
          />
        </div>
      </div>

      <!-- Mobile Menu - Upwork Style -->
      <div
        v-show="mobileMenuOpen"
        class="md:hidden py-4 border-t border-[var(--border-light)] animate-slide-down"
      >
        <div class="space-y-1">
          <template v-for="item in $page.props.menus?.header_main || navItems" :key="item.id || item.label">
            <Link
              :href="item.href || getMenuUrl(item)"
              :target="item.target || '_self'"
              class="block px-4 py-3 text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] rounded-xl font-medium transition-colors"
              @click="mobileMenuOpen = false"
            >
              {{ item.label }}
            </Link>
            
            <!-- Children items for mobile -->
            <template v-if="item.children">
              <Link
                v-for="child in item.children"
                :key="child.label"
                :href="child.href"
                class="block px-8 py-2.5 text-sm text-[var(--text-secondary)] hover:bg-[var(--bg-tertiary)] rounded-xl transition-colors"
                @click="mobileMenuOpen = false"
              >
                {{ child.label }}
              </Link>
            </template>
          </template>
        </div>
        
        <!-- Mobile Auth -->
        <div v-if="!isAuthenticated && canLogin" class="mt-4 pt-4 border-t border-[var(--border-light)] space-y-2">
          <Link
            :href="route('login')"
            class="block w-full text-center py-3 text-[var(--text-primary)] font-medium hover:bg-[var(--bg-tertiary)] rounded-xl transition-colors"
          >
            Log In
          </Link>
          <Link
            v-if="canRegister"
            :href="route('register')"
            class="block w-full btn-primary text-center"
          >
            Sign Up
          </Link>
        </div>
      </div>
    </nav>
  </header>
</template>
