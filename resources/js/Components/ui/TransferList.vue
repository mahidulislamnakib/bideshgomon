<template>
  <div :class="['transfer-list', themeClasses]">
    <div class="flex gap-4">
      <!-- Source List -->
      <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              :checked="allSourceSelected"
              :indeterminate="someSourceSelected"
              class="w-4 h-4 rounded border-gray-300 text-blue-600"
              @change="toggleAllSource"
            />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ sourceTitle }}
            </span>
          </div>
          <span class="text-xs text-gray-500">
            {{ sourceSelectedCount }}/{{ sourceItems.length }}
          </span>
        </div>

        <!-- Search -->
        <div v-if="filterable" class="p-2 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="sourceSearch"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg"
            />
          </div>
        </div>

        <!-- Items -->
        <div class="overflow-auto" :style="{ height: listHeight }">
          <label
            v-for="item in filteredSourceItems"
            :key="item[valueKey]"
            :class="[
              'flex items-center gap-3 px-4 py-2 cursor-pointer transition-colors',
              sourceSelected.includes(item[valueKey])
                ? 'bg-blue-50 dark:bg-blue-900/20'
                : 'hover:bg-gray-50 dark:hover:bg-gray-800'
            ]"
          >
            <input
              type="checkbox"
              :checked="sourceSelected.includes(item[valueKey])"
              class="w-4 h-4 rounded border-gray-300 text-blue-600"
              @change="toggleSourceItem(item)"
            />
            <slot name="item" :item="item">
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 dark:text-white truncate">{{ item[labelKey] }}</p>
                <p v-if="item.description" class="text-xs text-gray-500 truncate">{{ item.description }}</p>
              </div>
            </slot>
          </label>
          <div v-if="filteredSourceItems.length === 0" class="py-8 text-center">
            <InboxIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500">No items</p>
          </div>
        </div>
      </div>

      <!-- Transfer Buttons -->
      <div class="flex flex-col items-center justify-center gap-2">
        <button
          type="button"
          :disabled="sourceSelected.length === 0"
          class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          @click="transferToTarget"
        >
          <ChevronRightIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
        </button>
        <button
          type="button"
          :disabled="targetSelected.length === 0"
          class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          @click="transferToSource"
        >
          <ChevronLeftIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
        </button>
        <button
          v-if="showTransferAll"
          type="button"
          :disabled="sourceItems.length === 0"
          class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          @click="transferAllToTarget"
        >
          <ChevronDoubleRightIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
        </button>
        <button
          v-if="showTransferAll"
          type="button"
          :disabled="targetItems.length === 0"
          class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          @click="transferAllToSource"
        >
          <ChevronDoubleLeftIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
        </button>
      </div>

      <!-- Target List -->
      <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              :checked="allTargetSelected"
              :indeterminate="someTargetSelected"
              class="w-4 h-4 rounded border-gray-300 text-blue-600"
              @change="toggleAllTarget"
            />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ targetTitle }}
            </span>
          </div>
          <span class="text-xs text-gray-500">
            {{ targetSelectedCount }}/{{ targetItems.length }}
          </span>
        </div>

        <!-- Search -->
        <div v-if="filterable" class="p-2 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="targetSearch"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg"
            />
          </div>
        </div>

        <!-- Items -->
        <div class="overflow-auto" :style="{ height: listHeight }">
          <label
            v-for="item in filteredTargetItems"
            :key="item[valueKey]"
            :class="[
              'flex items-center gap-3 px-4 py-2 cursor-pointer transition-colors',
              targetSelected.includes(item[valueKey])
                ? 'bg-blue-50 dark:bg-blue-900/20'
                : 'hover:bg-gray-50 dark:hover:bg-gray-800'
            ]"
          >
            <input
              type="checkbox"
              :checked="targetSelected.includes(item[valueKey])"
              class="w-4 h-4 rounded border-gray-300 text-blue-600"
              @change="toggleTargetItem(item)"
            />
            <slot name="item" :item="item">
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 dark:text-white truncate">{{ item[labelKey] }}</p>
                <p v-if="item.description" class="text-xs text-gray-500 truncate">{{ item.description }}</p>
              </div>
            </slot>
          </label>
          <div v-if="filteredTargetItems.length === 0" class="py-8 text-center">
            <InboxIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500">No items</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  MagnifyingGlassIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon,
  InboxIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  data: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'value' },
  sourceTitle: { type: String, default: 'Available' },
  targetTitle: { type: String, default: 'Selected' },
  searchPlaceholder: { type: String, default: 'Search...' },
  listHeight: { type: String, default: '300px' },
  filterable: { type: Boolean, default: true },
  showTransferAll: { type: Boolean, default: true },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'change'])

