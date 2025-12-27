<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  ArrowLeftIcon,
  PencilIcon,
  PlayIcon,
  PauseIcon,
  StopIcon,
  DocumentDuplicateIcon,
  CalendarDaysIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  recurring: Object,
  generatedInvoices: Array,
})

const { formatCurrency, formatDate, formatDateTime } = useBangladeshFormat()

const statusColors = {
  active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
}

const frequencyLabels = {
  weekly: 'Weekly',
  monthly: 'Monthly',
  quarterly: 'Quarterly',
  yearly: 'Yearly',
}

function pause() {
  router.post(route('admin.accounting.recurring-invoices.pause', props.recurring.id))
}

function resume() {
  router.post(route('admin.accounting.recurring-invoices.resume', props.recurring.id))
}

function cancel() {
  if (confirm('Cancel this recurring invoice? No more invoices will be generated.')) {
    router.post(route('admin.accounting.recurring-invoices.cancel', props.recurring.id))
  }
}

function generate() {
  if (confirm('Generate an invoice now from this recurring template?')) {
    router.post(route('admin.accounting.recurring-invoices.generate', props.recurring.id))
  }
}
</script>

<template>
  <AdminLayout title="Recurring Invoice Details">
    <Head :title="`Recurring Invoice - ${recurring.title || recurring.client?.name}`" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.accounting.recurring-invoices.index')"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ recurring.title || 'Recurring Invoice' }}
              </h1>
              <span :class="[statusColors[recurring.status], 'px-3 py-1 text-sm font-medium rounded-full capitalize']">
                {{ recurring.status }}
              </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400">
              {{ recurring.client?.name }} • {{ frequencyLabels[recurring.frequency] }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <Link
            v-if="['active', 'paused'].includes(recurring.status)"
            :href="route('admin.accounting.recurring-invoices.edit', recurring.id)"
            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
          >
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          
          <button
            v-if="recurring.status === 'active'"
            @click="generate"
            class="flex items-center gap-2 px-3 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400"
          >
            <DocumentDuplicateIcon class="w-4 h-4" />
            Generate Now
          </button>
          
          <button
            v-if="recurring.status === 'active'"
            @click="pause"
            class="flex items-center gap-2 px-3 py-2 text-sm text-yellow-700 bg-yellow-100 rounded-lg hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400"
          >
            <PauseIcon class="w-4 h-4" />
            Pause
          </button>
          
          <button
            v-if="recurring.status === 'paused'"
            @click="resume"
            class="flex items-center gap-2 px-3 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400"
          >
            <PlayIcon class="w-4 h-4" />
            Resume
          </button>
          
          <button
            v-if="['active', 'paused'].includes(recurring.status)"
            @click="cancel"
            class="flex items-center gap-2 px-3 py-2 text-sm text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400"
          >
            <StopIcon class="w-4 h-4" />
            Cancel
          </button>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
          <p class="text-blue-100 text-sm">Amount per Invoice</p>
          <p class="text-3xl font-bold mt-2">{{ formatCurrency(recurring.amount) }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center gap-3">
            <CalendarDaysIcon class="w-8 h-8 text-purple-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Next Invoice</p>
              <p class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ recurring.next_invoice_date ? formatDate(recurring.next_invoice_date) : 'N/A' }}
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center gap-3">
            <ClockIcon class="w-8 h-8 text-green-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Generated</p>
              <p class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ recurring.invoices_generated || 0 }}
                <span v-if="recurring.max_invoices" class="text-sm text-gray-500">/ {{ recurring.max_invoices }}</span>
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center gap-3">
            <DocumentDuplicateIcon class="w-8 h-8 text-orange-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
              <p class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ formatCurrency((recurring.invoices_generated || 0) * recurring.amount) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Schedule Details</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400">Frequency</dt>
              <dd class="text-gray-900 dark:text-white">{{ frequencyLabels[recurring.frequency] }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400">Start Date</dt>
              <dd class="text-gray-900 dark:text-white">{{ formatDate(recurring.start_date) }}</dd>
            </div>
            <div v-if="recurring.end_date">
              <dt class="text-sm text-gray-500 dark:text-gray-400">End Date</dt>
              <dd class="text-gray-900 dark:text-white">{{ formatDate(recurring.end_date) }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400">Auto Send</dt>
              <dd class="text-gray-900 dark:text-white">{{ recurring.auto_send ? 'Yes' : 'No' }}</dd>
            </div>
            <div v-if="recurring.last_generated_at">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Last Generated</dt>
              <dd class="text-gray-900 dark:text-white">{{ formatDateTime(recurring.last_generated_at) }}</dd>
            </div>
          </dl>
          
          <!-- Client -->
          <div v-if="recurring.client" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Client</h4>
            <Link
              :href="route('admin.accounting.clients.show', recurring.client.id)"
              class="text-blue-600 hover:underline font-medium"
            >
              {{ recurring.client.name }}
            </Link>
            <p v-if="recurring.client.email" class="text-sm text-gray-500 dark:text-gray-400">{{ recurring.client.email }}</p>
          </div>
        </div>

        <!-- Generated Invoices -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Generated Invoices</h3>
          
          <div v-if="generatedInvoices?.length > 0" class="space-y-3">
            <Link
              v-for="invoice in generatedInvoices"
              :key="invoice.id"
              :href="route('admin.accounting.invoices.show', invoice.id)"
              class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ invoice.invoice_number }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(invoice.invoice_date) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(invoice.total_amount) }}</p>
                <span :class="[
                  'text-xs px-2 py-1 rounded-full',
                  invoice.status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                  invoice.status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' :
                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                ]">
                  {{ invoice.status }}
                </span>
              </div>
            </Link>
          </div>
          
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            No invoices generated yet
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="recurring.notes" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Notes</h3>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ recurring.notes }}</p>
      </div>
    </div>
  </AdminLayout>
</template>
