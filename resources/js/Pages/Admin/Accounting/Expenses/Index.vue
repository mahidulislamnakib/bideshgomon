<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  BanknotesIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  EyeIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  DocumentTextIcon,
  FunnelIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  expenses: Object,
  categories: Array,
  filters: Object,
  stats: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const localFilters = ref({
  search: props.filters?.search || '',
  category_id: props.filters?.category_id || '',
  status: props.filters?.status || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
})

const debounceTimeout = ref(null)

function debouncedSearch() {
  clearTimeout(debounceTimeout.value)
  debounceTimeout.value = setTimeout(applyFilters, 300)
}

function applyFilters() {
  router.get(route('admin.accounting.expenses.index'), localFilters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  localFilters.value = { search: '', category_id: '', status: '', date_from: '', date_to: '' }
  applyFilters()
}

const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.category_id || localFilters.value.status || localFilters.value.date_from || localFilters.value.date_to
})

function deleteExpense(expense) {
  if (confirm(`Are you sure you want to delete this expense?`)) {
    router.delete(route('admin.accounting.expenses.destroy', expense.id))
  }
}

function approveExpense(expense) {
  router.post(route('admin.accounting.expenses.approve', expense.id))
}

function rejectExpense(expense) {
  router.post(route('admin.accounting.expenses.reject', expense.id))
}

function markPaid(expense) {
  router.post(route('admin.accounting.expenses.mark-paid', expense.id))
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
</script>

<template>
  <AdminLayout title="Expenses">
    <Head title="Expenses - Accounting" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Expenses</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Track and manage all business expenses
          </p>
        </div>
        
        <Link
          :href="route('admin.accounting.expenses.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="w-4 h-4" />
          Add Expense
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.total || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Pending</p>
          <p class="mt-1 text-2xl font-bold text-yellow-600">{{ stats?.pending || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Approved</p>
          <p class="mt-1 text-2xl font-bold text-blue-600">{{ stats?.approved || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Paid</p>
          <p class="mt-1 text-2xl font-bold text-green-600">{{ stats?.paid || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total Amount</p>
          <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.total_amount || 0) }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="localFilters.search"
                type="text"
                placeholder="Search expenses..."
                class="w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                @input="debouncedSearch"
              />
            </div>
          </div>
          <select
            v-model="localFilters.category_id"
            @change="applyFilters"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <select
            v-model="localFilters.status"
            @change="applyFilters"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="paid">Paid</option>
          </select>
          <input
            v-model="localFilters.date_from"
            type="date"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
            @change="applyFilters"
          />
          <input
            v-model="localFilters.date_to"
            type="date"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
            @change="applyFilters"
          />
          <button
            v-if="hasFilters"
            @click="clearFilters"
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            Clear filters
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ expense.expense_number }}</p>
                    <p class="text-sm text-gray-500 truncate max-w-xs">{{ expense.description }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    v-if="expense.category" 
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium"
                    :style="{ backgroundColor: expense.category.color + '20', color: expense.category.color }"
                  >
                    {{ expense.category.name }}
                  </span>
                  <span v-else class="text-gray-400">Uncategorized</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(expense.expense_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900 dark:text-white">
                  {{ formatCurrency(expense.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span :class="[getStatusColor(expense.status), 'px-2 py-1 rounded-full text-xs font-medium capitalize']">
                    {{ expense.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex justify-end gap-1">
                    <button
                      v-if="expense.status === 'pending'"
                      @click="approveExpense(expense)"
                      class="p-1.5 text-green-500 hover:bg-green-50 rounded"
                      title="Approve"
                    >
                      <CheckCircleIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="expense.status === 'pending'"
                      @click="rejectExpense(expense)"
                      class="p-1.5 text-red-500 hover:bg-red-50 rounded"
                      title="Reject"
                    >
                      <XCircleIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="expense.status === 'approved'"
                      @click="markPaid(expense)"
                      class="p-1.5 text-blue-500 hover:bg-blue-50 rounded"
                      title="Mark as Paid"
                    >
                      <BanknotesIcon class="w-4 h-4" />
                    </button>
                    <Link
                      :href="route('admin.accounting.expenses.show', expense.id)"
                      class="p-1.5 text-gray-400 hover:text-blue-600 rounded"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </Link>
                    <Link
                      :href="route('admin.accounting.expenses.edit', expense.id)"
                      class="p-1.5 text-gray-400 hover:text-amber-600 rounded"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </Link>
                    <button
                      @click="deleteExpense(expense)"
                      class="p-1.5 text-gray-400 hover:text-red-600 rounded"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!expenses.data?.length">
                <td colspan="6" class="px-6 py-12 text-center">
                  <BanknotesIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No expenses found</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by adding your first expense.</p>
                  <Link
                    :href="route('admin.accounting.expenses.create')"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
                  >
                    <PlusIcon class="w-4 h-4" />
                    Add Expense
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="expenses.links?.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">
              Showing {{ expenses.from }} to {{ expenses.to }} of {{ expenses.total }} expenses
            </p>
            <div class="flex gap-1">
              <Link
                v-for="link in expenses.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700',
                  !link.url ? 'opacity-50 cursor-not-allowed' : ''
                ]"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
