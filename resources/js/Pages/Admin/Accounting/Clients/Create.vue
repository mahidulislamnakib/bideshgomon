<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  client: {
    type: Object,
    default: null
  }
})

const form = useForm({
  name: props.client?.name || '',
  email: props.client?.email || '',
  phone: props.client?.phone || '',
  client_type: props.client?.client_type || 'individual',
  company_name: props.client?.company_name || '',
  tax_id: props.client?.tax_id || '',
  address: props.client?.address || '',
  city: props.client?.city || '',
  division: props.client?.division || '',
  postal_code: props.client?.postal_code || '',
  credit_limit: props.client?.credit_limit || '',
  payment_terms: props.client?.payment_terms || 15,
  notes: props.client?.notes || '',
})

function submit() {
  if (props.client) {
    form.put(route('admin.accounting.clients.update', props.client.id))
  } else {
    form.post(route('admin.accounting.clients.store'))
  }
}

const divisions = [
  'Dhaka',
  'Chattogram',
  'Rajshahi',
  'Khulna',
  'Barisal',
  'Sylhet',
  'Rangpur',
  'Mymensingh',
]
</script>

<template>
  <AdminLayout :title="client ? 'Edit Client' : 'Add Client'">
    <Head :title="client ? 'Edit Client' : 'Add Client'" />
    
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.clients.index')"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ client ? 'Edit Client' : 'Add New Client' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ client ? 'Update client information' : 'Create a new client record' }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Client Type
              </label>
              <select
                v-model="form.client_type"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option value="individual">Individual</option>
                <option value="business">Business</option>
                <option value="agency">Agency</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Full Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                :class="{ 'border-red-500': form.errors.name }"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
            </div>
            
            <div v-if="form.client_type !== 'individual'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Company Name
              </label>
              <input
                v-model="form.company_name"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Email <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                :class="{ 'border-red-500': form.errors.email }"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Phone
              </label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="01XXX-XXXXXX"
              />
            </div>
            
            <div v-if="form.client_type === 'business'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tax ID / TIN
              </label>
              <input
                v-model="form.tax_id"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
          </div>
        </div>

        <!-- Address -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Address</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Street Address
              </label>
              <textarea
                v-model="form.address"
                rows="2"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              ></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                City
              </label>
              <input
                v-model="form.city"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Division
              </label>
              <select
                v-model="form.division"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option value="">Select Division</option>
                <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Postal Code
              </label>
              <input
                v-model="form.postal_code"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
          </div>
        </div>

        <!-- Billing Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Billing Settings</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Credit Limit (৳)
              </label>
              <input
                v-model="form.credit_limit"
                type="number"
                min="0"
                step="0.01"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="0.00"
              />
              <p class="mt-1 text-xs text-gray-500">Leave empty for no credit limit</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Payment Terms (Days)
              </label>
              <select
                v-model="form.payment_terms"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option :value="0">Due on Receipt</option>
                <option :value="7">Net 7</option>
                <option :value="15">Net 15</option>
                <option :value="30">Net 30</option>
                <option :value="45">Net 45</option>
                <option :value="60">Net 60</option>
                <option :value="90">Net 90</option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Notes
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Any additional notes about this client..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.clients.index')"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (client ? 'Update Client' : 'Create Client') }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
