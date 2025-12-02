<template>
  <Head title="Edit Visa Type" />
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Visa Type</h1>
        <p class="mt-1 text-sm text-gray-600">Update visa type information</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Visa Type Name <span class="text-red-500">*</span>
            </label>
            <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" :class="form.errors.name ? 'border-red-500' : 'border-gray-300'"/>
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input v-model="form.slug" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <div class="mb-6">
            <label class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
              <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
          </div>

          <div class="flex justify-end gap-3">
            <Link :href="route('admin.data.visa-types.index')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Cancel</Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ form.processing ? 'Updating...' : 'Update Visa Type' }}
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
  visaType: Object,
});

const form = useForm({
  name: props.visaType.name,
  slug: props.visaType.slug,
  description: props.visaType.description,
  is_active: props.visaType.is_active,
});

const submit = () => {
  form.put(route('admin.data.visa-types.update', props.visaType.id));
};
</script>
