<template>
  <div :class="containerClasses">
    <!-- Columns -->
    <div
      v-for="column in columns"
      :key="column.id"
      :class="columnClasses"
      @dragover.prevent="handleDragOver($event, column)"
      @dragleave="handleDragLeave"
      @drop="handleDrop($event, column)"
    >
      <!-- Column header -->
      <div :class="columnHeaderClasses">
        <div class="flex items-center gap-2">
          <div
            v-if="column.color"
            class="w-3 h-3 rounded-full"
            :style="{ backgroundColor: column.color }"
          />
          <h3 :class="columnTitleClasses">{{ column.title }}</h3>
          <span :class="countBadgeClasses">
            {{ getColumnItems(column.id).length }}
          </span>
        </div>
        
        <button
          v-if="allowAddCard"
          type="button"
          :class="addButtonClasses"
          @click="$emit('addCard', column.id)"
        >
          <PlusIcon class="w-4 h-4" />
        </button>
      </div>
      
      <!-- Cards -->
      <div :class="cardsContainerClasses">
        <TransitionGroup name="kanban" tag="div" class="space-y-2 min-h-[50px]">
          <div
            v-for="item in getColumnItems(column.id)"
            :key="item.id"
            :class="cardClasses(item)"
            :draggable="!disabled"
            @dragstart="handleDragStart($event, item, column)"
            @dragend="handleDragEnd"
            @click="$emit('cardClick', item)"
          >
            <!-- Card color bar -->
            <div
              v-if="item.color"
              class="absolute top-0 left-0 right-0 h-1 rounded-t-lg"
              :style="{ backgroundColor: item.color }"
            />
            
            <!-- Card content -->
            <div :class="cardContentClasses">
              <!-- Title -->
              <h4 :class="cardTitleClasses">{{ item.title }}</h4>
              
              <!-- Description -->
              <p v-if="item.description" :class="cardDescriptionClasses">
                {{ item.description }}
              </p>
              
              <!-- Tags -->
              <div v-if="item.tags?.length" class="flex flex-wrap gap-1 mt-2">
                <span
                  v-for="tag in item.tags"
                  :key="tag.id || tag"
                  :class="tagClasses"
                  :style="tag.color ? { backgroundColor: tag.color + '20', color: tag.color } : {}"
                >
                  {{ tag.label || tag }}
                </span>
              </div>
              
              <!-- Footer -->
              <div v-if="item.assignee || item.dueDate || item.priority" :class="cardFooterClasses">
                <!-- Assignee -->
                <div v-if="item.assignee" class="flex items-center gap-1">
                  <img
                    v-if="item.assignee.avatar"
                    :src="item.assignee.avatar"
                    :alt="item.assignee.name"
                    class="w-5 h-5 rounded-full"
                  />
                  <span v-else class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-xs">
                    {{ item.assignee.name?.charAt(0).toUpperCase() }}
                  </span>
                </div>
                
                <!-- Due date -->
                <div v-if="item.dueDate" class="flex items-center gap-1 text-xs">
                  <CalendarIcon class="w-3 h-3" />
                  <span :class="dueDateClasses(item)">{{ formatDate(item.dueDate) }}</span>
                </div>
                
                <!-- Priority -->
                <div v-if="item.priority" :class="priorityClasses(item.priority)">
                  <FlagIcon class="w-3 h-3" />
                </div>
              </div>
            </div>
            
            <!-- Actions menu -->
            <div v-if="showActions" :class="actionsClasses">
              <button
                type="button"
                class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                @click.stop="$emit('cardAction', { action: 'menu', item })"
              >
                <EllipsisVerticalIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </TransitionGroup>
        
        <!-- Drop indicator -->
        <div
          v-if="dragOverColumn === column.id && dragItem"
          :class="dropIndicatorClasses"
        >
          Drop here
        </div>
        
        <!-- Empty state -->
        <div
          v-if="getColumnItems(column.id).length === 0 && !dragOverColumn"
          :class="emptyStateClasses"
        >
          <span>No items</span>
        </div>
      </div>
      
      <!-- Add card button (bottom) -->
      <button
        v-if="allowAddCard && addCardPosition === 'bottom'"
        type="button"
        :class="addCardButtonClasses"
        @click="$emit('addCard', column.id)"
      >
        <PlusIcon class="w-4 h-4" />
        <span>Add card</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  PlusIcon, 
  CalendarIcon,
  FlagIcon,
  EllipsisVerticalIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  columns: {
    type: Array,
    required: true
    // { id, title, color? }
  },
  items: {
    type: Array,
    required: true
    // { id, columnId, title, description?, color?, tags?, assignee?, dueDate?, priority? }
  },
  
  // Features
  allowAddCard: {
    type: Boolean,
    default: true
  },
  addCardPosition: {
    type: String,
    default: 'bottom',
    validator: (v) => ['top', 'bottom'].includes(v)
  },
  showActions: {
    type: Boolean,
    default: true
  },
  
  // State
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:items', 'cardClick', 'cardAction', 'addCard', 'move'])

const dragItem = ref(null)
const dragColumn = ref(null)
const dragOverColumn = ref(null)

// Get items for a column
function getColumnItems(columnId) {
  return props.items.filter(item => item.columnId === columnId)
}

// Format date
function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diff = d - now
  const days = Math.ceil(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) return 'Today'
  if (days === 1) return 'Tomorrow'
  if (days === -1) return 'Yesterday'
  
  return d.toLocaleDateString('en-BD', { day: 'numeric', month: 'short' })
}