const sourceSearch = ref('')
const targetSearch = ref('')
const sourceSelected = ref([])
const targetSelected = ref([])

const themeClasses = computed(() =>
  props.theme === 'dark' ? 'text-white' : 'text-gray-900'
)

const sourceItems = computed(() =>
  props.data.filter(item => !props.modelValue.includes(item[props.valueKey]))
)

const targetItems = computed(() =>
  props.data.filter(item => props.modelValue.includes(item[props.valueKey]))
)

const filteredSourceItems = computed(() => {
  if (!sourceSearch.value) return sourceItems.value
  const q = sourceSearch.value.toLowerCase()
  return sourceItems.value.filter(item =>
    item[props.labelKey].toLowerCase().includes(q)
  )
})

const filteredTargetItems = computed(() => {
  if (!targetSearch.value) return targetItems.value
  const q = targetSearch.value.toLowerCase()
  return targetItems.value.filter(item =>
    item[props.labelKey].toLowerCase().includes(q)
  )
})

const sourceSelectedCount = computed(() => sourceSelected.value.length)
const targetSelectedCount = computed(() => targetSelected.value.length)

const allSourceSelected = computed(() =>
  sourceItems.value.length > 0 &&
  sourceItems.value.every(item => sourceSelected.value.includes(item[props.valueKey]))
)

const someSourceSelected = computed(() =>
  sourceSelected.value.length > 0 && !allSourceSelected.value
)

const allTargetSelected = computed(() =>
  targetItems.value.length > 0 &&
  targetItems.value.every(item => targetSelected.value.includes(item[props.valueKey]))
)

const someTargetSelected = computed(() =>
  targetSelected.value.length > 0 && !allTargetSelected.value
)

const toggleSourceItem = (item) => {
  const value = item[props.valueKey]
  const index = sourceSelected.value.indexOf(value)
  if (index > -1) {
    sourceSelected.value.splice(index, 1)
  } else {
    sourceSelected.value.push(value)
  }
}

const toggleTargetItem = (item) => {
  const value = item[props.valueKey]
  const index = targetSelected.value.indexOf(value)
  if (index > -1) {
    targetSelected.value.splice(index, 1)
  } else {
    targetSelected.value.push(value)
  }
}

const toggleAllSource = () => {
  if (allSourceSelected.value) {
    sourceSelected.value = []
  } else {
    sourceSelected.value = sourceItems.value.map(item => item[props.valueKey])
  }
}

const toggleAllTarget = () => {
  if (allTargetSelected.value) {
    targetSelected.value = []
  } else {
    targetSelected.value = targetItems.value.map(item => item[props.valueKey])
  }
}

const transferToTarget = () => {
  const newValue = [...props.modelValue, ...sourceSelected.value]
  emit('update:modelValue', newValue)
  emit('change', newValue)
  sourceSelected.value = []
}

const transferToSource = () => {
  const newValue = props.modelValue.filter(v => !targetSelected.value.includes(v))
  emit('update:modelValue', newValue)
  emit('change', newValue)
  targetSelected.value = []
}

const transferAllToTarget = () => {
  const newValue = props.data.map(item => item[props.valueKey])
  emit('update:modelValue', newValue)
  emit('change', newValue)
  sourceSelected.value = []
}

const transferAllToSource = () => {
  emit('update:modelValue', [])
  emit('change', [])
  targetSelected.value = []
}
</script>
