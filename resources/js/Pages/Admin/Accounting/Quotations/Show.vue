<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  XCircleIcon,
  ArrowPathIcon,
  DocumentDuplicateIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  quotation: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

function deleteQuotation() {
  if (confirm(`Are you sure you want to delete ${props.quotation.quotation_number}?`)) {
    router.delete(route('admin.accounting.quotations.destroy', props.quotation.id))
  }
}

function sendQuotation() {
  router.post(route('admin.accounting.quotations.send', props.quotation.id))
}

function acceptQuotation() {
  router.post(route('admin.accounting.quotations.accept', props.quotation.id))
}

function rejectQuotation() {
  router.post(route('admin.accounting.quotations.reject', props.quotation.id))
}

function convertToInvoice() {
  if (confirm('Convert this quotation to an invoice?')) {
    router.post(route('admin.accounting.quotations.convert', props.quotation.id))
  }
}

function duplicateQuotation() {
  router.post(route('admin.accounting.quotations.duplicate', props.quotation.id))
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
  <AdminLayout title="Quotation Details">
    <Head :title="`Quotation ${quotation.quotation_number}`" />
    
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.accounting.quotations.index')"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ quotation.quotation_number }}</h1>
              <span :class="[getStatusColor(quotation.status), 'px-2 py-1 rounded-full text-xs font-medium capitalize']">
                {{ quotation.status }}
              </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ quotation.client?.name || quotation.client_name }}</p>
          </div>
        </div>
        
        <div class="flex flex-wrap gap-2">
          <button
            v-if="quotation.status === 'draft'"
            @click="sendQuotation"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PaperAirplaneIcon class="w-4 h-4" />
            Send
          </button>
          <button
            v-if="quotation.status === 'sent'"
            @click="acceptQuotation"
            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          >
            <CheckCircleIcon class="w-4 h-4" />
            Accept
          </button>
          <button
            v-if="quotation.status === 'sent'"
            @click="rejectQuotation"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
          >
            <XCircleIcon class="w-4 h-4" />
            Reject
          </button>
          <button
            v-if="quotation.status === 'accepted'"
            @click="convertToInvoice"
            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
          >
            <ArrowPathIcon class="w-4 h-4" />
            Convert to Invoice
          </button>
          <button
            @click="duplicateQuotation"
            class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
          >
            <DocumentDuplicateIcon class="w-4 h-4" />
            Duplicate
          </button>
          <Link
            v-if="quotation.status === 'draft'"
            :href="route('admin.accounting.quotations.edit', quotation.id)"
            class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
          >
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          <a
            :href="route('admin.accounting.quotations.pdf', quotation.id)"
            target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
            PDF
          </a>
          <button
            @click="deleteQuotation"
            class="inline-flex items-center gap-2 px-4 py-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition"
          >
            <TrashIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Quotation Document -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">QUOTATION</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">{{ quotation.quotation_number }}</p>
          </div>
          <div class="text-right">
            <p class="font-semibold text-gray-900 dark:text-white">BideshGomon</p>
            <p class="text-sm text-gray-500">Dhaka, Bangladesh</p>
          </div>
        </div>

        <!-- Client & Dates -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Bill To</h3>
            <p class="font-medium text-gray-900 dark:text-white">{{ quotation.client?.name || quotation.client_name }}</p>
            <p class="text-gray-600 dark:text-gray-400">{{ quotation.client?.email || quotation.client_email }}</p>
            <p v-if="quotation.client?.phone || quotation.client_phone" class="text-gray-600 dark:text-gray-400">
              {{ quotation.client?.phone || quotation.client_phone }}
            </p>
            <p v-if="quotation.client?.address || quotation.client_address" class="text-gray-600 dark:text-gray-400">
              {{ quotation.client?.address || quotation.client_address }}
            </p>
          </div>
          <div class="text-right">
            <div class="mb-2">
              <span class="text-sm text-gray-500">Issue Date:</span>
              <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ formatDate(quotation.issue_date) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500">Valid Until:</span>
              <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ formatDate(quotation.valid_until) }}</span>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <table class="w-full mb-8">
          <thead>
            <tr class="border-b-2 border-gray-200 dark:border-gray-700">
              <th class="text-left py-3 text-sm font-semibold text-gray-500 uppercase">Description</th>
              <th class="text-center py-3 text-sm font-semibold text-gray-500 uppercase">Qty</th>
              <th class="text-right py-3 text-sm font-semibold text-gray-500 uppercase">Unit Price</th>
              <th class="text-right py-3 text-sm font-semibold text-gray-500 uppercase">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="item in quotation.items" :key="item.id">
              <td class="py-4 text-gray-900 dark:text-white">{{ item.description }}</td>
              <td class="py-4 text-center text-gray-600 dark:text-gray-400">{{ item.quantity }}</td>
              <td class="py-4 text-right text-gray-600 dark:text-gray-400">{{ formatCurrency(item.unit_price) }}</td>
              <td class="py-4 text-right font-medium text-gray-900 dark:text-white">{{ formatCurrency(item.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end">
          <div class="w-64 space-y-2">
            <div class="flex justify-between py-2">
              <span class="text-gray-500">Subtotal</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(quotation.subtotal) }}</span>
            </div>
            <div v-if="quotation.discount_amount > 0" class="flex justify-between py-2 text-red-600">
              <span>Discount</span>
              <span>-{{ formatCurrency(quotation.discount_amount) }}</span>
            </div>
            <div v-if="quotation.tax_amount > 0" class="flex justify-between py-2">
              <span class="text-gray-500">Tax ({{ quotation.tax_rate }}%)</span>
              <span class="text-gray-900 dark:text-white">{{ formatCurrency(quotation.tax_amount) }}</span>
            </div>
            <div class="flex justify-between py-3 border-t-2 border-gray-200 dark:border-gray-700">
              <span class="text-lg font-semibold text-gray-900 dark:text-white">Total</span>
              <span class="text-lg font-bold text-blue-600">{{ formatCurrency(quotation.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Notes & Terms -->
        <div v-if="quotation.notes || quotation.terms" class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-if="quotation.notes">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Notes</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm whitespace-pre-line">{{ quotation.notes }}</p>
          </div>
          <div v-if="quotation.terms">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Terms & Conditions</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm whitespace-pre-line">{{ quotation.terms }}</p>
          </div>
        </div>
      </div>

      <!-- Converted Invoice Link -->
      <div v-if="quotation.invoice" class="bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-green-800 dark:text-green-400">Converted to Invoice</p>
            <p class="text-sm text-green-600 dark:text-green-500">{{ quotation.invoice.invoice_number }}</p>
          </div>
          <Link
            :href="route('admin.accounting.invoices.show', quotation.invoice.id)"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          >
            View Invoice
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
