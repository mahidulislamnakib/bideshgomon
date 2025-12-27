<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { ref } from 'vue'
import {
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  DocumentCheckIcon,
  PrinterIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  creditNote: Object,
  availableInvoices: Array,
})

const { formatCurrency, formatDate, formatDateTime } = useBangladeshFormat()

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

// Apply to invoice modal
const showApplyModal = ref(false)
const selectedInvoiceId = ref('')

function approve() {
  if (confirm('Approve this credit note?')) {
    router.post(route('admin.accounting.credit-notes.approve', props.creditNote.id))
  }
}

function voidNote() {
  if (confirm('Void this credit note? This cannot be undone.')) {
    router.post(route('admin.accounting.credit-notes.void', props.creditNote.id))
  }
}

function applyToInvoice() {
  if (!selectedInvoiceId.value) return
  router.post(route('admin.accounting.credit-notes.apply', props.creditNote.id), {
    invoice_id: selectedInvoiceId.value
  }, {
    onSuccess: () => {
      showApplyModal.value = false
      selectedInvoiceId.value = ''
    }
  })
}

function print() {
  window.print()
}
</script>

<template>
  <AdminLayout title="Credit Note Details">
    <Head :title="`Credit Note ${creditNote.credit_note_number}`" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.accounting.credit-notes.index')"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ creditNote.credit_note_number }}
              </h1>
              <span :class="[statusColors[creditNote.status], 'px-3 py-1 text-sm font-medium rounded-full capitalize']">
                {{ creditNote.status }}
              </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400">
              Created {{ formatDateTime(creditNote.created_at) }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button
            @click="print"
            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
          >
            <PrinterIcon class="w-4 h-4" />
            Print
          </button>
          
          <button
            v-if="creditNote.status === 'pending'"
            @click="approve"
            class="flex items-center gap-2 px-3 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400"
          >
            <CheckIcon class="w-4 h-4" />
            Approve
          </button>
          
          <button
            v-if="creditNote.status === 'approved' && availableInvoices?.length > 0"
            @click="showApplyModal = true"
            class="flex items-center gap-2 px-3 py-2 text-sm text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400"
          >
            <DocumentCheckIcon class="w-4 h-4" />
            Apply to Invoice
          </button>
          
          <button
            v-if="['draft', 'pending', 'approved'].includes(creditNote.status)"
            @click="voidNote"
            class="flex items-center gap-2 px-3 py-2 text-sm text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400"
          >
            <XMarkIcon class="w-4 h-4" />
            Void
          </button>
        </div>
      </div>

      <!-- Credit Note Document -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 print:shadow-none print:border-none">
        <!-- Header -->
        <div class="flex justify-between items-start pb-6 border-b border-gray-200 dark:border-gray-700">
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">CREDIT NOTE</h2>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ creditNote.credit_note_number }}</p>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-gray-900 dark:text-white">BideshGomon</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Dhaka, Bangladesh</p>
          </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-8 py-6">
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Credit To</h3>
            <div v-if="creditNote.client">
              <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ creditNote.client.name }}</p>
              <p v-if="creditNote.client.email" class="text-gray-600 dark:text-gray-400">{{ creditNote.client.email }}</p>
              <p v-if="creditNote.client.phone" class="text-gray-600 dark:text-gray-400">{{ creditNote.client.phone }}</p>
            </div>
            <p v-else class="text-gray-500 dark:text-gray-400">No client specified</p>
          </div>
          
          <div class="text-right">
            <div class="space-y-2">
              <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Date:</span>
                <span class="ml-2 text-gray-900 dark:text-white">{{ formatDate(creditNote.credit_note_date) }}</span>
              </div>
              <div v-if="creditNote.invoice">
                <span class="text-sm text-gray-500 dark:text-gray-400">Invoice Ref:</span>
                <Link 
                  :href="route('admin.accounting.invoices.show', creditNote.invoice.id)"
                  class="ml-2 text-blue-600 hover:underline"
                >
                  {{ creditNote.invoice.invoice_number }}
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Amount Box -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 text-center">
          <p class="text-sm text-gray-500 dark:text-gray-400 uppercase mb-2">Credit Amount</p>
          <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(creditNote.amount) }}</p>
        </div>

        <!-- Reason & Description -->
        <div class="mt-6 space-y-4">
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</h3>
            <p class="mt-1 text-gray-900 dark:text-white">{{ reasonLabels[creditNote.reason] || creditNote.reason }}</p>
          </div>
          
          <div v-if="creditNote.description">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Description</h3>
            <p class="mt-1 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ creditNote.description }}</p>
          </div>
        </div>

        <!-- Approval Info -->
        <div v-if="creditNote.approved_by || creditNote.applied_at" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div v-if="creditNote.approved_by">
              <span class="text-gray-500 dark:text-gray-400">Approved By:</span>
              <span class="ml-2 text-gray-900 dark:text-white">{{ creditNote.approver?.name || 'Admin' }}</span>
              <span class="text-gray-500 dark:text-gray-400 ml-2">{{ formatDateTime(creditNote.approved_at) }}</span>
            </div>
            <div v-if="creditNote.applied_at">
              <span class="text-gray-500 dark:text-gray-400">Applied:</span>
              <span class="ml-2 text-gray-900 dark:text-white">{{ formatDateTime(creditNote.applied_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Apply to Invoice Modal -->
    <Teleport to="body">
      <div v-if="showApplyModal" class="fixed inset-0 z-50 flex items-center justify-center print:hidden">
        <div class="absolute inset-0 bg-black/50" @click="showApplyModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Apply Credit to Invoice</h3>
          
          <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Apply {{ formatCurrency(creditNote.amount) }} credit to an invoice.
            </p>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Select Invoice
              </label>
              <select
                v-model="selectedInvoiceId"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option value="">Choose an invoice...</option>
                <option v-for="invoice in availableInvoices" :key="invoice.id" :value="invoice.id">
                  {{ invoice.invoice_number }} - {{ formatCurrency(invoice.amount_due) }} due
                </option>
              </select>
            </div>
          </div>
          
          <div class="flex justify-end gap-3 mt-6">
            <button
              @click="showApplyModal = false"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              @click="applyToInvoice"
              :disabled="!selectedInvoiceId"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              Apply Credit
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
