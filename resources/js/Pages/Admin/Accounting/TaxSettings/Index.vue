<script setup>
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  XMarkIcon,
  ReceiptPercentIcon
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  taxSettings: Array,
})

// Modal state
const showModal = ref(false)
const editingTax = ref(null)
const form = ref({
  name: '',
  rate: '',
  type: 'percentage',
  is_compound: false,
  is_active: true,
})

function openCreate() {
  editingTax.value = null
  form.value = {
    name: '',
    rate: '',
    type: 'percentage',
    is_compound: false,
    is_active: true,
  }
  showModal.value = true
}

function openEdit(tax) {
  editingTax.value = tax
  form.value = {
    name: tax.name,
    rate: tax.rate,
    type: tax.type,
    is_compound: tax.is_compound,
    is_active: tax.is_active,
  }
  showModal.value = true
}

function submit() {
  if (editingTax.value) {
    router.put(route('admin.accounting.tax-settings.update', editingTax.value.id), form.value, {
      onSuccess: () => showModal.value = false
    })
  } else {
    router.post(route('admin.accounting.tax-settings.store'), form.value, {
      onSuccess: () => showModal.value = false
    })
  }
}

function setDefault(id) {
  router.post(route('admin.accounting.tax-settings.set-default', id))
}

function deleteTax(id) {
  if (confirm('Delete this tax setting?')) {
    router.delete(route('admin.accounting.tax-settings.destroy', id))
  }
}
</script>

<template>
  <AdminLayout title="Tax Settings">
    <Head title="Tax Settings" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Settings</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Configure VAT and other tax rates for invoicing</p>
        </div>
        <button
          @click="openCreate"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="w-5 h-5" />
          Add Tax Rate
        </button>
      </div>

      <!-- Info Banner -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex gap-3">
          <ReceiptPercentIcon class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" />
          <div>
            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Bangladesh VAT</p>
            <p class="text-sm text-blue-700 dark:text-blue-400">
              Standard VAT rate in Bangladesh is 15%. Service-related businesses may have different rates.
              Configure your applicable tax rates here.
            </p>
          </div>
        </div>
      </div>

      <!-- Tax Settings Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="tax in taxSettings"
          :key="tax.id"
          :class="[
            'bg-white dark:bg-gray-800 rounded-xl border p-6',
            tax.is_default 
              ? 'border-blue-300 dark:border-blue-700 ring-2 ring-blue-100 dark:ring-blue-900'
              : 'border-gray-200 dark:border-gray-700'
          ]"
        >
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ tax.name }}</h3>
                <span v-if="tax.is_default" class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                  Default
                </span>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                {{ tax.rate }}{{ tax.type === 'percentage' ? '%' : '৳' }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ tax.type === 'percentage' ? 'Percentage rate' : 'Fixed amount' }}
                <span v-if="tax.is_compound" class="text-orange-600 dark:text-orange-400"> • Compound</span>
              </p>
            </div>
            
            <div class="flex items-center gap-1">
              <span 
                :class="[
                  'w-2 h-2 rounded-full',
                  tax.is_active ? 'bg-green-500' : 'bg-gray-400'
                ]"
              ></span>
            </div>
          </div>
          
          <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
              @click="openEdit(tax)"
              class="flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700 rounded-lg"
            >
              <PencilIcon class="w-4 h-4" />
              Edit
            </button>
            <button
              v-if="!tax.is_default"
              @click="setDefault(tax.id)"
              class="flex items-center gap-1 px-3 py-1.5 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
            >
              <CheckCircleIcon class="w-4 h-4" />
              Set Default
            </button>
            <button
              v-if="!tax.is_default"
              @click="deleteTax(tax.id)"
              class="flex items-center gap-1 px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:text-red-700 bg-red-50 dark:bg-red-900/20 rounded-lg"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        
        <!-- Add New Card -->
        <button
          @click="openCreate"
          class="flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition group"
        >
          <PlusIcon class="w-8 h-8 text-gray-400 group-hover:text-blue-500" />
          <span class="mt-2 text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400">
            Add Tax Rate
          </span>
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="taxSettings.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <ReceiptPercentIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tax rates configured</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a tax rate to start applying taxes to invoices.</p>
        <button
          @click="openCreate"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Add Tax Rate
        </button>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingTax ? 'Edit Tax Rate' : 'Add Tax Rate' }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="e.g., VAT, Service Tax"
              />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Rate <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.rate"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Type
                </label>
                <select
                  v-model="form.type"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                >
                  <option value="percentage">Percentage (%)</option>
                  <option value="fixed">Fixed Amount (৳)</option>
                </select>
              </div>
            </div>
            
            <div class="space-y-3">
              <label class="flex items-center gap-3">
                <input
                  v-model="form.is_compound"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Compound tax (calculated on subtotal + other taxes)</span>
              </label>
              
              <label class="flex items-center gap-3">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
              </label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                {{ editingTax ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
