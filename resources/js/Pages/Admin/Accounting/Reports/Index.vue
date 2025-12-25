<template>
  <AdminLayout title="Financial Reports">
    <div class="space-y-8">
      <!-- Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-8">
        <div class="absolute inset-0 bg-grid-white/10"></div>
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-purple-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                <ChartBarIcon class="w-6 h-6 text-white" />
              </div>
              <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium text-white">
                Analytics & Insights
              </span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Financial Reports</h1>
            <p class="text-indigo-100 max-w-xl">Comprehensive revenue analytics, payment trends, and business performance metrics at your fingertips.</p>
          </div>
          
          <div class="flex items-center gap-3">
            <select
              v-model="selectedPeriod"
              @change="changePeriod"
              class="px-4 py-2.5 bg-white/20 backdrop-blur-sm text-white border-0 rounded-xl text-sm focus:ring-2 focus:ring-white/50 cursor-pointer"
            >
              <option value="today" class="text-gray-900">Today</option>
              <option value="week" class="text-gray-900">This Week</option>
              <option value="month" class="text-gray-900">This Month</option>
              <option value="quarter" class="text-gray-900">This Quarter</option>
              <option value="year" class="text-gray-900">This Year</option>
              <option value="all" class="text-gray-900">All Time</option>
            </select>
            <button
              @click="exportReport"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-indigo-600 rounded-xl hover:bg-indigo-50 transition-all duration-200 font-semibold shadow-lg shadow-indigo-500/20"
            >
              <ArrowDownTrayIcon class="w-5 h-5" />
              Export Report
            </button>
          </div>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg shadow-emerald-500/20">
                <CurrencyDollarIcon class="w-6 h-6 text-white" />
              </div>
              <div class="flex items-center gap-1" :class="metrics.revenueGrowth >= 0 ? 'text-emerald-600' : 'text-red-500'">
                <component :is="metrics.revenueGrowth >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="w-4 h-4" />
                <span class="text-sm font-semibold">{{ Math.abs(metrics.revenueGrowth || 0) }}%</span>
              </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(metrics.totalRevenue) }}</p>
            <p class="text-xs text-gray-400 mt-2">vs previous period</p>
          </div>
        </div>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/20">
                <CheckCircleIcon class="w-6 h-6 text-white" />
              </div>
              <div class="flex items-center">
                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-semibold rounded-full">
                  {{ metrics.collectionRate || 0 }}%
                </span>
              </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Collected</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(metrics.totalCollected) }}</p>
            <p class="text-xs text-gray-400 mt-2">collection rate</p>
          </div>
        </div>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg shadow-amber-500/20">
                <ClockIcon class="w-6 h-6 text-white" />
              </div>
              <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                {{ metrics.outstandingInvoices || 0 }} invoices
              </span>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Outstanding</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(metrics.totalOutstanding) }}</p>
            <p class="text-xs text-gray-400 mt-2">pending payment</p>
          </div>
        </div>

        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg shadow-red-500/20">
                <ExclamationTriangleIcon class="w-6 h-6 text-white" />
              </div>
              <span class="text-xs text-red-500 bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded-full font-medium">
                {{ metrics.overdueInvoices || 0 }} overdue
              </span>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Overdue</p>
            <p class="text-3xl font-bold text-red-600">{{ formatCurrency(metrics.totalOverdue) }}</p>
            <p class="text-xs text-gray-400 mt-2">requires attention</p>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Trend</h3>
              <p class="text-sm text-gray-500">Monthly revenue comparison</p>
            </div>
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                <span class="text-xs text-gray-500">Total</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                <span class="text-xs text-gray-500">Collected</span>
              </div>
            </div>
          </div>
          <RevenueChart :data="revenueData" />
        </div>

        <!-- Payment Methods Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Methods</h3>
              <p class="text-sm text-gray-500">Distribution by payment type</p>
            </div>
            <ChartPieIcon class="w-5 h-5 text-gray-400" />
          </div>
          <div class="space-y-4">
            <div
              v-for="method in paymentMethods"
              :key="method.method"
              class="group"
            >
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                  <div :class="getMethodIconBg(method.method)" class="w-10 h-10 rounded-xl flex items-center justify-center">
                    <component :is="getMethodIcon(method.method)" class="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ getMethodLabel(method.method) }}</p>
                    <p class="text-xs text-gray-500">{{ method.percentage || 0 }}% of total</p>
                  </div>
                </div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(method.total) }}</p>
              </div>
              <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div 
                  :class="getMethodBarColor(method.method)"
                  class="h-full rounded-full transition-all duration-700 ease-out"
                  :style="{ width: `${method.percentage || 0}%` }"
                ></div>
              </div>
            </div>
            
            <div v-if="!paymentMethods?.length" class="text-center py-12">
              <ChartPieIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-sm text-gray-500">No payment data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Clients -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clients</h3>
                <p class="text-sm text-gray-500">By revenue generated</p>
              </div>
              <TrophyIcon class="w-5 h-5 text-amber-500" />
            </div>
          </div>
          <div class="p-4">
            <div class="space-y-2">
              <div
                v-for="(client, index) in topClients"
                :key="client.id"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
              >
                <div class="relative">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                      {{ getInitials(client.name) }}
                    </span>
                  </div>
                  <div v-if="index < 3" :class="['absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold', 
                    index === 0 ? 'bg-amber-400 text-amber-900' : 
                    index === 1 ? 'bg-gray-300 text-gray-700' : 'bg-amber-600 text-white']">
                    {{ index + 1 }}
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ client.name }}</p>
                  <p class="text-xs text-gray-500">{{ client.invoiceCount }} invoices</p>
                </div>
                <span class="text-sm font-bold text-emerald-600">{{ formatCurrency(client.total) }}</span>
              </div>
              
              <div v-if="!topClients?.length" class="text-center py-8">
                <UsersIcon class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-gray-500">No client data</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Invoice Status -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Status</h3>
                <p class="text-sm text-gray-500">Current breakdown</p>
              </div>
              <DocumentTextIcon class="w-5 h-5 text-gray-400" />
            </div>
          </div>
          <div class="p-4">
            <div class="space-y-2">
              <div
                v-for="status in invoiceStatusBreakdown"
                :key="status.status"
                :class="getStatusBgClass(status.status)"
                class="flex items-center justify-between p-4 rounded-xl transition-transform hover:scale-[1.02]"
              >
                <div class="flex items-center gap-3">
                  <div :class="getStatusDotClass(status.status)" class="w-3 h-3 rounded-full"></div>
                  <span class="text-sm font-medium capitalize">{{ status.status }}</span>
                </div>
                <div class="text-right">
                  <p class="text-lg font-bold">{{ status.count }}</p>
                  <p class="text-xs opacity-70">{{ formatCurrency(status.total) }}</p>
                </div>
              </div>
              
              <div v-if="!invoiceStatusBreakdown?.length" class="text-center py-8">
                <DocumentTextIcon class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-gray-500">No invoice data</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Aging Report -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Aging Report</h3>
                <p class="text-sm text-gray-500">Outstanding by age</p>
              </div>
              <CalendarIcon class="w-5 h-5 text-gray-400" />
            </div>
          </div>
          <div class="p-4">
            <div class="space-y-2">
              <div
                v-for="(aging, index) in agingReport"
                :key="aging.period"
                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', getAgingColorClass(index)]">
                    <span class="text-sm font-bold text-white">{{ index * 30 }}+</span>
                  </div>
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ aging.period }}</span>
                </div>
                <div class="text-right">
                  <p class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(aging.amount) }}</p>
                  <p class="text-xs text-gray-500">{{ aging.count }} invoices</p>
                </div>
              </div>
              
              <div v-if="!agingReport?.length" class="text-center py-8">
                <CalendarIcon class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-gray-500">No aging data</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Summary Table -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Summary</h3>
              <p class="text-sm text-gray-500">Year-to-date breakdown</p>
            </div>
            <TableCellsIcon class="w-5 h-5 text-gray-400" />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50/80 dark:bg-gray-900/50">
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Month</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoiced</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Collected</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Outstanding</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoices</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Collection %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr
                v-for="month in monthlySummary"
                :key="month.month"
                class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ month.monthName }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(month.invoiced) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="text-sm font-medium text-emerald-600">{{ formatCurrency(month.collected) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="text-sm font-medium text-amber-600">{{ formatCurrency(month.outstanding) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ month.invoiceCount }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                      month.collectionRate >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 
                      month.collectionRate >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 
                      'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                    ]"
                  >
                    {{ month.collectionRate }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-if="!monthlySummary?.length" class="py-16 text-center">
            <TableCellsIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No monthly data available</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RevenueChart from '@/Components/Accounting/RevenueChart.vue'
import {
  ChartBarIcon,
  CurrencyDollarIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  ArrowDownTrayIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ChartPieIcon,
  TrophyIcon,
  UsersIcon,
  DocumentTextIcon,
  CalendarIcon,
  TableCellsIcon,
  DevicePhoneMobileIcon,
  BuildingLibraryIcon,
  CreditCardIcon,
  DocumentCheckIcon,
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  metrics: Object,
  revenueData: Array,
  paymentMethods: Array,
  topClients: Array,
  invoiceStatusBreakdown: Array,
  agingReport: Array,
  monthlySummary: Array,
  period: String,
})

const { formatCurrency } = useBangladeshFormat()

const selectedPeriod = ref(props.period || 'month')

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

const methodIconBgs = {
  bkash: 'bg-gradient-to-br from-pink-500 to-pink-600',
  nagad: 'bg-gradient-to-br from-orange-500 to-orange-600',
  rocket: 'bg-gradient-to-br from-purple-500 to-purple-600',
  bank_transfer: 'bg-gradient-to-br from-blue-500 to-blue-600',
  cash: 'bg-gradient-to-br from-emerald-500 to-emerald-600',
  card: 'bg-gradient-to-br from-indigo-500 to-indigo-600',
  check: 'bg-gradient-to-br from-amber-500 to-amber-600',
  other: 'bg-gradient-to-br from-gray-500 to-gray-600',
}

const methodBarColors = {
  bkash: 'bg-gradient-to-r from-pink-500 to-pink-400',
  nagad: 'bg-gradient-to-r from-orange-500 to-orange-400',
  rocket: 'bg-gradient-to-r from-purple-500 to-purple-400',
  bank_transfer: 'bg-gradient-to-r from-blue-500 to-blue-400',
  cash: 'bg-gradient-to-r from-emerald-500 to-emerald-400',
  card: 'bg-gradient-to-r from-indigo-500 to-indigo-400',
  check: 'bg-gradient-to-r from-amber-500 to-amber-400',
  other: 'bg-gradient-to-r from-gray-500 to-gray-400',
}

function getMethodLabel(method) {
  return methodLabels[method] || method
}

function getMethodIconBg(method) {
  return methodIconBgs[method] || methodIconBgs.other
}

function getMethodBarColor(method) {
  return methodBarColors[method] || methodBarColors.other
}

function getMethodIcon(method) {
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

function getStatusBgClass(status) {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-300',
    sent: 'bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
    paid: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300',
    partial: 'bg-amber-50 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300',
    overdue: 'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-300',
    cancelled: 'bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400',
  }
  return classes[status] || classes.draft
}

function getStatusDotClass(status) {
  const classes = {
    draft: 'bg-gray-400',
    sent: 'bg-blue-500',
    paid: 'bg-emerald-500',
    partial: 'bg-amber-500',
    overdue: 'bg-red-500',
    cancelled: 'bg-gray-400',
  }
  return classes[status] || classes.draft
}

function getAgingColorClass(index) {
  const colors = [
    'bg-emerald-500',
    'bg-amber-500',
    'bg-orange-500',
    'bg-red-500',
    'bg-red-700',
  ]
  return colors[index] || colors[colors.length - 1]
}

function changePeriod() {
  router.get(route('admin.accounting.reports.index'), { period: selectedPeriod.value }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function exportReport() {
  window.open(route('admin.accounting.reports.export', { type: 'full', period: selectedPeriod.value }), '_blank')
}
</script>

<style scoped>
.bg-grid-white\/10 {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
</style>
