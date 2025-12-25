<template>
  <AdminLayout title="Create Invoice">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('admin.accounting.invoices.index')"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-1" />
          Back to Invoices
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Invoice</h1>
        <p class="mt-1 text-sm text-gray-500">Invoice #{{ invoiceNumber }}</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Client Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Client Information</h3>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Select Client
                  </label>
                  <select
                    v-model="form.client_id"
                    @change="onClientSelect"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  >
                    <option value="">-- Custom Client --</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.name }} ({{ client.email }})
                    </option>
                  </select>
                </div>

                <div v-if="!form.client_id" class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Client Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.client_name"
                      type="text"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                      :class="{ 'border-red-500': form.errors.client_name }"
                    />
                    <p v-if="form.errors.client_name" class="mt-1 text-sm text-red-500">{{ form.errors.client_name }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input
                      v-model="form.client_email"
                      type="email"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input
                      v-model="form.client_phone"
                      type="text"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <input
                      v-model="form.client_address"
                      type="text"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Invoice Items -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Line Items</h3>
                <button
                  type="button"
                  @click="addItem"
                  class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800"
                >
                  <PlusIcon class="w-4 h-4" />
                  Add Item
                </button>
              </div>

              <div class="space-y-4">
                <div
                  v-for="(item, index) in form.items"
                  :key="index"
                  class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg"
                >
                  <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-5">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                      <div class="flex gap-2">
                        <select
                          v-model="item.service_id"
                          @change="onServiceSelect(index)"
                          class="w-32 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                        >
                          <option value="">Custom</option>
                          <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.name }}
                          </option>
                        </select>
                        <input
                          v-model="item.description"
                          type="text"
                          placeholder="Description"
                          class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                        />
                      </div>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Qty</label>
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                        @input="calculateItemAmount(index)"
                      />
                    </div>
                    <div class="col-span-4 md:col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Rate (৳)</label>
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                        @input="calculateItemAmount(index)"
                      />
                    </div>
                    <div class="col-span-4 md:col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
                      <p class="py-2 text-sm font-medium text-gray-900 dark:text-white">
                        {{ formatCurrency(item.quantity * item.unit_price) }}
                      </p>
                    </div>
                    <div class="col-span-12 md:col-span-1 flex items-end justify-end">
                      <button
                        v-if="form.items.length > 1"
                        type="button"
                        @click="removeItem(index)"
                        class="p-2 text-red-500 hover:bg-red-50 rounded"
                      >
                        <TrashIcon class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <p v-if="form.errors.items" class="mt-2 text-sm text-red-500">{{ form.errors.items }}</p>
            </div>

            <!-- Notes & Terms -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Notes visible to client..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terms & Conditions</label>
                  <textarea
                    v-model="form.terms"
                    rows="3"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Dates -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice Details</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issue Date</label>
                  <input
                    v-model="form.issue_date"
                    type="date"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                  <input
                    v-model="form.due_date"
                    type="date"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  />
                </div>
              </div>
            </div>

            <!-- Totals -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Summary</h3>
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Subtotal</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(subtotal) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500">VAT</span>
                    <input
                      v-model.number="form.tax_rate"
                      type="number"
                      min="0"
                      max="100"
                      step="0.5"
                      class="w-16 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm text-center"
                    />
                    <span class="text-gray-500">%</span>
                  </div>
                  <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(taxAmount) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">Discount</span>
                  <input
                    v-model.number="form.discount_amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-24 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm text-right"
                  />
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                  <div class="flex justify-between">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Total</span>
                    <span class="text-lg font-bold text-blue-600">{{ formatCurrency(grandTotal) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-3">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium"
              >
                {{ form.processing ? 'Saving...' : 'Save as Draft' }}
              </button>
              <button
                type="button"
                @click="saveAndSend"
                :disabled="form.processing"
                class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 font-medium"
              >
                Save & Send
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
  invoiceNumber: String,
  clients: Array,
  services: Array,
  defaultTaxRate: Number,
  defaultTerms: String,
})

const { formatCurrency } = useBangladeshFormat()

const today = new Date().toISOString().split('T')[0]
const dueDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]

const form = useForm({
  client_id: '',
  client_name: '',
  client_email: '',
  client_phone: '',
  client_address: '',
  issue_date: today,
  due_date: dueDate,
  tax_rate: props.defaultTaxRate,
  discount_amount: 0,
  notes: '',
  terms: props.defaultTerms,
  status: 'draft',
  items: [
    { description: '', quantity: 1, unit_price: 0, tax_rate: 0, discount: 0, service_id: '' }
  ],
})

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
})

const taxAmount = computed(() => {
  return subtotal.value * (form.tax_rate / 100)
})

const grandTotal = computed(() => {
  return subtotal.value + taxAmount.value - (form.discount_amount || 0)
})

function onClientSelect() {
  if (form.client_id) {
    const client = props.clients.find(c => c.id === form.client_id)
    if (client) {
      form.client_name = ''
      form.client_email = ''
      form.client_phone = ''
      form.client_address = ''
    }
  }
}

function onServiceSelect(index) {
  const item = form.items[index]
  if (item.service_id) {
    const service = props.services.find(s => s.id === item.service_id)
    if (service) {
      item.description = service.name
      item.unit_price = service.price
    }
  }
}

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0, discount: 0, service_id: '' })
}

function removeItem(index) {
  form.items.splice(index, 1)
}

function calculateItemAmount(index) {
  // Trigger reactivity
}

function submit() {
  form.status = 'draft'
  form.post(route('admin.accounting.invoices.store'))
}

function saveAndSend() {
  form.status = 'sent'
  form.post(route('admin.accounting.invoices.store'))
}
</script>
