<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  MagnifyingGlassIcon,
  FunnelIcon,
  ArrowPathIcon,
  EyeIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  DocumentTextIcon,
  UserIcon,
  BuildingOfficeIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  applications: Object,
  stats: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const serviceFilter = ref(props.filters?.service_module_id || 'all');
const showFilters = ref(false);

const statuses = [
  { value: 'all', label: 'All Status', color: 'gray' },
  { value: 'pending', label: 'Pending', color: 'yellow' },
  { value: 'quoted', label: 'Quoted', color: 'blue' },
  { value: 'accepted', label: 'Accepted', color: 'green' },
  { value: 'in_progress', label: 'In Progress', color: 'indigo' },
  { value: 'completed', label: 'Completed', color: 'green' },
  { value: 'cancelled', label: 'Cancelled', color: 'red' },
];

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    quoted: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    accepted: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
    completed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  };
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-gray-300';
};

const filterApplications = () => {
  router.get('/admin/service-applications', {
    search: search.value,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    service_module_id: serviceFilter.value !== 'all' ? serviceFilter.value : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  search.value = '';
  statusFilter.value = 'all';
  serviceFilter.value = 'all';
  router.get('/admin/service-applications', {}, { preserveState: true });
};

const hasActiveFilters = computed(() => {
  return search.value || statusFilter.value !== 'all' || serviceFilter.value !== 'all';
});
</script>

<template>
  <Head title="Service Applications - Plugin System" />

  <AdminLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
      <!-- Hero Header -->
      <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
        <!-- SVG Pattern -->
        <div class="absolute inset-0 opacity-10">
          <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <pattern id="serviceAppGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#serviceAppGrid)" />
          </svg>
        </div>

        <!-- Gradient Orbs -->
        <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-rose-500/20 to-pink-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-fuchsia-500/20 to-purple-500/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <!-- Header -->
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg">
                <DocumentTextIcon class="h-8 w-8 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-white">Service Applications</h1>
                <p class="mt-1 text-gray-300">Universal Plugin System - Manage all 38 service applications</p>
              </div>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
              <button
                @click="showFilters = !showFilters"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
              >
                <FunnelIcon class="h-5 w-5" />
                {{ showFilters ? 'Hide' : 'Show' }} Filters
              </button>
              <button
                @click="filterApplications"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-xl font-semibold hover:from-rose-600 hover:to-pink-700 transition-all shadow-lg"
              >
                <ArrowPathIcon class="h-5 w-5" />
                Refresh
              </button>
            </div>
          </div>

          <!-- Stats Cards in Hero -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-500/20 rounded-2xl">
                  <ClockIcon class="h-5 w-5 text-yellow-300" />
                </div>
                <div>
                  <p class="text-yellow-300 text-xs font-medium">Pending</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.pending || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-500/20 rounded-2xl">
                  <DocumentTextIcon class="h-5 w-5 text-blue-300" />
                </div>
                <div>
                  <p class="text-blue-300 text-xs font-medium">Quoted</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.quoted || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-500/20 rounded-2xl">
                  <ArrowPathIcon class="h-5 w-5 text-indigo-300" />
                </div>
                <div>
                  <p class="text-indigo-300 text-xs font-medium">In Progress</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.in_progress || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-500/20 rounded-2xl">
                  <CheckCircleIcon class="h-5 w-5 text-emerald-300" />
                </div>
                <div>
                  <p class="text-emerald-300 text-xs font-medium">Completed</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.completed || 0 }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
        <!-- Filters Panel -->
        <transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
              <!-- Search -->
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <div class="relative">
                  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    v-model="search"
                    @keyup.enter="filterApplications"
                    type="text"
                    placeholder="Search by application number, user, service..."
                    class="block w-full rounded-xl border-0 py-3 pl-10 pr-3 text-gray-900 dark:text-white dark:bg-neutral-700 ring-1 ring-inset ring-gray-300 dark:ring-neutral-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-rose-500 sm:text-sm"
                  />
                </div>
              </div>

              <!-- Status Filter -->
              <div class="lg:w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select
                  v-model="statusFilter"
                  @change="filterApplications"
                  class="block w-full rounded-xl border-0 py-3 text-gray-900 dark:text-white dark:bg-neutral-700 ring-1 ring-inset ring-gray-300 dark:ring-neutral-600 focus:ring-2 focus:ring-rose-500 sm:text-sm"
                >
                  <option v-for="status in statuses" :key="status.value" :value="status.value">
                    {{ status.label }}
                  </option>
                </select>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-end gap-3">
                <button
                  @click="filterApplications"
                  class="px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-medium transition-colors"
                >
                  Apply
                </button>
                <button
                  v-if="hasActiveFilters"
                  @click="resetFilters"
                  class="px-5 py-3 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors flex items-center gap-2"
                >
                  <XMarkIcon class="h-4 w-4" />
                  Reset
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Applications Table -->
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
              <thead class="bg-gray-50 dark:bg-neutral-900/50">
                <tr>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Application
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Service
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    User
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Quotes
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Created
                  </th>
                  <th scope="col" class="relative px-6 py-4">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-neutral-800 divide-y divide-gray-200 dark:divide-neutral-700">
                <tr
                  v-for="application in applications.data"
                  :key="application.id"
                  class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900/30 dark:to-pink-900/30 rounded-xl flex items-center justify-center">
                        <DocumentTextIcon class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                          {{ application.application_number }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          ID: {{ application.id }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ application.service_module?.name || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ application.service_module?.slug || 'N/A' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="p-1.5 bg-gray-100 dark:bg-neutral-700 rounded-full mr-2">
                        <UserIcon class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                      </div>
                      <div>
                        <div class="text-sm text-gray-900 dark:text-white">
                          {{ application.user?.name || 'N/A' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          {{ application.user?.email || 'N/A' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize',
                        getStatusColor(application.status)
                      ]"
                    >
                      {{ application.status?.replace('_', ' ') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <BuildingOfficeIcon class="h-4 w-4 text-gray-400 mr-2" />
                      <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ application.quotes_count || 0 }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(application.created_at).toLocaleDateString('en-GB') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="`/admin/service-applications/${application.id}`"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-2xl transition-colors"
                    >
                      <EyeIcon class="h-4 w-4" />
                      View
                    </Link>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="!applications.data || applications.data.length === 0">
                  <td colspan="7" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                      <div class="p-4 bg-rose-100 dark:bg-rose-900/30 rounded-full mb-4">
                        <DocumentTextIcon class="h-8 w-8 text-rose-600 dark:text-rose-400" />
                      </div>
                      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No applications found</h3>
                      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Get started by creating a service application.
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="applications.data && applications.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Showing <span class="font-medium text-gray-900 dark:text-white">{{ applications.from }}</span>
                to <span class="font-medium text-gray-900 dark:text-white">{{ applications.to }}</span>
                of <span class="font-medium text-gray-900 dark:text-white">{{ applications.total }}</span> applications
              </p>
              <div class="flex items-center gap-2">
                <Link
                  v-if="applications.prev_page_url"
                  :href="applications.prev_page_url"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                >
                  <ChevronLeftIcon class="h-4 w-4" />
                  Previous
                </Link>
                <Link
                  v-if="applications.next_page_url"
                  :href="applications.next_page_url"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                >
                  Next
                  <ChevronRightIcon class="h-4 w-4" />
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
