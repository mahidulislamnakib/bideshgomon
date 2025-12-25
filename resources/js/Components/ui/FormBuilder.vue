<template>
  <div :class="['form-builder', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
          <DocumentTextIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">Form Builder</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ fields.length }} fields
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
            mode === 'edit'
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
          ]"
          @click="mode = 'edit'"
        >
          Edit
        </button>
        <button
          type="button"
          :class="[
            'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
            mode === 'preview'
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
          ]"
          @click="mode = 'preview'"
        >
          Preview
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="exportForm"
        >
          Export JSON
        </button>
      </div>
    </div>

    <div class="flex" :style="{ height }">
      <!-- Field Types Panel -->
      <div v-if="mode === 'edit'" class="w-56 border-r border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Field Types</h4>
        <div class="space-y-2">
          <div
            v-for="type in fieldTypes"
            :key="type.value"
            class="flex items-center gap-3 p-2.5 bg-gray-50 dark:bg-gray-800 rounded-lg cursor-grab hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            draggable="true"
            @dragstart="onDragStart(type, $event)"
          >
            <component :is="type.icon" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ type.label }}</span>
          </div>
        </div>
      </div>

      <!-- Form Canvas -->
      <div
        ref="canvasRef"
        class="flex-1 p-6 overflow-y-auto"
        :class="mode === 'edit' ? 'bg-gray-50 dark:bg-gray-800/50' : ''"
        @dragover.prevent
        @drop="onDrop"
      >
        <!-- Empty State -->
        <div
          v-if="fields.length === 0 && mode === 'edit'"
          class="h-full flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl"
        >
          <div class="text-center">
            <CursorArrowRaysIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500 dark:text-gray-400">
              Drag fields here to build your form
            </p>
          </div>
        </div>

        <!-- Fields -->
        <div v-else class="space-y-4 max-w-2xl mx-auto">
          <TransitionGroup name="list">
            <div
              v-for="(field, index) in fields"
              :key="field.id"
              :class="[
                'relative',
                mode === 'edit' ? 'group' : ''
              ]"
              :draggable="mode === 'edit'"
              @dragstart="onFieldDragStart(index, $event)"
              @dragover.prevent="onFieldDragOver(index)"
              @drop="onFieldDrop(index)"
            >
              <!-- Edit Controls -->
              <div
                v-if="mode === 'edit'"
                class="absolute -left-10 top-1/2 -translate-y-1/2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <button
                  type="button"
                  class="p-1.5 bg-white dark:bg-gray-700 rounded shadow hover:bg-gray-50 dark:hover:bg-gray-600"
                  @click="moveField(index, -1)"
                  :disabled="index === 0"
                >
                  <ChevronUpIcon class="w-4 h-4 text-gray-500" />
                </button>
                <button
                  type="button"
                  class="p-1.5 bg-white dark:bg-gray-700 rounded shadow hover:bg-gray-50 dark:hover:bg-gray-600"
                  @click="moveField(index, 1)"
                  :disabled="index === fields.length - 1"
                >
                  <ChevronDownIcon class="w-4 h-4 text-gray-500" />
                </button>
              </div>

              <!-- Field Container -->
              <div
                :class="[
                  'p-4 rounded-lg transition-all',
                  mode === 'edit'
                    ? 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 cursor-grab'
                    : '',
                  selectedField?.id === field.id ? 'ring-2 ring-green-500' : ''
                ]"
                @click="mode === 'edit' && selectField(field)"
              >
                <!-- Field Label -->
                <label
                  v-if="field.label"
                  class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
                >
                  {{ field.label }}
                  <span v-if="field.required" class="text-red-500">*</span>
                </label>
                
                <!-- Field Description -->
                <p v-if="field.description" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                  {{ field.description }}
                </p>

                <!-- Render Field Based on Type -->
                <component
                  :is="getFieldComponent(field.type)"
                  v-model="formValues[field.name]"
                  :field="field"
                  :disabled="mode === 'edit'"
                />
              </div>

              <!-- Delete Button -->
              <button
                v-if="mode === 'edit'"
                type="button"
                class="absolute -right-3 -top-3 p-1.5 bg-red-500 text-white rounded-full shadow opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                @click.stop="removeField(index)"
              >
                <XMarkIcon class="w-3 h-3" />
              </button>
            </div>
          </TransitionGroup>
        </div>
      </div>

      <!-- Properties Panel -->
      <div v-if="mode === 'edit' && selectedField" class="w-72 border-l border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Properties</h4>
          <button
            type="button"
            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            @click="selectedField = null"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <!-- Label -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label</label>
            <input
              v-model="selectedField.label"
              type="text"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg"
            />
          </div>

          <!-- Name -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Field Name</label>
            <input
              v-model="selectedField.name"
              type="text"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-mono"
            />
          </div>

          <!-- Placeholder -->
          <div v-if="hasPlaceholder(selectedField.type)">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Placeholder</label>
            <input
              v-model="selectedField.placeholder"
              type="text"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
            <textarea
              v-model="selectedField.description"
              rows="2"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg"
            />
          </div>

          <!-- Required -->
          <div class="flex items-center gap-2">
            <input
              :id="`required-${selectedField.id}`"
              v-model="selectedField.required"
              type="checkbox"
              class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
            />
            <label :for="`required-${selectedField.id}`" class="text-sm text-gray-700 dark:text-gray-300">
              Required field
            </label>
          </div>

          <!-- Options (for select, radio, checkbox) -->
          <div v-if="hasOptions(selectedField.type)">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Options</label>
            <div class="space-y-2">
              <div
                v-for="(option, optIndex) in selectedField.options"
                :key="optIndex"
                class="flex items-center gap-2"
              >
                <input
                  v-model="option.label"
                  type="text"
                  placeholder="Label"
                  class="flex-1 px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded"
                />
                <input
                  v-model="option.value"
                  type="text"
                  placeholder="Value"
                  class="w-20 px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded font-mono"
                />
                <button
                  type="button"
                  class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                  @click="removeOption(optIndex)"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
              <button
                type="button"
                class="w-full py-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                @click="addOption"
              >
                + Add Option
              </button>
            </div>
          </div>

          <!-- Validation -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Validation</label>
            <div class="space-y-2">
              <div v-if="selectedField.type === 'text' || selectedField.type === 'textarea'" class="flex items-center gap-2">
                <input
                  v-model.number="selectedField.minLength"
                  type="number"
                  placeholder="Min"
                  class="w-20 px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded"
                />
                <span class="text-gray-500">-</span>
                <input
                  v-model.number="selectedField.maxLength"
                  type="number"
                  placeholder="Max"
                  class="w-20 px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded"
                />
                <span class="text-xs text-gray-500">chars</span>
              </div>
              <div v-if="selectedField.type === 'number'" class="flex items-center gap-2">
                <input
                  v-model.number="selectedField.min"
                  type="number"
                  placeholder="Min"
                  class="w-20 px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded"
                />
                <span class="text-gray-500">-</span>
                <input
                  v-model.number="selectedField.max"
                  type="number"
                  placeholder="Max"
                  class="w-20 px-2 py-1.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, h } from 'vue'
