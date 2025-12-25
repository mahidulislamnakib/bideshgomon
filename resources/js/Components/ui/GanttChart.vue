<template>
  <div :class="['gantt-chart', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
          <CalendarDaysIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ tasks.length }} tasks • {{ formatDateRange(dateRange.start, dateRange.end) }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
          <button
            v-for="view in views"
            :key="view.value"
            :class="[
              'px-3 py-1.5 text-sm font-medium transition-colors',
              currentView === view.value
                ? 'bg-blue-600 text-white'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            @click="currentView = view.value"
          >
            {{ view.label }}
          </button>
        </div>
        <button
          type="button"
          class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="scrollToToday"
          title="Go to Today"
        >
          <CalendarIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <div class="flex overflow-hidden" :style="{ height }">
      <!-- Task List Panel -->
      <div class="w-64 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
        <!-- Task List Header -->
        <div class="sticky top-0 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-3 font-medium text-sm text-gray-700 dark:text-gray-300">
          Task Name
        </div>
        
        <!-- Tasks -->
        <div
          v-for="task in sortedTasks"
          :key="task.id"
          :class="[
            'flex items-center gap-2 px-3 py-2.5 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition-colors',
            selectedTask?.id === task.id
              ? 'bg-blue-50 dark:bg-blue-900/20'
              : 'hover:bg-gray-50 dark:hover:bg-gray-800'
          ]"
          :style="{ paddingLeft: `${(task.level || 0) * 16 + 12}px` }"
          @click="selectTask(task)"
        >
          <div
            class="w-3 h-3 rounded-full flex-shrink-0"
            :style="{ backgroundColor: task.color || getTaskColor(task.status) }"
          />
          <span class="text-sm text-gray-900 dark:text-white truncate flex-1">
            {{ task.name }}
          </span>
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ task.progress || 0 }}%
          </span>
        </div>
      </div>

      <!-- Timeline Panel -->
      <div ref="timelineRef" class="flex-1 overflow-x-auto overflow-y-auto">
        <!-- Timeline Header -->
        <div class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <!-- Month/Week Row -->
          <div class="flex">
            <div
              v-for="group in timeGroups"
              :key="group.key"
              class="text-center text-xs font-medium text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 py-2"
              :style="{ width: `${group.width}px` }"
            >
              {{ group.label }}
            </div>
          </div>
          <!-- Day/Hour Row -->
          <div class="flex">
            <div
              v-for="unit in timeUnits"
              :key="unit.key"
              :class="[
                'text-center text-xs border-r border-gray-200 dark:border-gray-700 py-1.5',
                unit.isToday ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-500 dark:text-gray-500',
                unit.isWeekend ? 'bg-gray-100 dark:bg-gray-700/50' : ''
              ]"
              :style="{ width: `${unitWidth}px` }"
            >
              {{ unit.label }}
            </div>
          </div>
        </div>

        <!-- Timeline Body -->
        <div class="relative" :style="{ width: `${totalWidth}px` }">
          <!-- Grid Lines -->
          <div class="absolute inset-0 flex">
            <div
              v-for="unit in timeUnits"
              :key="`grid-${unit.key}`"
              :class="[
                'border-r border-gray-100 dark:border-gray-700/50',
                unit.isWeekend ? 'bg-gray-50 dark:bg-gray-800/30' : ''
              ]"
              :style="{ width: `${unitWidth}px`, height: `${sortedTasks.length * rowHeight}px` }"
            />
          </div>

          <!-- Today Line -->
          <div
            v-if="todayPosition >= 0"
            class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-20"
            :style="{ left: `${todayPosition}px`, height: `${sortedTasks.length * rowHeight}px` }"
          >
            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-red-500 rounded-full" />
          </div>

          <!-- Task Bars -->
          <div
            v-for="(task, index) in sortedTasks"
            :key="`bar-${task.id}`"
            class="absolute flex items-center"
            :style="{
              top: `${index * rowHeight}px`,
              height: `${rowHeight}px`,
              left: `${getTaskLeft(task)}px`,
              width: `${getTaskWidth(task)}px`
            }"
          >
            <div
              :class="[
                'relative h-7 rounded-md shadow-sm cursor-pointer transition-all group',
                selectedTask?.id === task.id ? 'ring-2 ring-blue-500 ring-offset-1' : ''
              ]"
              :style="{
                width: '100%',
                backgroundColor: task.color || getTaskColor(task.status)
              }"
              @click="selectTask(task)"
            >
              <!-- Progress -->
              <div
                class="absolute inset-y-0 left-0 rounded-l-md bg-black/20"
                :style="{ width: `${task.progress || 0}%` }"
              />
              
              <!-- Label -->
              <div class="absolute inset-0 flex items-center px-2">
                <span class="text-xs font-medium text-white truncate">
                  {{ task.name }}
                </span>
              </div>

              <!-- Resize Handles -->
              <div
                v-if="selectedTask?.id === task.id"
                class="absolute left-0 top-0 bottom-0 w-1.5 cursor-ew-resize bg-white/30 rounded-l-md opacity-0 group-hover:opacity-100 transition-opacity"
                @mousedown.stop="startResize(task, 'left', $event)"
              />
              <div
                v-if="selectedTask?.id === task.id"
                class="absolute right-0 top-0 bottom-0 w-1.5 cursor-ew-resize bg-white/30 rounded-r-md opacity-0 group-hover:opacity-100 transition-opacity"
                @mousedown.stop="startResize(task, 'right', $event)"
              />
            </div>

            <!-- Dependencies -->
            <svg
              v-if="task.dependencies?.length"
              class="absolute pointer-events-none overflow-visible"
              :style="{ left: 0, top: `${rowHeight / 2}px` }"
            >
              <path
                v-for="depId in task.dependencies"
                :key="`dep-${task.id}-${depId}`"
                :d="getDependencyPath(task, depId)"
                fill="none"
                stroke="#9CA3AF"
                stroke-width="1.5"
                stroke-dasharray="4,2"
                marker-end="url(#arrowhead)"
              />
            </svg>
          </div>

          <!-- Arrow Marker Definition -->
          <svg class="absolute w-0 h-0">
            <defs>
              <marker
                id="arrowhead"
                markerWidth="6"
                markerHeight="6"
                refX="5"
                refY="3"
                orient="auto"
              >
                <polygon points="0,0 6,3 0,6" fill="#9CA3AF" />
              </marker>
            </defs>
          </svg>
        </div>
      </div>
    </div>

    <!-- Task Details Panel -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="translate-y-full opacity-0"
      leave-active-class="transition-all duration-200"
      leave-to-class="translate-y-full opacity-0"
    >
      <div
        v-if="selectedTask && showDetails"
        class="border-t border-gray-200 dark:border-gray-700 p-4"
      >
        <div class="flex items-start justify-between">
          <div>
            <h4 class="font-semibold text-gray-900 dark:text-white">{{ selectedTask.name }}</h4>
            <p v-if="selectedTask.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ selectedTask.description }}
            </p>
          </div>
          <button
            type="button"
            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            @click="selectedTask = null"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
        
        <div class="grid grid-cols-4 gap-4 mt-4">
          <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Start Date</span>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ formatDate(selectedTask.startDate) }}
            </p>
          </div>
          <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">End Date</span>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ formatDate(selectedTask.endDate) }}
            </p>
          </div>
          <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Progress</span>
            <div class="flex items-center gap-2">
              <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :style="{
                    width: `${selectedTask.progress || 0}%`,
                    backgroundColor: selectedTask.color || getTaskColor(selectedTask.status)
                  }"
                />
              </div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ selectedTask.progress || 0 }}%
              </span>
            </div>
          </div>
          <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Status</span>
            <span
              :class="[
                'inline-flex px-2 py-0.5 text-xs font-medium rounded-full',
                getStatusClasses(selectedTask.status)
              ]"
            >
              {{ selectedTask.status || 'Not Started' }}
            </span>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import {
  CalendarDaysIcon,
  CalendarIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    default: 'Project Timeline'
  },
  tasks: {
    type: Array,
    default: () => [
      { id: 1, name: 'Project Planning', startDate: '2025-01-01', endDate: '2025-01-10', progress: 100, status: 'completed', color: '#10B981' },
      { id: 2, name: 'Design Phase', startDate: '2025-01-08', endDate: '2025-01-20', progress: 80, status: 'in-progress', dependencies: [1] },
      { id: 3, name: 'Development', startDate: '2025-01-18', endDate: '2025-02-15', progress: 40, status: 'in-progress', dependencies: [2] },
      { id: 4, name: 'Backend API', startDate: '2025-01-18', endDate: '2025-02-05', progress: 60, status: 'in-progress', level: 1, dependencies: [2] },
      { id: 5, name: 'Frontend UI', startDate: '2025-01-25', endDate: '2025-02-10', progress: 30, status: 'in-progress', level: 1, dependencies: [2] },
      { id: 6, name: 'Testing', startDate: '2025-02-10', endDate: '2025-02-20', progress: 0, status: 'not-started', dependencies: [3] },
      { id: 7, name: 'Deployment', startDate: '2025-02-18', endDate: '2025-02-25', progress: 0, status: 'not-started', dependencies: [6] }
    ]
  },
  height: {
    type: String,
    default: '400px'
  },
  theme: {
    type: String,
    default: 'light',
    validator: (v) => ['light', 'dark'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showDetails: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['task-click', 'task-update'])

const timelineRef = ref(null)
const currentView = ref('day')
const selectedTask = ref(null)
const resizing = ref(null)

const views = [
  { value: 'day', label: 'Day' },
  { value: 'week', label: 'Week' },
  { value: 'month', label: 'Month' }
]

const rowHeight = 40

const unitWidth = computed(() => {
  switch (currentView.value) {
    case 'day': return 40
    case 'week': return 80
    case 'month': return 30
    default: return 40
  }
})

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark'
    ? 'bg-gray-900 border-gray-700'
    : 'bg-white border-gray-200'
])

