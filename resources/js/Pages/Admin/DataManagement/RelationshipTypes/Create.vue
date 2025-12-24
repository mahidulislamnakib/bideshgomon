<template>
  <Head title="Add Relationship Type" />
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Add New Relationship Type</h1>
        <p class="mt-1 text-sm text-gray-600">Create a new family or professional relationship type</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Relationship Name <span class="text-red-500">*</span>
            </label>
            <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-md focus:ring-growth-600 focus:border-growth-600" :class="form.errors.name ? 'border-red-500' : 'border-gray-300'" placeholder="e.g., Spouse"/>
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name (Bengali)</label>
            <input v-model="form.name_bn" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-growth-600 focus:border-growth-600" placeholder="e.g., ??????/??????"/>
            <p v-if="form.errors.name_bn" class="mt-1 text-sm text-red-600">{{ form.errors.name_bn }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input v-model="form.slug" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-growth-600 focus:border-growth-600" placeholder="Auto-generated if empty"/>
            <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate</p>
            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Category <span class="text-red-500">*</span>
            </label>
            <select v-model="form.category" required class="w-full px-4 py-3 border-2 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white" :class="form.errors.category ? 'border-red-500' : 'border-gray-200'">
              <option value="">Select Category</option>
              <option value="family">Family</option>
              <option value="professional">Professional</option>
              <option value="emergency_contact">Emergency Contact</option>
            </select>
            <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all resize-none" placeholder="Brief description"></textarea>
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input v-model.number="form.sort_order" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-growth-600 focus:border-growth-600" placeholder="0"/>
            <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p>
          </div>

          <div class="mb-6">
            <label class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-growth-600 focus:ring-growth-600"/>
              <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
          </div>

          <div class="flex justify-end gap-3">
            <Link :href="route('admin.data.relationship-types.index')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Cancel</Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-growth-600 text-white rounded-md hover:bg-growth-700 disabled:opacity-50">
              {{ form.processing ? 'Creating...' : 'Create Relationship Type' }}
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

const form = useForm({
  name: '',
  name_bn: '',
  slug: '',
  description: '',
  category: '',
  sort_order: 0,
  is_active: true,
});

const submit = () => {
  form.post(route('admin.data.relationship-types.store'));
};
</script>
