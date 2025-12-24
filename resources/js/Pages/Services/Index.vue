<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  ArrowRightIcon,
  CheckCircleIcon,
  SparklesIcon,
  StarIcon,
  ClockIcon,
  UserGroupIcon,
  ChevronRightIcon,
  DocumentCheckIcon,
  AcademicCapIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  BanknotesIcon,
  GlobeAltIcon,
  PaperAirplaneIcon,
  BuildingOfficeIcon,
  LanguageIcon,
  HeartIcon,
  ShieldCheckIcon,
  ClipboardDocumentListIcon,
  TruckIcon,
  HomeModernIcon,
  MapPinIcon,
  CurrencyDollarIcon,
  ScaleIcon,
  FireIcon,
  RocketLaunchIcon,
  Squares2X2Icon,
} from '@heroicons/vue/24/outline';
import {
  CheckBadgeIcon,
  StarIcon as StarSolidIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  services: Array,
  categories: Array,
  featured: Array,
});

// Category configuration with icons, colors and gradients
const categoryConfig = {
  'Visa Services': { 
    icon: DocumentCheckIcon, 
    color: 'emerald',
    gradient: 'from-emerald-500 to-teal-600',
    lightBg: 'bg-emerald-50',
    lightText: 'text-emerald-600',
  },
  'Travel Services': { 
    icon: PaperAirplaneIcon, 
    color: 'blue',
    gradient: 'from-blue-500 to-cyan-600',
    lightBg: 'bg-blue-50',
    lightText: 'text-blue-600',
  },
  'Education Services': { 
    icon: AcademicCapIcon, 
    color: 'purple',
    gradient: 'from-purple-500 to-pink-600',
    lightBg: 'bg-purple-50',
    lightText: 'text-purple-600',
  },
  'Employment Services': { 
    icon: BriefcaseIcon, 
    color: 'orange',
    gradient: 'from-orange-500 to-red-600',
    lightBg: 'bg-orange-50',
    lightText: 'text-orange-600',
  },
  'Document Services': { 
    icon: DocumentTextIcon, 
    color: 'green',
    gradient: 'from-green-500 to-emerald-600',
    lightBg: 'bg-green-50',
    lightText: 'text-green-600',
  },
  'Financial Services': { 
    icon: BanknotesIcon, 
    color: 'yellow',
    gradient: 'from-yellow-500 to-orange-600',
    lightBg: 'bg-yellow-50',
    lightText: 'text-yellow-600',
  },
};

// Service type badges
const serviceTypeBadges = {
  query_based: { label: 'Consultation', icon: '💬', bg: 'bg-blue-100', text: 'text-blue-700' },
  api_based: { label: 'Instant', icon: '⚡', bg: 'bg-green-100', text: 'text-green-700' },
  premade: { label: 'Self-Service', icon: '🎯', bg: 'bg-purple-100', text: 'text-purple-700' },
  marketplace: { label: 'Marketplace', icon: '🛍️', bg: 'bg-orange-100', text: 'text-orange-700' },
};

const search = ref('');
const selectedCategory = ref('all');
const viewMode = ref('grid'); // grid or list

const getCategoryConfig = (categoryName) => {
  const name = typeof categoryName === 'object' ? categoryName?.name : categoryName;
  return categoryConfig[name] || { 
    icon: Squares2X2Icon, 
    color: 'gray',
    gradient: 'from-gray-500 to-gray-600',
    lightBg: 'bg-gray-50',
    lightText: 'text-gray-600',
  };
};

const filteredServices = computed(() => {
  let services = props.services || [];
  
  if (selectedCategory.value !== 'all') {
    services = services.filter(s => {
      const categoryName = typeof s.category === 'object' ? s.category?.name : s.category;
      return categoryName === selectedCategory.value;
    });
  }
  
  if (search.value) {
    const searchLower = (search.value || '').toLowerCase();
    services = services.filter(s => 
      (s.name || '').toLowerCase().includes(searchLower) ||
      (s.description || '').toLowerCase().includes(searchLower)
    );
  }
  
  return services;
});

const categoryList = computed(() => {
  const cats = props.categories || [];
  return cats.map(cat => {
    const categoryName = typeof cat === 'object' ? cat.name : cat;
    const config = getCategoryConfig(categoryName);
    return {
      name: categoryName,
      ...config,
      count: (props.services || []).filter(s => {
        const sCategory = typeof s.category === 'object' ? s.category?.name : s.category;
        return sCategory === categoryName;
      }).length,
    };
  });
});

const featuredServices = computed(() => props.featured || []);

