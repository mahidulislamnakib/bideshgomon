<template>
  <div :class="['kanban-board', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
          <ViewColumnsIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ totalTasks }} tasks across {{ columns.length }} columns
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
          @click="showAddColumn = true"
        >
          + Add Column
        </button>
      </div>
    </div>

    <!-- Board -->
    <div class="flex gap-4 p-4 overflow-x-auto" :style="{ height }">
      <!-- Columns -->
      <div
        v-for="(column, colIndex) in columns"
        :key="column.id"
        class="flex-shrink-0 w-72 flex flex-col bg-gray-100 dark:bg-gray-800 rounded-xl"
        @dragover.prevent
        @drop="onDropCard(colIndex, $event)"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-2">
            <div
              class="w-3 h-3 rounded-full"
              :style="{ backgroundColor: column.color || '#6B7280' }"
            />
            <h4 class="font-medium text-gray-900 dark:text-white">{{ column.title }}</h4>
            <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">
              {{ column.cards?.length || 0 }}
            </span>
          </div>
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
              @click="addCard(colIndex)"
            >
              <PlusIcon class="w-4 h-4" />
            </button>
            <button
              type="button"
              class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
              @click="deleteColumn(colIndex)"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Cards Container -->
        <div class="flex-1 p-2 space-y-2 overflow-y-auto">
          <TransitionGroup name="card">
            <div
              v-for="(card, cardIndex) in column.cards"
              :key="card.id"
              class="relative bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 cursor-grab hover:shadow-md transition-shadow"
              draggable="true"
              @dragstart="onDragStart(colIndex, cardIndex, $event)"
              @dragend="onDragEnd"
              @click="editCard(colIndex, cardIndex)"
            >
              <!-- Priority Indicator -->
              <div
                v-if="card.priority"
                class="absolute top-0 left-0 w-1 h-full rounded-l-lg"
                :style="{ backgroundColor: getPriorityColor(card.priority) }"
              />

              <!-- Card Labels -->
              <div v-if="card.labels?.length" class="flex flex-wrap gap-1 mb-2">
                <span
                  v-for="label in card.labels"
                  :key="label"
                  class="px-2 py-0.5 text-xs font-medium rounded-full"
                  :style="{ backgroundColor: getLabelColor(label) + '20', color: getLabelColor(label) }"
                >
                  {{ label }}
                </span>
              </div>

              <!-- Card Title -->
              <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                {{ card.title }}
              </h5>

              <!-- Card Description -->
              <p v-if="card.description" class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2">
                {{ card.description }}
              </p>

              <!-- Card Footer -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <!-- Due Date -->
                  <span
                    v-if="card.dueDate"
                    :class="[
                      'flex items-center gap-1 text-xs',
                      isOverdue(card.dueDate) ? 'text-red-500' : 'text-gray-500 dark:text-gray-400'
                    ]"
                  >
                    <CalendarIcon class="w-3 h-3" />
                    {{ formatDate(card.dueDate) }}
                  </span>
                </div>

                <!-- Assignee -->
                <div v-if="card.assignee" class="flex items-center">
                  <div
                    class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium"
                    :title="card.assignee"
                  >
                    {{ card.assignee.charAt(0).toUpperCase() }}
                  </div>
                </div>
              </div>
            </div>
          </TransitionGroup>

          <!-- Add Card Button -->
          <button
            type="button"
            class="w-full p-2 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center justify-center gap-1"
            @click="addCard(colIndex)"
          >
            <PlusIcon class="w-4 h-4" />
            Add Card
          </button>
        </div>
      </div>

      <!-- Add Column Placeholder -->
      <div
        v-if="showAddColumn"
        class="flex-shrink-0 w-72 bg-gray-100 dark:bg-gray-800 rounded-xl p-3"
      >
        <input
          v-model="newColumnTitle"
          type="text"
          placeholder="Column title..."
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg mb-2"
          @keydown.enter="createColumn"
          @keydown.esc="showAddColumn = false"
        />
        <div class="flex gap-2">
          <button
            type="button"
            class="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg"
            @click="createColumn"
          >
            Add
          </button>
          <button
            type="button"
            class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg"
            @click="showAddColumn = false"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Card Edit Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-200"
        leave-to-class="opacity-0"
      >
        <div
          v-if="editingCard"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="editingCard = null"
        >
          <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
              <input
                v-model="editingCard.card.title"
                type="text"
                class="w-full text-lg font-semibold bg-transparent text-gray-900 dark:text-white border-none outline-none"
                placeholder="Card title..."
              />
            </div>
            
            <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea
                  v-model="editingCard.card.description"
                  rows="3"
                  class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg"
                  placeholder="Add a description..."
                />
              </div>

              <!-- Labels -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Labels</label>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="label in availableLabels"
                    :key="label"
                    :class="[
                      'px-3 py-1 text-xs font-medium rounded-full transition-colors',
                      editingCard.card.labels?.includes(label)
                        ? 'ring-2 ring-offset-1 ring-gray-400'
                        : ''
                    ]"
                    :style="{ backgroundColor: getLabelColor(label) + '20', color: getLabelColor(label) }"
                    @click="toggleLabel(label)"
                  >
                    {{ label }}
                  </button>
                </div>
              </div>

              <!-- Due Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                <input
                  v-model="editingCard.card.dueDate"
                  type="date"
                  class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg"
                />
              </div>

              <!-- Priority -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
                <div class="flex gap-2">
                  <button
                    v-for="p in priorities"
                    :key="p.value"
                    :class="[
                      'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                      editingCard.card.priority === p.value
                        ? 'ring-2 ring-offset-1'
                        : ''
                    ]"
                    :style="{ backgroundColor: p.color + '20', color: p.color }"
                    @click="editingCard.card.priority = p.value"
                  >
                    {{ p.label }}
                  </button>
                </div>
              </div>

              <!-- Assignee -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assignee</label>
                <input
                  v-model="editingCard.card.assignee"
                  type="text"
                  class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg"
                  placeholder="Assign to..."
                />
              </div>
            </div>

            <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700">
              <button
                type="button"
                class="px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
                @click="deleteCard(editingCard.colIndex, editingCard.cardIndex)"
              >
                Delete Card
              </button>
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg"
                @click="saveCard"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed} from 'vue'
