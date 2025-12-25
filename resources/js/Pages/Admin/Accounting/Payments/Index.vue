<template>
  <AdminLayout title="Payments">
    <div class="space-y-8">
      <!-- Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500 p-8">
        <div class="absolute inset-0 bg-grid-white/10"></div>
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                <BanknotesIcon class="w-6 h-6 text-white" />
              </div>
              <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium text-white">
                Payment Management
              </span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Payments</h1>
            <p class="text-emerald-100 max-w-xl">Track all payment transactions, analyze collection trends, and manage your revenue streams efficiently.</p>
          </div>
          
          <div class="flex items-center gap-3">
            <button
              @click="exportPayments"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 backdrop-blur-sm text-white rounded-xl hover:bg-white/30 transition-all duration-200 font-medium"
            >
              <ArrowDownTrayIcon class="w-5 h-5" />
              Export Data
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg shadow-emerald-500/20">
                <BanknotesIcon class="w-6 h-6 text-white" />
              </div>
              <span class="flex items-center gap-1 text-emerald-600 text-sm font-medium">
                <ArrowTrendingUpIcon class="w-4 h-4" />
                +12.5%
              </span>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Collected</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats.totalCollected) }}</p>
          </div>
        </div>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/20">
                <CalendarDaysIcon class="w-6 h-6 text-white" />
              </div>
              <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">This Month</span>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Monthly Revenue</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats.thisMonth) }}</p>
          </div>
        </div>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-violet-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl shadow-lg shadow-violet-500/20">
                <ReceiptPercentIcon class="w-6 h-6 text-white" />
              </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Transactions</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.totalCount?.toLocaleString() || 0 }}</p>
          </div>
        </div>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg shadow-amber-500/20">
                <ChartBarIcon class="w-6 h-6 text-white" />
              </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Average Payment</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats.average) }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Payments Table -->
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <!-- Filters -->
          <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-4">
              <div class="flex-1 relative">
                <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search by invoice, client, or transaction ID..."
                  class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition-all"
                />
              </div>
              <div class="flex flex-wrap gap-3">
                <select
                  v-model="filters.method"
                  class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 min-w-[140px]"
                >
                  <option value="">All Methods</option>
                  <option value="bkash">bKash</option>
                  <option value="nagad">Nagad</option>
                  <option value="rocket">Rocket</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="check">Check</option>
                </select>
                <input
                  v-model="filters.date_from"
                  type="date"
                  class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
                <input
                  v-model="filters.date_to"
                  type="date"
                  class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
                <button
                  @click="applyFilters"
                  class="px-5 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors text-sm font-medium"
                >
                  Apply
                </button>
                <button
                  v-if="hasFilters"
                  @click="clearFilters"
                  class="px-5 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors text-sm"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-900/50">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr
                  v-for="payment in payments.data"
                  :key="payment.id"
                  class="group hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(payment.payment_date) }}</div>
                    <div class="text-xs text-gray-500">{{ formatTime(payment.payment_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link
                      :href="route('admin.accounting.invoices.show', payment.invoice_id)"
                      class="inline-flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors"
                    >
                      <DocumentTextIcon class="w-4 h-4" />
                      {{ payment.invoice?.invoice_number || `#${payment.invoice_id}` }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                          {{ getInitials(payment.invoice?.client_name || payment.invoice?.client?.name) }}
                        </span>
                      </div>
                      <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ payment.invoice?.client_name || payment.invoice?.client?.name || 'N/A' }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="methodBadgeClass(payment.payment_method)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg">
                      <component :is="methodIcon(payment.payment_method)" class="w-3.5 h-3.5" />
                      {{ methodLabel(payment.payment_method) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="text-sm font-bold text-emerald-600">{{ formatCurrency(payment.amount) }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Link
                        :href="route('admin.accounting.payments.show', payment.id)"
                        class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-all"
                        title="View Details"
                      >
                        <EyeIcon class="w-4 h-4" />
                      </Link>
                      <button
                        @click="confirmDelete(payment)"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                        title="Delete"
                      >
                        <TrashIcon class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Empty State -->
            <div v-if="payments.data.length === 0" class="py-16 text-center">
              <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl flex items-center justify-center">
                <BanknotesIcon class="w-10 h-10 text-gray-400" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No payments found</h3>
              <p class="text-gray-500 max-w-sm mx-auto">Payments will appear here once you record them from invoice pages.</p>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="payments.links?.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">
                Showing <span class="font-medium text-gray-900 dark:text-white">{{ payments.from }}</span> to 
                <span class="font-medium text-gray-900 dark:text-white">{{ payments.to }}</span> of 
                <span class="font-medium text-gray-900 dark:text-white">{{ payments.total }}</span> payments
              </p>
              <div class="flex gap-1">
                <Link
                  v-for="link in payments.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  :class="[
                    'px-3 py-2 text-sm rounded-lg transition-all',
                    link.active 
                      ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' 
                      : link.url 
                        ? 'text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700' 
                        : 'text-gray-300 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Payment Methods Breakdown -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Methods</h3>
              <ChartPieIcon class="w-5 h-5 text-gray-400" />
            </div>
            <div class="space-y-4">
              <div
                v-for="method in methodBreakdown"
                :key="method.method"
                class="group"
              >
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-3">
                    <div :class="methodIconBgClass(method.method)" class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm">
                      <component :is="methodIcon(method.method)" class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ methodLabel(method.method) }}</p>
                      <p class="text-xs text-gray-500">{{ method.count }} transactions</p>
                    </div>
                  </div>
                  <p class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(method.total) }}</p>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                  <div 
                    :class="methodBarClass(method.method)"
                    class="h-full rounded-full transition-all duration-500 group-hover:opacity-80"
                    :style="{ width: `${getMethodPercentage(method.total)}%` }"
                  ></div>
                </div>
              </div>
              
              <div v-if="!methodBreakdown?.length" class="text-center py-8">
                <p class="text-sm text-gray-500">No payment data available</p>
              </div>
            </div>
          </div>

          <!-- Recent Payments -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
              <ClockIcon class="w-5 h-5 text-gray-400" />
            </div>
            <div class="space-y-4">
              <div
                v-for="payment in recentPayments"
                :key="payment.id"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
              >
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                  <CheckIcon class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ payment.invoice?.client_name || 'Client' }}
                  </p>
                  <p class="text-xs text-gray-500">{{ formatDate(payment.payment_date) }}</p>
                </div>
                <span class="text-sm font-bold text-emerald-600">{{ formatCurrency(payment.amount) }}</span>
              </div>
              
              <div v-if="!recentPayments?.length" class="text-center py-8">
                <p class="text-sm text-gray-500">No recent payments</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  BanknotesIcon, 
  CalendarDaysIcon,
  ChartBarIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  TrashIcon,
  DevicePhoneMobileIcon,
  BuildingLibraryIcon,
  CurrencyDollarIcon,
  DocumentCheckIcon,
  CreditCardIcon,
  MagnifyingGlassIcon,
  DocumentTextIcon,
  ChartPieIcon,
  ClockIcon,
  CheckIcon,
  ArrowTrendingUpIcon,
  ReceiptPercentIcon,
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  payments: Object,
  stats: Object,
  methodBreakdown: Array,
  recentPayments: Array,
  filters: Object,
})