// Stats for hero
const stats = computed(() => [
  { label: 'Total Services', value: (props.services || []).length },
  { label: 'Categories', value: (props.categories || []).length },
  { label: 'Featured', value: (props.featured || []).length },
]);
</script>

<template>
  <Head title="Services - Explore All Services" />

  <AuthenticatedLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
      
      <!-- Compact Hero with Search -->
      <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
        <div class="max-w-7xl mx-auto">
          <h1 class="text-xl md:text-2xl font-bold text-white">Services</h1>
          <p class="text-sm text-white/80 mt-0.5">Visa applications, career services & everything for your journey abroad</p>
          
          <!-- Search in Hero -->
          <div class="flex flex-wrap items-center gap-3 mt-4">
            <input
              v-model="search"
              type="text"
              placeholder="Search services..."
              class="w-48 md:w-64 px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50 placeholder-gray-500"
            />
            <button
              v-if="search || selectedCategory !== 'all'"
              @click="search = ''; selectedCategory = 'all'"
              class="flex items-center gap-1.5 px-3 py-2 text-sm text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors"
            >
              <XMarkIcon class="h-4 w-4" />
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Category Pills Row -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 bg-gray-50 dark:bg-neutral-900">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
          <!-- All Categories -->
          <button
            @click="selectedCategory = 'all'"
            :class="[
              'flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap',
              selectedCategory === 'all'
                ? 'bg-growth-600 text-white'
                : 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-neutral-700 hover:bg-gray-100'
            ]"
          >
            All ({{ services.length }})
          </button>

          <!-- Category Buttons -->
          <button
            v-for="cat in categoryList"
            :key="cat.name"
            @click="selectedCategory = cat.name"
            :class="[
              'flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap',
              selectedCategory === cat.name
                ? 'bg-growth-600 text-white'
                : 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-neutral-700 hover:bg-gray-100'
            ]"
          >
            {{ cat.name }} ({{ cat.count }})
          </button>
        </div>
      </div>

      <!-- Results Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
          {{ filteredServices.length }} services found
          <span v-if="selectedCategory !== 'all'" class="ml-1">in "{{ selectedCategory }}"</span>
          <span v-if="search" class="ml-1">matching "{{ search }}"</span>
        </p>

        <!-- Featured Services -->
        <div v-if="featuredServices.length > 0 && selectedCategory === 'all' && !search" class="mb-8">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Featured Services</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Link
              v-for="service in featuredServices.slice(0, 3)"
              :key="service.id"
              :href="`/services/${service.slug}`"
              class="group relative bg-gradient-to-br from-white to-gray-50 rounded-2xl border-2 border-orange-200 hover:border-orange-400 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden"
            >
              <!-- Featured Badge -->
              <div class="absolute top-4 right-4 z-10">
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold rounded-full shadow-lg">
                  <StarSolidIcon class="w-3 h-3" />
                  Featured
                </span>
              </div>

              <div class="p-6">
                <!-- Category Icon -->
                <div :class="[
                  'w-14 h-14 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110',
                  getCategoryConfig(service.category).lightBg
                ]">
                  <component 
                    :is="getCategoryConfig(service.category).icon" 
                    :class="['w-7 h-7', getCategoryConfig(service.category).lightText]"
                  />
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-growth-600 transition-colors">
                  {{ service.name }}
                </h3>
                <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                  {{ service.short_description || service.description }}
                </p>

                <div class="flex items-center justify-between">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    {{ service.category?.name || service.category }}
                  </span>
                  <span class="inline-flex items-center gap-1 text-growth-600 font-medium text-sm group-hover:translate-x-1 transition-transform">
                    Learn More
                    <ArrowRightIcon class="w-4 h-4" />
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- Results Header -->
        <div class="flex items-center justify-between mb-6">
          <p class="text-gray-600">
            Showing <span class="font-bold text-gray-900">{{ filteredServices.length }}</span> 
            service{{ filteredServices.length !== 1 ? 's' : '' }}
            <span v-if="selectedCategory !== 'all'" class="text-growth-600"> in {{ selectedCategory }}</span>
            <span v-if="search" class="text-growth-600"> matching "{{ search }}"</span>
          </p>

          <!-- Clear Filters -->
          <button
            v-if="search || selectedCategory !== 'all'"
            @click="search = ''; selectedCategory = 'all'"
            class="text-sm font-medium text-gray-600 hover:text-growth-600 transition-colors flex items-center gap-1"
          >
            <XMarkIcon class="w-4 h-4" />
            Clear filters
          </button>
        </div>

        <!-- Services Grid -->
        <div :class="[
          'grid gap-6',
          viewMode === 'grid' ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' : 'grid-cols-1'
        ]">
          <Link
            v-for="service in filteredServices"
            :key="service.id"
            :href="`/services/${service.slug}`"
            :class="[
              'group bg-white rounded-2xl border-2 border-gray-100 hover:border-growth-300 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden',
              viewMode === 'list' ? 'flex items-center' : ''
            ]"
          >
            <div :class="[
              'p-6',
              viewMode === 'list' ? 'flex items-center gap-6 flex-1' : ''
            ]">
              <!-- Icon -->
              <div :class="[
                'flex items-center justify-center rounded-2xl transition-all duration-300 group-hover:scale-110',
                getCategoryConfig(service.category).lightBg,
                viewMode === 'list' ? 'w-16 h-16 flex-shrink-0' : 'w-14 h-14 mb-4'
              ]">
                <component 
                  :is="getCategoryConfig(service.category).icon" 
                  :class="[
                    getCategoryConfig(service.category).lightText,
                    viewMode === 'list' ? 'w-8 h-8' : 'w-7 h-7'
                  ]"
                />
              </div>

              <div :class="viewMode === 'list' ? 'flex-1' : ''">
                <!-- Status Badge -->
                <div class="flex items-center gap-2 mb-2">
                  <span 
                    v-if="service.is_featured"
                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full"
                  >
                    <FireIcon class="w-3 h-3" />
                    Popular
                  </span>
                  <span 
                    v-if="service.coming_soon"
                    class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full"
                  >
                    Coming Soon
                  </span>
                  <span 
                    v-else-if="!service.coming_soon"
                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full"
                  >
                    <CheckCircleIcon class="w-3 h-3" />
                    Available
                  </span>
                </div>

                <!-- Title -->
                <h3 :class="[
                  'font-bold text-gray-900 group-hover:text-growth-600 transition-colors',
                  viewMode === 'list' ? 'text-lg' : 'text-base mb-2'
                ]">
                  {{ service.name }}
                </h3>

                <!-- Description -->
                <p :class="[
                  'text-gray-600 line-clamp-2',
                  viewMode === 'list' ? 'text-sm mt-1' : 'text-sm mb-4'
                ]">
                  {{ service.short_description || service.description }}
                </p>

                <!-- Footer -->
                <div :class="[
                  'flex items-center justify-between',
                  viewMode === 'list' ? 'mt-2' : 'pt-4 border-t border-gray-100'
                ]">
                  <span 
                    v-if="service.service_type && serviceTypeBadges[service.service_type]"
                    :class="[
                      'inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium',
                      serviceTypeBadges[service.service_type].bg,
                      serviceTypeBadges[service.service_type].text
                    ]"
                  >
                    <span>{{ serviceTypeBadges[service.service_type].icon }}</span>
                    {{ serviceTypeBadges[service.service_type].label }}
                  </span>
                  <span v-else class="text-xs text-gray-500">
                    {{ service.category?.name || service.category }}
                  </span>
                  
                  <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-growth-600 group-hover:translate-x-1 transition-all" />
                </div>
              </div>
            </div>
          </Link>
        </div>

        <!-- Empty State -->
        <div v-if="filteredServices.length === 0" class="text-center py-20">
          <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <MagnifyingGlassIcon class="w-12 h-12 text-gray-400" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">No services found</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            We couldn't find any services matching your criteria. Try adjusting your search or filters.
          </p>
          <button
            @click="search = ''; selectedCategory = 'all'"
            class="inline-flex items-center gap-2 px-6 py-3 bg-growth-600 text-white font-semibold rounded-xl hover:bg-growth-700 transition-colors shadow-lg shadow-growth-500/25"
          >
            <XMarkIcon class="w-5 h-5" />
            Clear All Filters
          </button>
        </div>

        <!-- CTA Section -->
        <div class="mt-12 bg-white dark:bg-neutral-800 rounded-xl p-6 border border-gray-200 dark:border-neutral-700 text-center">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can't find what you're looking for?</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Request a custom service or get in touch with our support team.
          </p>
          <div class="flex flex-wrap gap-3 justify-center">
            <Link
              href="/support/create"
              class="px-4 py-2 text-sm font-medium bg-growth-600 text-white rounded-lg hover:bg-growth-700 transition-colors"
            >
              Request Custom Service
            </Link>
            <Link
              href="/support/create"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors"
            >
              Talk to an Expert
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
