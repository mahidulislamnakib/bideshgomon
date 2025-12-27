<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  UserIcon,
  BuildingOfficeIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  ClockIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  client: Object,
  invoices: Object,
  stats: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

function deleteClient() {
  if (confirm(`Are you sure you want to delete ${props.client.name}?`)) {
    router.delete(route('admin.accounting.clients.destroy', props.client.id))
  }
}

function getTypeIcon(type) {
  return type === 'individual' ? UserIcon : BuildingOfficeIcon
}

function getStatusColor(status) {
  const colors = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-600',
    paid: 'bg-green-100 text-green-600',
    partial: 'bg-yellow-100 text-yellow-600',
    overdue: 'bg-red-100 text-red-600',
    cancelled: 'bg-gray-100 text-gray-500',
  }
  return colors[status] || colors.draft
}
</script>

<template>
  <AdminLayout title="Client Details">
    <Head :title="client.name" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.accounting.clients.index')"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
              <component :is="getTypeIcon(client.client_type)" class="w-6 h-6 text-gray-500" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ client.name }}</h1>
              <p class="text-sm text-gray-500">{{ client.company_name || client.email }}</p>
            </div>
          </div>
        </div>
        
        <div class="flex gap-2">
          <Link
            :href="route('admin.accounting.invoices.create', { client_id: client.id })"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="w-4 h-4" />
            New Invoice
          </Link>
          <Link
            :href="route('admin.accounting.clients.edit', client.id)"
            class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
          >
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          <button
            @click="deleteClient"
            class="inline-flex items-center gap-2 px-4 py-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition"
          >
            <TrashIcon class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Client Info -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Contact Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Contact Information</h2>
            
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <EnvelopeIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <p class="text-sm text-gray-500">Email</p>
                  <a :href="`mailto:${client.email}`" class="text-blue-600 hover:underline">{{ client.email }}</a>
                </div>
              </div>
              
              <div v-if="client.phone" class="flex items-start gap-3">
                <PhoneIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <p class="text-sm text-gray-500">Phone</p>
                  <a :href="`tel:${client.phone}`" class="text-blue-600 hover:underline">{{ client.phone }}</a>
                </div>
              </div>
              
              <div v-if="client.address" class="flex items-start gap-3">
                <MapPinIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <p class="text-sm text-gray-500">Address</p>
                  <p class="text-gray-900 dark:text-white">
                    {{ client.address }}<br>
                    <span v-if="client.city">{{ client.city }}, </span>
                    <span v-if="client.division">{{ client.division }}</span>
                    <span v-if="client.postal_code"> - {{ client.postal_code }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Billing Info -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Billing Settings</h2>
            
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-gray-500">Payment Terms</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  {{ client.payment_terms === 0 ? 'Due on Receipt' : `Net ${client.payment_terms}` }}
                </span>
              </div>
              <div v-if="client.credit_limit" class="flex justify-between">
                <span class="text-gray-500">Credit Limit</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(client.credit_limit) }}</span>
              </div>
              <div v-if="client.tax_id" class="flex justify-between">
                <span class="text-gray-500">Tax ID</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ client.tax_id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Status</span>
                <span :class="[
                  'px-2 py-0.5 rounded-full text-xs font-medium',
                  client.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                ]">
                  {{ client.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="client.notes" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Notes</h2>
            <p class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-line">{{ client.notes }}</p>
          </div>
        </div>

        <!-- Invoices & Stats -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
              <p class="text-xs text-gray-500 uppercase tracking-wide">Total Invoices</p>
              <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.total_invoices || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
              <p class="text-xs text-gray-500 uppercase tracking-wide">Total Billed</p>
              <p class="mt-1 text-2xl font-bold text-blue-600">{{ formatCurrency(stats?.total_billed || 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
              <p class="text-xs text-gray-500 uppercase tracking-wide">Paid</p>
              <p class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(stats?.total_paid || 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
              <p class="text-xs text-gray-500 uppercase tracking-wide">Outstanding</p>
              <p class="mt-1 text-2xl font-bold text-orange-600">{{ formatCurrency(client.outstanding_balance || 0) }}</p>
            </div>
          </div>

          <!-- Recent Invoices -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
              <Link
                :href="route('admin.accounting.invoices.index', { client_id: client.id })"
                class="text-sm text-blue-600 hover:underline"
              >
                View all
              </Link>
            </div>
            
            <div v-if="invoices?.data?.length" class="divide-y divide-gray-200 dark:divide-gray-700">
              <Link
                v-for="invoice in invoices.data"
                :key="invoice.id"
                :href="route('admin.accounting.invoices.show', invoice.id)"
                class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50"
              >
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ invoice.invoice_number }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(invoice.issue_date) }}</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(invoice.total_amount) }}</p>
                  <span :class="[getStatusColor(invoice.status), 'px-2 py-0.5 rounded-full text-xs font-medium capitalize']">
                    {{ invoice.status }}
                  </span>
                </div>
              </Link>
            </div>
            
            <div v-else class="px-6 py-12 text-center">
              <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No invoices yet</h3>
              <p class="mt-1 text-sm text-gray-500">Create the first invoice for this client.</p>
              <Link
                :href="route('admin.accounting.invoices.create', { client_id: client.id })"
                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
              >
                <PlusIcon class="w-4 h-4" />
                Create Invoice
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
