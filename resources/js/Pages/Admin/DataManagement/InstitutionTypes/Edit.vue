<template>
  <Head title="Edit Institution Type" />
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Institution Type</h1>
        <p class="mt-1 text-sm text-gray-600">Update institution type information</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Institution Type Name <span class="text-red-500">*</span>
            </label>
            <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-md focus:ring-brand-red-600 focus:border-brand-red-600" :class="form.errors.name ? 'border-red-500' : 'border-gray-300'"/>
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name (Bengali)</label>
            <input v-model="form.name_bn" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-brand-red-600 focus:border-brand-red-600"/>
            <p v-if="form.errors.name_bn" class="mt-1 text-sm text-red-600">{{ form.errors.name_bn }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input v-model="form.slug" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-brand-red-600 focus:border-brand-red-600"/>
            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Category <span class="text-red-500">*</span>
            </label>
            <select v-model="form.category" required class="w-full px-3 py-2 border rounded-md focus:ring-brand-red-600 focus:border-brand-red-600" :class="form.errors.category ? 'border-red-500' : 'border-gray-300'">
              <option value="">Select Category</option>
              <option value="academic">Academic</option>
              <option value="professional">Professional</option>
              <option value="vocational">Vocational</option>
              <option value="research">Research</option>
            </select>
            <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
            <select v-model="form.level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-brand-red-600 focus:border-brand-red-600">
              <option value="">Select Level</option>
              <option value="primary">Primary</option>
              <option value="secondary">Secondary</option>
              <option value="higher_secondary">Higher Secondary</option>
              <option value="undergraduate">Undergraduate</option>
              <option value="postgraduate">Postgraduate</option>
              <option value="doctoral">Doctoral</option>
            </select>
            <p v-if="form.errors.level" class="mt-1 text-sm text-red-600">{{ form.errors.level }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"></textarea>
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input v-model.number="form.sort_order" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-brand-red-600 focus:border-brand-red-600"/>
            <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p>
          </div>

          <div class="mb-6">
            <label class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-brand-red-600 focus:ring-brand-red-600"/>
              <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
          </div>

          <div class="flex justify-end gap-3">
            <Link :href="route('admin.data.institution-types.index')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Cancel</Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-brand-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
              {{ form.processing ? 'Updating...' : 'Update Institution Type' }}
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
  institutionType: Object,
});

const form = useForm({
  name: props.institutionType.name,
  name_bn: props.institutionType.name_bn,
  slug: props.institutionType.slug,
  description: props.institutionType.description,
  category: props.institutionType.category,
  level: props.institutionType.level,
  sort_order: props.institutionType.sort_order,
  is_active: props.institutionType.is_active,
});

const submit = () => {
  form.put(route('admin.data.institution-types.update', props.institutionType.id));
};
</script>