const dateRange = computed(() => {
  if (props.tasks.length === 0) {
    const today = new Date()
    return {
      start: new Date(today.getFullYear(), today.getMonth(), 1),
      end: new Date(today.getFullYear(), today.getMonth() + 2, 0)
    }
  }
  
  let minDate = new Date(props.tasks[0].startDate)
  let maxDate = new Date(props.tasks[0].endDate)
  
  props.tasks.forEach(task => {
    const start = new Date(task.startDate)
    const end = new Date(task.endDate)
    if (start < minDate) minDate = start
    if (end > maxDate) maxDate = end
  })
  
  // Add padding
  minDate.setDate(minDate.getDate() - 7)
  maxDate.setDate(maxDate.getDate() + 7)
  
  return { start: minDate, end: maxDate }
})

const sortedTasks = computed(() => {
  return [...props.tasks].sort((a, b) => new Date(a.startDate) - new Date(b.startDate))
})

const timeUnits = computed(() => {
  const units = []
  const current = new Date(dateRange.value.start)
  const end = dateRange.value.end
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  while (current <= end) {
    const isToday = current.getTime() === today.getTime()
    const isWeekend = current.getDay() === 0 || current.getDay() === 6
    
    if (currentView.value === 'day') {
      units.push({
        key: current.toISOString(),
        label: current.getDate(),
        date: new Date(current),
        isToday,
        isWeekend
      })
      current.setDate(current.getDate() + 1)
    } else if (currentView.value === 'week') {
      const weekStart = new Date(current)
      units.push({
        key: current.toISOString(),
        label: `W${getWeekNumber(current)}`,
        date: new Date(current),
        isToday: false,
        isWeekend: false
      })
      current.setDate(current.getDate() + 7)
    } else {
      units.push({
        key: current.toISOString(),
        label: current.getDate(),
        date: new Date(current),
        isToday,
        isWeekend
      })
      current.setDate(current.getDate() + 1)
    }
  }
  
  return units
})

