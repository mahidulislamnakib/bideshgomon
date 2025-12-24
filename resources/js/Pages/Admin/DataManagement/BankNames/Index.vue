<template>
  <Head title="Bank Names" />
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Bank Names Management</h1>
        <p class="mt-1 text-sm text-gray-600">Manage banks and financial institutions</p>
      </div>

      <!-- Actions Bar -->
      <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <Link :href="route('admin.data.bank-names.create')" class="inline-flex items-center px-4 py-2 bg-growth-600 text-white text-sm font-medium rounded-md hover:bg-growth-700">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Bank
        </Link>
        
        <Link :href="route('admin.data.bank-names.bulk-upload')" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
          </svg>
          Bulk Upload
        </Link>

        <a :href="route('admin.data.bank-names.export', filters)" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Export CSV
        </a>
      </div>

      <!-- Filters -->
      <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input v-model="filters.search" type="text" placeholder="Search banks..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-growth-600 focus:border-growth-600" @input="debounceSearch"/>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="filters.type" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-growth-600 focus:border-growth-600 bg-white" @change="applyFilters">
              <option value="">All Types</option>
              <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="filters.is_active" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-growth-600 focus:border-growth-600 bg-white" @change="applyFilters">
              <option value="">All Status</option>
              <option value="true">Active</option>
              <option value="false">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
        {{ $page.props.flash.success }}
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SWIFT Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="bank in bankNames.data" :key="bank.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ bank.sort_order }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ bank.name }}</div>
                  <div v-if="bank.name_bn" class="text-sm text-gray-500">{{ bank.name_bn }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ bank.short_name || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-growth-600">
                  {{ bank.type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ bank.swift_code || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button @click="toggleStatus(bank)" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" :class="bank.is_active ? 'bg-green-600' : 'bg-gray-300'">
                  <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="bank.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link :href="route('admin.data.bank-names.edit', bank.id)" class="text-growth-600 hover:text-red-900 mr-3">Edit</Link>
                <button @click="confirmDelete(bank)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="bankNames.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No banks found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by adding a new bank.</p>
        </div>

        <!-- Pagination -->
        <div v-if="bankNames.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ bankNames.from }} to {{ bankNames.to }} of {{ bankNames.total }} results
            </div>
            <div class="flex gap-2">
              <template v-for="link in bankNames.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  class="px-3 py-1 border rounded-md text-sm"
                  :class="link.active ? 'bg-growth-600 text-white border-growth-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                />
                <span
                  v-else
                  v-html="link.label"
                  class="px-3 py-1 border rounded-md text-sm opacity-50 cursor-not-allowed"
                  :class="link.active ? 'bg-growth-600 text-white border-growth-600' : 'bg-white text-gray-700 border-gray-300'"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  bankNames: Object,
  types: Array,
  filters: Object,
});

const filters = reactive({
  search: props.filters.search || '',
  type: props.filters.type || '',
  is_active: props.filters.is_active || '',
});

let searchTimeout;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 500);
};

const applyFilters = () => {
  router.get(route('admin.data.bank-names.index'), filters, { 
    preserveState: true,
    preserveScroll: true 
  });
};

const toggleStatus = (bank) => {
  router.post(route('admin.data.bank-names.toggle-status', bank.id), {}, {
    preserveScroll: true,
    onSuccess: () => {},
  });
};

const confirmDelete = (bank) => {
  if (confirm(`Are you sure you want to delete "${bank.name}"?`)) {
    router.delete(route('admin.data.bank-names.destroy', bank.id), {
      preserveScroll: true,
    });
  }
};
</script>
