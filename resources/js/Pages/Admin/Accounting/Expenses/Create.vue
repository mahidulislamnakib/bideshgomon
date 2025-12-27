<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeftIcon, PaperClipIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  categories: Array,
  expense: {
    type: Object,
    default: null
  }
})

const form = useForm({
  expense_date: props.expense?.expense_date || new Date().toISOString().split('T')[0],
  category_id: props.expense?.category_id || '',
  vendor_name: props.expense?.vendor_name || '',
  description: props.expense?.description || '',
  amount: props.expense?.amount || '',
  tax_amount: props.expense?.tax_amount || 0,
  payment_method: props.expense?.payment_method || 'cash',
  reference_number: props.expense?.reference_number || '',
  is_billable: props.expense?.is_billable || false,
  is_recurring: props.expense?.is_recurring || false,
  recurring_frequency: props.expense?.recurring_frequency || 'monthly',
  notes: props.expense?.notes || '',
  receipt: null,
})

const receiptPreview = ref(props.expense?.receipt_path ? `/storage/${props.expense.receipt_path}` : null)

function handleReceiptUpload(event) {
  const file = event.target.files[0]
  if (file) {
    form.receipt = file
    receiptPreview.value = URL.createObjectURL(file)
  }
}

function removeReceipt() {
  form.receipt = null
  receiptPreview.value = null
}

function submit() {
  if (props.expense) {
    form.post(route('admin.accounting.expenses.update', props.expense.id), {
      forceFormData: true,
      _method: 'PUT'
    })
  } else {
    form.post(route('admin.accounting.expenses.store'))
  }
}

const paymentMethods = [
  { value: 'cash', label: 'Cash' },
  { value: 'bkash', label: 'bKash' },
  { value: 'nagad', label: 'Nagad' },
  { value: 'rocket', label: 'Rocket' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
  { value: 'card', label: 'Card' },
  { value: 'check', label: 'Check' },
]
</script>

<template>
  <AdminLayout :title="expense ? 'Edit Expense' : 'Add Expense'">
    <Head :title="expense ? 'Edit Expense' : 'Add Expense'" />
    
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.expenses.index')"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ expense ? 'Edit Expense' : 'Add New Expense' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ expense ? 'Update expense details' : 'Record a new business expense' }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expense Details</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.expense_date"
                type="date"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <p v-if="form.errors.expense_date" class="mt-1 text-sm text-red-500">{{ form.errors.expense_date }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Category
              </label>
              <select
                v-model="form.category_id"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option value="">Select Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Vendor / Payee <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.vendor_name"
                type="text"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Who did you pay?"
              />
              <p v-if="form.errors.vendor_name" class="mt-1 text-sm text-red-500">{{ form.errors.vendor_name }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Payment Method
              </label>
              <select
                v-model="form.payment_method"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                  {{ method.label }}
                </option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Description <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.description"
                required
                rows="2"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="What was this expense for?"
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
            </div>
          </div>
        </div>

        <!-- Amount -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Amount</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Amount (৳) <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.amount"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="0.00"
              />
              <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">{{ form.errors.amount }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tax Amount (৳)
              </label>
              <input
                v-model="form.tax_amount"
                type="number"
                min="0"
                step="0.01"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="0.00"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Reference Number
              </label>
              <input
                v-model="form.reference_number"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Invoice/Receipt #"
              />
            </div>
          </div>
        </div>

        <!-- Receipt Upload -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Receipt</h2>
          
          <div v-if="receiptPreview" class="mb-4 relative inline-block">
            <img :src="receiptPreview" alt="Receipt preview" class="max-h-48 rounded-lg border" />
            <button
              type="button"
              @click="removeReceipt"
              class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
          
          <label class="flex items-center justify-center gap-2 p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition">
            <PaperClipIcon class="w-5 h-5 text-gray-400" />
            <span class="text-sm text-gray-500">{{ receiptPreview ? 'Change receipt' : 'Upload receipt (optional)' }}</span>
            <input type="file" accept="image/*,.pdf" class="hidden" @change="handleReceiptUpload" />
          </label>
        </div>

        <!-- Options -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Options</h2>
          
          <div class="space-y-4">
            <label class="flex items-center gap-3">
              <input
                v-model="form.is_billable"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">Billable to client</span>
            </label>
            
            <label class="flex items-center gap-3">
              <input
                v-model="form.is_recurring"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">Recurring expense</span>
            </label>
            
            <div v-if="form.is_recurring" class="ml-7">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Frequency
              </label>
              <select
                v-model="form.recurring_frequency"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
              >
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Notes
              </label>
              <textarea
                v-model="form.notes"
                rows="2"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Any additional notes..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.expenses.index')"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (expense ? 'Update Expense' : 'Save Expense') }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
