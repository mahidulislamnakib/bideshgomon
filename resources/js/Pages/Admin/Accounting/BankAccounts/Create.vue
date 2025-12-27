<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  account: {
    type: Object,
    default: null
  },
  accountTypes: Object,
  mobileBankingProviders: Array,
})

const form = useForm({
  account_name: props.account?.account_name || '',
  account_number: props.account?.account_number || '',
  account_type: props.account?.account_type || 'bank',
  bank_name: props.account?.bank_name || '',
  branch_name: props.account?.branch_name || '',
  routing_number: props.account?.routing_number || '',
  mobile_banking_provider: props.account?.mobile_banking_provider || '',
  opening_balance: props.account?.opening_balance || '',
  description: props.account?.description || '',
  is_primary: props.account?.is_primary || false,
})

function submit() {
  if (props.account) {
    form.put(route('admin.accounting.bank-accounts.update', props.account.id))
  } else {
    form.post(route('admin.accounting.bank-accounts.store'))
  }
}
</script>

<template>
  <AdminLayout :title="account ? 'Edit Account' : 'Add Account'">
    <Head :title="account ? 'Edit Bank Account' : 'Add Bank Account'" />
    
    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.bank-accounts.index')"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ account ? 'Edit Account' : 'Add New Account' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ account ? 'Update account details' : 'Add a bank account, mobile banking, or cash account' }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
          <!-- Account Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
              Account Type
            </label>
            <div class="grid grid-cols-3 gap-4">
              <label
                v-for="(label, value) in accountTypes"
                :key="value"
                :class="[
                  'flex flex-col items-center p-4 rounded-lg border-2 cursor-pointer transition',
                  form.account_type === value
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                ]"
              >
                <input
                  v-model="form.account_type"
                  type="radio"
                  :value="value"
                  class="sr-only"
                />
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ label }}</span>
              </label>
            </div>
          </div>

          <!-- Account Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Account Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.account_name"
              type="text"
              required
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="e.g., Main Business Account"
            />
            <p v-if="form.errors.account_name" class="mt-1 text-sm text-red-500">{{ form.errors.account_name }}</p>
          </div>

          <!-- Account Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Account Number <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.account_number"
              type="text"
              required
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              :placeholder="form.account_type === 'mobile_banking' ? '01XXXXXXXXX' : 'Account number'"
            />
            <p v-if="form.errors.account_number" class="mt-1 text-sm text-red-500">{{ form.errors.account_number }}</p>
          </div>

          <!-- Bank Details (for bank type) -->
          <template v-if="form.account_type === 'bank'">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Bank Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.bank_name"
                type="text"
                :required="form.account_type === 'bank'"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="e.g., Dutch Bangla Bank"
              />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Branch Name
                </label>
                <input
                  v-model="form.branch_name"
                  type="text"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  placeholder="e.g., Gulshan Branch"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Routing Number
                </label>
                <input
                  v-model="form.routing_number"
                  type="text"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                />
              </div>
            </div>
          </template>

          <!-- Mobile Banking Provider -->
          <div v-if="form.account_type === 'mobile_banking'">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Provider <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.mobile_banking_provider"
              :required="form.account_type === 'mobile_banking'"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            >
              <option value="">Select Provider</option>
              <option v-for="provider in mobileBankingProviders" :key="provider" :value="provider">
                {{ provider }}
              </option>
            </select>
          </div>

          <!-- Opening Balance -->
          <div v-if="!account">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Opening Balance (৳)
            </label>
            <input
              v-model="form.opening_balance"
              type="number"
              min="0"
              step="0.01"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="0.00"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="2"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="Optional notes about this account..."
            ></textarea>
          </div>

          <!-- Primary Account -->
          <label class="flex items-center gap-3">
            <input
              v-model="form.is_primary"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">Set as primary account</span>
          </label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.bank-accounts.index')"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (account ? 'Update Account' : 'Create Account') }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
