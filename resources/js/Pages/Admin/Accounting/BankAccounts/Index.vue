<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
  BuildingLibraryIcon,
  DevicePhoneMobileIcon,
  BanknotesIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  EyeIcon,
  StarIcon,
  CheckCircleIcon,
  XCircleIcon,
  ArrowUpIcon,
  ArrowDownIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
  accounts: Array,
  stats: Object,
})

const { formatCurrency } = useBangladeshFormat()

// Modal state
const showAdjustModal = ref(false)
const selectedAccount = ref(null)

const adjustForm = useForm({
  adjustment_type: 'credit',
  amount: '',
  reason: '',
})

function openAdjustModal(account) {
  selectedAccount.value = account
  adjustForm.reset()
  showAdjustModal.value = true
}

function submitAdjustment() {
  adjustForm.post(route('admin.accounting.bank-accounts.adjust-balance', selectedAccount.value.id), {
    onSuccess: () => {
      showAdjustModal.value = false
      selectedAccount.value = null
    }
  })
}

function setPrimary(account) {
  router.post(route('admin.accounting.bank-accounts.set-primary', account.id))
}

function toggleActive(account) {
  router.post(route('admin.accounting.bank-accounts.toggle-active', account.id))
}

function deleteAccount(account) {
  if (confirm(`Are you sure you want to delete ${account.account_name}?`)) {
    router.delete(route('admin.accounting.bank-accounts.destroy', account.id))
  }
}

function getTypeIcon(type) {
  const icons = {
    bank: BuildingLibraryIcon,
    mobile_banking: DevicePhoneMobileIcon,
    cash: BanknotesIcon,
  }
  return icons[type] || BuildingLibraryIcon
}

function getTypeColor(type) {
  const colors = {
    bank: 'bg-blue-100 text-blue-600',
    mobile_banking: 'bg-pink-100 text-pink-600',
    cash: 'bg-green-100 text-green-600',
  }
  return colors[type] || 'bg-gray-100 text-gray-600'
}
</script>

<template>
  <AdminLayout title="Bank Accounts">
    <Head title="Bank Accounts - Accounting" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bank Accounts</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your business accounts and balances
          </p>
        </div>
        
        <Link
          :href="route('admin.accounting.bank-accounts.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="w-4 h-4" />
          Add Account
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total Balance</p>
          <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(stats?.total_balance || 0) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Bank Accounts</p>
          <p class="mt-1 text-2xl font-bold text-blue-600">{{ stats?.bank_accounts || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Mobile Banking</p>
          <p class="mt-1 text-2xl font-bold text-pink-600">{{ stats?.mobile_accounts || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Cash Accounts</p>
          <p class="mt-1 text-2xl font-bold text-green-600">{{ stats?.cash_accounts || 0 }}</p>
        </div>
      </div>

      <!-- Accounts Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="account in accounts"
          :key="account.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
          :class="{ 'ring-2 ring-blue-500': account.is_primary }"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div :class="[getTypeColor(account.account_type), 'w-12 h-12 rounded-lg flex items-center justify-center']">
                  <component :is="getTypeIcon(account.account_type)" class="w-6 h-6" />
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ account.account_name }}</h3>
                    <button
                      v-if="account.is_primary"
                      class="text-yellow-500"
                      title="Primary Account"
                    >
                      <StarIconSolid class="w-4 h-4" />
                    </button>
                    <button
                      v-else
                      @click="setPrimary(account)"
                      class="text-gray-300 hover:text-yellow-500"
                      title="Set as Primary"
                    >
                      <StarIcon class="w-4 h-4" />
                    </button>
                  </div>
                  <p class="text-sm text-gray-500">{{ account.account_number }}</p>
                </div>
              </div>
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  account.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                ]"
              >
                {{ account.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            
            <div v-if="account.bank_name" class="text-sm text-gray-500 mb-2">
              {{ account.bank_name }}
              <span v-if="account.branch_name" class="text-gray-400">• {{ account.branch_name }}</span>
            </div>
            <div v-if="account.mobile_banking_provider" class="text-sm text-gray-500 mb-2">
              {{ account.mobile_banking_provider }}
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
              <p class="text-sm text-gray-500 mb-1">Current Balance</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(account.current_balance) }}</p>
            </div>
          </div>
          
          <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
            <div class="flex gap-1">
              <button
                @click="openAdjustModal(account)"
                class="p-2 text-gray-400 hover:text-blue-600 rounded"
                title="Adjust Balance"
              >
                <ArrowUpIcon class="w-4 h-4" />
              </button>
              <Link
                :href="route('admin.accounting.bank-accounts.show', account.id)"
                class="p-2 text-gray-400 hover:text-blue-600 rounded"
              >
                <EyeIcon class="w-4 h-4" />
              </Link>
              <Link
                :href="route('admin.accounting.bank-accounts.edit', account.id)"
                class="p-2 text-gray-400 hover:text-amber-600 rounded"
              >
                <PencilIcon class="w-4 h-4" />
              </Link>
            </div>
            <div class="flex gap-1">
              <button
                @click="toggleActive(account)"
                :class="[
                  'p-2 rounded',
                  account.is_active ? 'text-gray-400 hover:text-red-600' : 'text-gray-400 hover:text-green-600'
                ]"
                :title="account.is_active ? 'Deactivate' : 'Activate'"
              >
                <component :is="account.is_active ? XCircleIcon : CheckCircleIcon" class="w-4 h-4" />
              </button>
              <button
                v-if="!account.is_primary"
                @click="deleteAccount(account)"
                class="p-2 text-gray-400 hover:text-red-600 rounded"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="!accounts?.length" class="col-span-full text-center py-12">
          <BuildingLibraryIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No accounts yet</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by adding your first bank account.</p>
          <Link
            :href="route('admin.accounting.bank-accounts.create')"
            class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
          >
            <PlusIcon class="w-4 h-4" />
            Add Account
          </Link>
        </div>
      </div>
    </div>

    <!-- Adjust Balance Modal -->
    <Teleport to="body">
      <div v-if="showAdjustModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showAdjustModal = false"></div>
          
          <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Adjust Balance - {{ selectedAccount?.account_name }}
            </h3>
            
            <form @submit.prevent="submitAdjustment" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Adjustment Type
                </label>
                <div class="flex gap-4">
                  <label class="flex items-center gap-2">
                    <input v-model="adjustForm.adjustment_type" type="radio" value="credit" class="text-green-600" />
                    <span class="flex items-center gap-1 text-green-600">
                      <ArrowUpIcon class="w-4 h-4" /> Credit
                    </span>
                  </label>
                  <label class="flex items-center gap-2">
                    <input v-model="adjustForm.adjustment_type" type="radio" value="debit" class="text-red-600" />
                    <span class="flex items-center gap-1 text-red-600">
                      <ArrowDownIcon class="w-4 h-4" /> Debit
                    </span>
                  </label>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Amount (৳)
                </label>
                <input
                  v-model="adjustForm.amount"
                  type="number"
                  min="0.01"
                  step="0.01"
                  required
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Reason
                </label>
                <textarea
                  v-model="adjustForm.reason"
                  required
                  rows="2"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  placeholder="Why are you adjusting the balance?"
                ></textarea>
              </div>
              
              <div class="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  @click="showAdjustModal = false"
                  class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="adjustForm.processing"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                  {{ adjustForm.processing ? 'Saving...' : 'Adjust Balance' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
