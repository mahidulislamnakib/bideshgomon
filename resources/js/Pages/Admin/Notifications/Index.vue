<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import EmptyState from '@/Components/Base/EmptyState.vue'
import { useForm } from '@inertiajs/vue3'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
import {
    BellIcon,
    BellAlertIcon,
    InboxIcon,
    ExclamationTriangleIcon,
    PaperAirplaneIcon,
    EnvelopeIcon,
    EnvelopeOpenIcon,
    CheckCircleIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  recent: Array,
  stats: Object
})

const { formatDate, formatTime } = useBangladeshFormat()

const form = useForm({
  title: '',
  body: '',
  priority: 'normal',
  scope: 'all',
  user_ids: '',
})

function submit() {
  let payload = { ...form.data() }
  if (payload.scope === 'user_ids') {
    payload.user_ids = payload.user_ids.split(',').map(v => v.trim()).filter(v => v.length).map(v => parseInt(v))
  } else {
    delete payload.user_ids
  }
  form.post(route('admin.notifications.broadcast'), {
    onSuccess: () => form.reset('title', 'body')
  })
}

const getBadgeClass = (priority) => {
    return {
        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': priority === 'normal',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': priority === 'high',
        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': priority === 'critical'
    }[priority] || 'bg-neutral-100 text-neutral-800'
}
</script>

<template>
    <Head title="Notifications Center" />

    <AdminLayout>
        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Hero Header with Stats -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- Animated Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="notificationGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#notificationGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-blue-500/20 to-indigo-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header Row -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                    <BellIcon class="h-8 w-8 text-white" />
                                </div>
                                <h1 class="text-3xl font-bold text-white">Notifications Center</h1>
                            </div>
                            <p class="text-neutral-300">Send broadcasts and manage notifications</p>
                        </div>
                    </div>

                    <!-- Stats in Hero -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-xl">
                                    <InboxIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Total</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-xl">
                                    <EnvelopeIcon class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Unread</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.unread || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-500/20 rounded-xl">
                                    <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
                                </div>
                                <div>
                                    <p class="text-neutral-400 text-sm">Critical</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.critical || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                <!-- Send Broadcast Card -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl">
                            <PaperAirplaneIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Send Broadcast</h2>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Title</label>
                            <input 
                                v-model="form.title" 
                                type="text" 
                                class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5"
                                placeholder="Notification title..."
                            />
                            <p v-if="form.errors.title" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Body</label>
                            <textarea 
                                v-model="form.body" 
                                rows="4" 
                                class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Write your notification message..."
                            ></textarea>
                            <p v-if="form.errors.body" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.body }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Priority</label>
                                <select 
                                    v-model="form.priority" 
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5"
                                >
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Scope</label>
                                <select 
                                    v-model="form.scope" 
                                    class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5"
                                >
                                    <option value="all">All Users</option>
                                    <option value="user_ids">Specific User IDs</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="form.scope === 'user_ids'">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">User IDs (comma separated)</label>
                            <input 
                                v-model="form.user_ids" 
                                type="text" 
                                class="w-full rounded-xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 py-2.5"
                                placeholder="e.g. 1, 2, 5"
                            />
                            <p v-if="form.errors.user_ids" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.user_ids }}</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <button 
                                type="submit"
                                :disabled="form.processing" 
                                class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-medium rounded-xl hover:from-indigo-600 hover:to-purple-600 transition-all disabled:opacity-50 inline-flex items-center gap-2"
                            >
                                <PaperAirplaneIcon class="h-4 w-4" />
                                Send Broadcast
                            </button>
                            <p v-if="form.recentlySuccessful" class="text-green-600 dark:text-green-400 text-sm inline-flex items-center gap-1">
                                <CheckCircleIcon class="h-4 w-4" />
                                Broadcast sent successfully!
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Recent Notifications -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                                <BellAlertIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Recent Notifications</h2>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="!recent || recent.length === 0"
                        icon="InboxIcon"
                        title="No notifications"
                        description="You're all caught up! There are no notifications to display."
                    />

                    <ul v-else class="divide-y divide-neutral-100 dark:divide-neutral-700">
                        <li v-for="n in recent" :key="n.id" class="p-5 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span 
                                            :class="getBadgeClass(n.priority)"
                                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full capitalize"
                                        >
                                            {{ n.priority }}
                                        </span>
                                        <h3 class="font-semibold text-neutral-900 dark:text-white">{{ n.title }}</h3>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                        <UserGroupIcon class="h-4 w-4" />
                                        <span>{{ n.user?.name || 'User #' + n.user_id }}</span>
                                    </div>
                                    <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ n.body }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                        {{ formatDate(n.created_at) }} at {{ formatTime(n.created_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span 
                                        :class="n.read_at ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1"
                                    >
                                        <component :is="n.read_at ? EnvelopeOpenIcon : EnvelopeIcon" class="h-3 w-3" />
                                        {{ n.read_at ? 'Read' : 'Unread' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