import { Teleport, Transition, TransitionGroup } from 'vue'
import {
  ViewColumnsIcon,
  PlusIcon,
  TrashIcon,
  CalendarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'Kanban Board' },
  initialColumns: {
    type: Array,
    default: () => [
      { id: 'todo', title: 'To Do', color: '#6B7280', cards: [
        { id: 1, title: 'Research competitors', description: 'Analyze top 5 competitors', labels: ['Research'], priority: 'high', dueDate: '2025-01-15' },
        { id: 2, title: 'Create wireframes', labels: ['Design'], priority: 'medium' }
      ]},
      { id: 'progress', title: 'In Progress', color: '#3B82F6', cards: [
        { id: 3, title: 'Develop API endpoints', description: 'REST API for user management', labels: ['Development'], assignee: 'John', priority: 'high' }
      ]},
      { id: 'review', title: 'Review', color: '#F59E0B', cards: [
        { id: 4, title: 'Code review for auth module', labels: ['Development', 'Review'] }
      ]},
      { id: 'done', title: 'Done', color: '#10B981', cards: [
        { id: 5, title: 'Project setup', labels: ['Setup'], priority: 'low' }
      ]}
    ]
  },
  height: { type: String, default: '600px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true }
})

const emit = defineEmits(['change', 'card-move', 'card-create', 'card-delete'])

const columns = ref(JSON.parse(JSON.stringify(props.initialColumns)))
const draggedCard = ref(null)
const editingCard = ref(null)
const showAddColumn = ref(false)
const newColumnTitle = ref('')