import {
  DocumentTextIcon,
  CursorArrowRaysIcon,
  XMarkIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  Bars3Icon,
  EnvelopeIcon,
  PhoneIcon,
  HashtagIcon,
  CalendarIcon,
  ClockIcon,
  CheckIcon,
  ListBulletIcon,
  DocumentIcon,
  PhotoIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  height: {
    type: String,
    default: '500px'
  },
  theme: {
    type: String,
    default: 'light',
    validator: (v) => ['light', 'dark'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'export'])

const canvasRef = ref(null)
const mode = ref('edit')
const fields = ref(props.modelValue.length > 0 ? props.modelValue : [])
const selectedField = ref(null)
const formValues = reactive({})
const draggedType = ref(null)
const draggedFieldIndex = ref(null)
const dropTargetIndex = ref(null)

const fieldTypes = [
  { value: 'text', label: 'Text', icon: Bars3Icon },
  { value: 'textarea', label: 'Text Area', icon: DocumentIcon },
  { value: 'email', label: 'Email', icon: EnvelopeIcon },
  { value: 'phone', label: 'Phone', icon: PhoneIcon },
  { value: 'number', label: 'Number', icon: HashtagIcon },
  { value: 'date', label: 'Date', icon: CalendarIcon },
  { value: 'time', label: 'Time', icon: ClockIcon },
  { value: 'select', label: 'Dropdown', icon: ListBulletIcon },
  { value: 'radio', label: 'Radio', icon: CheckIcon },
  { value: 'checkbox', label: 'Checkbox', icon: CheckIcon },
  { value: 'file', label: 'File Upload', icon: PhotoIcon }
]

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark'
    ? 'bg-gray-900 border-gray-700'
    : 'bg-white border-gray-200'
])

const generateId = () => `field_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`

const generateName = (type) => `${type}_${Date.now().toString(36)}`

const createField = (type) => {
  const base = {
    id: generateId(),
    type: type.value,
    name: generateName(type.value),
    label: type.label,
    placeholder: '',
    description: '',
    required: false
  }

  if (hasOptions(type.value)) {
    base.options = [
      { label: 'Option 1', value: 'option1' },
      { label: 'Option 2', value: 'option2' }
    ]
  }

  return base
}

const hasPlaceholder = (type) => {
  return ['text', 'textarea', 'email', 'phone', 'number'].includes(type)
}