const { formatCurrency, formatDate, formatTime } = useBangladeshFormat()

const filters = ref({
  search: props.filters?.search || '',
  method: props.filters?.method || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
})

const hasFilters = computed(() => {
  return filters.value.search || filters.value.method || filters.value.date_from || filters.value.date_to
})

const totalMethodAmount = computed(() => {
  return props.methodBreakdown?.reduce((sum, m) => sum + parseFloat(m.total || 0), 0) || 1
})

function getMethodPercentage(total) {
  return Math.round((parseFloat(total || 0) / totalMethodAmount.value) * 100)
}

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const methodLabels = {
  bkash: 'bKash',
  nagad: 'Nagad',
  rocket: 'Rocket',
  bank_transfer: 'Bank Transfer',
  cash: 'Cash',
  card: 'Card',
  check: 'Check',
  other: 'Other',
}

const methodBadgeClasses = {
  bkash: 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
  nagad: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
  rocket: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  bank_transfer: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  cash: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
  card: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
  check: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  other: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
}

const methodIconBgClasses = {
  bkash: 'bg-gradient-to-br from-pink-500 to-pink-600',
  nagad: 'bg-gradient-to-br from-orange-500 to-orange-600',
  rocket: 'bg-gradient-to-br from-purple-500 to-purple-600',
  bank_transfer: 'bg-gradient-to-br from-blue-500 to-blue-600',
  cash: 'bg-gradient-to-br from-emerald-500 to-emerald-600',
  card: 'bg-gradient-to-br from-indigo-500 to-indigo-600',
  check: 'bg-gradient-to-br from-amber-500 to-amber-600',
  other: 'bg-gradient-to-br from-gray-500 to-gray-600',
}

const methodBarClasses = {
  bkash: 'bg-gradient-to-r from-pink-500 to-pink-400',
  nagad: 'bg-gradient-to-r from-orange-500 to-orange-400',
  rocket: 'bg-gradient-to-r from-purple-500 to-purple-400',
  bank_transfer: 'bg-gradient-to-r from-blue-500 to-blue-400',
  cash: 'bg-gradient-to-r from-emerald-500 to-emerald-400',
  card: 'bg-gradient-to-r from-indigo-500 to-indigo-400',
  check: 'bg-gradient-to-r from-amber-500 to-amber-400',
  other: 'bg-gradient-to-r from-gray-500 to-gray-400',
}

function methodLabel(method) {
  return methodLabels[method] || method
}

function methodBadgeClass(method) {
  return methodBadgeClasses[method] || methodBadgeClasses.other
}

function methodIconBgClass(method) {
  return methodIconBgClasses[method] || methodIconBgClasses.other
}

function methodBarClass(method) {
  return methodBarClasses[method] || methodBarClasses.other
}

function methodIcon(method) {
  const icons = {
    bkash: DevicePhoneMobileIcon,
    nagad: DevicePhoneMobileIcon,
    rocket: DevicePhoneMobileIcon,
    bank_transfer: BuildingLibraryIcon,
    cash: CurrencyDollarIcon,
    card: CreditCardIcon,
    check: DocumentCheckIcon,
  }
  return icons[method] || CurrencyDollarIcon
}

function applyFilters() {
  router.get(route('admin.accounting.payments.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  filters.value = { search: '', method: '', date_from: '', date_to: '' }
  applyFilters()
}

function confirmDelete(payment) {
  if (confirm(`Are you sure you want to delete this payment of ${formatCurrency(payment.amount)}? This action cannot be undone.`)) {
    router.delete(route('admin.accounting.payments.destroy', payment.id))
  }
}

function exportPayments() {
  window.open(route('admin.accounting.reports.export', { type: 'payments', ...filters.value }), '_blank')
}
</script>

<style scoped>
.bg-grid-white\/10 {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
</style>
