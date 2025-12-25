<template>
  <div :class="['activity-feed rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg">
          <ClockIcon class="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ activities.length }} activities</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <select
          v-model="filterType"
          class="text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-300"
        >
          <option value="all">All Activities</option>
          <option v-for="type in activityTypes" :key="type" :value="type">{{ formatType(type) }}</option>
        </select>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Refresh"
          @click="$emit('refresh')"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Timeline -->
    <div class="overflow-auto" :style="{ maxHeight: height }">
      <div class="relative">
        <!-- Timeline Line -->
        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700" />

        <!-- Activity Items -->
        <TransitionGroup name="activity" tag="div">
          <div
            v-for="(activity, index) in filteredActivities"
            :key="activity.id"
            class="relative flex gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          >
            <!-- Icon -->
            <div
              :class="[
                'relative z-10 flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-gray-900',
                getActivityColor(activity.type)
              ]"
            >
              <component :is="getActivityIcon(activity.type)" class="w-5 h-5 text-white" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm text-gray-900 dark:text-white">
                    <span v-if="activity.user" class="font-medium">{{ activity.user.name }}</span>
                    {{ activity.action }}
                    <span v-if="activity.target" class="font-medium text-blue-600 dark:text-blue-400">
                      {{ activity.target }}
                    </span>
                  </p>
                  <p v-if="activity.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ activity.description }}
                  </p>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap flex-shrink-0">
                  {{ formatTime(activity.timestamp) }}
                </span>
              </div>

              <!-- Activity Details -->
              <div v-if="activity.details" class="mt-2 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <div v-if="activity.details.changes" class="space-y-1">
                  <div
                    v-for="(change, key) in activity.details.changes"
                    :key="key"
                    class="flex items-center gap-2 text-xs"
                  >
                    <span class="text-gray-500 dark:text-gray-400 font-medium">{{ key }}:</span>
                    <span class="text-red-500 line-through">{{ change.from }}</span>
                    <ArrowRightIcon class="w-3 h-3 text-gray-400" />
                    <span class="text-green-500">{{ change.to }}</span>
                  </div>
                </div>
                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                  {{ activity.details }}
                </p>
              </div>

              <!-- Attachments -->
              <div v-if="activity.attachments?.length" class="flex flex-wrap gap-2 mt-2">
                <div
                  v-for="attachment in activity.attachments"
                  :key="attachment.id"
                  class="flex items-center gap-2 px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs"
                >
                  <DocumentIcon class="w-4 h-4 text-gray-400" />
                  <span class="text-gray-600 dark:text-gray-400">{{ attachment.name }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div v-if="activity.actions?.length" class="flex items-center gap-2 mt-2">
                <button
                  v-for="action in activity.actions"
                  :key="action.id"
                  type="button"
                  class="px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                  @click="$emit('action', { activity, action })"
                >
                  {{ action.label }}
                </button>
              </div>
            </div>

            <!-- User Avatar -->
            <div v-if="activity.user?.avatar && showAvatars" class="flex-shrink-0">
              <img
                :src="activity.user.avatar"
                :alt="activity.user.name"
                class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-gray-900"
              />
            </div>
          </div>
        </TransitionGroup>

        <!-- Load More -->
        <div v-if="hasMore" class="p-4 text-center">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
            @click="$emit('loadMore')"
          >
            Load more activities
          </button>
        </div>

        <!-- Empty State -->
        <div v-if="filteredActivities.length === 0" class="py-12 text-center">
          <ClockIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
          <p class="text-gray-500 dark:text-gray-400 font-medium">No activities yet</p>
          <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Activities will appear here as they happen</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  ClockIcon,
  ArrowPathIcon,
  ArrowRightIcon,
  DocumentIcon,
  UserPlusIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  ChatBubbleLeftIcon,
  ArrowUpTrayIcon,
  CurrencyDollarIcon,
  BellIcon,
  CogIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'Activity Feed' },
  activities: { type: Array, default: () => [] },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showAvatars: { type: Boolean, default: true },
  hasMore: { type: Boolean, default: false }
})

const emit = defineEmits(['refresh', 'loadMore', 'action'])

const filterType = ref('all')

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const defaultActivities = [
  {
    id: 1,
    type: 'create',
    user: { name: 'John Doe', avatar: null },
    action: 'created a new',
    target: 'visa application',
    description: 'Tourist visa for Singapore',
    timestamp: new Date(Date.now() - 5 * 60 * 1000)
  },
  {
    id: 2,
    type: 'update',
    user: { name: 'Jane Smith' },
    action: 'updated',
    target: 'passport details',
    timestamp: new Date(Date.now() - 30 * 60 * 1000),
    details: {
      changes: {
        'Expiry Date': { from: '2024-12-01', to: '2029-12-01' }
      }
    }
  },
  {
    id: 3,
    type: 'comment',
    user: { name: 'Admin' },
    action: 'commented on',
    target: 'Application #12345',
    description: 'Please provide additional documents.',
    timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
    actions: [{ id: 'reply', label: 'Reply' }, { id: 'view', label: 'View' }]
  },
  {
    id: 4,
    type: 'payment',
    user: { name: 'System' },
    action: 'received payment of',
    target: '৳5,000',
    timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000)
  },
  {
    id: 5,
    type: 'approve',
    user: { name: 'Consultant' },
    action: 'approved',
    target: 'visa application',
    timestamp: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000)
  }
]

const allActivities = computed(() => 
  props.activities.length > 0 ? props.activities : defaultActivities
)

const activityTypes = computed(() => 
  [...new Set(allActivities.value.map(a => a.type))]
)

const filteredActivities = computed(() => {
  if (filterType.value === 'all') return allActivities.value
  return allActivities.value.filter(a => a.type === filterType.value)
})

const formatType = (type) => {
  return type.charAt(0).toUpperCase() + type.slice(1)
}

const getActivityIcon = (type) => {
  const icons = {
    create: UserPlusIcon,
    update: PencilIcon,
    delete: TrashIcon,
    approve: CheckCircleIcon,
    reject: XCircleIcon,
    comment: ChatBubbleLeftIcon,
    upload: ArrowUpTrayIcon,
    payment: CurrencyDollarIcon,
    notification: BellIcon,
    settings: CogIcon,
    view: EyeIcon
  }
  return icons[type] || ClockIcon
}

const getActivityColor = (type) => {
  const colors = {
    create: 'bg-green-500',
    update: 'bg-blue-500',
    delete: 'bg-red-500',
    approve: 'bg-emerald-500',
    reject: 'bg-red-500',
    comment: 'bg-purple-500',
    upload: 'bg-indigo-500',
    payment: 'bg-yellow-500',
    notification: 'bg-pink-500',
    settings: 'bg-gray-500',
    view: 'bg-cyan-500'
  }
  return colors[type] || 'bg-gray-500'
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date

  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return date.toLocaleDateString()
}
</script>

<style scoped>
.activity-enter-active,
.activity-leave-active {
  transition: all 0.3s ease;
}

.activity-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.activity-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
