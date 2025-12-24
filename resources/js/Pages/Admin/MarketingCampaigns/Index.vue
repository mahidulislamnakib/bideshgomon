<script setup>
import { ref } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PlusIcon,
  EyeIcon,
  PencilIcon,
  PauseIcon,
  PlayIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  ChartBarIcon,
  EnvelopeIcon,
  DevicePhoneMobileIcon,
  BellIcon,
  InboxIcon,
  UserGroupIcon,
  ArrowTrendingUpIcon,
  CursorArrowRaysIcon,
  MegaphoneIcon,
  FunnelIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const { formatDate: formatBdDate } = useBangladeshFormat()

const props = defineProps({
  campaigns: Object,
  filters: Object,
  stats: Object,
})

const filterForm = ref({
  type: props.filters?.type || '',
  status: props.filters?.status || '',
  search: props.filters?.search || '',
})

const showFilters = ref(false)

const applyFilters = () => {
  router.get(route('admin.marketing-campaigns.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  filterForm.value = { type: '', status: '', search: '' }
  applyFilters()
}

const hasActiveFilters = () => {
  return filterForm.value.type || filterForm.value.status || filterForm.value.search
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    sending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    sent: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    paused: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  }
  return colors[status] || colors.draft
}

const getTypeIcon = (type) => {
  const icons = {
    email: { component: EnvelopeIcon, color: 'text-blue-600 dark:text-blue-400' },
    sms: { component: DevicePhoneMobileIcon, color: 'text-green-600 dark:text-green-400' },
    push: { component: BellIcon, color: 'text-purple-600 dark:text-purple-400' },
    notification: { component: InboxIcon, color: 'text-orange-600 dark:text-orange-400' },
  }
  return icons[type] || icons.email
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-GB', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatPercentage = (value) => value ? `${value.toFixed(1)}%` : '0.0%'

const pauseCampaign = (campaignId) => {
  if (!confirm('Are you sure you want to pause this campaign?')) return
  router.post(route('admin.marketing-campaigns.pause', campaignId), {}, { preserveScroll: true })
}

const resumeCampaign = (campaignId) => {
  if (!confirm('Are you sure you want to resume this campaign?')) return
  router.post(route('admin.marketing-campaigns.resume', campaignId), {}, { preserveScroll: true })
}

const cancelCampaign = (campaignId) => {
  if (!confirm('Are you sure you want to cancel this campaign? This action cannot be undone.')) return
  router.post(route('admin.marketing-campaigns.cancel', campaignId), {}, { preserveScroll: true })
}

const duplicateCampaign = (campaignId) => {
  router.post(route('admin.marketing-campaigns.duplicate', campaignId), {}, { preserveScroll: true })
}

const deleteCampaign = (campaignId) => {
  if (!confirm('Are you sure you want to delete this draft campaign? This action cannot be undone.')) return
  router.delete(route('admin.marketing-campaigns.destroy', campaignId), { preserveScroll: true })
}
</script>

<template>
  <Head title="Marketing Campaigns" />
  
  <AdminLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
      <!-- Compact Hero -->
      <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/10 backdrop-blur-sm rounded-2xl">
                <MegaphoneIcon class="h-6 w-6 text-white" />
              </div>
              <div>
                <h1 class="text-xl md:text-2xl font-bold text-white">Marketing Campaigns</h1>
                <p class="text-sm text-white/80 mt-0.5">Email, SMS, push notifications & more</p>
              </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
              <!-- Inline Stats -->
              <div class="hidden md:flex items-center gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 flex items-center gap-2">
                  <ChartBarIcon class="h-4 w-4 text-white/70" />
                  <span class="text-lg font-bold text-white">{{ stats?.total || 0 }}</span>
                  <span class="text-xs text-white/70">Campaigns</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 flex items-center gap-2">
                  <UserGroupIcon class="h-4 w-4 text-white/70" />
                  <span class="text-lg font-bold text-white">{{ (stats?.total_recipients || 0).toLocaleString() }}</span>
                  <span class="text-xs text-white/70">Reach</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 flex items-center gap-2">
                  <ArrowTrendingUpIcon class="h-4 w-4 text-white/70" />
                  <span class="text-lg font-bold text-white">{{ formatPercentage(stats?.avg_open_rate) }}</span>
                  <span class="text-xs text-white/70">Open</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 flex items-center gap-2">
                  <CursorArrowRaysIcon class="h-4 w-4 text-white/70" />
                  <span class="text-lg font-bold text-white">{{ formatPercentage(stats?.avg_click_rate) }}</span>
                  <span class="text-xs text-white/70">Click</span>
                </div>
              </div>
              <Link
                :href="route('admin.marketing-campaigns.create')"
                class="inline-flex items-center px-4 py-2 bg-white text-growth-600 font-semibold rounded-2xl shadow-sm hover:bg-gray-50 transition-colors"
              >
                <PlusIcon class="h-5 w-5 mr-2" />
                Create Campaign
              </Link>
            </div>
          </div>
          
          <!-- Mobile Stats -->
          <div class="md:hidden grid grid-cols-2 gap-2 mt-4">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 text-center">
              <span class="text-lg font-bold text-white">{{ stats?.total || 0 }}</span>
              <span class="text-xs text-white/70 block">Campaigns</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 text-center">
              <span class="text-lg font-bold text-white">{{ (stats?.total_recipients || 0).toLocaleString() }}</span>
              <span class="text-xs text-white/70 block">Reach</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 text-center">
              <span class="text-lg font-bold text-white">{{ formatPercentage(stats?.avg_open_rate) }}</span>
              <span class="text-xs text-white/70 block">Open Rate</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-3 py-2 text-center">
              <span class="text-lg font-bold text-white">{{ formatPercentage(stats?.avg_click_rate) }}</span>
              <span class="text-xs text-white/70 block">Click Rate</span>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filters Toggle & Search -->
        <div class="mb-6">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
              <input
                v-model="filterForm.search"
                type="text"
                placeholder="Search campaigns by name..."
                class="w-full pl-4 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-neutral-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-500 focus:border-transparent transition-colors"
                @input="applyFilters"
              />
            </div>
            <button
              @click="showFilters = !showFilters"
              :class="[
                'inline-flex items-center px-4 py-2.5 rounded-2xl font-medium transition-colors',
                hasActiveFilters()
                  ? 'bg-growth-100 text-growth-700 dark:bg-growth-900/30 dark:text-growth-300'
                  : 'bg-white dark:bg-neutral-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700'
              ]"
            >
              <FunnelIcon class="h-5 w-5 mr-2" />
              Filters
              <span v-if="hasActiveFilters()" class="ml-2 bg-growth-600 text-white text-xs px-2 py-0.5 rounded-full">Active</span>
            </button>
          </div>
          
          <!-- Expanded Filters -->
          <div v-show="showFilters" class="mt-4 bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campaign Type</label>
                <select
                  v-model="filterForm.type"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-neutral-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                  @change="applyFilters"
                >
                  <option value="">All Types</option>
                  <option value="email">📧 Email</option>
                  <option value="sms">💬 SMS</option>
                  <option value="push">🔔 Push</option>
                  <option value="notification">📬 Notification</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select
                  v-model="filterForm.status"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-neutral-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                  @change="applyFilters"
                >
                  <option value="">All Statuses</option>
                  <option value="draft">Draft</option>
                  <option value="scheduled">Scheduled</option>
                  <option value="sending">Sending</option>
                  <option value="sent">Sent</option>
                  <option value="paused">Paused</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div class="flex items-end">
                <button
                  @click="clearFilters"
                  class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-2xl transition-colors"
                >
                  <XMarkIcon class="h-4 w-4 mr-2" />
                  Reset Filters
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Campaigns Table -->
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Empty State -->
          <div v-if="campaigns.data.length === 0" class="p-12 text-center">
            <div class="mx-auto h-16 w-16 bg-gray-100 dark:bg-neutral-700 rounded-full flex items-center justify-center mb-4">
              <MegaphoneIcon class="h-8 w-8 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No campaigns found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new marketing campaign.</p>
            <div class="mt-6">
              <Link
                :href="route('admin.marketing-campaigns.create')"
                class="inline-flex items-center px-4 py-2 bg-growth-600 hover:bg-growth-700 text-white font-medium rounded-2xl transition-colors"
              >
                <PlusIcon class="h-5 w-5 mr-2" />
                Create Campaign
              </Link>
            </div>
          </div>

          <!-- Table -->
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-neutral-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaign</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recipients</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Performance</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-neutral-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="campaign in campaigns.data"
                  :key="campaign.id"
                  class="hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-2xl bg-gray-100 dark:bg-neutral-700 flex items-center justify-center">
                        <component
                          :is="getTypeIcon(campaign.type).component"
                          :class="['w-5 h-5', getTypeIcon(campaign.type).color]"
                        />
                      </div>
                      <div class="ml-4">
                        <Link
                          :href="route('admin.marketing-campaigns.show', campaign.id)"
                          class="text-sm font-medium text-gray-900 dark:text-white hover:text-growth-600 dark:hover:text-growth-400"
                        >
                          {{ campaign.name }}
                        </Link>
                        <p v-if="campaign.slug" class="text-xs text-gray-500 dark:text-gray-400">{{ campaign.slug }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-gray-600 dark:text-gray-300 capitalize">{{ campaign.type }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2.5 py-1 text-xs font-medium rounded-full capitalize', getStatusColor(campaign.status)]">
                      {{ campaign.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <UserGroupIcon class="h-4 w-4 text-gray-400" />
                      <span class="text-sm font-medium text-gray-900 dark:text-white">{{ campaign.total_recipients?.toLocaleString() || 0 }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">
                      <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">Open:</span>
                        <span class="font-medium text-green-600 dark:text-green-400">{{ formatPercentage(campaign.open_rate) }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">Click:</span>
                        <span class="font-medium text-blue-600 dark:text-blue-400">{{ formatPercentage(campaign.click_rate) }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                    <div v-if="campaign.scheduled_at">{{ formatDate(campaign.scheduled_at) }}</div>
                    <div v-else-if="campaign.started_at">{{ formatDate(campaign.started_at) }}</div>
                    <div v-else class="text-gray-400 dark:text-gray-500">—</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="flex justify-end gap-1">
                      <Link
                        :href="route('admin.marketing-campaigns.show', campaign.id)"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-growth-600 dark:hover:text-growth-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                        title="View"
                      >
                        <EyeIcon class="w-5 h-5" />
                      </Link>
                      <Link
                        v-if="['draft', 'scheduled', 'paused'].includes(campaign.status)"
                        :href="route('admin.marketing-campaigns.edit', campaign.id)"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                        title="Edit"
                      >
                        <PencilIcon class="w-5 h-5" />
                      </Link>
                      <button
                        v-if="['scheduled', 'sending'].includes(campaign.status)"
                        @click="pauseCampaign(campaign.id)"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                        title="Pause"
                      >
                        <PauseIcon class="w-5 h-5" />
                      </button>
                      <button
                        v-if="campaign.status === 'paused'"
                        @click="resumeCampaign(campaign.id)"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                        title="Resume"
                      >
                        <PlayIcon class="w-5 h-5" />
                      </button>
                      <button
                        @click="duplicateCampaign(campaign.id)"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                        title="Duplicate"
                      >
                        <DocumentDuplicateIcon class="w-5 h-5" />
                      </button>
                      <button
                        v-if="campaign.status === 'draft'"
                        @click="deleteCampaign(campaign.id)"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                        title="Delete"
                      >
                        <TrashIcon class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="campaigns.data.length > 0 && campaigns.links && campaigns.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-neutral-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
              <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing <span class="font-medium text-gray-900 dark:text-white">{{ campaigns.from }}</span> to <span class="font-medium text-gray-900 dark:text-white">{{ campaigns.to }}</span> of <span class="font-medium text-gray-900 dark:text-white">{{ campaigns.total }}</span> campaigns
              </div>
              <nav class="flex gap-1">
                <Link
                  v-for="link in campaigns.links"
                  :key="link.label"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    'px-3 py-2 rounded-2xl text-sm font-medium transition-colors',
                    link.active
                      ? 'bg-growth-600 text-white'
                      : link.url
                      ? 'bg-white dark:bg-neutral-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700'
                      : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                  ]"
                />
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