// Container classes
const containerClasses = computed(() => [
  'flex gap-4 overflow-x-auto pb-4',
  'min-h-[400px]'
])

// Column classes
const columnClasses = computed(() => [
  'flex-shrink-0 w-72',
  'bg-gray-100 dark:bg-gray-800/50 rounded-xl',
  'flex flex-col'
])

// Column header classes
const columnHeaderClasses = computed(() => [
  'flex items-center justify-between',
  'px-3 py-2 border-b border-gray-200 dark:border-gray-700'
])

// Column title classes
const columnTitleClasses = computed(() => [
  'font-semibold text-gray-900 dark:text-white text-sm'
])

// Count badge classes
const countBadgeClasses = computed(() => [
  'px-1.5 py-0.5 text-xs rounded-full',
  'bg-gray-200 dark:bg-gray-700',
  'text-gray-600 dark:text-gray-400'
])

// Add button classes
const addButtonClasses = computed(() => [
  'p-1 rounded-lg',
  'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
  'hover:bg-gray-200 dark:hover:bg-gray-700',
  'transition-colors'
])

// Cards container classes
const cardsContainerClasses = computed(() => [
  'flex-1 p-2 overflow-y-auto',
  'max-h-[calc(100vh-200px)]'
])

// Card classes
function cardClasses(item) {
  return [
    'relative group cursor-pointer',
    'bg-white dark:bg-gray-800 rounded-lg shadow-sm',
    'border border-gray-200 dark:border-gray-700',
    'hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600',
    'transition-all duration-200',
    item.color && 'pt-1',
    props.disabled && 'opacity-60 cursor-not-allowed'
  ]
}

// Card content classes
const cardContentClasses = computed(() => [
  'p-3'
])

// Card title classes
const cardTitleClasses = computed(() => [
  'font-medium text-gray-900 dark:text-white text-sm',
  'line-clamp-2'
])

// Card description classes
const cardDescriptionClasses = computed(() => [
  'mt-1 text-xs text-gray-500 dark:text-gray-400',
  'line-clamp-2'
])

// Tag classes
const tagClasses = computed(() => [
  'px-1.5 py-0.5 text-xs rounded',
  'bg-gray-100 dark:bg-gray-700',
  'text-gray-600 dark:text-gray-300'
])

// Card footer classes
const cardFooterClasses = computed(() => [
  'flex items-center gap-2 mt-3 pt-2',
  'border-t border-gray-100 dark:border-gray-700'
])

// Due date classes
function dueDateClasses(item) {
  const d = new Date(item.dueDate)
  const now = new Date()
  const isOverdue = d < now
  const isSoon = d - now < 1000 * 60 * 60 * 24 * 2 // 2 days
  
  return [
    isOverdue && 'text-red-600 dark:text-red-400',
    isSoon && !isOverdue && 'text-amber-600 dark:text-amber-400',
    !isOverdue && !isSoon && 'text-gray-500 dark:text-gray-400'
  ]
}

// Priority classes
function priorityClasses(priority) {
  const colors = {
    high: 'text-red-500',
    medium: 'text-amber-500',
    low: 'text-blue-500'
  }
  return colors[priority] || 'text-gray-400'
}

// Actions classes
const actionsClasses = computed(() => [
  'absolute top-2 right-2',
  'opacity-0 group-hover:opacity-100',
  'transition-opacity'
])

// Drop indicator classes
const dropIndicatorClasses = computed(() => [
  'border-2 border-dashed border-primary-400 rounded-lg',
  'p-4 text-center text-sm text-primary-600',
  'bg-primary-50 dark:bg-primary-900/20'
])

// Empty state classes
const emptyStateClasses = computed(() => [
  'text-center py-8 text-sm text-gray-400'
])

// Add card button classes
const addCardButtonClasses = computed(() => [
  'flex items-center gap-2 w-full p-2',
  'text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
  'hover:bg-gray-200 dark:hover:bg-gray-700',
  'rounded-lg transition-colors'
])

// Drag handlers
function handleDragStart(event, item, column) {
  if (props.disabled) return
  
  dragItem.value = item
  dragColumn.value = column
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', item.id)
}

function handleDragOver(event, column) {
  if (props.disabled || !dragItem.value) return
  
  event.dataTransfer.dropEffect = 'move'
  dragOverColumn.value = column.id
}

function handleDragLeave() {
  dragOverColumn.value = null
}

function handleDrop(event, column) {
  if (props.disabled || !dragItem.value) return
  
  const fromColumnId = dragColumn.value.id
  const toColumnId = column.id
  
  if (fromColumnId !== toColumnId) {
    const updatedItems = props.items.map(item => {
      if (item.id === dragItem.value.id) {
        return { ...item, columnId: toColumnId }
      }
      return item
    })
    
    emit('update:items', updatedItems)
    emit('move', {
      item: dragItem.value,
      from: fromColumnId,
      to: toColumnId
    })
  }
  
  handleDragEnd()
}

function handleDragEnd() {
  dragItem.value = null
  dragColumn.value = null
  dragOverColumn.value = null
}

// Expose
defineExpose({
  getColumnItems
})
</script>

<style scoped>
.kanban-enter-active,
.kanban-leave-active {
  transition: all 0.3s ease;
}

.kanban-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.kanban-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.kanban-move {
  transition: transform 0.3s ease;
}
</style>
