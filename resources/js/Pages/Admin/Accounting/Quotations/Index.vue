<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  DocumentTextIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  EyeIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  XCircleIcon,
  DocumentDuplicateIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  quotations: Object,
  filters: Object,
  stats: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const localFilters = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
})

const debounceTimeout = ref(null)

function debouncedSearch() {
  clearTimeout(debounceTimeout.value)
  debounceTimeout.value = setTimeout(applyFilters, 300)
}

function applyFilters() {
  router.get(route('admin.accounting.quotations.index'), localFilters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  localFilters.value = { search: '', status: '' }
  applyFilters()
}

const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.status
})

function deleteQuotation(quotation) {
  if (confirm(`Are you sure you want to delete ${quotation.quotation_number}?`)) {
    router.delete(route('admin.accounting.quotations.destroy', quotation.id))
  }
}

function sendQuotation(quotation) {
  router.post(route('admin.accounting.quotations.send', quotation.id))
}

function convertToInvoice(quotation) {
  if (confirm('Convert this quotation to an invoice?')) {
    router.post(route('admin.accounting.quotations.convert', quotation.id))
  }
}

function duplicateQuotation(quotation) {
  router.post(route('admin.accounting.quotations.duplicate', quotation.id))
}

function getStatusColor(status) {
  const colors = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-600',
    accepted: 'bg-green-100 text-green-600',
    rejected: 'bg-red-100 text-red-600',
    expired: 'bg-orange-100 text-orange-600',
    converted: 'bg-purple-100 text-purple-600',
  }
  return colors[status] || colors.draft
}
</script>

<template>
  <AdminLayout title="Quotations">
    <Head title="Quotations - Accounting" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quotations</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Create and manage quotes for clients
          </p>
        </div>
        
        <Link
          :href="route('admin.accounting.quotations.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="w-4 h-4" />
          Create Quotation
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.total || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Draft</p>
          <p class="mt-1 text-2xl font-bold text-gray-500">{{ stats?.draft || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Sent</p>
          <p class="mt-1 text-2xl font-bold text-blue-600">{{ stats?.sent || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Accepted</p>
          <p class="mt-1 text-2xl font-bold text-green-600">{{ stats?.accepted || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total Value</p>
          <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.total_value || 0) }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="localFilters.search"
                type="text"
                placeholder="Search quotations..."
                class="w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                @input="debouncedSearch"
              />
            </div>
          </div>
          <select
            v-model="localFilters.status"
            @change="applyFilters"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
          >
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
            <option value="expired">Expired</option>
            <option value="converted">Converted</option>
          </select>
          <button
            v-if="hasFilters"
            @click="clearFilters"
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            Clear filters
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quotation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="quotation in quotations.data" :key="quotation.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="route('admin.accounting.quotations.show', quotation.id)" class="font-medium text-blue-600 hover:underline">
                    {{ quotation.quotation_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-gray-900 dark:text-white">{{ quotation.client?.name || quotation.client_name }}</p>
                  <p class="text-sm text-gray-500">{{ quotation.client?.email || quotation.client_email }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(quotation.issue_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(quotation.valid_until) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900 dark:text-white">
                  {{ formatCurrency(quotation.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span :class="[getStatusColor(quotation.status), 'px-2 py-1 rounded-full text-xs font-medium capitalize']">
                    {{ quotation.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex justify-end gap-1">
                    <button
                      v-if="quotation.status === 'draft'"
                      @click="sendQuotation(quotation)"
                      class="p-1.5 text-blue-500 hover:bg-blue-50 rounded"
                      title="Send"
                    >
                      <PaperAirplaneIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="quotation.status === 'accepted'"
                      @click="convertToInvoice(quotation)"
                      class="p-1.5 text-green-500 hover:bg-green-50 rounded"
                      title="Convert to Invoice"
                    >
                      <ArrowPathIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="duplicateQuotation(quotation)"
                      class="p-1.5 text-gray-400 hover:text-purple-600 rounded"
                      title="Duplicate"
                    >
                      <DocumentDuplicateIcon class="w-4 h-4" />
                    </button>
                    <Link
                      :href="route('admin.accounting.quotations.show', quotation.id)"
                      class="p-1.5 text-gray-400 hover:text-blue-600 rounded"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </Link>
                    <Link
                      v-if="quotation.status === 'draft'"
                      :href="route('admin.accounting.quotations.edit', quotation.id)"
                      class="p-1.5 text-gray-400 hover:text-amber-600 rounded"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </Link>
                    <button
                      @click="deleteQuotation(quotation)"
                      class="p-1.5 text-gray-400 hover:text-red-600 rounded"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!quotations.data?.length">
                <td colspan="7" class="px-6 py-12 text-center">
                  <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No quotations found</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by creating your first quotation.</p>
                  <Link
                    :href="route('admin.accounting.quotations.create')"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
                  >
                    <PlusIcon class="w-4 h-4" />
                    Create Quotation
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="quotations.links?.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">
              Showing {{ quotations.from }} to {{ quotations.to }} of {{ quotations.total }} quotations
            </p>
            <div class="flex gap-1">
              <Link
                v-for="link in quotations.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700',
                  !link.url ? 'opacity-50 cursor-not-allowed' : ''
                ]"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
