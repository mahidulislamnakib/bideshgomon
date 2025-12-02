<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DateInput from '@/Components/DateInput.vue'
import Button from '@/Components/ui/Button.vue'
import Card from '@/Components/ui/Card.vue'
import { PlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  emailTemplates: Array,
})

const form = useForm({
  name: '',
  type: 'email',
  email_template_id: '',
  audience_type: 'all_users',
  audience_filters: {},
  recipient_ids: [],
  scheduled_at: '',
  is_ab_test: false,
  ab_test_config: {},
})

const submit = () => {
  form.post(route('admin.marketing-campaigns.store'))
}
</script>

<template>
  <AdminLayout>
    <!-- Modern Header -->
    <div class="relative overflow-hidden bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-3 bg-red-100 rounded-lg">
                <PlusIcon class="w-8 h-8 text-brand-red-600" />
              </div>
              <h1 class="text-4xl font-bold text-gray-900 tracking-tight">Create Campaign</h1>
            </div>
            <p class="text-gray-600 text-lg max-w-2xl">
              Set up a new marketing campaign with powerful targeting options
            </p>
          </div>
          
          <Link
            :href="route('admin.marketing-campaigns.index')"
            class="inline-flex items-center px-6 py-3.5 bg-white hover:bg-pink-50 text-pink-700 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold group"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" />
            Back to Campaigns
          </Link>
        </div>
      </div>
    </div>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto -mt-8">
      <Card class="overflow-hidden shadow-xl">
        <form class="p-8 space-y-8" @submit.prevent="submit">
        <!-- Campaign Name -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            Campaign Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
            placeholder="e.g., Summer 2025 Promotion"
            required
          />
          <p v-if="form.errors.name" class="text-red-500 text-sm mt-1.5">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Campaign Type -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            Campaign Type <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <label
              v-for="type in ['email', 'sms', 'push', 'notification']"
              :key="type"
              :class="[
                'flex items-center justify-center p-5 border-2 rounded-lg cursor-pointer transition-all',
                form.type === type
                  ? 'border-brand-red-600 bg-red-50 shadow-md'
                  : 'border-gray-300 hover:border-indigo-400 hover:shadow-md',
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
                  {{ type === 'email' ? '📧' : type === 'sms' ? '💬' : type === 'push' ? '🔔' : '📬' }}
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
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            Email Template <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.email_template_id"
            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
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
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            Target Audience <span class="text-red-500">*</span>
          </label>
          <div class="space-y-3">
            <label
              v-for="type in ['all_users', 'segment', 'custom']"
              :key="type"
              :class="[
                'flex flex-wrap items-start p-5 border-2 rounded-lg cursor-pointer transition-all',
                form.audience_type === type
                  ? 'border-brand-red-600 bg-red-50 shadow-md'
                  : 'border-gray-300 hover:border-indigo-400 hover:shadow-md',
              ]"
            >
              <input
                v-model="form.audience_type"
                type="radio"
                :value="type"
                class="mt-1 mr-3 w-5 h-5 text-pink-600 focus:ring-pink-500"
              />
              <div>
                <div class="font-semibold text-gray-900 dark:text-white text-base">
                  {{ type === 'all_users' ? 'All Users' : type === 'segment' ? 'Segment' : 'Custom List' }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
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
        <div v-if="form.audience_type === 'segment'" class="bg-red-50 p-6 rounded-lg border-2 border-red-200">
          <div class="flex items-center gap-2 mb-5">
            <svg class="w-6 h-6 text-brand-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <label class="block text-lg font-bold text-gray-900">Comprehensive Segment Filters</label>
          </div>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Target specific user segments with powerful filtering criteria</p>

          <!-- User Demographics -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 mb-4 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">👥</span> Demographics & Profile
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">User Status</label>
                <select
                  v-model="form.audience_filters.status"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Status</option>
                  <option value="active">✅ Active</option>
                  <option value="inactive">⛔ Inactive</option>
                  <option value="suspended">🚫 Suspended</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Email Verified</label>
                <select
                  v-model="form.audience_filters.email_verified"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="yes">✅ Verified</option>
                  <option value="no">❌ Not Verified</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Age Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input
                    v-model.number="form.audience_filters.age_min"
                    type="number"
                    min="18"
                    max="100"
                    placeholder="Min (18)"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                  />
                  <input
                    v-model.number="form.audience_filters.age_max"
                    type="number"
                    min="18"
                    max="100"
                    placeholder="Max (100)"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Gender</label>
                <select
                  v-model="form.audience_filters.gender"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Gender</option>
                  <option value="male">👨 Male</option>
                  <option value="female">👩 Female</option>
                  <option value="other">🧑 Other</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Profile Completion</label>
                <div class="grid grid-cols-2 gap-2">
                  <input
                    v-model.number="form.audience_filters.profile_completion_min"
                    type="number"
                    min="0"
                    max="100"
                    placeholder="Min %"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                  />
                  <input
                    v-model.number="form.audience_filters.profile_completion_max"
                    type="number"
                    min="0"
                    max="100"
                    placeholder="Max %"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">User Role</label>
                <select
                  v-model="form.audience_filters.role"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Role</option>
                  <option value="user">👤 User</option>
                  <option value="agency">🏢 Agency</option>
                  <option value="consultant">💼 Consultant</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Location -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 mb-4 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">🌍</span> Location & Geographic
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Country</label>
                <select
                  v-model="form.audience_filters.country"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Country</option>
                  <option value="BD">🇧🇩 Bangladesh</option>
                  <option value="IN">🇮🇳 India</option>
                  <option value="PK">🇵🇰 Pakistan</option>
                  <option value="NP">🇳🇵 Nepal</option>
                  <option value="LK">🇱🇰 Sri Lanka</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Division (Bangladesh)</label>
                <select
                  v-model="form.audience_filters.division"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Division</option>
                  <option value="Dhaka">Dhaka</option>
                  <option value="Chittagong">Chittagong</option>
                  <option value="Sylhet">Sylhet</option>
                  <option value="Rajshahi">Rajshahi</option>
                  <option value="Khulna">Khulna</option>
                  <option value="Barisal">Barisal</option>
                  <option value="Rangpur">Rangpur</option>
                  <option value="Mymensingh">Mymensingh</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Visa & Applications -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 mb-4 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">📋</span> Visa & Applications
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Has Application</label>
                <select
                  v-model="form.audience_filters.has_application"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="yes">✅ Has Application</option>
                  <option value="no">❌ No Application</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Visa Type</label>
                <select
                  v-model="form.audience_filters.visa_type"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Type</option>
                  <option value="tourist">🏖️ Tourist Visa</option>
                  <option value="work">💼 Work Visa</option>
                  <option value="student">🎓 Student Visa</option>
                  <option value="business">👔 Business Visa</option>
                  <option value="family">👨‍👩‍👧 Family Visa</option>
                  <option value="medical">🏥 Medical Visa</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Application Status</label>
                <select
                  v-model="form.audience_filters.application_status"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Status</option>
                  <option value="pending">⏳ Pending</option>
                  <option value="processing">🔄 Processing</option>
                  <option value="approved">✅ Approved</option>
                  <option value="rejected">❌ Rejected</option>
                  <option value="on_hold">⏸️ On Hold</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Has Booking</label>
                <select
                  v-model="form.audience_filters.has_booking"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="yes">✅ Has Booking</option>
                  <option value="no">❌ No Booking</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Education -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 mb-4 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">🎓</span> Education & Qualifications
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Education Level</label>
                <select
                  v-model="form.audience_filters.education_level"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Level</option>
                  <option value="high_school">🏫 High School</option>
                  <option value="bachelor">🎓 Bachelor's Degree</option>
                  <option value="master">🎓🎓 Master's Degree</option>
                  <option value="phd">👨‍🎓 PhD</option>
                  <option value="diploma">📜 Diploma</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Field of Study</label>
                <select
                  v-model="form.audience_filters.field_of_study"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Field</option>
                  <option value="engineering">⚙️ Engineering</option>
                  <option value="business">💼 Business</option>
                  <option value="medical">🏥 Medical</option>
                  <option value="computer_science">💻 Computer Science</option>
                  <option value="arts">🎨 Arts</option>
                  <option value="science">🔬 Science</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">IELTS Score Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input
                    v-model.number="form.audience_filters.ielts_min"
                    type="number"
                    min="0"
                    max="9"
                    step="0.5"
                    placeholder="Min (0)"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                  />
                  <input
                    v-model.number="form.audience_filters.ielts_max"
                    type="number"
                    min="0"
                    max="9"
                    step="0.5"
                    placeholder="Max (9)"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Language Proficiency</label>
                <select
                  v-model="form.audience_filters.language_proficiency"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="beginner">🔰 Beginner</option>
                  <option value="intermediate">📚 Intermediate</option>
                  <option value="advanced">🏆 Advanced</option>
                  <option value="native">🌟 Native</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Engagement & Behavior -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 mb-4 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">📊</span> Engagement & Activity
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Last Login</label>
                <select
                  v-model="form.audience_filters.last_login"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Anytime</option>
                  <option value="today">📅 Today</option>
                  <option value="week">📅 This Week</option>
                  <option value="month">📅 This Month</option>
                  <option value="3months">📅 Last 3 Months</option>
                  <option value="6months">📅 Last 6 Months</option>
                  <option value="inactive">💤 Inactive (6+ months)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Registered</label>
                <select
                  v-model="form.audience_filters.registered_period"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Anytime</option>
                  <option value="today">🆕 Today</option>
                  <option value="week">🆕 This Week</option>
                  <option value="month">🆕 This Month</option>
                  <option value="3months">Last 3 Months</option>
                  <option value="6months">Last 6 Months</option>
                  <option value="year">Last Year</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Registered After (Custom)</label>
                <DateInput
                  v-model="form.audience_filters.registered_after"
                  placeholder="DD/MM/YYYY"
                  class="w-full"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Email Engagement</label>
                <select
                  v-model="form.audience_filters.email_engagement"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="opened">📧 Opened Recent Emails</option>
                  <option value="clicked">🖱️ Clicked Email Links</option>
                  <option value="never_opened">📪 Never Opened</option>
                  <option value="unsubscribed">🚫 Unsubscribed</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Interests & Preferences -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 mb-4 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">❤️</span> Interests & Travel Preferences
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Travel Interests</label>
                <select
                  v-model="form.audience_filters.travel_interest"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Interest</option>
                  <option value="tourism">🏖️ Tourism & Leisure</option>
                  <option value="study">📚 Study Abroad</option>
                  <option value="work">💼 Work Abroad</option>
                  <option value="business">👔 Business Travel</option>
                  <option value="medical">🏥 Medical Tourism</option>
                  <option value="religious">🕌 Religious (Hajj/Umrah)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Preferred Destinations</label>
                <select
                  v-model="form.audience_filters.preferred_destination"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Destination</option>
                  <option value="europe">🇪🇺 Europe</option>
                  <option value="north_america">🇺🇸 North America</option>
                  <option value="middle_east">🕌 Middle East</option>
                  <option value="southeast_asia">🌏 Southeast Asia</option>
                  <option value="australia">🇦🇺 Australia/NZ</option>
                  <option value="africa">🌍 Africa</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Budget Range (BDT)</label>
                <select
                  v-model="form.audience_filters.budget_range"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Budget</option>
                  <option value="low">৳ Low (< ৳100,000)</option>
                  <option value="medium">৳৳ Medium (৳100K - ৳500K)</option>
                  <option value="high">৳৳৳ High (৳500K - ৳1M)</option>
                  <option value="premium">💎 Premium (> ৳1M)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Communication Preference</label>
                <select
                  v-model="form.audience_filters.communication_preference"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="email">📧 Email</option>
                  <option value="sms">💬 SMS</option>
                  <option value="whatsapp">📱 WhatsApp</option>
                  <option value="phone">📞 Phone</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Special Segments -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 shadow-sm">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="text-lg">⭐</span> Special Segments & Tags
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Customer Segment</label>
                <select
                  v-model="form.audience_filters.customer_segment"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Segment</option>
                  <option value="vip">👑 VIP Customers</option>
                  <option value="high_value">💎 High Value</option>
                  <option value="returning">🔁 Returning Customers</option>
                  <option value="first_time">🆕 First-Time Users</option>
                  <option value="dormant">💤 Dormant Users</option>
                  <option value="at_risk">⚠️ At-Risk Customers</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Referral Status</label>
                <select
                  v-model="form.audience_filters.referral_status"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="has_referred">🎁 Has Referred Others</option>
                  <option value="was_referred">🔗 Was Referred</option>
                  <option value="both">🔄 Both</option>
                  <option value="neither">❌ Neither</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Booking History</label>
                <select
                  v-model="form.audience_filters.booking_frequency"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any</option>
                  <option value="first_booking">🎯 First Booking</option>
                  <option value="frequent">🔥 Frequent (5+ bookings)</option>
                  <option value="occasional">📅 Occasional (2-4)</option>
                  <option value="one_time">1️⃣ One-Time Only</option>
                  <option value="no_bookings">❌ No Bookings Yet</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Wallet Balance</label>
                <select
                  v-model="form.audience_filters.wallet_balance"
                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-brand-red-600"
                >
                  <option value="">Any Balance</option>
                  <option value="has_balance">💰 Has Balance (> ৳0)</option>
                  <option value="high">💰💰 High (> ৳10,000)</option>
                  <option value="zero">💸 Zero Balance</option>
                </select>
              </div>
            </div>
          </div>

          <div class="mt-5 p-4 bg-red-50 dark:bg-blue-900/30 rounded-lg border border-red-200 dark:border-blue-800">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              <strong>💡 Pro Tip:</strong> Combine multiple filters to create highly targeted segments. 
              For example: "Active users from Dhaka division with tourist visa applications who haven't logged in this month" 
              - perfect for re-engagement campaigns!
            </p>
          </div>
        </div>

        <!-- Recipient IDs (for custom) -->
        <div v-if="form.audience_type === 'custom'" class="bg-gray-50 p-4 rounded-lg">
          <label class="block text-sm font-medium text-gray-700 mb-2">Recipient IDs</label>
          <textarea
            v-model="form.recipient_ids_text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
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
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            Schedule Campaign
          </label>
          <input
            v-model="form.scheduled_at"
            type="datetime-local"
            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
          />
          <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
            Leave empty to save as draft. Set a future date to schedule.
          </p>
          <p v-if="form.errors.scheduled_at" class="text-red-500 text-sm mt-1.5">
            {{ form.errors.scheduled_at }}
          </p>
        </div>

        <!-- A/B Testing -->
        <div class="border-t-2 border-gray-200 dark:border-gray-700 pt-6">
          <label class="flex flex-wrap items-center cursor-pointer group">
            <input
              v-model="form.is_ab_test"
              type="checkbox"
              class="w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
            />
            <span class="ml-3 text-base font-semibold text-gray-900 dark:text-white">Enable A/B Testing</span>
          </label>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 ml-8">
            Test different versions of your campaign to optimize performance
          </p>
        </div>

        <!-- A/B Test Configuration -->
        <div v-if="form.is_ab_test" class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6">
          <h3 class="font-bold text-gray-900 text-lg mb-4">
            ⚡ A/B Test Configuration
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Test Split (%)</label>
              <input
                v-model.number="form.ab_test_config.split_percentage"
                type="number"
                min="10"
                max="90"
                class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-yellow-500"
                placeholder="50"
              />
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1.5">
                Percentage of users for variant A (remaining for B)
              </p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Test Duration (hours)</label>
              <input
                v-model.number="form.ab_test_config.duration_hours"
                type="number"
                min="1"
                max="168"
                class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-yellow-500"
                placeholder="24"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap justify-end gap-4 pt-6 border-t-2 border-gray-200 dark:border-gray-700">
          <Link
            :href="route('admin.marketing-campaigns.index')"
            class="px-8 py-3.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all font-semibold text-gray-700 dark:text-gray-300 hover:shadow-md"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-8 py-3.5 bg-brand-red-600 hover:bg-red-700 text-white rounded-lg transition-all shadow-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ form.processing ? 'Creating...' : 'Create Campaign' }}
          </button>
        </div>
      </form>
    </Card>
    </div>
  </AdminLayout>
</template>
