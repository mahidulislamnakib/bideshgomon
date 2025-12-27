<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { computed, watch } from 'vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  clients: Array,
  invoices: Array,
  reasons: Object,
})

const { formatCurrency } = useBangladeshFormat()

const form = useForm({
  client_id: '',
  invoice_id: '',
  amount: '',
  reason: 'refund',
  description: '',
  credit_note_date: new Date().toISOString().split('T')[0],
  status: 'draft',
})

const selectedInvoice = computed(() => {
  if (!form.invoice_id) return null
  return props.invoices?.find(i => i.id == form.invoice_id)
})

const maxAmount = computed(() => {
  if (selectedInvoice.value) {
    return selectedInvoice.value.total_amount - (selectedInvoice.value.credit_applied || 0)
  }
  return null
})

// Filter invoices by selected client
const filteredInvoices = computed(() => {
  if (!form.client_id) return props.invoices || []
  return (props.invoices || []).filter(i => i.client_id == form.client_id)
})

// Reset invoice when client changes
watch(() => form.client_id, () => {
  form.invoice_id = ''
})

// Set max amount when invoice selected
watch(() => form.invoice_id, () => {
  if (maxAmount.value && !form.amount) {
    form.amount = maxAmount.value
  }
})

function submit() {
  form.post(route('admin.accounting.credit-notes.store'))
}
</script>

<template>
  <AdminLayout title="Create Credit Note">
    <Head title="Create Credit Note" />
    
    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.credit-notes.index')"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Credit Note</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Issue a refund or credit to a client</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
          <!-- Client -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Client
            </label>
            <select
              v-model="form.client_id"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            >
              <option value="">Select a client (optional)</option>
              <option v-for="client in clients" :key="client.id" :value="client.id">
                {{ client.name }}
              </option>
            </select>
            <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-500">{{ form.errors.client_id }}</p>
          </div>

          <!-- Invoice -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Related Invoice
            </label>
            <select
              v-model="form.invoice_id"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            >
              <option value="">Select an invoice (optional)</option>
              <option v-for="invoice in filteredInvoices" :key="invoice.id" :value="invoice.id">
                {{ invoice.invoice_number }} - {{ formatCurrency(invoice.total_amount) }}
              </option>
            </select>
            <p v-if="form.errors.invoice_id" class="mt-1 text-sm text-red-500">{{ form.errors.invoice_id }}</p>
          </div>

          <!-- Selected Invoice Info -->
          <div v-if="selectedInvoice" class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Selected Invoice</p>
            <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
              <div>
                <span class="text-blue-600 dark:text-blue-400">Invoice Total:</span>
                <span class="ml-1 text-blue-900 dark:text-blue-300">{{ formatCurrency(selectedInvoice.total_amount) }}</span>
              </div>
              <div>
                <span class="text-blue-600 dark:text-blue-400">Max Credit:</span>
                <span class="ml-1 text-blue-900 dark:text-blue-300">{{ formatCurrency(maxAmount) }}</span>
              </div>
            </div>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Credit Amount (৳) <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.amount"
              type="number"
              min="0"
              step="0.01"
              :max="maxAmount"
              required
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="0.00"
            />
            <p v-if="maxAmount" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Maximum allowed: {{ formatCurrency(maxAmount) }}
            </p>
            <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">{{ form.errors.amount }}</p>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Reason <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.reason"
              required
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            >
              <option v-for="(label, value) in reasons" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-500">{{ form.errors.reason }}</p>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Credit Note Date <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.credit_note_date"
              type="date"
              required
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            />
            <p v-if="form.errors.credit_note_date" class="mt-1 text-sm text-red-500">{{ form.errors.credit_note_date }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="Additional details about this credit note..."
            ></textarea>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Status
            </label>
            <div class="flex gap-4">
              <label class="flex items-center gap-2">
                <input
                  v-model="form.status"
                  type="radio"
                  value="draft"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Save as Draft</span>
              </label>
              <label class="flex items-center gap-2">
                <input
                  v-model="form.status"
                  type="radio"
                  value="pending"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Submit for Approval</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.credit-notes.index')"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Credit Note' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
