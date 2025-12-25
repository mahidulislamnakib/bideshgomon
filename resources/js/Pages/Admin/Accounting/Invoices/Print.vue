<template>
  <AdminLayout :title="`Invoice ${invoice.invoice_number}`">
    <div class="max-w-4xl mx-auto">
      <!-- Print Controls (hidden in print) -->
      <div class="mb-6 flex items-center justify-between print:hidden">
        <Link
          :href="route('admin.accounting.invoices.show', invoice.id)"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-1" />
          Back to Invoice
        </Link>
        <div class="flex items-center gap-3">
          <button
            @click="downloadPDF"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
            Download PDF
          </button>
          <button
            @click="printInvoice"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            <PrinterIcon class="w-4 h-4" />
            Print
          </button>
        </div>
      </div>

      <!-- Invoice Document -->
      <div id="invoice-print" class="bg-white rounded-xl shadow-lg p-8 print:shadow-none print:rounded-none">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">INVOICE</h1>
            <p class="text-lg text-gray-600 mt-1">{{ invoice.invoice_number }}</p>
          </div>
          <div class="text-right">
            <img 
              src="/images/logo.png" 
              alt="BideshGomon" 
              class="h-12 w-auto ml-auto mb-2"
              onerror="this.style.display='none'"
            />
            <h2 class="text-xl font-bold text-blue-600">BideshGomon</h2>
            <p class="text-sm text-gray-500">Visa & Immigration Services</p>
            <p class="text-sm text-gray-500">Dhaka, Bangladesh</p>
            <p class="text-sm text-gray-500">contact@bideshgomon.com</p>
          </div>
        </div>

        <!-- Status & Dates -->
        <div class="grid grid-cols-3 gap-6 mb-8 p-4 bg-gray-50 rounded-lg print:bg-gray-100">
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</p>
            <p class="mt-1 text-lg font-semibold" :class="statusColorClass">
              {{ statusLabel }}
            </p>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Issue Date</p>
            <p class="mt-1 text-lg font-medium text-gray-900">{{ formatDate(invoice.issue_date) }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Due Date</p>
            <p class="mt-1 text-lg font-medium" :class="isOverdue ? 'text-red-600' : 'text-gray-900'">
              {{ formatDate(invoice.due_date) }}
              <span v-if="isOverdue" class="text-sm">(Overdue)</span>
            </p>
          </div>
        </div>

        <!-- Bill To -->
        <div class="mb-8">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Bill To</p>
          <div class="p-4 border border-gray-200 rounded-lg">
            <p class="font-semibold text-gray-900 text-lg">{{ clientName }}</p>
            <p v-if="clientEmail" class="text-gray-600">{{ clientEmail }}</p>
            <p v-if="clientPhone" class="text-gray-600">{{ clientPhone }}</p>
            <p v-if="clientAddress" class="text-gray-600">{{ clientAddress }}</p>
          </div>
        </div>

        <!-- Line Items -->
        <div class="mb-8">
          <table class="w-full">
            <thead>
              <tr class="border-b-2 border-gray-200">
                <th class="py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                <th class="py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Qty</th>
                <th class="py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Rate</th>
                <th class="py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in invoice.items" :key="item.id">
                <td class="py-4 text-gray-900">{{ item.description }}</td>
                <td class="py-4 text-right text-gray-600">{{ item.quantity }}</td>
                <td class="py-4 text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                <td class="py-4 text-right font-medium text-gray-900">{{ formatCurrency(item.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
          <div class="w-72">
            <div class="space-y-2">
              <div class="flex justify-between py-2">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(invoice.subtotal) }}</span>
              </div>
              <div class="flex justify-between py-2">
                <span class="text-gray-600">VAT ({{ invoice.tax_rate }}%)</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(invoice.tax_amount) }}</span>
              </div>
              <div v-if="invoice.discount_amount > 0" class="flex justify-between py-2">
                <span class="text-gray-600">Discount</span>
                <span class="font-medium text-red-600">-{{ formatCurrency(invoice.discount_amount) }}</span>
              </div>
              <div class="flex justify-between py-3 border-t-2 border-gray-900">
                <span class="text-lg font-bold text-gray-900">Total</span>
                <span class="text-lg font-bold text-gray-900">{{ formatCurrency(invoice.total_amount) }}</span>
              </div>
              <div v-if="invoice.paid_amount > 0" class="flex justify-between py-2 text-green-600">
                <span>Paid</span>
                <span class="font-medium">-{{ formatCurrency(invoice.paid_amount) }}</span>
              </div>
              <div v-if="balanceDue > 0" class="flex justify-between py-3 bg-red-50 px-3 -mx-3 rounded">
                <span class="font-bold text-red-600">Balance Due</span>
                <span class="font-bold text-red-600">{{ formatCurrency(balanceDue) }}</span>
              </div>
              <div v-else-if="invoice.status === 'paid'" class="flex justify-between py-3 bg-green-50 px-3 -mx-3 rounded">
                <span class="font-bold text-green-600">PAID IN FULL</span>
                <CheckCircleIcon class="w-6 h-6 text-green-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Notes & Terms -->
        <div class="grid grid-cols-2 gap-6 pt-6 border-t border-gray-200">
          <div v-if="invoice.notes">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Notes</p>
            <p class="text-sm text-gray-600 whitespace-pre-line">{{ invoice.notes }}</p>
          </div>
          <div v-if="invoice.terms">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Terms & Conditions</p>
            <p class="text-sm text-gray-600 whitespace-pre-line">{{ invoice.terms }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
          <p>Thank you for your business!</p>
          <p class="mt-1">For questions, contact us at contact@bideshgomon.com</p>
        </div>
      </div>

      <!-- Payment Information (if any payments) -->
      <div v-if="invoice.payments?.length > 0" class="mt-6 print:hidden">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h3>
          <div class="space-y-3">
            <div
              v-for="payment in invoice.payments"
              :key="payment.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(payment.payment_date) }} • {{ paymentMethodLabel(payment.payment_method) }}</p>
              </div>
              <span v-if="payment.transaction_id" class="text-sm text-gray-500">
                Ref: {{ payment.transaction_id }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  ArrowLeftIcon, 
  PrinterIcon, 
  ArrowDownTrayIcon,
  CheckCircleIcon 
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  invoice: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const statusLabels = {
  draft: 'Draft',
  sent: 'Sent',
  paid: 'Paid',
  partial: 'Partially Paid',
  overdue: 'Overdue',
  cancelled: 'Cancelled',
}

const statusColors = {
  draft: 'text-gray-600',
  sent: 'text-blue-600',
  paid: 'text-green-600',
  partial: 'text-yellow-600',
  overdue: 'text-red-600',
  cancelled: 'text-gray-400',
}

const statusLabel = computed(() => statusLabels[props.invoice.status] || props.invoice.status)
const statusColorClass = computed(() => statusColors[props.invoice.status] || 'text-gray-600')

const clientName = computed(() => {
  return props.invoice.client?.name || props.invoice.client_name || 'N/A'
})

const clientEmail = computed(() => {
  return props.invoice.client?.email || props.invoice.client_email
})

const clientPhone = computed(() => {
  return props.invoice.client?.phone || props.invoice.client_phone
})

const clientAddress = computed(() => {
  return props.invoice.client?.address || props.invoice.client_address
})

const balanceDue = computed(() => {
  return props.invoice.total_amount - (props.invoice.paid_amount || 0)
})

const isOverdue = computed(() => {
  if (props.invoice.status === 'paid' || props.invoice.status === 'cancelled') return false
  return new Date(props.invoice.due_date) < new Date()
})

const paymentMethods = {
  bkash: 'bKash',
  nagad: 'Nagad',
  rocket: 'Rocket',
  bank_transfer: 'Bank Transfer',
  cash: 'Cash',
  card: 'Card',
  check: 'Check',
  other: 'Other',
}

function paymentMethodLabel(method) {
  return paymentMethods[method] || method
}

function printInvoice() {
  window.print()
}

function downloadPDF() {
  // Open PDF download in new tab
  window.open(route('admin.accounting.invoices.pdf', props.invoice.id), '_blank')
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  #invoice-print, #invoice-print * {
    visibility: visible;
  }
  #invoice-print {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 40px;
  }
  .print\\:hidden {
    display: none !important;
  }
}
</style>
