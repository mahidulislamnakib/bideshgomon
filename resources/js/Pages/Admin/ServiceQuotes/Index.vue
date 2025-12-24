<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  MagnifyingGlassIcon,
  FunnelIcon,
  EyeIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  BuildingOfficeIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  quotes: Object,
  stats: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showFilters = ref(false);

const searchQuotes = () => {
  router.get(route('admin.service-quotes.index'), {
    search: search.value,
    status: statusFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  search.value = '';
  statusFilter.value = '';
  router.get(route('admin.service-quotes.index'));
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    accepted: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  };
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-gray-300';
};

const deleteQuote = (quoteId) => {
  if (confirm('Are you sure you want to delete this quote?')) {
    router.delete(route('admin.service-quotes.destroy', quoteId));
  }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-BD', {
    minimumFractionDigits: 0,
  }).format(amount || 0);
};

const hasActiveFilters = computed(() => {
  return search.value || statusFilter.value;
});
</script>

<template>
  <Head title="Service Quotes Management" />

  <AdminLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
      <!-- Hero Header -->
      <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
        <!-- SVG Pattern -->
        <div class="absolute inset-0 opacity-10">
          <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <pattern id="quotesGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#quotesGrid)" />
          </svg>
        </div>

        <!-- Gradient Orbs -->
        <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-fuchsia-500/20 to-pink-500/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <!-- Header -->
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl shadow-lg">
                <CurrencyDollarIcon class="h-8 w-8 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-white">Service Quotes</h1>
                <p class="mt-1 text-gray-300">Manage agency quotes for service applications</p>
              </div>
            </div>
            <button
              @click="showFilters = !showFilters"
              class="mt-4 md:mt-0 inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
            >
              <FunnelIcon class="h-5 w-5" />
              {{ showFilters ? 'Hide' : 'Show' }} Filters
            </button>
          </div>

          <!-- Stats Cards in Hero -->
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-violet-500/20 rounded-xl">
                  <DocumentTextIcon class="h-5 w-5 text-violet-300" />
                </div>
                <div>
                  <p class="text-violet-300 text-xs font-medium">Total</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.total || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-500/20 rounded-xl">
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
                <div class="p-2 bg-emerald-500/20 rounded-xl">
                  <CheckCircleIcon class="h-5 w-5 text-emerald-300" />
                </div>
                <div>
                  <p class="text-emerald-300 text-xs font-medium">Accepted</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.accepted || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-red-500/20 rounded-xl">
                  <XCircleIcon class="h-5 w-5 text-red-300" />
                </div>
                <div>
                  <p class="text-red-300 text-xs font-medium">Rejected</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.rejected || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-500/20 rounded-xl">
                  <CurrencyDollarIcon class="h-5 w-5 text-purple-300" />
                </div>
                <div>
                  <p class="text-purple-300 text-xs font-medium">Total Value</p>
                  <p class="text-xl font-bold text-white">৳{{ formatCurrency(stats?.total_amount) }}</p>
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
                    @keyup.enter="searchQuotes"
                    type="text"
                    placeholder="Search by quote #, agency, or application #..."
                    class="block w-full rounded-xl border-0 py-3 pl-10 pr-3 text-gray-900 dark:text-white dark:bg-neutral-700 ring-1 ring-inset ring-gray-300 dark:ring-neutral-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-violet-500 sm:text-sm"
                  />
                </div>
              </div>

              <!-- Status Filter -->
              <div class="lg:w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select
                  v-model="statusFilter"
                  @change="searchQuotes"
                  class="block w-full rounded-xl border-0 py-3 text-gray-900 dark:text-white dark:bg-neutral-700 ring-1 ring-inset ring-gray-300 dark:ring-neutral-600 focus:ring-2 focus:ring-violet-500 sm:text-sm"
                >
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="accepted">Accepted</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-end gap-3">
                <button
                  @click="searchQuotes"
                  class="px-5 py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-medium transition-colors"
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

        <!-- Quotes Table -->
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
              <thead class="bg-gray-50 dark:bg-neutral-900/50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Quote #
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Agency
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Application
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Service
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Amount
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Date
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                <tr
                  v-for="quote in quotes.data"
                  :key="quote.id"
                  class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900/30 dark:to-purple-900/30 rounded-xl flex items-center justify-center">
                        <DocumentTextIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                          {{ quote.quote_number }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="p-1.5 bg-gray-100 dark:bg-neutral-700 rounded-full">
                        <BuildingOfficeIcon class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ quote.agency?.name || 'N/A' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          {{ quote.agency?.email }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ quote.service_application?.application_number }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ quote.service_application?.user?.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ quote.service_application?.service_module?.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                      ৳{{ formatCurrency(quote.quoted_amount) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ quote.processing_time_days }} days
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="getStatusColor(quote.status)"
                      class="px-3 py-1 text-xs font-semibold rounded-full capitalize"
                    >
                      {{ quote.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(quote.created_at).toLocaleDateString('en-GB') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('admin.service-quotes.show', quote.id)"
                        class="p-2 text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 rounded-xl transition-colors"
                      >
                        <EyeIcon class="h-5 w-5" />
                      </Link>
                      <button
                        @click="deleteQuote(quote.id)"
                        class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                      >
                        <TrashIcon class="h-5 w-5" />
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="!quotes.data || quotes.data.length === 0">
                  <td colspan="8" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                      <div class="p-4 bg-violet-100 dark:bg-violet-900/30 rounded-full mb-4">
                        <DocumentTextIcon class="h-8 w-8 text-violet-600 dark:text-violet-400" />
                      </div>
                      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No quotes found</h3>
                      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Try adjusting your filters
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="quotes.data && quotes.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Showing <span class="font-medium text-gray-900 dark:text-white">{{ quotes.from }}</span>
                to <span class="font-medium text-gray-900 dark:text-white">{{ quotes.to }}</span>
                of <span class="font-medium text-gray-900 dark:text-white">{{ quotes.total }}</span> quotes
              </p>
              <div class="flex items-center gap-2">
                <Link
                  v-if="quotes.prev_page_url"
                  :href="quotes.prev_page_url"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                >
                  <ChevronLeftIcon class="h-4 w-4" />
                  Previous
                </Link>
                <Link
                  v-if="quotes.next_page_url"
                  :href="quotes.next_page_url"
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
