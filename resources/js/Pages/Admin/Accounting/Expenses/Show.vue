<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  BanknotesIcon,
  DocumentTextIcon,
  CalendarIcon,
  UserIcon,
  TagIcon,
  CreditCardIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  expense: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

function deleteExpense() {
  if (confirm('Are you sure you want to delete this expense?')) {
    router.delete(route('admin.accounting.expenses.destroy', props.expense.id))
  }
}

function approveExpense() {
  router.post(route('admin.accounting.expenses.approve', props.expense.id))
}

function rejectExpense() {
  router.post(route('admin.accounting.expenses.reject', props.expense.id))
}

function markPaid() {
  router.post(route('admin.accounting.expenses.mark-paid', props.expense.id))
}

function getStatusColor(status) {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-blue-100 text-blue-700',
    rejected: 'bg-red-100 text-red-700',
    paid: 'bg-green-100 text-green-700',
  }
  return colors[status] || 'bg-gray-100 text-gray-700'
}

const paymentMethodLabels = {
  cash: 'Cash',
  bkash: 'bKash',
  nagad: 'Nagad',
  rocket: 'Rocket',
  bank_transfer: 'Bank Transfer',
  card: 'Card',
  check: 'Check',
}
</script>

<template>
  <AdminLayout title="Expense Details">
    <Head :title="`Expense ${expense.expense_number}`" />
    
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.accounting.expenses.index')"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ expense.expense_number }}</h1>
              <span :class="[getStatusColor(expense.status), 'px-2 py-1 rounded-full text-xs font-medium capitalize']">
                {{ expense.status }}
              </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ expense.vendor_name }}</p>
          </div>
        </div>
        
        <div class="flex gap-2">
          <button
            v-if="expense.status === 'pending'"
            @click="approveExpense"
            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          >
            <CheckCircleIcon class="w-4 h-4" />
            Approve
          </button>
          <button
            v-if="expense.status === 'pending'"
            @click="rejectExpense"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
          >
            <XCircleIcon class="w-4 h-4" />
            Reject
          </button>
          <button
            v-if="expense.status === 'approved'"
            @click="markPaid"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <BanknotesIcon class="w-4 h-4" />
            Mark as Paid
          </button>
          <Link
            :href="route('admin.accounting.expenses.edit', expense.id)"
            class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
          >
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          <button
            @click="deleteExpense"
            class="inline-flex items-center gap-2 px-4 py-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition"
          >
            <TrashIcon class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Description -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Description</h2>
            <p class="text-gray-900 dark:text-white">{{ expense.description }}</p>
            
            <div v-if="expense.notes" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
              <p class="text-gray-700 dark:text-gray-300 text-sm">{{ expense.notes }}</p>
            </div>
          </div>

          <!-- Receipt -->
          <div v-if="expense.receipt_path" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Receipt</h2>
            <a 
              :href="`/storage/${expense.receipt_path}`" 
              target="_blank"
              class="block"
            >
              <img 
                :src="`/storage/${expense.receipt_path}`" 
                alt="Receipt" 
                class="max-h-96 rounded-lg border hover:opacity-90 transition"
              />
            </a>
          </div>

          <!-- Approval Info -->
          <div v-if="expense.approved_by || expense.rejected_reason" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Approval Details</h2>
            
            <div v-if="expense.approver" class="flex items-center gap-3 mb-3">
              <UserIcon class="w-5 h-5 text-gray-400" />
              <div>
                <p class="text-sm text-gray-500">{{ expense.status === 'rejected' ? 'Rejected by' : 'Approved by' }}</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ expense.approver.name }}</p>
              </div>
            </div>
            
            <div v-if="expense.approved_at" class="flex items-center gap-3 mb-3">
              <CalendarIcon class="w-5 h-5 text-gray-400" />
              <div>
                <p class="text-sm text-gray-500">Date</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(expense.approved_at) }}</p>
              </div>
            </div>
            
            <div v-if="expense.rejected_reason" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
              <p class="text-sm text-red-700 dark:text-red-400">{{ expense.rejected_reason }}</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Amount Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Amount</h2>
            
            <div class="text-center py-4">
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(expense.total_amount) }}</p>
            </div>
            
            <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span class="text-gray-900 dark:text-white">{{ formatCurrency(expense.amount) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Tax</span>
                <span class="text-gray-900 dark:text-white">{{ formatCurrency(expense.tax_amount || 0) }}</span>
              </div>
            </div>
          </div>

          <!-- Details Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Details</h2>
            
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <CalendarIcon class="w-5 h-5 text-gray-400" />
                <div>
                  <p class="text-sm text-gray-500">Date</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(expense.expense_date) }}</p>
                </div>
              </div>
              
              <div v-if="expense.category" class="flex items-center gap-3">
                <TagIcon class="w-5 h-5 text-gray-400" />
                <div>
                  <p class="text-sm text-gray-500">Category</p>
                  <span 
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium"
                    :style="{ backgroundColor: expense.category.color + '20', color: expense.category.color }"
                  >
                    {{ expense.category.name }}
                  </span>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <CreditCardIcon class="w-5 h-5 text-gray-400" />
                <div>
                  <p class="text-sm text-gray-500">Payment Method</p>
                  <p class="font-medium text-gray-900 dark:text-white">
                    {{ paymentMethodLabels[expense.payment_method] || expense.payment_method }}
                  </p>
                </div>
              </div>
              
              <div v-if="expense.reference_number" class="flex items-center gap-3">
                <DocumentTextIcon class="w-5 h-5 text-gray-400" />
                <div>
                  <p class="text-sm text-gray-500">Reference</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ expense.reference_number }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Flags -->
          <div v-if="expense.is_billable || expense.is_recurring" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Flags</h2>
            
            <div class="space-y-2">
              <div v-if="expense.is_billable" class="flex items-center gap-2 text-sm">
                <CheckCircleIcon class="w-4 h-4 text-green-500" />
                <span class="text-gray-700 dark:text-gray-300">Billable to client</span>
              </div>
              <div v-if="expense.is_recurring" class="flex items-center gap-2 text-sm">
                <CheckCircleIcon class="w-4 h-4 text-green-500" />
                <span class="text-gray-700 dark:text-gray-300">
                  Recurring ({{ expense.recurring_frequency }})
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
