<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DateInput from '@/Components/DateInput.vue'
import Button from '@/Components/ui/Button.vue'

const props = defineProps({
  campaign: Object,
  emailTemplates: Array,
})

const form = useForm({
  name: props.campaign.name || '',
  type: props.campaign.type || 'email',
  email_template_id: props.campaign.email_template_id || '',
  audience_type: props.campaign.audience_type || 'all_users',
  audience_filters: props.campaign.audience_filters || {},
  recipient_ids: props.campaign.recipient_ids || [],
  scheduled_at: props.campaign.scheduled_at || '',
  is_ab_test: props.campaign.is_ab_test || false,
  ab_test_config: props.campaign.ab_test_config || {},
})

const submit = () => {
  form.put(route('admin.marketing-campaigns.update', props.campaign.id))
}
</script>

<template>
  <AdminLayout>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Edit Marketing Campaign
        </h1>
        <p class="text-gray-600 mt-1">
          Update your campaign settings
        </p>
      </div>

      <form class="bg-white rounded-2xl shadow-sm p-6 space-y-6" @submit.prevent="submit">
        <!-- Campaign Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Campaign Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-growth-600"
            placeholder="e.g., Summer 2024 Promotion"
            required
          />
          <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Campaign Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Campaign Type <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <label
              v-for="type in ['email', 'sms', 'push', 'notification']"
              :key="type"
              :class="[
                'flex items-center justify-center p-4 border-2 rounded-2xl cursor-pointer transition',
                form.type === type
                  ? 'border-growth-600 bg-growth-100'
                  : 'border-gray-300 hover:border-growth-600',
              ]"
            >
              <input
                v-model="form.type"
                type="radio"
                :value="type"
                class="sr-only"
              />
              <div class="text-center">
                <div class="text-2xl mb-1">
                  {{ type === 'email' ? '✉️' : type === 'sms' ? '💬' : type === 'push' ? '🔔' : '📢' }}
                </div>
                <div class="text-sm font-medium capitalize">{{ type }}</div>
              </div>
            </label>
          </div>
          <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">
            {{ form.errors.type }}
          </p>
        </div>

        <!-- Email Template -->
        <div v-if="form.type === 'email'">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Email Template <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.email_template_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-growth-600"
            required
          >
            <option value="">
              Select a template
            </option>
            <optgroup
              v-for="category in [...new Set(emailTemplates.map(t => t.category))]"
              :key="category"
              :label="category"
            >
              <option
                v-for="template in emailTemplates.filter(t => t.category === category)"
                :key="template.id"
                :value="template.id"
              >
                {{ template.name }}
              </option>
            </optgroup>
          </select>
          <p v-if="form.errors.email_template_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.email_template_id }}
          </p>
        </div>

        <!-- Audience Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Target Audience <span class="text-red-500">*</span>
          </label>
          <div class="space-y-3">
            <label
              v-for="type in ['all_users', 'segment', 'custom']"
              :key="type"
              :class="[
                'flex flex-wrap items-start p-4 border-2 rounded-2xl cursor-pointer transition',
                form.audience_type === type
                  ? 'border-growth-600 bg-growth-100'
                  : 'border-gray-300 hover:border-growth-600',
              ]"
            >
              <input
                v-model="form.audience_type"
                type="radio"
                :value="type"
                class="mt-1 mr-3"
              />
              <div>
                <div class="font-medium text-gray-900 dark:text-white">
                  {{ type === 'all_users' ? 'All Users' : type === 'segment' ? 'Segment' : 'Custom List' }}
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  {{
                    type === 'all_users'
                      ? 'Send to all registered users'
                      : type === 'segment'
                        ? 'Target specific user segments based on filters'
                        : 'Upload or select specific recipients'
                  }}
                </div>
              </div>
            </label>
          </div>
          <p v-if="form.errors.audience_type" class="text-red-500 text-sm mt-1">
            {{ form.errors.audience_type }}
          </p>
        </div>

        <!-- Audience Filters (for segment) -->
        <div v-if="form.audience_type === 'segment'" class="bg-gray-50 p-4 rounded-2xl">
          <label class="block text-sm font-medium text-gray-700 mb-3">Segment Filters</label>
          <div class="space-y-3">
            <div>
              <label class="block text-xs text-gray-600 mb-1">User Status</label>
              <select
                v-model="form.audience_filters.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl"
              >
                <option value="">
                  Any Status
                </option>
                <option value="active">
                  Active
                </option>
                <option value="inactive">
                  Inactive
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-600 mb-1">Has Application</label>
              <select
                v-model="form.audience_filters.has_application"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl"
              >
                <option value="">
                  Any
                </option>
                <option value="yes">
                  Yes
                </option>
                <option value="no">
                  No
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-600 mb-1">Registered After</label>
              <DateInput
                v-model="form.audience_filters.registered_after"
                placeholder="DD/MM/YYYY"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Recipient IDs (for custom) -->
        <div v-if="form.audience_type === 'custom'" class="bg-gray-50 p-4 rounded-2xl">
          <label class="block text-sm font-medium text-gray-700 mb-2">Recipient IDs</label>
          <textarea
            v-model="form.recipient_ids_text"
            class="w-full px-3 py-2 border border-gray-300 rounded-2xl"
            rows="4"
            placeholder="Enter user IDs separated by commas (e.g., 1, 2, 3, 4)"
            @input="form.recipient_ids = $event.target.value.split(',').map(id => id.trim()).filter(Boolean)"
          ></textarea>
          <p class="text-xs text-gray-600 mt-1">
            Enter user IDs separated by commas, or upload a CSV file
          </p>
        </div>

        <!-- Scheduling -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Schedule Campaign
          </label>
          <input
            v-model="form.scheduled_at"
            type="datetime-local"
            class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-growth-600"
          />
          <p class="text-xs text-gray-600 mt-1">
            Leave empty to save as draft. Set a future date to schedule.
          </p>
          <p v-if="form.errors.scheduled_at" class="text-red-500 text-sm mt-1">
            {{ form.errors.scheduled_at }}
          </p>
        </div>

        <!-- A/B Testing -->
        <div class="border-t pt-4">
          <label class="flex flex-wrap items-center cursor-pointer">
            <input
              v-model="form.is_ab_test"
              type="checkbox"
              class="w-4 h-4 text-growth-600 border-gray-300 rounded focus:ring-growth-600"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Enable A/B Testing</span>
          </label>
          <p class="text-xs text-gray-600 mt-1 ml-6">
            Test different versions of your campaign to optimize performance
          </p>
        </div>

        <!-- A/B Test Configuration -->
        <div v-if="form.is_ab_test" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
          <h3 class="font-medium text-gray-900 dark:text-white mb-3">
            A/B Test Configuration
          </h3>
          <div class="space-y-3">
            <div>
              <label class="block text-xs text-gray-600 mb-1">Test Split (%)</label>
              <input
                v-model.number="form.ab_test_config.split_percentage"
                type="number"
                min="10"
                max="90"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl"
                placeholder="50"
              />
              <p class="text-xs text-gray-500 mt-1">
                Percentage of users for variant A (remaining for B)
              </p>
            </div>
            <div>
              <label class="block text-xs text-gray-600 mb-1">Test Duration (hours)</label>
              <input
                v-model.number="form.ab_test_config.duration_hours"
                type="number"
                min="1"
                max="168"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl"
                placeholder="24"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t">
          <a :href="route('admin.marketing-campaigns.index')">
            <Button variant="secondary" as="span">
              Cancel
            </Button>
          </a>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-growth-600 hover:bg-growth-700 transition-colors text-white rounded-2xl hover:bg-growth-700 transition disabled:opacity-50"
          >
            {{ form.processing ? 'Updating...' : 'Update Campaign' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
