<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AnimatedStat from '@/Components/ui/AnimatedStat.vue'
import InvoiceStatusBadge from '@/Components/Accounting/InvoiceStatusBadge.vue'
import RevenueChart from '@/Components/Accounting/RevenueChart.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  CurrencyDollarIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  ArrowTrendingUpIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  PlusIcon,
  ChartBarIcon,
  BanknotesIcon,
  ReceiptPercentIcon,
  CalendarDaysIcon,
  UserGroupIcon,
  BuildingLibraryIcon,
  DevicePhoneMobileIcon,
  CreditCardIcon,
  DocumentCheckIcon,
  ArrowRightIcon,
  EyeIcon,
  ChartPieIcon,
} from '@heroicons/vue/24/outline'
import {
  ChartBarIcon as ChartBarIconSolid,
} from '@heroicons/vue/24/solid'

const props = defineProps({
  stats: Object,
  monthlyRevenue: Array,
  topClients: Array,
  paymentMethods: Array,
  agingReport: Object,
  recentInvoices: Array,
  recentPayments: Array,
  year: Number,
  month: Number,
  availableYears: Array,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const selectedYear = ref(props.year)
const selectedMonth = ref(props.month)

// Current time
const currentTime = ref(new Date())
onMounted(() => {
  setInterval(() => {
    currentTime.value = new Date()
  }, 1000)
})

const greeting = computed(() => {
  const hour = currentTime.value.getHours()
  if (hour < 12) return 'Good Morning'
  if (hour < 18) return 'Good Afternoon'
  return 'Good Evening'
})

const formattedTime = computed(() => {
  return currentTime.value.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })
})

const formattedDate = computed(() => {
  return currentTime.value.toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
})

const monthName = computed(() => {
  return new Date(2024, props.month - 1).toLocaleString('en-US', { month: 'long' })
})

const collectionRate = computed(() => {
  if (!props.stats?.monthly?.total_amount) return 0
  return Math.round((props.stats.monthly.paid_amount / props.stats.monthly.total_amount) * 100)
})

function getMonthName(m) {
  return new Date(2024, m - 1).toLocaleString('en-US', { month: 'short' })
}

function updatePeriod() {
  router.get(route('admin.accounting.dashboard'), {
    year: selectedYear.value,
    month: selectedMonth.value,
  }, { preserveState: true })
}

// Max values for charts
const maxRevenue = computed(() => {
  if (!props.monthlyRevenue?.length) return 1
  return Math.max(...props.monthlyRevenue.map(d => d.total), 1)
})

const totalPayments = computed(() => {
  if (!props.paymentMethods?.length) return 0
  return props.paymentMethods.reduce((sum, m) => sum + parseFloat(m.total || 0), 0)
})

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

const methodColors = {
  bkash: { bg: '#fce7f3', text: '#db2777', bar: 'from-pink-500 to-pink-600' },
  nagad: { bg: '#ffedd5', text: '#ea580c', bar: 'from-orange-500 to-orange-600' },
  rocket: { bg: '#f3e8ff', text: '#9333ea', bar: 'from-purple-500 to-purple-600' },
  bank_transfer: { bg: '#dbeafe', text: '#2563eb', bar: 'from-blue-500 to-blue-600' },
  cash: { bg: '#dcfce7', text: '#16a34a', bar: 'from-emerald-500 to-emerald-600' },
  card: { bg: '#e0e7ff', text: '#4f46e5', bar: 'from-indigo-500 to-indigo-600' },
  check: { bg: '#fef3c7', text: '#d97706', bar: 'from-amber-500 to-amber-600' },
  other: { bg: '#f3f4f6', text: '#6b7280', bar: 'from-gray-500 to-gray-600' },
}

function getMethodLabel(method) {
  return methodLabels[method] || method
}

function getMethodColor(method) {
  return methodColors[method] || methodColors.other
}

