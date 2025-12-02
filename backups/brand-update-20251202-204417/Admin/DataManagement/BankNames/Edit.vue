<template>
  <Head title="Edit Bank Name" />
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Bank</h1>
        <p class="mt-1 text-sm text-gray-600">Update bank information</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Bank Name <span class="text-red-500">*</span>
              </label>
              <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" :class="form.errors.name ? 'border-red-500' : 'border-gray-300'"/>
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name (Bengali)</label>
              <input v-model="form.name_bn" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.name_bn" class="mt-1 text-sm text-red-600">{{ form.errors.name_bn }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Short Name</label>
              <input v-model="form.short_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.short_name" class="mt-1 text-sm text-red-600">{{ form.errors.short_name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
              <input v-model="form.slug" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Bank Type <span class="text-red-500">*</span>
              </label>
              <select v-model="form.type" required class="w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" :class="form.errors.type ? 'border-red-500' : 'border-gray-300'">
                <option value="">Select Type</option>
                <option value="commercial">Commercial Bank</option>
                <option value="islamic">Islamic Bank</option>
                <option value="specialized">Specialized Bank</option>
                <option value="foreign">Foreign Bank</option>
                <option value="mobile_financial">Mobile Financial Service</option>
              </select>
              <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">SWIFT Code</label>
              <input v-model="form.swift_code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.swift_code" class="mt-1 text-sm text-red-600">{{ form.errors.swift_code }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Routing Number</label>
              <input v-model="form.routing_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.routing_number" class="mt-1 text-sm text-red-600">{{ form.errors.routing_number }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
              <input v-model="form.website" type="url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
              <input v-model.number="form.sort_order" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
              <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                <span class="ml-2 text-sm text-gray-700">Active</span>
              </label>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <Link :href="route('admin.data.bank-names.index')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Cancel</Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ form.processing ? 'Updating...' : 'Update Bank' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  bankName: Object,
});

const form = useForm({
  name: props.bankName.name,
  name_bn: props.bankName.name_bn,
  slug: props.bankName.slug,
  short_name: props.bankName.short_name,
  swift_code: props.bankName.swift_code,
  routing_number: props.bankName.routing_number,
  type: props.bankName.type,
  website: props.bankName.website,
  description: props.bankName.description,
  sort_order: props.bankName.sort_order,
  is_active: props.bankName.is_active,
});

const submit = () => {
  form.put(route('admin.data.bank-names.update', props.bankName.id));
};
</script>
