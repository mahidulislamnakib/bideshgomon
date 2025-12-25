<template>
  <div :class="['notification-center rounded-xl border overflow-hidden', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="relative">
          <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg">
            <BellIcon class="w-5 h-5 text-white" />
          </div>
          <span
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
          >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ unreadCount }} unread, {{ notifications.length }} total
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="unreadCount > 0"
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
          @click="markAllAsRead"
        >
          Mark all read
        </button>
        <button
          type="button"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          title="Settings"
        >
          <Cog6ToothIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        :class="[
          'flex-1 px-4 py-3 text-sm font-medium text-center transition-colors relative',
          activeTab === tab.id
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
        ]"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
        <span
          v-if="tab.count > 0"
          :class="[
            'ml-1.5 px-1.5 py-0.5 text-xs rounded-full',
            activeTab === tab.id
              ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
          ]"
        >
          {{ tab.count }}
        </span>
        <div
          v-if="activeTab === tab.id"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"
        />
      </button>
    </div>

    <!-- Notifications List -->
    <div class="overflow-auto" :style="{ maxHeight: height }">
      <TransitionGroup name="notification" tag="div">
        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          :class="[
            'flex gap-4 p-4 border-b border-gray-100 dark:border-gray-800 transition-colors cursor-pointer',
            notification.read
              ? 'bg-white dark:bg-gray-900'
              : 'bg-blue-50/50 dark:bg-blue-900/10'
          ]"
          @click="handleNotificationClick(notification)"
        >
          <!-- Icon -->
          <div
            :class="[
              'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
              getTypeStyles(notification.type).bg
            ]"
          >
            <component
              :is="getTypeIcon(notification.type)"
              :class="['w-5 h-5', getTypeStyles(notification.type).icon]"
            />
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p :class="[
                  'text-sm',
                  notification.read
                    ? 'text-gray-600 dark:text-gray-400'
                    : 'text-gray-900 dark:text-white font-medium'
                ]">
                  {{ notification.title }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                  {{ notification.message }}
                </p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                  {{ formatTime(notification.timestamp) }}
                </span>
                <button
                  type="button"
                  class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 opacity-0 group-hover:opacity-100 transition-opacity"
                  title="Dismiss"
                  @click.stop="dismissNotification(notification.id)"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Actions -->
            <div v-if="notification.actions?.length" class="flex items-center gap-2 mt-2">
              <button
                v-for="action in notification.actions"
                :key="action.id"
                type="button"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-lg transition-colors',
                  action.primary
                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                    : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700'
                ]"
                @click.stop="handleAction(notification, action)"
              >
                {{ action.label }}
              </button>
            </div>

            <!-- Tags -->
            <div v-if="notification.tags?.length" class="flex items-center gap-1 mt-2">
              <span
                v-for="tag in notification.tags"
                :key="tag"
                class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full"
              >
                {{ tag }}
              </span>
            </div>
          </div>

          <!-- Unread indicator -->
          <div v-if="!notification.read" class="flex-shrink-0 mt-2">
            <div class="w-2 h-2 bg-blue-500 rounded-full" />
          </div>
        </div>
      </TransitionGroup>

      <!-- Empty State -->
      <div v-if="filteredNotifications.length === 0" class="py-12 text-center">
        <BellSlashIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
        <p class="text-gray-500 dark:text-gray-400 font-medium">No notifications</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">You're all caught up!</p>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showFooter && notifications.length > 0" class="p-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 text-center">
      <button
        type="button"
        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium"
        @click="$emit('viewAll')"
      >
        View all notifications
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  BellIcon,
  BellSlashIcon,
  XMarkIcon,
  Cog6ToothIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
  UserIcon,
  ChatBubbleLeftIcon,
  CalendarIcon,
  DocumentIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, default: 'Notifications' },
  initialNotifications: { type: Array, default: () => [] },
  height: { type: String, default: '500px' },
  theme: { type: String, default: 'light' },
  showHeader: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true }
})

const emit = defineEmits(['click', 'action', 'dismiss', 'markRead', 'viewAll'])

const activeTab = ref('all')

