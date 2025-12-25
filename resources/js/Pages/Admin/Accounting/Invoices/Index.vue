<template>
  <AdminLayout title="Invoices">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoices</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage all invoices and billing
          </p>
        </div>
        
        <Link
          :href="route('admin.accounting.invoices.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="w-4 h-4" />
          Create Invoice
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Draft</p>
          <p class="mt-1 text-2xl font-bold text-gray-500">{{ stats.draft }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Sent</p>
          <p class="mt-1 text-2xl font-bold text-blue-600">{{ stats.sent }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Paid</p>
          <p class="mt-1 text-2xl font-bold text-green-600">{{ stats.paid }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Overdue</p>
          <p class="mt-1 text-2xl font-bold text-red-600">{{ stats.overdue }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <input
              v-model="localFilters.search"
              type="text"
              placeholder="Search invoices..."
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
              @input="debouncedSearch"
            />
          </div>
          <select
            v-model="localFilters.status"
            @change="applyFilters"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
          >
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="partial">Partial</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <input
            v-model="localFilters.date_from"
            type="date"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
            @change="applyFilters"
          />
          <input
            v-model="localFilters.date_to"
            type="date"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
            @change="applyFilters"
          />
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Invoice
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Client
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Issue Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Due Date
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="invoice in invoices.data"
                :key="invoice.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link
                    :href="route('admin.accounting.invoices.show', invoice.id)"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800"
                  >
                    {{ invoice.invoice_number }}
                  </Link>
                  <p class="text-xs text-gray-500">{{ invoice.items_count }} items</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm text-gray-900 dark:text-white">
                    {{ invoice.client?.name || invoice.client_name || 'N/A' }}
                  </p>
                  <p class="text-xs text-gray-500">{{ invoice.client?.email || invoice.client_email }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(invoice.issue_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="isOverdue(invoice) ? 'text-red-600 font-medium' : 'text-gray-500'">
                  {{ formatDate(invoice.due_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatCurrency(invoice.total_amount) }}
                  </p>
                  <p v-if="invoice.paid_amount > 0" class="text-xs text-green-600">
                    Paid: {{ formatCurrency(invoice.paid_amount) }}
                  </p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <InvoiceStatusBadge :status="invoice.status" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.accounting.invoices.show', invoice.id)"
                      class="p-1 text-gray-400 hover:text-gray-600"
                      title="View"
                    >
                      <EyeIcon class="w-5 h-5" />
                    </Link>
                    <Link
                      v-if="invoice.status === 'draft'"
                      :href="route('admin.accounting.invoices.edit', invoice.id)"
                      class="p-1 text-gray-400 hover:text-blue-600"
                      title="Edit"
                    >
                      <PencilIcon class="w-5 h-5" />
                    </Link>
                    <button
                      @click="duplicateInvoice(invoice)"
                      class="p-1 text-gray-400 hover:text-green-600"
                      title="Duplicate"
                    >
                      <DocumentDuplicateIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="invoices.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <DocumentTextIcon class="w-12 h-12 mx-auto text-gray-300" />
                  <p class="mt-2 text-sm text-gray-500">No invoices found</p>
                  <Link
                    :href="route('admin.accounting.invoices.create')"
                    class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                  >
                    <PlusIcon class="w-4 h-4 mr-1" />
                    Create your first invoice
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="invoices.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <Pagination :links="invoices.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/ui/Pagination.vue'
import InvoiceStatusBadge from '@/Components/Accounting/InvoiceStatusBadge.vue'
import { 
  PlusIcon, 
  EyeIcon, 
  PencilIcon, 
  DocumentDuplicateIcon,
  DocumentTextIcon 
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { useDebouncedSearch } from '@/Composables/useDebouncedSearch'

const props = defineProps({
  invoices: Object,
  stats: Object,
  filters: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const localFilters = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.status || localFilters.value.date_from || localFilters.value.date_to
})

function applyFilters() {
  router.get(route('admin.accounting.invoices.index'), localFilters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const { debouncedSearch } = useDebouncedSearch(applyFilters, 300)

function clearFilters() {
  localFilters.value = { search: '', status: '', date_from: '', date_to: '' }
  applyFilters()
}

function isOverdue(invoice) {
  return invoice.status === 'overdue' || (
    new Date(invoice.due_date) < new Date() && 
    !['paid', 'cancelled'].includes(invoice.status)
  )
}

function duplicateInvoice(invoice) {
  if (confirm('Create a copy of this invoice?')) {
    router.post(route('admin.accounting.invoices.duplicate', invoice.id))
  }
}
</script>
