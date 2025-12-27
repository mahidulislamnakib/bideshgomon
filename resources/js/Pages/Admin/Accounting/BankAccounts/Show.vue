<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { ref } from 'vue'
import { 
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  StarIcon,
  ArrowPathIcon,
  BanknotesIcon,
  ArrowUpIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  account: Object,
  transactions: Object,
  stats: Object,
})

const { formatCurrency, formatDate, formatDateTime } = useBangladeshFormat()

const typeColors = {
  bank: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  mobile_banking: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400',
  cash: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
}

const typeLabels = {
  bank: 'Bank Account',
  mobile_banking: 'Mobile Banking',
  cash: 'Cash Account',
}

// Adjustment modal
const showAdjustModal = ref(false)
const adjustmentAmount = ref('')
const adjustmentType = ref('credit')
const adjustmentReason = ref('')

function setPrimary() {
  if (confirm('Set this as the primary account?')) {
    router.post(route('admin.accounting.bank-accounts.set-primary', props.account.id))
  }
}

function toggleActive() {
  router.post(route('admin.accounting.bank-accounts.toggle-active', props.account.id))
}

function deleteAccount() {
  if (confirm('Are you sure you want to delete this account? This cannot be undone.')) {
    router.delete(route('admin.accounting.bank-accounts.destroy', props.account.id))
  }
}

function adjustBalance() {
  router.post(route('admin.accounting.bank-accounts.adjust-balance', props.account.id), {
    amount: adjustmentAmount.value,
    type: adjustmentType.value,
    reason: adjustmentReason.value
  }, {
    onSuccess: () => {
      showAdjustModal.value = false
      adjustmentAmount.value = ''
      adjustmentReason.value = ''
    }
  })
}
</script>

<template>
  <AdminLayout title="Account Details">
    <Head :title="`${account.account_name} - Bank Account`" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.accounting.bank-accounts.index')"
            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ account.account_name }}
              </h1>
              <span :class="[typeColors[account.account_type], 'px-2 py-1 text-xs font-medium rounded-full']">
                {{ typeLabels[account.account_type] }}
              </span>
              <span v-if="account.is_primary" class="flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                <StarSolidIcon class="w-3 h-3" />
                Primary
              </span>
              <span v-if="!account.is_active" class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                Inactive
              </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400">
              {{ account.account_number }}
              <span v-if="account.bank_name"> • {{ account.bank_name }}</span>
              <span v-if="account.mobile_banking_provider"> • {{ account.mobile_banking_provider }}</span>
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button
            v-if="!account.is_primary && account.is_active"
            @click="setPrimary"
            class="flex items-center gap-2 px-3 py-2 text-sm text-yellow-700 bg-yellow-100 rounded-lg hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400"
          >
            <StarIcon class="w-4 h-4" />
            Set Primary
          </button>
          <button
            @click="toggleActive"
            :class="[
              'flex items-center gap-2 px-3 py-2 text-sm rounded-lg',
              account.is_active
                ? 'text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'
                : 'text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400'
            ]"
          >
            <ArrowPathIcon class="w-4 h-4" />
            {{ account.is_active ? 'Deactivate' : 'Activate' }}
          </button>
          <Link
            :href="route('admin.accounting.bank-accounts.edit', account.id)"
            class="flex items-center gap-2 px-3 py-2 text-sm text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400"
          >
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          <button
            @click="deleteAccount"
            class="flex items-center gap-2 px-3 py-2 text-sm text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400"
          >
            <TrashIcon class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>

      <!-- Balance Card -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white md:col-span-2">
          <p class="text-blue-100 text-sm">Current Balance</p>
          <p class="text-4xl font-bold mt-2">{{ formatCurrency(account.current_balance) }}</p>
          <p class="text-blue-100 text-sm mt-2">Opening Balance: {{ formatCurrency(account.opening_balance) }}</p>
          <button
            @click="showAdjustModal = true"
            class="mt-4 flex items-center gap-2 px-4 py-2 bg-white/20 rounded-lg text-sm hover:bg-white/30"
          >
            <BanknotesIcon class="w-4 h-4" />
            Adjust Balance
          </button>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total In</p>
            <ArrowDownIcon class="w-5 h-5 text-green-500" />
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
            {{ formatCurrency(stats?.total_in || 0) }}
          </p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ stats?.in_count || 0 }} transactions
          </p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Out</p>
            <ArrowUpIcon class="w-5 h-5 text-red-500" />
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
            {{ formatCurrency(stats?.total_out || 0) }}
          </p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ stats?.out_count || 0 }} transactions
          </p>
        </div>
      </div>

      <!-- Account Details -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Account Details</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400">Account Type</dt>
              <dd class="text-gray-900 dark:text-white">{{ typeLabels[account.account_type] }}</dd>
            </div>
            <div v-if="account.bank_name">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Bank</dt>
              <dd class="text-gray-900 dark:text-white">{{ account.bank_name }}</dd>
            </div>
            <div v-if="account.branch_name">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Branch</dt>
              <dd class="text-gray-900 dark:text-white">{{ account.branch_name }}</dd>
            </div>
            <div v-if="account.routing_number">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Routing Number</dt>
              <dd class="text-gray-900 dark:text-white">{{ account.routing_number }}</dd>
            </div>
            <div v-if="account.mobile_banking_provider">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Provider</dt>
              <dd class="text-gray-900 dark:text-white">{{ account.mobile_banking_provider }}</dd>
            </div>
            <div v-if="account.description">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Description</dt>
              <dd class="text-gray-900 dark:text-white">{{ account.description }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400">Created</dt>
              <dd class="text-gray-900 dark:text-white">{{ formatDateTime(account.created_at) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
          
          <div v-if="transactions?.data?.length > 0" class="space-y-3">
            <div
              v-for="transaction in transactions.data"
              :key="transaction.id"
              class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"
            >
              <div class="flex items-center gap-3">
                <div :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center',
                  transaction.type === 'credit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'
                ]">
                  <ArrowDownIcon v-if="transaction.type === 'credit'" class="w-4 h-4" />
                  <ArrowUpIcon v-else class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ transaction.description }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDateTime(transaction.created_at) }}
                  </p>
                </div>
              </div>
              <span :class="[
                'font-medium',
                transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'
              ]">
                {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
              </span>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            No transactions yet
          </div>
        </div>
      </div>
    </div>

    <!-- Balance Adjustment Modal -->
    <Teleport to="body">
      <div v-if="showAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showAdjustModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Adjust Balance</h3>
          
          <div class="space-y-4">
            <div class="flex gap-2">
              <button
                @click="adjustmentType = 'credit'"
                :class="[
                  'flex-1 py-2 rounded-lg font-medium text-sm transition',
                  adjustmentType === 'credit'
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                ]"
              >
                Add Funds
              </button>
              <button
                @click="adjustmentType = 'debit'"
                :class="[
                  'flex-1 py-2 rounded-lg font-medium text-sm transition',
                  adjustmentType === 'debit'
                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                ]"
              >
                Deduct Funds
              </button>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Amount (৳)
              </label>
              <input
                v-model="adjustmentAmount"
                type="number"
                min="0"
                step="0.01"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="0.00"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Reason
              </label>
              <input
                v-model="adjustmentReason"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Reason for adjustment..."
              />
            </div>
          </div>
          
          <div class="flex justify-end gap-3 mt-6">
            <button
              @click="showAdjustModal = false"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              @click="adjustBalance"
              :disabled="!adjustmentAmount"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              Apply Adjustment
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