function getMethodIcon(method) {
  const icons = {
    bkash: DevicePhoneMobileIcon,
    nagad: DevicePhoneMobileIcon,
    rocket: DevicePhoneMobileIcon,
    bank_transfer: BuildingLibraryIcon,
    cash: BanknotesIcon,
    card: CreditCardIcon,
    check: DocumentCheckIcon,
  }
  return icons[method] || CurrencyDollarIcon
}

function formatTimeAgo(date) {
  const now = new Date()
  const past = new Date(date)
  const diff = Math.floor((now - past) / 1000)

  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}
</script>

<template>
  <Head title="Accounting Dashboard" />

  <AdminLayout>
    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
      <!-- Modern Header -->
      <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
        <!-- Animated background pattern -->
        <div class="absolute inset-0 opacity-10">
          <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <!-- Gradient orbs -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-emerald-500/30 to-emerald-600/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-blue-500/30 to-blue-400/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left: Greeting -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                  <ChartBarIconSolid class="h-6 w-6 text-white" />
                </div>
                <span class="text-sm font-medium text-gray-300 uppercase tracking-wider">Accounting Dashboard</span>
              </div>
              <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2">{{ greeting }}, Admin</h1>
              <p class="text-sm sm:text-base text-gray-300">Financial overview for {{ monthName }} {{ year }}</p>
            </div>

            <!-- Right: Time & Period Selector -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 flex-shrink-0">
              <!-- Period Selector -->
              <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 border border-white/20">
                <div class="flex items-center gap-2">
                  <CalendarDaysIcon class="h-5 w-5 text-gray-300 flex-shrink-0" />
                  <select
                    v-model="selectedMonth"
                    @change="updatePeriod"
                    class="bg-transparent text-white border-0 text-sm focus:ring-0 cursor-pointer pr-6"
                  >
                    <option v-for="m in 12" :key="m" :value="m" class="text-gray-900">{{ getMonthName(m) }}</option>
                  </select>
                  <select
                    v-model="selectedYear"
                    @change="updatePeriod"
                    class="bg-transparent text-white border-0 text-sm focus:ring-0 cursor-pointer pr-6"
                  >
                    <option v-for="y in availableYears" :key="y" :value="y" class="text-gray-900">{{ y }}</option>
                  </select>
                </div>
              </div>

              <!-- Monthly Revenue Highlight -->
              <div class="bg-emerald-500/20 backdrop-blur-sm rounded-2xl px-4 py-3 border border-emerald-400/30 min-w-[180px]">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-emerald-500/30 rounded-2xl flex-shrink-0">
                    <ArrowTrendingUpIcon class="h-5 w-5 text-emerald-300" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xl sm:text-2xl font-bold text-white tabular-nums whitespace-nowrap">{{ formatCurrency(stats?.monthly?.total_amount || 0) }}</p>
                    <p class="text-xs sm:text-sm text-emerald-300">Monthly Revenue</p>
                  </div>
                </div>
              </div>

              <!-- New Invoice Button -->
              <Link
                :href="route('admin.accounting.invoices.create')"
                class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white text-gray-900 rounded-2xl hover:bg-gray-100 transition-all font-semibold shadow-lg"
              >
                <PlusIcon class="w-5 h-5" />
                <span class="hidden sm:inline">New Invoice</span>
              </Link>
            </div>
          </div>

          <!-- Collection Rate Banner -->
          <div v-if="collectionRate > 0" class="mt-6 flex flex-wrap gap-3">
            <div class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm border bg-emerald-500/20 text-emerald-200 border-emerald-400/30">
              <CheckCircleIcon class="h-4 w-4" />
              {{ collectionRate }}% collection rate this month
            </div>
            <div v-if="stats?.overdue?.count > 0" class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm border bg-red-500/20 text-red-200 border-red-400/30">
              <ExclamationTriangleIcon class="h-4 w-4" />
              {{ stats.overdue.count }} overdue invoices need attention
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Primary Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          <!-- Monthly Revenue -->
          <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Monthly Revenue</p>
                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="stats?.monthly?.total_amount || 0" prefix="৳" :duration="1500" />
                </p>
                <div class="mt-2 flex items-center gap-1">
                  <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ stats?.monthly?.total_invoices || 0 }} invoices</span>
                </div>
              </div>
              <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <CurrencyDollarIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
              <p class="text-xs text-neutral-500 dark:text-neutral-400">
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ formatCurrency(stats?.yearly?.total_amount || 0) }}</span> yearly total
              </p>
            </div>
          </div>

          <!-- Collected -->
          <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-blue-200 dark:hover:border-blue-800 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Collected</p>
                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="stats?.monthly?.paid_amount || 0" prefix="৳" :duration="1500" :delay="200" />
                </p>
                <div class="mt-2 flex items-center gap-1">
                  <span class="inline-flex items-center gap-0.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                    <ArrowUpIcon class="h-3 w-3" />
                    {{ collectionRate }}%
                  </span>
                  <span class="text-sm text-neutral-500 dark:text-neutral-400">rate</span>
                </div>
              </div>
              <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <CheckCircleIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
              <p class="text-xs text-neutral-500 dark:text-neutral-400">
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(stats?.yearly?.paid_amount || 0) }}</span> yearly collected
              </p>
            </div>
          </div>

          <!-- Outstanding -->
          <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-amber-200 dark:hover:border-amber-800 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Outstanding</p>
                <p class="mt-2 text-2xl md:text-3xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="stats?.outstanding?.amount || 0" prefix="৳" :duration="1500" :delay="400" />
                </p>
                <div class="mt-2 flex items-center gap-1">
                  <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ stats?.outstanding?.count || 0 }} invoices</span>
                </div>
              </div>
              <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <ClockIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
              <Link :href="route('admin.accounting.invoices.index', { status: 'sent' })" class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                View pending invoices →
              </Link>
            </div>
          </div>

          <!-- Overdue -->
          <div class="group relative bg-white dark:bg-neutral-800 rounded-2xl p-5 md:p-6 shadow-card border border-neutral-100 dark:border-neutral-700 hover:shadow-card-hover hover:border-red-200 dark:hover:border-red-800 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Overdue</p>
                <p class="mt-2 text-2xl md:text-3xl font-bold text-red-600 dark:text-red-400">
                  <AnimatedStat :value="stats?.overdue?.amount || 0" prefix="৳" :duration="1500" :delay="600" />
                </p>
                <div class="mt-2 flex items-center gap-1">
                  <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ stats?.overdue?.count || 0 }} invoices</span>
                </div>
              </div>
              <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <ExclamationTriangleIcon class="h-5 w-5 md:h-6 md:w-6 text-white" />
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700">
              <Link :href="route('admin.accounting.invoices.index', { status: 'overdue' })" class="text-xs text-red-600 hover:text-red-700 font-medium">
                View overdue invoices →
              </Link>
            </div>
          </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
          <!-- Current (Not Due) -->
          <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-2xl" style="background-color: #dcfce7;">
                <CheckCircleIcon class="h-5 w-5" style="color: #16a34a;" />
              </div>
              <div>
                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="agingReport?.current || 0" :duration="1000" />
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Current</p>
              </div>
            </div>
          </div>

          <!-- 1-30 Days -->
          <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-2xl" style="background-color: #fef9c3;">
                <ClockIcon class="h-5 w-5" style="color: #ca8a04;" />
              </div>
              <div>
                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="agingReport?.['1_30_days'] || 0" :duration="1000" :delay="100" />
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">1-30 Days</p>
              </div>
            </div>
          </div>

          <!-- 31-60 Days -->
          <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-2xl" style="background-color: #ffedd5;">
                <ClockIcon class="h-5 w-5" style="color: #ea580c;" />
              </div>
              <div>
                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="agingReport?.['31_60_days'] || 0" :duration="1000" :delay="200" />
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">31-60 Days</p>
              </div>
            </div>
          </div>

          <!-- 61-90 Days -->
          <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-2xl" style="background-color: #fee2e2;">
                <ExclamationTriangleIcon class="h-5 w-5" style="color: #dc2626;" />
              </div>
              <div>
                <p class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-white">
                  <AnimatedStat :value="agingReport?.['61_90_days'] || 0" :duration="1000" :delay="300" />
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">61-90 Days</p>
              </div>
            </div>
          </div>

          <!-- Over 90 Days -->
          <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-2xl" style="background-color: #fecaca;">
                <ExclamationTriangleIcon class="h-5 w-5" style="color: #991b1b;" />
              </div>
              <div>
                <p class="text-xl md:text-2xl font-bold text-red-600 dark:text-red-400">
                  <AnimatedStat :value="agingReport?.over_90_days || 0" :duration="1000" :delay="400" />
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">90+ Days</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts & Activities Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Revenue Chart -->
          <div class="lg:col-span-2 bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
            <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-2xl" style="background-color: #dcfce7;">
                  <ChartBarIcon class="h-4 w-4" style="color: #16a34a;" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Revenue Trend</h3>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">Monthly comparison for {{ year }}</p>
                </div>
              </div>
              <Link :href="route('admin.accounting.reports.index')" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                View Reports <ArrowRightIcon class="w-3 h-3" />
              </Link>
            </div>
            <div class="p-5 md:p-6">
              <div v-if="monthlyRevenue && monthlyRevenue.length > 0" class="h-64">
                <RevenueChart :data="monthlyRevenue" />
              </div>
              <div v-else class="text-center py-12 text-neutral-500 dark:text-neutral-400">
                <ChartBarIcon class="h-12 w-12 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No revenue data available</p>
              </div>
            </div>
          </div>

          <!-- Payment Methods -->
          <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
            <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-2xl" style="background-color: #dbeafe;">
                  <ChartPieIcon class="h-4 w-4" style="color: #2563eb;" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Payment Methods</h3>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">Distribution</p>
                </div>
              </div>
            </div>
            <div class="p-5 md:p-6">
              <div v-if="paymentMethods && paymentMethods.length > 0" class="space-y-4">
                <div
                  v-for="method in paymentMethods"
                  :key="method.payment_method"
                  class="group"
                >
                  <div class="flex items-center justify-between text-sm mb-2">
                    <div class="flex items-center gap-2">
                      <div class="p-1.5 rounded-lg" :style="{ backgroundColor: getMethodColor(method.payment_method).bg }">
                        <component :is="getMethodIcon(method.payment_method)" class="h-4 w-4" :style="{ color: getMethodColor(method.payment_method).text }" />
                      </div>
                      <span class="text-neutral-700 dark:text-neutral-300 font-medium">{{ getMethodLabel(method.payment_method) }}</span>
                    </div>
                    <span class="font-semibold text-neutral-900 dark:text-white tabular-nums">{{ formatCurrency(method.total) }}</span>
                  </div>
                  <div class="h-2 bg-neutral-100 dark:bg-neutral-700 rounded-full overflow-hidden">
                    <div
                      :class="['h-full rounded-full transition-all duration-700 ease-out bg-gradient-to-r', getMethodColor(method.payment_method).bar]"
                      :style="{ width: `${totalPayments > 0 ? (method.total / totalPayments) * 100 : 0}%` }"
                    ></div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                <ChartPieIcon class="h-12 w-12 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No payment data available</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Invoices -->
          <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
            <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-2xl" style="background-color: #e0e7ff;">
                  <DocumentTextIcon class="h-4 w-4" style="color: #4f46e5;" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent Invoices</h3>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">Latest activity</p>
                </div>
              </div>
              <Link :href="route('admin.accounting.invoices.index')" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                View all <ArrowRightIcon class="w-3 h-3" />
              </Link>
            </div>
            <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
              <div
                v-for="invoice in recentInvoices"
                :key="invoice.id"
                class="px-5 md:px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1 min-w-0">
                    <Link
                      :href="route('admin.accounting.invoices.show', invoice.id)"
                      class="text-sm font-semibold text-neutral-900 dark:text-white hover:text-blue-600 transition-colors"
                    >
                      {{ invoice.invoice_number }}
                    </Link>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                      {{ invoice.client?.name || invoice.client_name }}
                    </p>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm font-bold text-neutral-900 dark:text-white tabular-nums">
                      {{ formatCurrency(invoice.total_amount) }}
                    </p>
                    <InvoiceStatusBadge :status="invoice.status" size="xs" />
                  </div>
                </div>
              </div>
              <div v-if="!recentInvoices?.length" class="px-5 md:px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                <DocumentTextIcon class="h-10 w-10 mx-auto mb-2 opacity-30" />
                <p class="text-sm">No invoices yet</p>
              </div>
            </div>
          </div>

          <!-- Top Clients -->
          <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 overflow-hidden">
            <div class="px-5 md:px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-2xl" style="background-color: #fef3c7;">
                  <UserGroupIcon class="h-4 w-4" style="color: #d97706;" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Top Clients</h3>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">By revenue</p>
                </div>
              </div>
            </div>
            <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
              <div
                v-for="(client, index) in topClients"
                :key="client.client_id"
                class="px-5 md:px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-full"
                    :class="[
                      index === 0 ? 'bg-amber-100 text-amber-700' :
                      index === 1 ? 'bg-gray-100 text-gray-600' :
                      index === 2 ? 'bg-orange-100 text-orange-700' :
                      'bg-neutral-100 text-neutral-500'
                    ]"
                  >
                    {{ index + 1 }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">
                      {{ client.client?.name }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                      {{ client.invoice_count }} invoices
                    </p>
                  </div>
                  <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    {{ formatCurrency(client.total_revenue) }}
                  </span>
                </div>
              </div>
              <div v-if="!topClients?.length" class="px-5 md:px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                <UserGroupIcon class="h-10 w-10 mx-auto mb-2 opacity-30" />
                <p class="text-sm">No client data available</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <Link
            :href="route('admin.accounting.invoices.index')"
            class="group bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-md transition-all"
          >
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-blue-100 dark:bg-blue-900/30 group-hover:bg-blue-200 transition-colors">
                <DocumentTextIcon class="h-5 w-5 text-blue-600" />
              </div>
              <div>
                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Invoices</p>
                <p class="text-xs text-neutral-500">Manage all invoices</p>
              </div>
            </div>
          </Link>

          <Link
            :href="route('admin.accounting.payments.index')"
            class="group bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md transition-all"
          >
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 group-hover:bg-emerald-200 transition-colors">
                <BanknotesIcon class="h-5 w-5 text-emerald-600" />
              </div>
              <div>
                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Payments</p>
                <p class="text-xs text-neutral-500">Track payments</p>
              </div>
            </div>
          </Link>

          <Link
            :href="route('admin.accounting.reports.index')"
            class="group bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md transition-all"
          >
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-purple-100 dark:bg-purple-900/30 group-hover:bg-purple-200 transition-colors">
                <ChartPieIcon class="h-5 w-5 text-purple-600" />
              </div>
              <div>
                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Reports</p>
                <p class="text-xs text-neutral-500">Financial reports</p>
              </div>
            </div>
          </Link>

          <Link
            :href="route('admin.accounting.invoices.create')"
            class="group bg-white dark:bg-neutral-800 rounded-xl p-4 shadow-card border border-neutral-100 dark:border-neutral-700 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-md transition-all"
          >
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/30 group-hover:bg-amber-200 transition-colors">
                <PlusIcon class="h-5 w-5 text-amber-600" />
              </div>
              <div>
                <p class="text-sm font-semibold text-neutral-900 dark:text-white">New Invoice</p>
                <p class="text-xs text-neutral-500">Create invoice</p>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
