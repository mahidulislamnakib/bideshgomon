<template>
  <AdminLayout :title="`Invoice ${invoice.invoice_number}`">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-6 flex items-start justify-between">
        <div>
          <Link
            :href="route('admin.accounting.invoices.index')"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-1" />
            Back to Invoices
          </Link>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ invoice.invoice_number }}</h1>
          <div class="mt-1 flex items-center gap-3">
            <InvoiceStatusBadge :status="invoice.status" />
            <span class="text-sm text-gray-500">Created by {{ invoice.creator?.name }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="invoice.status === 'draft'"
            @click="sendInvoice"
            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            <PaperAirplaneIcon class="w-4 h-4" />
            Mark as Sent
          </button>
          <Link
            v-if="invoice.status === 'draft'"
            :href="route('admin.accounting.invoices.edit', invoice.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          <button
            @click="duplicateInvoice"
            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <DocumentDuplicateIcon class="w-4 h-4" />
            Duplicate
          </button>
          <Link
            :href="route('admin.accounting.invoices.pdf', invoice.id)"
            target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <PrinterIcon class="w-4 h-4" />
            Print
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Invoice Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Invoice Preview Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Invoice Header -->
            <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
              <div class="flex justify-between">
                <div>
                  <h2 class="text-2xl font-bold">INVOICE</h2>
                  <p class="text-blue-100">{{ invoice.invoice_number }}</p>
                </div>
                <div class="text-right">
                  <p class="font-bold text-lg">BideshGomon</p>
                  <p class="text-sm text-blue-100">Dhaka, Bangladesh</p>
                </div>
              </div>
            </div>

            <!-- Client & Dates -->
            <div class="p-6 grid grid-cols-2 gap-6 border-b border-gray-200 dark:border-gray-700">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Bill To</p>
                <p class="font-medium text-gray-900 dark:text-white">
                  {{ invoice.client?.name || invoice.client_name }}
                </p>
                <p class="text-sm text-gray-500">{{ invoice.client?.email || invoice.client_email }}</p>
                <p class="text-sm text-gray-500">{{ invoice.client?.phone || invoice.client_phone }}</p>
                <p v-if="invoice.client_address" class="text-sm text-gray-500">{{ invoice.client_address }}</p>
              </div>
              <div class="text-right">
                <div class="mb-3">
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Issue Date</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(invoice.issue_date) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Due Date</p>
                  <p class="font-medium" :class="isOverdue ? 'text-red-600' : 'text-gray-900 dark:text-white'">
                    {{ formatDate(invoice.due_date) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Items -->
            <div class="p-6">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wide pb-3">Description</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wide pb-3">Qty</th>
                    <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wide pb-3">Rate</th>
                    <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wide pb-3">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                  <tr v-for="item in invoice.items" :key="item.id">
                    <td class="py-3">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.description }}</p>
                      <p v-if="item.service" class="text-xs text-gray-500">{{ item.service.name }}</p>
                    </td>
                    <td class="py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ item.quantity }}</td>
                    <td class="py-3 text-right text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(item.unit_price) }}</td>
                    <td class="py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(item.amount) }}</td>
                  </tr>
                </tbody>
              </table>

              <!-- Totals -->
              <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="w-64 ml-auto space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-900 dark:text-white">{{ formatCurrency(invoice.subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">VAT ({{ invoice.tax_rate }}%)</span>
                    <span class="text-gray-900 dark:text-white">{{ formatCurrency(invoice.tax_amount) }}</span>
                  </div>
                  <div v-if="invoice.discount_amount > 0" class="flex justify-between text-sm">
                    <span class="text-gray-500">Discount</span>
                    <span class="text-red-600">-{{ formatCurrency(invoice.discount_amount) }}</span>
                  </div>
                  <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-900 dark:text-white">Total</span>
                    <span class="text-blue-600">{{ formatCurrency(invoice.total_amount) }}</span>
                  </div>
                  <div v-if="invoice.paid_amount > 0" class="flex justify-between text-sm">
                    <span class="text-gray-500">Paid</span>
                    <span class="text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
                  </div>
                  <div v-if="balanceDue > 0" class="flex justify-between text-sm font-medium">
                    <span class="text-gray-900 dark:text-white">Balance Due</span>
                    <span class="text-red-600">{{ formatCurrency(balanceDue) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes & Terms -->
            <div v-if="invoice.notes || invoice.terms" class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
              <div class="grid grid-cols-2 gap-6">
                <div v-if="invoice.notes">
                  <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Notes</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ invoice.notes }}</p>
                </div>
                <div v-if="invoice.terms">
                  <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Terms & Conditions</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ invoice.terms }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Payment Summary -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Summary</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-500">Total Amount</span>
                <span class="font-medium">{{ formatCurrency(invoice.total_amount) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Paid</span>
                <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                <span class="font-medium">Balance Due</span>
                <span class="font-bold text-lg" :class="balanceDue > 0 ? 'text-red-600' : 'text-green-600'">
                  {{ formatCurrency(balanceDue) }}
                </span>
              </div>
            </div>

            <button
              v-if="balanceDue > 0 && invoice.status !== 'cancelled'"
              @click="showPaymentModal = true"
              class="mt-4 w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
            >
              Record Payment
            </button>
          </div>

          <!-- Payment History -->
          <div v-if="invoice.payments && invoice.payments.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment History</h3>
            <div class="space-y-3">
              <div
                v-for="payment in invoice.payments"
                :key="payment.id"
                class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
              >
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatCurrency(payment.amount) }}
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ getMethodLabel(payment.payment_method) }} • {{ formatDate(payment.payment_date) }}
                  </p>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">
                  {{ payment.status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="invoice.status !== 'cancelled'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
            <div class="space-y-2">
              <button
                @click="cancelInvoice"
                class="w-full py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50"
              >
                Cancel Invoice
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <Modal :show="showPaymentModal" @close="showPaymentModal = false" max-width="md">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Record Payment</h3>
        <form @submit.prevent="submitPayment" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (৳)</label>
            <input
              v-model.number="paymentForm.amount"
              type="number"
              step="0.01"
              :max="balanceDue"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            />
            <p class="mt-1 text-xs text-gray-500">Maximum: {{ formatCurrency(balanceDue) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
            <select
              v-model="paymentForm.payment_method"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            >
              <option v-for="(label, key) in paymentMethods" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Date</label>
            <input
              v-model="paymentForm.payment_date"
              type="date"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction ID</label>
            <input
              v-model="paymentForm.transaction_id"
              type="text"
              placeholder="Optional"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea
              v-model="paymentForm.notes"
              rows="2"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            ></textarea>
          </div>
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showPaymentModal = false"
              class="flex-1 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="paymentForm.processing"
              class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
            >
              {{ paymentForm.processing ? 'Saving...' : 'Record Payment' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/ui/Modal.vue'
import InvoiceStatusBadge from '@/Components/Accounting/InvoiceStatusBadge.vue'
import { 
  ArrowLeftIcon, 
  PencilIcon, 
  PrinterIcon, 
  DocumentDuplicateIcon,
  PaperAirplaneIcon 
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  invoice: Object,
  paymentMethods: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const showPaymentModal = ref(false)

const balanceDue = computed(() => {
  return Math.max(0, props.invoice.total_amount - props.invoice.paid_amount)
})

const isOverdue = computed(() => {
  return new Date(props.invoice.due_date) < new Date() && 
         !['paid', 'cancelled'].includes(props.invoice.status)
})

const paymentForm = useForm({
  invoice_id: props.invoice.id,
  amount: balanceDue.value,
  payment_method: 'cash',
  payment_date: new Date().toISOString().split('T')[0],
  transaction_id: '',
  notes: '',
})

function getMethodLabel(method) {
  return props.paymentMethods[method] || method
}

function submitPayment() {
  paymentForm.post(route('admin.accounting.payments.store'), {
    onSuccess: () => {
      showPaymentModal.value = false
      paymentForm.reset()
    },
  })
}

function sendInvoice() {
  if (confirm('Mark this invoice as sent?')) {
    router.post(route('admin.accounting.invoices.send', props.invoice.id))
  }
}

function duplicateInvoice() {
  if (confirm('Create a copy of this invoice?')) {
    router.post(route('admin.accounting.invoices.duplicate', props.invoice.id))
  }
}

function cancelInvoice() {
  if (confirm('Are you sure you want to cancel this invoice? This action cannot be undone.')) {
    router.post(route('admin.accounting.invoices.cancel', props.invoice.id))
  }
}
</script>
