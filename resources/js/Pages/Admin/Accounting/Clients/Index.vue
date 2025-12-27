<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  UserGroupIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  EyeIcon,
  BuildingOfficeIcon,
  UserIcon,
  PhoneIcon,
  EnvelopeIcon,
  CurrencyDollarIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  clients: Object,
  filters: Object,
  stats: Object,
})

const { formatCurrency, formatDate } = useBangladeshFormat()

const localFilters = ref({
  search: props.filters?.search || '',
  type: props.filters?.type || '',
  status: props.filters?.status || '',
})

const debounceTimeout = ref(null)

function debouncedSearch() {
  clearTimeout(debounceTimeout.value)
  debounceTimeout.value = setTimeout(applyFilters, 300)
}

function applyFilters() {
  router.get(route('admin.accounting.clients.index'), localFilters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  localFilters.value = { search: '', type: '', status: '' }
  applyFilters()
}

const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.type || localFilters.value.status
})

function deleteClient(client) {
  if (confirm(`Are you sure you want to delete ${client.name}?`)) {
    router.delete(route('admin.accounting.clients.destroy', client.id))
  }
}

function toggleActive(client) {
  router.post(route('admin.accounting.clients.toggle-active', client.id))
}

function getTypeColor(type) {
  const colors = {
    individual: 'bg-blue-100 text-blue-700',
    business: 'bg-purple-100 text-purple-700',
    agency: 'bg-orange-100 text-orange-700',
  }
  return colors[type] || 'bg-gray-100 text-gray-700'
}

function getTypeIcon(type) {
  return type === 'individual' ? UserIcon : BuildingOfficeIcon
}
</script>

<template>
  <AdminLayout title="Clients">
    <Head title="Clients - Accounting" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clients</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your business clients and contacts
          </p>
        </div>
        
        <Link
          :href="route('admin.accounting.clients.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="w-4 h-4" />
          Add Client
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total Clients</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Individuals</p>
          <p class="mt-1 text-2xl font-bold text-blue-600">{{ stats.individuals }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Businesses</p>
          <p class="mt-1 text-2xl font-bold text-purple-600">{{ stats.businesses }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Outstanding</p>
          <p class="mt-1 text-2xl font-bold text-orange-600">{{ formatCurrency(stats.total_outstanding || 0) }}</p>
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
                placeholder="Search clients..."
                class="w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                @input="debouncedSearch"
              />
            </div>
          </div>
          <select
            v-model="localFilters.type"
            @change="applyFilters"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
          >
            <option value="">All Types</option>
            <option value="individual">Individual</option>
            <option value="business">Business</option>
            <option value="agency">Agency</option>
          </select>
          <select
            v-model="localFilters.status"
            @change="applyFilters"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <component :is="getTypeIcon(client.client_type)" class="w-5 h-5 text-gray-500" />
                    </div>
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white">{{ client.name }}</div>
                      <div v-if="client.company_name" class="text-sm text-gray-500">{{ client.company_name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[getTypeColor(client.client_type), 'px-2 py-1 rounded-full text-xs font-medium capitalize']">
                    {{ client.client_type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm">
                    <div v-if="client.email" class="flex items-center gap-1 text-gray-500">
                      <EnvelopeIcon class="w-3.5 h-3.5" />
                      {{ client.email }}
                    </div>
                    <div v-if="client.phone" class="flex items-center gap-1 text-gray-500 mt-1">
                      <PhoneIcon class="w-3.5 h-3.5" />
                      {{ client.phone }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span :class="[
                    'font-medium',
                    client.outstanding_balance > 0 ? 'text-orange-600' : 'text-gray-500'
                  ]">
                    {{ formatCurrency(client.outstanding_balance || 0) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <button
                    @click="toggleActive(client)"
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-medium',
                      client.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                    ]"
                  >
                    {{ client.is_active ? 'Active' : 'Inactive' }}
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex justify-end gap-2">
                    <Link
                      :href="route('admin.accounting.clients.show', client.id)"
                      class="p-1.5 text-gray-400 hover:text-blue-600 rounded"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </Link>
                    <Link
                      :href="route('admin.accounting.clients.edit', client.id)"
                      class="p-1.5 text-gray-400 hover:text-amber-600 rounded"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </Link>
                    <button
                      @click="deleteClient(client)"
                      class="p-1.5 text-gray-400 hover:text-red-600 rounded"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!clients.data?.length">
                <td colspan="6" class="px-6 py-12 text-center">
                  <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No clients found</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by adding your first client.</p>
                  <Link
                    :href="route('admin.accounting.clients.create')"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
                  >
                    <PlusIcon class="w-4 h-4" />
                    Add Client
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="clients.links?.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">
              Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} clients
            </p>
            <div class="flex gap-1">
              <Link
                v-for="link in clients.links"
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