const notifications = ref(props.initialNotifications.length > 0 ? props.initialNotifications : [
  {
    id: 1,
    type: 'success',
    title: 'Payment Received',
    message: 'Your visa application fee of ৳5,000 has been successfully processed.',
    timestamp: new Date(Date.now() - 5 * 60 * 1000),
    read: false,
    tags: ['Payment']
  },
  {
    id: 2,
    type: 'info',
    title: 'Application Update',
    message: 'Your visa application #12345 has been submitted for review.',
    timestamp: new Date(Date.now() - 30 * 60 * 1000),
    read: false,
    actions: [
      { id: 'view', label: 'View Details', primary: true },
      { id: 'later', label: 'Remind Later' }
    ]
  },
  {
    id: 3,
    type: 'warning',
    title: 'Document Expiring Soon',
    message: 'Your passport will expire in 30 days. Please renew to avoid delays.',
    timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
    read: false,
    tags: ['Document', 'Urgent']
  },
  {
    id: 4,
    type: 'message',
    title: 'New Message from Agent',
    message: 'Hi, I have reviewed your application and need additional documents.',
    timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000),
    read: true
  },
  {
    id: 5,
    type: 'error',
    title: 'Document Upload Failed',
    message: 'Your passport scan upload failed. Please try again.',
    timestamp: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
    read: true
  },
  {
    id: 6,
    type: 'calendar',
    title: 'Appointment Reminder',
    message: 'You have an embassy appointment tomorrow at 10:00 AM.',
    timestamp: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000),
    read: true,
    actions: [
      { id: 'calendar', label: 'Add to Calendar', primary: true }
    ]
  }
])

const themeClasses = computed(() => 
  props.theme === 'dark' 
    ? 'bg-gray-900 border-gray-700' 
    : 'bg-white border-gray-200'
)

const unreadCount = computed(() => 
  notifications.value.filter(n => !n.read).length
)

const tabs = computed(() => [
  { id: 'all', label: 'All', count: notifications.value.length },
  { id: 'unread', label: 'Unread', count: unreadCount.value },
  { id: 'messages', label: 'Messages', count: notifications.value.filter(n => n.type === 'message').length }
])

const filteredNotifications = computed(() => {
  switch (activeTab.value) {
    case 'unread':
      return notifications.value.filter(n => !n.read)
    case 'messages':
      return notifications.value.filter(n => n.type === 'message')
    default:
      return notifications.value
  }
})

const getTypeIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    error: ExclamationCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    message: ChatBubbleLeftIcon,
    calendar: CalendarIcon,
    document: DocumentIcon,
    user: UserIcon
  }
  return icons[type] || BellIcon
}

const getTypeStyles = (type) => {
  const styles = {
    success: { bg: 'bg-green-100 dark:bg-green-900/30', icon: 'text-green-600 dark:text-green-400' },
    error: { bg: 'bg-red-100 dark:bg-red-900/30', icon: 'text-red-600 dark:text-red-400' },
    warning: { bg: 'bg-yellow-100 dark:bg-yellow-900/30', icon: 'text-yellow-600 dark:text-yellow-400' },
    info: { bg: 'bg-blue-100 dark:bg-blue-900/30', icon: 'text-blue-600 dark:text-blue-400' },
    message: { bg: 'bg-purple-100 dark:bg-purple-900/30', icon: 'text-purple-600 dark:text-purple-400' },
    calendar: { bg: 'bg-indigo-100 dark:bg-indigo-900/30', icon: 'text-indigo-600 dark:text-indigo-400' },
    document: { bg: 'bg-gray-100 dark:bg-gray-800', icon: 'text-gray-600 dark:text-gray-400' },
    user: { bg: 'bg-pink-100 dark:bg-pink-900/30', icon: 'text-pink-600 dark:text-pink-400' }
  }
  return styles[type] || styles.info
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

const handleNotificationClick = (notification) => {
  if (!notification.read) {
    notification.read = true
    emit('markRead', notification.id)
  }
  emit('click', notification)
}

const handleAction = (notification, action) => {
  emit('action', { notification, action })
}

const dismissNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
  emit('dismiss', id)
}

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
  emit('markRead', 'all')
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
