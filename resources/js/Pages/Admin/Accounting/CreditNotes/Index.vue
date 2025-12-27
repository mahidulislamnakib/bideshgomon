<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  ArrowPathIcon,
  ReceiptRefundIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  creditNotes: Object,
  filters: Object,
  stats: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')

const statusColors = {
  draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
  approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  applied: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  voided: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
}

const reasonLabels = {
  refund: 'Refund',
  cancellation: 'Cancellation',
  service_issue: 'Service Issue',
  overcharge: 'Overcharge',
  goodwill: 'Goodwill',
  other: 'Other',
}

function applyFilters() {
  router.get(route('admin.accounting.credit-notes.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
  }, { preserveState: true })
}

const debouncedApplyFilters = useDebounceFn(applyFilters, 300)

watch([search], () => debouncedApplyFilters())
watch([status], applyFilters)

function approve(id) {
  if (confirm('Approve this credit note?')) {
    router.post(route('admin.accounting.credit-notes.approve', id))
  }
}

function voidNote(id) {
  if (confirm('Void this credit note? This cannot be undone.')) {
    router.post(route('admin.accounting.credit-notes.void', id))
  }
}
</script>

<template>
  <AdminLayout title="Credit Notes">
    <Head title="Credit Notes" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Credit Notes</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Manage refunds and credits</p>
        </div>
        <Link
          :href="route('admin.accounting.credit-notes.create')"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="w-5 h-5" />
          Create Credit Note
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <ReceiptRefundIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.total || 0 }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Credit Notes</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
              <ArrowPathIcon class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.pending || 0 }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Pending Approval</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
              <CheckIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.approved_amount || 0) }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Approved Value</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <DocumentTextIcon class="w-5 h-5 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.applied_amount || 0) }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Applied to Invoices</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="relative flex-1">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search credit notes..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            />
          </div>
          <select
            v-model="status"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
          >
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="applied">Applied</option>
            <option value="voided">Voided</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Credit Note #</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Client</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr 
                v-for="note in creditNotes.data" 
                :key="note.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
              >
                <td class="px-6 py-4">
                  <span class="font-medium text-gray-900 dark:text-white">{{ note.credit_note_number }}</span>
                </td>
                <td class="px-6 py-4">
                  <Link 
                    v-if="note.client"
                    :href="route('admin.accounting.clients.show', note.client.id)"
                    class="text-blue-600 hover:underline"
                  >
                    {{ note.client.name }}
                  </Link>
                  <span v-else class="text-gray-500">-</span>
                </td>
                <td class="px-6 py-4">
                  <Link 
                    v-if="note.invoice"
                    :href="route('admin.accounting.invoices.show', note.invoice.id)"
                    class="text-blue-600 hover:underline"
                  >
                    {{ note.invoice.invoice_number }}
                  </Link>
                  <span v-else class="text-gray-500">No invoice</span>
                </td>
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ formatCurrency(note.amount) }}
                </td>
                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                  {{ reasonLabels[note.reason] || note.reason }}
                </td>
                <td class="px-6 py-4">
                  <span :class="[statusColors[note.status], 'px-2 py-1 text-xs font-medium rounded-full']">
                    {{ note.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                  {{ formatDate(note.credit_note_date) }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.accounting.credit-notes.show', note.id)"
                      class="p-1 text-gray-400 hover:text-blue-600"
                    >
                      <EyeIcon class="w-5 h-5" />
                    </Link>
                    <button
                      v-if="note.status === 'pending'"
                      @click="approve(note.id)"
                      class="p-1 text-gray-400 hover:text-green-600"
                      title="Approve"
                    >
                      <CheckIcon class="w-5 h-5" />
                    </button>
                    <button
                      v-if="['draft', 'pending', 'approved'].includes(note.status)"
                      @click="voidNote(note.id)"
                      class="p-1 text-gray-400 hover:text-red-600"
                      title="Void"
                    >
                      <XMarkIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="creditNotes.data.length === 0" class="text-center py-12">
          <ReceiptRefundIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No credit notes</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a credit note to issue refunds or credits.</p>
        </div>

        <!-- Pagination -->
        <div v-if="creditNotes.links?.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Showing {{ creditNotes.from }} to {{ creditNotes.to }} of {{ creditNotes.total }}
            </p>
            <div class="flex gap-1">
              <Link
                v-for="link in creditNotes.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
