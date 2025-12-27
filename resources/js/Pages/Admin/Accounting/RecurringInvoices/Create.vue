<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import { computed } from 'vue'
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  recurring: {
    type: Object,
    default: null
  },
  clients: Array,
  services: Array,
  frequencies: Object,
})

const { formatCurrency } = useBangladeshFormat()

const form = useForm({
  client_id: props.recurring?.client_id || '',
  title: props.recurring?.title || '',
  frequency: props.recurring?.frequency || 'monthly',
  start_date: props.recurring?.start_date || new Date().toISOString().split('T')[0],
  end_date: props.recurring?.end_date || '',
  next_invoice_date: props.recurring?.next_invoice_date || new Date().toISOString().split('T')[0],
  max_invoices: props.recurring?.max_invoices || '',
  auto_send: props.recurring?.auto_send ?? true,
  notes: props.recurring?.notes || '',
  items: props.recurring?.items || [
    { description: '', quantity: 1, unit_price: '', service_id: '' }
  ],
})

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: '', service_id: '' })
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

function submit() {
  if (props.recurring) {
    form.put(route('admin.accounting.recurring-invoices.update', props.recurring.id))
  } else {
    form.post(route('admin.accounting.recurring-invoices.store'))
  }
}
</script>

<template>
  <AdminLayout :title="recurring ? 'Edit Recurring Invoice' : 'Create Recurring Invoice'">
    <Head :title="recurring ? 'Edit Recurring Invoice' : 'Create Recurring Invoice'" />
    
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.accounting.recurring-invoices.index')"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ recurring ? 'Edit Recurring Invoice' : 'Create Recurring Invoice' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set up automated recurring billing</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                Title
              </label>
              <input
                v-model="form.title"
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="e.g., Monthly Consultation Fee"
              />
            </div>
          </div>
        </div>

        <!-- Schedule -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Schedule</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Frequency <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.frequency"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option v-for="(label, value) in frequencies" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Start Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.start_date"
                type="date"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Next Invoice Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.next_invoice_date"
                type="date"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                End Date (Optional)
              </label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <p class="mt-1 text-xs text-gray-500">Leave empty for indefinite billing</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Max Invoices (Optional)
              </label>
              <input
                v-model="form.max_invoices"
                type="number"
                min="1"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Unlimited"
              />
              <p class="mt-1 text-xs text-gray-500">Stop after generating this many invoices</p>
            </div>
          </div>
          
          <label class="flex items-center gap-3">
            <input
              v-model="form.auto_send"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">Automatically send invoice to client when generated</span>
          </label>
        </div>

        <!-- Line Items -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Items</h2>
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
                <label class="block text-xs text-gray-500 mb-1">Service (optional)</label>
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
                <label class="block text-xs text-gray-500 mb-1">Unit Price (৳)</label>
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
          
          <!-- Total -->
          <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-right">
              <p class="text-sm text-gray-500 dark:text-gray-400">Recurring Amount</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(subtotal) }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Notes (will appear on invoice)
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
            placeholder="Additional notes or terms..."
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('admin.accounting.recurring-invoices.index')"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (recurring ? 'Update' : 'Create Recurring Invoice') }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