const timeGroups = computed(() => {
  const groups = []
  let currentGroup = null
  
  timeUnits.value.forEach(unit => {
    const groupKey = currentView.value === 'month'
      ? `${unit.date.getFullYear()}`
      : `${unit.date.getFullYear()}-${unit.date.getMonth()}`
    
    const groupLabel = currentView.value === 'month'
      ? unit.date.toLocaleDateString('en-US', { month: 'short' })
      : unit.date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
    
    if (!currentGroup || currentGroup.key !== groupKey) {
      if (currentGroup) groups.push(currentGroup)
      currentGroup = { key: groupKey, label: groupLabel, width: unitWidth.value }
    } else {
      currentGroup.width += unitWidth.value
    }
  })
  
  if (currentGroup) groups.push(currentGroup)
  return groups
})

const totalWidth = computed(() => timeUnits.value.length * unitWidth.value)

const todayPosition = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  const daysDiff = Math.floor((today - dateRange.value.start) / (1000 * 60 * 60 * 24))
  if (daysDiff < 0 || daysDiff > timeUnits.value.length) return -1
  
  return daysDiff * unitWidth.value + unitWidth.value / 2
})

const getWeekNumber = (date) => {
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
  const dayNum = d.getUTCDay() || 7
  d.setUTCDate(d.getUTCDate() + 4 - dayNum)
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
  return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
}

