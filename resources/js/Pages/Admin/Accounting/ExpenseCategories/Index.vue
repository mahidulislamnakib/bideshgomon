<script setup>
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  FolderIcon,
  SwatchIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  categories: Array,
})

// Modal state
const showModal = ref(false)
const editingCategory = ref(null)
const form = ref({
  name: '',
  parent_id: '',
  color: '#3B82F6',
  icon: 'folder',
  description: '',
  is_active: true,
})

const colorOptions = [
  '#EF4444', '#F97316', '#F59E0B', '#84CC16', '#22C55E',
  '#14B8A6', '#06B6D4', '#3B82F6', '#6366F1', '#8B5CF6',
  '#A855F7', '#D946EF', '#EC4899', '#F43F5E', '#6B7280'
]

const iconOptions = [
  'folder', 'briefcase', 'building', 'truck', 'computer',
  'phone', 'document', 'chart', 'users', 'globe'
]

function openCreate() {
  editingCategory.value = null
  form.value = {
    name: '',
    parent_id: '',
    color: '#3B82F6',
    icon: 'folder',
    description: '',
    is_active: true,
  }
  showModal.value = true
}

function openEdit(category) {
  editingCategory.value = category
  form.value = {
    name: category.name,
    parent_id: category.parent_id || '',
    color: category.color || '#3B82F6',
    icon: category.icon || 'folder',
    description: category.description || '',
    is_active: category.is_active,
  }
  showModal.value = true
}

function submit() {
  const data = { ...form.value }
  if (!data.parent_id) delete data.parent_id
  
  if (editingCategory.value) {
    router.put(route('admin.accounting.expense-categories.update', editingCategory.value.id), data, {
      onSuccess: () => showModal.value = false
    })
  } else {
    router.post(route('admin.accounting.expense-categories.store'), data, {
      onSuccess: () => showModal.value = false
    })
  }
}

function deleteCategory(id) {
  if (confirm('Delete this category? Sub-categories will be orphaned.')) {
    router.delete(route('admin.accounting.expense-categories.destroy', id))
  }
}

// Get parent categories (only root level)
const parentCategories = props.categories?.filter(c => !c.parent_id) || []
</script>

<template>
  <AdminLayout title="Expense Categories">
    <Head title="Expense Categories" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Expense Categories</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Organize expenses into categories for better tracking</p>
        </div>
        <button
          @click="openCreate"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="w-5 h-5" />
          Add Category
        </button>
      </div>

      <!-- Categories Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="category in categories"
          :key="category.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5"
        >
          <div class="flex items-start gap-4">
            <div 
              :style="{ backgroundColor: category.color + '20' }"
              class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0"
            >
              <FolderIcon :style="{ color: category.color }" class="w-6 h-6" />
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ category.name }}</h3>
                <span 
                  v-if="!category.is_active"
                  class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400"
                >
                  Inactive
                </span>
              </div>
              
              <p v-if="category.parent" class="text-sm text-gray-500 dark:text-gray-400">
                Under: {{ category.parent.name }}
              </p>
              
              <p v-if="category.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                {{ category.description }}
              </p>
              
              <div class="flex items-center gap-4 mt-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ category.expenses_count || 0 }} expenses
                </span>
                <span v-if="category.children?.length" class="text-sm text-gray-500 dark:text-gray-400">
                  {{ category.children.length }} sub-categories
                </span>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
              @click="openEdit(category)"
              class="flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700 rounded-lg"
            >
              <PencilIcon class="w-4 h-4" />
              Edit
            </button>
            <button
              @click="deleteCategory(category.id)"
              class="flex items-center gap-1 px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:text-red-700 bg-red-50 dark:bg-red-900/20 rounded-lg"
            >
              <TrashIcon class="w-4 h-4" />
              Delete
            </button>
          </div>
        </div>
        
        <!-- Add New Card -->
        <button
          @click="openCreate"
          class="flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition group min-h-[180px]"
        >
          <PlusIcon class="w-8 h-8 text-gray-400 group-hover:text-blue-500" />
          <span class="mt-2 text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400">
            Add Category
          </span>
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="categories.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No categories yet</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create categories to organize your expenses.</p>
        <button
          @click="openCreate"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Add Category
        </button>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingCategory ? 'Edit Category' : 'Add Category' }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="e.g., Office Supplies"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Parent Category
              </label>
              <select
                v-model="form.parent_id"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              >
                <option value="">None (Root category)</option>
                <option 
                  v-for="cat in parentCategories.filter(c => c.id !== editingCategory?.id)" 
                  :key="cat.id" 
                  :value="cat.id"
                >
                  {{ cat.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Color
              </label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="color in colorOptions"
                  :key="color"
                  type="button"
                  @click="form.color = color"
                  :class="[
                    'w-8 h-8 rounded-lg transition',
                    form.color === color ? 'ring-2 ring-offset-2 ring-blue-500' : ''
                  ]"
                  :style="{ backgroundColor: color }"
                ></button>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="2"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                placeholder="Optional description..."
              ></textarea>
            </div>
            
            <label class="flex items-center gap-3">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
            
            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                {{ editingCategory ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