const availableLabels = ['Bug', 'Feature', 'Design', 'Research', 'Development', 'Review', 'Testing', 'Documentation']
const priorities = [
  { value: 'low', label: 'Low', color: '#10B981' },
  { value: 'medium', label: 'Medium', color: '#F59E0B' },
  { value: 'high', label: 'High', color: '#EF4444' }
]

const labelColors = {
  'Bug': '#EF4444', 'Feature': '#3B82F6', 'Design': '#8B5CF6', 'Research': '#EC4899',
  'Development': '#10B981', 'Review': '#F59E0B', 'Testing': '#06B6D4', 'Documentation': '#6B7280', 'Setup': '#A855F7'
}

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
])

const totalTasks = computed(() => columns.value.reduce((sum, col) => sum + (col.cards?.length || 0), 0))

const getLabelColor = (label) => labelColors[label] || '#6B7280'
const getPriorityColor = (priority) => priorities.find(p => p.value === priority)?.color || '#6B7280'
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
const isOverdue = (dateStr) => new Date(dateStr) < new Date()

const onDragStart = (colIndex, cardIndex, event) => {
  draggedCard.value = { colIndex, cardIndex }
  event.dataTransfer.effectAllowed = 'move'
}

const onDragEnd = () => { draggedCard.value = null }

const onDropCard = (targetColIndex) => {
  if (!draggedCard.value) return
  const { colIndex: sourceColIndex, cardIndex: sourceCardIndex } = draggedCard.value
  if (sourceColIndex === targetColIndex) return
  const card = columns.value[sourceColIndex].cards.splice(sourceCardIndex, 1)[0]
  columns.value[targetColIndex].cards.push(card)
  emit('card-move', { card, fromColumn: sourceColIndex, toColumn: targetColIndex })
  emit('change', columns.value)
  draggedCard.value = null
}

const addCard = (colIndex) => {
  const newCard = { id: Date.now(), title: 'New Card', description: '', labels: [], priority: null, dueDate: null, assignee: null }
  if (!columns.value[colIndex].cards) columns.value[colIndex].cards = []
  columns.value[colIndex].cards.push(newCard)
  editingCard.value = { colIndex, cardIndex: columns.value[colIndex].cards.length - 1, card: newCard }
  emit('card-create', { card: newCard, column: colIndex })
  emit('change', columns.value)
}

const editCard = (colIndex, cardIndex) => {
  editingCard.value = { colIndex, cardIndex, card: { ...columns.value[colIndex].cards[cardIndex] } }
}

const saveCard = () => {
  if (!editingCard.value) return
  const { colIndex, cardIndex, card } = editingCard.value
  columns.value[colIndex].cards[cardIndex] = card
  emit('change', columns.value)
  editingCard.value = null
}

const deleteCard = (colIndex, cardIndex) => {
  const card = columns.value[colIndex].cards.splice(cardIndex, 1)[0]
  emit('card-delete', { card, column: colIndex })
  emit('change', columns.value)
  editingCard.value = null
}

const toggleLabel = (label) => {
  if (!editingCard.value.card.labels) editingCard.value.card.labels = []
  const index = editingCard.value.card.labels.indexOf(label)
  if (index === -1) editingCard.value.card.labels.push(label)
  else editingCard.value.card.labels.splice(index, 1)
}

const createColumn = () => {
  if (!newColumnTitle.value.trim()) return
  columns.value.push({ id: `col_${Date.now()}`, title: newColumnTitle.value.trim(), color: '#6B7280', cards: [] })
  newColumnTitle.value = ''
  showAddColumn.value = false
  emit('change', columns.value)
}

const deleteColumn = (colIndex) => {
  if (confirm('Delete this column and all its cards?')) {
    columns.value.splice(colIndex, 1)
    emit('change', columns.value)
  }
}
</script>

<style scoped>
.card-enter-active, .card-leave-active { transition: all 0.3s ease; }
.card-enter-from, .card-leave-to { opacity: 0; transform: translateY(-10px); }
.card-move { transition: transform 0.3s ease; }
</style>
