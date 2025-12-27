<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { 
  ArrowLeftIcon, 
  PlusIcon, 
  TrashIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  clients: Array,
  services: Array,
  quotation: {
    type: Object,
    default: null
  }
})

const { formatCurrency } = useBangladeshFormat()

const form = useForm({
  client_id: props.quotation?.client_id || '',
  client_name: props.quotation?.client_name || '',
  client_email: props.quotation?.client_email || '',
  client_phone: props.quotation?.client_phone || '',
  client_address: props.quotation?.client_address || '',
  issue_date: props.quotation?.issue_date || new Date().toISOString().split('T')[0],
  valid_until: props.quotation?.valid_until || new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  items: props.quotation?.items || [{ description: '', quantity: 1, unit_price: 0 }],
  tax_rate: props.quotation?.tax_rate || 0,
  discount_amount: props.quotation?.discount_amount || 0,
  discount_type: props.quotation?.discount_type || 'fixed',
  notes: props.quotation?.notes || '',
  terms: props.quotation?.terms || 'This quotation is valid for 30 days from the issue date.',
})

const selectedClient = ref(null)

function selectClient(client) {
  selectedClient.value = client
  form.client_id = client.id
  form.client_name = client.name
  form.client_email = client.email
  form.client_phone = client.phone || ''
  form.client_address = client.address || ''
}

function clearClient() {
  selectedClient.value = null
  form.client_id = ''
}

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

function removeItem(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

function addServiceItem(service) {
  form.items.push({
    description: service.name,
    quantity: 1,
    unit_price: service.price,
  })
}

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)
  }, 0)
})

const discountValue = computed(() => {
  if (form.discount_type === 'percentage') {
    return subtotal.value * (parseFloat(form.discount_amount) || 0) / 100
  }
  return parseFloat(form.discount_amount) || 0
})

const taxValue = computed(() => {
  return (subtotal.value - discountValue.value) * (parseFloat(form.tax_rate) || 0) / 100
})

const total = computed(() => {
  return subtotal.value - discountValue.value + taxValue.value
})

function submit() {
  if (props.quotation) {
    form.put(route('admin.accounting.quotations.update', props.quotation.id))
  } else {
    form.post(route('admin.accounting.quotations.store'))
  }
}
</script>

<template>
  <AdminLayout :title="quotation ? 'Edit Quotation' : 'Create Quotation'">
    <Head :title="quotation ? 'Edit Quotation' : 'Create Quotation'" />
    
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.quotations.index')"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ quotation ? 'Edit Quotation' : 'Create New Quotation' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ quotation ? 'Update quotation details' : 'Create a quote for a client' }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Client Selection -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Client Information</h2>
          
          <div v-if="!selectedClient && !form.client_id" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Select Existing Client
                </label>
                <select
                  @change="e => selectClient(clients.find(c => c.id == e.target.value))"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                >
                  <option value="">Choose a client...</option>
                  <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.name }} ({{ client.email }})
                  </option>
                </select>
              </div>
              <div class="flex items-end">
                <p class="text-sm text-gray-500">or enter client details manually below</p>
              </div>
            </div>
          </div>

          <div v-if="selectedClient" class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex justify-between items-start">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ selectedClient.name }}</p>
              <p class="text-sm text-gray-500">{{ selectedClient.email }}</p>
              <p v-if="selectedClient.phone" class="text-sm text-gray-500">{{ selectedClient.phone }}</p>
            </div>
            <button type="button" @click="clearClient" class="text-sm text-blue-600 hover:underline">Change</button>
          </div>
          
          <div v-if="!selectedClient" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Client Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.client_name"
                type="text"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Email <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.client_email"
                type="email"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Phone
              </label>
              <input
                v-model="form.client_phone"
                type="tel"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Address
              </label>
              <input
                v-model="form.client_address"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
          </div>
        </div>

        <!-- Dates -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dates</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Issue Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.issue_date"
                type="date"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Valid Until <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.valid_until"
                type="date"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Items</h2>
            <div class="flex gap-2">
              <select
                @change="e => { if(e.target.value) { addServiceItem(services.find(s => s.id == e.target.value)); e.target.value = '' } }"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
              >
                <option value="">Add from services...</option>
                <option v-for="service in services" :key="service.id" :value="service.id">
                  {{ service.name }} - {{ formatCurrency(service.price) }}
                </option>
              </select>
              <button
                type="button"
                @click="addItem"
                class="inline-flex items-center gap-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200"
              >
                <PlusIcon class="w-4 h-4" />
                Add Item
              </button>
            </div>
          </div>
          
          <div class="space-y-3">
            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 gap-3 items-start">
              <div class="col-span-6">
                <input
                  v-model="item.description"
                  type="text"
                  required
                  placeholder="Description"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                />
              </div>
              <div class="col-span-2">
                <input
                  v-model="item.quantity"
                  type="number"
                  min="1"
                  required
                  placeholder="Qty"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                />
              </div>
              <div class="col-span-3">
                <input
                  v-model="item.unit_price"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  placeholder="Price"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                />
              </div>
              <div class="col-span-1 flex justify-center">
                <button
                  type="button"
                  @click="removeItem(index)"
                  :disabled="form.items.length === 1"
                  class="p-2 text-gray-400 hover:text-red-500 disabled:opacity-30"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
          
          <!-- Totals -->
          <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount</label>
                    <div class="flex gap-2">
                      <input
                        v-model="form.discount_amount"
                        type="number"
                        min="0"
                        step="0.01"
                        class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                      />
                      <select
                        v-model="form.discount_type"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                      >
                        <option value="fixed">৳</option>
                        <option value="percentage">%</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Rate (%)</label>
                    <input
                      v-model="form.tax_rate"
                      type="number"
                      min="0"
                      max="100"
                      step="0.01"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                    />
                  </div>
                </div>
              </div>
              
              <div class="space-y-2 text-right">
                <div class="flex justify-between">
                  <span class="text-gray-500">Subtotal</span>
                  <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
                </div>
                <div v-if="discountValue > 0" class="flex justify-between text-red-600">
                  <span>Discount</span>
                  <span>-{{ formatCurrency(discountValue) }}</span>
                </div>
                <div v-if="taxValue > 0" class="flex justify-between">
                  <span class="text-gray-500">Tax ({{ form.tax_rate }}%)</span>
                  <span>{{ formatCurrency(taxValue) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                  <span class="font-semibold text-lg">Total</span>
                  <span class="font-bold text-lg text-blue-600">{{ formatCurrency(total) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes & Terms -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Any notes for the client..."
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terms & Conditions</label>
              <textarea
                v-model="form.terms"
                rows="3"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.quotations.index')"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (quotation ? 'Update Quotation' : 'Create Quotation') }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
