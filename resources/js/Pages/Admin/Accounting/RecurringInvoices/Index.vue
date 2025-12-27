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
  PencilIcon,
  PlayIcon,
  PauseIcon,
  StopIcon,
  ArrowPathIcon,
  CalendarDaysIcon,
  BanknotesIcon,
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  recurringInvoices: Object,
  filters: Object,
  stats: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')

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

function applyFilters() {
  router.get(route('admin.accounting.recurring-invoices.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
  }, { preserveState: true })
}

const debouncedApplyFilters = useDebounceFn(applyFilters, 300)

watch([search], () => debouncedApplyFilters())
watch([status], applyFilters)

function pause(id) {
  router.post(route('admin.accounting.recurring-invoices.pause', id))
}

function resume(id) {
  router.post(route('admin.accounting.recurring-invoices.resume', id))
}

function cancel(id) {
  if (confirm('Cancel this recurring invoice? No more invoices will be generated.')) {
    router.post(route('admin.accounting.recurring-invoices.cancel', id))
  }
}

function generate(id) {
  if (confirm('Generate an invoice now from this recurring template?')) {
    router.post(route('admin.accounting.recurring-invoices.generate', id))
  }
}
</script>

<template>
  <AdminLayout title="Recurring Invoices">
    <Head title="Recurring Invoices" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Recurring Invoices</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Automate subscription and recurring billing</p>
        </div>
        <Link
          :href="route('admin.accounting.recurring-invoices.create')"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="w-5 h-5" />
          Create Recurring Invoice
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
              <ArrowPathIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.active || 0 }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Active Recurring</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <BanknotesIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.monthly_revenue || 0) }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Revenue</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <DocumentDuplicateIcon class="w-5 h-5 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.generated_this_month || 0 }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Generated This Month</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
              <CalendarDaysIcon class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.due_this_week || 0 }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Due This Week</p>
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
              placeholder="Search recurring invoices..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            />
          </div>
          <select
            v-model="status"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Client</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Frequency</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Next Invoice</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Generated</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr 
                v-for="recurring in recurringInvoices.data" 
                :key="recurring.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
              >
                <td class="px-6 py-4">
                  <Link 
                    v-if="recurring.client"
                    :href="route('admin.accounting.clients.show', recurring.client.id)"
                    class="font-medium text-blue-600 hover:underline"
                  >
                    {{ recurring.client.name }}
                  </Link>
                  <span v-else class="text-gray-500">No client</span>
                  <p v-if="recurring.title" class="text-sm text-gray-500 dark:text-gray-400">{{ recurring.title }}</p>
                </td>
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ formatCurrency(recurring.amount) }}
                </td>
                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                  {{ frequencyLabels[recurring.frequency] }}
                </td>
                <td class="px-6 py-4">
                  <span v-if="recurring.next_invoice_date" class="text-gray-900 dark:text-white">
                    {{ formatDate(recurring.next_invoice_date) }}
                  </span>
                  <span v-else class="text-gray-500">-</span>
                </td>
                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                  {{ recurring.invoices_generated || 0 }}
                  <span v-if="recurring.max_invoices">/ {{ recurring.max_invoices }}</span>
                </td>
                <td class="px-6 py-4">
                  <span :class="[statusColors[recurring.status], 'px-2 py-1 text-xs font-medium rounded-full']">
                    {{ recurring.status }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.accounting.recurring-invoices.show', recurring.id)"
                      class="p-1 text-gray-400 hover:text-blue-600"
                    >
                      <EyeIcon class="w-5 h-5" />
                    </Link>
                    <Link
                      v-if="['active', 'paused'].includes(recurring.status)"
                      :href="route('admin.accounting.recurring-invoices.edit', recurring.id)"
                      class="p-1 text-gray-400 hover:text-gray-600"
                    >
                      <PencilIcon class="w-5 h-5" />
                    </Link>
                    <button
                      v-if="recurring.status === 'active'"
                      @click="generate(recurring.id)"
                      class="p-1 text-gray-400 hover:text-green-600"
                      title="Generate Now"
                    >
                      <DocumentDuplicateIcon class="w-5 h-5" />
                    </button>
                    <button
                      v-if="recurring.status === 'active'"
                      @click="pause(recurring.id)"
                      class="p-1 text-gray-400 hover:text-yellow-600"
                      title="Pause"
                    >
                      <PauseIcon class="w-5 h-5" />
                    </button>
                    <button
                      v-if="recurring.status === 'paused'"
                      @click="resume(recurring.id)"
                      class="p-1 text-gray-400 hover:text-green-600"
                      title="Resume"
                    >
                      <PlayIcon class="w-5 h-5" />
                    </button>
                    <button
                      v-if="['active', 'paused'].includes(recurring.status)"
                      @click="cancel(recurring.id)"
                      class="p-1 text-gray-400 hover:text-red-600"
                      title="Cancel"
                    >
                      <StopIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="recurringInvoices.data.length === 0" class="text-center py-12">
          <ArrowPathIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recurring invoices</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set up recurring billing for subscription services.</p>
        </div>

        <!-- Pagination -->
        <div v-if="recurringInvoices.links?.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Showing {{ recurringInvoices.from }} to {{ recurringInvoices.to }} of {{ recurringInvoices.total }}
            </p>
            <div class="flex gap-1">
              <Link
                v-for="link in recurringInvoices.links"
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
