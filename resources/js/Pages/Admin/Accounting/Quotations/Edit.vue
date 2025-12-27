<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { computed } from 'vue'
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  quotation: Object,
  clients: Array,
  services: Array,
  taxSettings: Array,
})

const { formatCurrency } = useBangladeshFormat()

const form = useForm({
  client_id: props.quotation?.client_id || '',
  quotation_date: props.quotation?.quotation_date || new Date().toISOString().split('T')[0],
  valid_until: props.quotation?.valid_until || '',
  tax_setting_id: props.quotation?.tax_setting_id || '',
  discount_type: props.quotation?.discount_type || 'fixed',
  discount_value: props.quotation?.discount_value || 0,
  notes: props.quotation?.notes || '',
  terms: props.quotation?.terms || '',
  items: props.quotation?.items?.length > 0 
    ? props.quotation.items.map(item => ({
        id: item.id,
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        service_id: item.service_id || ''
      }))
    : [{ id: null, description: '', quantity: 1, unit_price: '', service_id: '' }],
})

function addItem() {
  form.items.push({ id: null, description: '', quantity: 1, unit_price: '', service_id: '' })
}

function removeItem(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

function selectService(index) {
  const serviceId = form.items[index].service_id
  if (serviceId) {
    const service = props.services?.find(s => s.id == serviceId)
    if (service) {
      form.items[index].description = service.name
      form.items[index].unit_price = service.price
    }
  }
}

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0))
  }, 0)
})

const discountAmount = computed(() => {
  if (!form.discount_value) return 0
  if (form.discount_type === 'percentage') {
    return subtotal.value * (parseFloat(form.discount_value) / 100)
  }
  return parseFloat(form.discount_value)
})

const taxAmount = computed(() => {
  if (!form.tax_setting_id) return 0
  const tax = props.taxSettings?.find(t => t.id == form.tax_setting_id)
  if (!tax) return 0
  const taxableAmount = subtotal.value - discountAmount.value
  if (tax.type === 'percentage') {
    return taxableAmount * (tax.rate / 100)
  }
  return tax.rate
})

const total = computed(() => {
  return subtotal.value - discountAmount.value + taxAmount.value
})

function submit() {
  form.put(route('admin.accounting.quotations.update', props.quotation.id))
}
</script>

<template>
  <AdminLayout title="Edit Quotation">
    <Head :title="`Edit Quotation ${quotation.quotation_number}`" />
    
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.quotations.show', quotation.id)"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Edit {{ quotation.quotation_number }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update quotation details</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Client & Dates -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Client <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.client_id"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option value="">Select client...</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
              <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-500">{{ form.errors.client_id }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Quote Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.quotation_date"
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
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Items</h2>
            <button
              type="button"
              @click="addItem"
              class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700"
            >
              <PlusIcon class="w-4 h-4" />
              Add Item
            </button>
          </div>
          
          <div class="space-y-4">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="grid grid-cols-12 gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
              <div class="col-span-12 md:col-span-4">
                <label class="block text-xs text-gray-500 mb-1">Service</label>
                <select
                  v-model="item.service_id"
                  @change="selectService(index)"
                  class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                >
                  <option value="">Manual entry</option>
                  <option v-for="service in services" :key="service.id" :value="service.id">
                    {{ service.name }} - {{ formatCurrency(service.price) }}
                  </option>
                </select>
              </div>
              <div class="col-span-12 md:col-span-3">
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <input
                  v-model="item.description"
                  type="text"
                  required
                  class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                />
              </div>
              <div class="col-span-4 md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Qty</label>
                <input
                  v-model="item.quantity"
                  type="number"
                  min="1"
                  required
                  class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                />
              </div>
              <div class="col-span-6 md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Unit Price</label>
                <input
                  v-model="item.unit_price"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                />
              </div>
              <div class="col-span-2 md:col-span-1 flex items-end">
                <button
                  v-if="form.items.length > 1"
                  type="button"
                  @click="removeItem(index)"
                  class="p-2 text-red-400 hover:text-red-600"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Totals -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tax
                </label>
                <select
                  v-model="form.tax_setting_id"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                >
                  <option value="">No tax</option>
                  <option v-for="tax in taxSettings" :key="tax.id" :value="tax.id">
                    {{ tax.name }} ({{ tax.rate }}{{ tax.type === 'percentage' ? '%' : '৳' }})
                  </option>
                </select>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Discount Type
                  </label>
                  <select
                    v-model="form.discount_type"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  >
                    <option value="fixed">Fixed (৳)</option>
                    <option value="percentage">Percentage (%)</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Discount
                  </label>
                  <input
                    v-model="form.discount_value"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  />
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                <span class="text-gray-900 dark:text-white">{{ formatCurrency(subtotal) }}</span>
              </div>
              <div v-if="discountAmount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Discount</span>
                <span class="text-red-600">-{{ formatCurrency(discountAmount) }}</span>
              </div>
              <div v-if="taxAmount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Tax</span>
                <span class="text-gray-900 dark:text-white">{{ formatCurrency(taxAmount) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-600">
                <span class="text-gray-900 dark:text-white">Total</span>
                <span class="text-gray-900 dark:text-white">{{ formatCurrency(total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes & Terms -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="2"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="Additional notes for the client..."
            ></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terms & Conditions</label>
            <textarea
              v-model="form.terms"
              rows="2"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              placeholder="Payment terms, validity, etc..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.quotations.show', quotation.id)"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Update Quotation' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