const hasOptions = (type) => {
  return ['select', 'radio', 'checkbox'].includes(type)
}

const onDragStart = (type, event) => {
  draggedType.value = type
  event.dataTransfer.effectAllowed = 'copy'
}

const onDrop = (event) => {
  if (draggedType.value) {
    const newField = createField(draggedType.value)
    fields.value.push(newField)
    selectedField.value = newField
    draggedType.value = null
    emitUpdate()
  }
}

const onFieldDragStart = (index, event) => {
  draggedFieldIndex.value = index
  event.dataTransfer.effectAllowed = 'move'
}

const onFieldDragOver = (index) => {
  dropTargetIndex.value = index
}

const onFieldDrop = (index) => {
  if (draggedFieldIndex.value !== null && draggedFieldIndex.value !== index) {
    const field = fields.value.splice(draggedFieldIndex.value, 1)[0]
    fields.value.splice(index, 0, field)
    emitUpdate()
  }
  draggedFieldIndex.value = null
  dropTargetIndex.value = null
}

const selectField = (field) => {
  selectedField.value = field
}

const removeField = (index) => {
  const removed = fields.value.splice(index, 1)[0]
  if (selectedField.value?.id === removed.id) {
    selectedField.value = null
  }
  emitUpdate()
}

const moveField = (index, direction) => {
  const newIndex = index + direction
  if (newIndex >= 0 && newIndex < fields.value.length) {
    const field = fields.value.splice(index, 1)[0]
    fields.value.splice(newIndex, 0, field)
    emitUpdate()
  }
}

const addOption = () => {
  if (!selectedField.value.options) {
    selectedField.value.options = []
  }
  const num = selectedField.value.options.length + 1
  selectedField.value.options.push({
    label: `Option ${num}`,
    value: `option${num}`
  })
}

const removeOption = (index) => {
  selectedField.value.options.splice(index, 1)
}

const exportForm = () => {
  const json = JSON.stringify(fields.value, null, 2)
  emit('export', json)
  
  // Copy to clipboard
  navigator.clipboard.writeText(json)
    .then(() => alert('Form JSON copied to clipboard!'))
    .catch(console.error)
}

const emitUpdate = () => {
  emit('update:modelValue', [...fields.value])
}

// Field Component Renderers
const getFieldComponent = (type) => {
  return {
    render() {
      const field = this.field
      const modelValue = this.modelValue
      const disabled = this.disabled
      
      const baseClass = 'w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed'
      
      switch (type) {
        case 'text':
        case 'email':
        case 'phone':
          return h('input', {
            type: type === 'phone' ? 'tel' : type,
            value: modelValue,
            placeholder: field.placeholder,
            disabled,
            class: baseClass
          })
        
        case 'number':
          return h('input', {
            type: 'number',
            value: modelValue,
            placeholder: field.placeholder,
            min: field.min,
            max: field.max,
            disabled,
            class: baseClass
          })
        
        case 'date':
        case 'time':
          return h('input', {
            type,
            value: modelValue,
            disabled,
            class: baseClass
          })
        
        case 'textarea':
          return h('textarea', {
            value: modelValue,
            placeholder: field.placeholder,
            rows: 3,
            disabled,
            class: baseClass
          })
        
        case 'select':
          return h('select', { disabled, class: baseClass }, [
            h('option', { value: '' }, 'Select...'),
            ...(field.options || []).map(opt =>
              h('option', { value: opt.value }, opt.label)
            )
          ])
        
        case 'radio':
          return h('div', { class: 'space-y-2' },
            (field.options || []).map(opt =>
              h('label', { class: 'flex items-center gap-2 cursor-pointer' }, [
                h('input', {
                  type: 'radio',
                  name: field.name,
                  value: opt.value,
                  disabled,
                  class: 'w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500'
                }),
                h('span', { class: 'text-sm text-gray-700 dark:text-gray-300' }, opt.label)
              ])
            )
          )
        
        case 'checkbox':
          return h('div', { class: 'space-y-2' },
            (field.options || []).map(opt =>
              h('label', { class: 'flex items-center gap-2 cursor-pointer' }, [
                h('input', {
                  type: 'checkbox',
                  value: opt.value,
                  disabled,
                  class: 'w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500'
                }),
                h('span', { class: 'text-sm text-gray-700 dark:text-gray-300' }, opt.label)
              ])
            )
          )
        
        case 'file':
          return h('input', {
            type: 'file',
            disabled,
            class: 'w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100 disabled:opacity-50'
          })
        
        default:
          return h('input', { type: 'text', disabled, class: baseClass })
      }
    },
    props: ['field', 'modelValue', 'disabled']
  }
}
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.list-move {
  transition: transform 0.3s ease;
}
</style>