const getTaskLeft = (task) => {
  const start = new Date(task.startDate)
  const daysDiff = Math.floor((start - dateRange.value.start) / (1000 * 60 * 60 * 24))
  return daysDiff * unitWidth.value
}

const getTaskWidth = (task) => {
  const start = new Date(task.startDate)
  const end = new Date(task.endDate)
  const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
  return Math.max(days * unitWidth.value - 4, unitWidth.value)
}

const getTaskColor = (status) => {
  switch (status) {
    case 'completed': return '#10B981'
    case 'in-progress': return '#3B82F6'
    case 'delayed': return '#EF4444'
    case 'on-hold': return '#F59E0B'
    default: return '#6B7280'
  }
}

const getStatusClasses = (status) => {
  switch (status) {
    case 'completed': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
    case 'in-progress': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
    case 'delayed': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
    case 'on-hold': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
}

const getDependencyPath = (task, depId) => {
  const depTask = props.tasks.find(t => t.id === depId)
  if (!depTask) return ''
  
  const depIndex = sortedTasks.value.findIndex(t => t.id === depId)
  const taskIndex = sortedTasks.value.findIndex(t => t.id === task.id)
  
  const depRight = getTaskLeft(depTask) + getTaskWidth(depTask)
  const taskLeft = getTaskLeft(task)
  
  const startY = (depIndex - taskIndex) * rowHeight
  const endY = 0
  
  const midX = (depRight + taskLeft) / 2 - taskLeft
  
  return `M ${depRight - taskLeft} ${startY} L ${midX} ${startY} L ${midX} ${endY} L 0 ${endY}`
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatDateRange = (start, end) => {
  return `${formatDate(start)} - ${formatDate(end)}`
}

const selectTask = (task) => {
  selectedTask.value = task
  emit('task-click', task)
}

const scrollToToday = () => {
  if (timelineRef.value && todayPosition.value >= 0) {
    timelineRef.value.scrollLeft = todayPosition.value - timelineRef.value.clientWidth / 2
  }
}

const startResize = (task, side, event) => {
  resizing.value = { task, side, startX: event.clientX }
  document.addEventListener('mousemove', onResize)
  document.addEventListener('mouseup', endResize)
}

const onResize = (event) => {
  if (!resizing.value) return
  
  const dx = event.clientX - resizing.value.startX
  const daysDiff = Math.round(dx / unitWidth.value)
  
  if (daysDiff === 0) return
  
  const task = resizing.value.task
  const newTask = { ...task }
  
  if (resizing.value.side === 'left') {
    const start = new Date(task.startDate)
    start.setDate(start.getDate() + daysDiff)
    newTask.startDate = start.toISOString().split('T')[0]
  } else {
    const end = new Date(task.endDate)
    end.setDate(end.getDate() + daysDiff)
    newTask.endDate = end.toISOString().split('T')[0]
  }
  
  resizing.value.startX = event.clientX
  emit('task-update', newTask)
}

const endResize = () => {
  resizing.value = null
  document.removeEventListener('mousemove', onResize)
  document.removeEventListener('mouseup', endResize)
}

onMounted(() => {
  scrollToToday()
})
</script>
