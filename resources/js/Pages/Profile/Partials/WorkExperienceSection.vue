<script setup>
import { ref, onMounted, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

// Import components
import Modal from '@/Components/Modal.vue'
import RhythmicCard from '@/Components/Rhythmic/RhythmicCard.vue'
import FlowButton from '@/Components/Rhythmic/FlowButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import DateInput from '@/Components/DateInput.vue'
import InputError from '@/Components/InputError.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { BriefcaseIcon, PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  workExperiences: {
    type: Array,
    default: () => []
  },
  countries: {
    type: Array,
    default: () => []
  },
})

// State
const workList = ref(props.workExperiences || [])
const isLoading = ref(false)
const showModal = ref(false)
const isEditMode = ref(false)
const currentWorkId = ref(null)

// Form
const form = ref({
  company_name: '',
  position: '',
  employment_type: '',
  start_date: '',
  end_date: '',
  is_current_employment: false,
  country: 'Bangladesh',
  city: '',
  job_description: '',
  salary: '',
  currency: 'BDT',
  salary_period: 'monthly',
  supervisor_name: '',
  supervisor_phone: '',
  supervisor_email: '',
  reason_for_leaving: '',
})

const formErrors = ref({})
const isProcessing = ref(false)

// Define employment types locally (must match database enum values)
const employmentTypes = [
  { value: 'full_time', label: 'Full-time' },
  { value: 'part_time', label: 'Part-time' },
  { value: 'contract', label: 'Contract' },
  { value: 'freelance', label: 'Freelance' },
  { value: 'internship', label: 'Internship' },
]

// Currency options
const currencies = ['BDT', 'USD', 'GBP', 'EUR', 'AUD', 'CAD']
const salaryPeriods = [
  { value: 'monthly', label: 'Monthly' },
  { value: 'yearly', label: 'Yearly' },
]

// Helper to get employment type label
const getEmploymentTypeLabel = (value) => {
  const type = employmentTypes.find(t => t.value === value)
  return type ? type.label : value
}

const countries = computed(() => props.countries ? props.countries.map(c => c.name) : [])

// Use props directly instead of fetching
onMounted(() => {
  // Data is passed via props from ProfileController
  workList.value = props.workExperiences || []
})

// Watch for prop changes
import { watch } from 'vue'
watch(() => props.workExperiences, (newVal) => {
  workList.value = newVal || []
})

// Open modal
const openModal = (work = null) => {
  if (work) {
    isEditMode.value = true
    currentWorkId.value = work.id
    form.value = {
      company_name: work.company_name || '',
      position: work.position || '',
      employment_type: work.employment_type || '',
      start_date: work.start_date ? work.start_date.substring(0, 10) : '',
      end_date: work.end_date ? work.end_date.substring(0, 10) : '',
      is_current_employment: work.is_current_employment || false,
      country: work.country || 'Bangladesh',
      city: work.city || '',
      job_description: work.job_description || '',
      salary: work.salary || '',
      currency: work.currency || 'BDT',
      salary_period: work.salary_period || 'monthly',
      supervisor_name: work.supervisor_name || '',
      supervisor_phone: work.supervisor_phone || '',
      supervisor_email: work.supervisor_email || '',
      reason_for_leaving: work.reason_for_leaving || '',
    }
  } else {
    isEditMode.value = false
    currentWorkId.value = null
    form.value = {
      company_name: '',
      position: '',
      employment_type: '',
      start_date: '',
      end_date: '',
      is_current_employment: false,
      country: 'Bangladesh',
      city: '',
      job_description: '',
      salary: '',
      currency: 'BDT',
      salary_period: 'monthly',
      supervisor_name: '',
      supervisor_phone: '',
      supervisor_email: '',
      reason_for_leaving: '',
    }
  }
  formErrors.value = {}
  showModal.value = true
}

// Close modal
const closeModal = () => {
  showModal.value = false
  form.value = {
    company_name: '',
    position: '',
    employment_type: '',
    start_date: '',
    end_date: '',
    is_current_employment: false,
    country: 'Bangladesh',
    city: '',
    job_description: '',
    salary: '',
    currency: 'BDT',
    salary_period: 'monthly',
    supervisor_name: '',
    supervisor_phone: '',
    supervisor_email: '',
    reason_for_leaving: '',
  }
  formErrors.value = {}
}

// Submit form
const submit = async () => {
  isProcessing.value = true
  formErrors.value = {}

  try {
    if (isEditMode.value) {
      await axios.put(route('api.profile.work-experience.update', currentWorkId.value), form.value)
    } else {
      await axios.post(route('api.profile.work-experience.store'), form.value)
    }
    
    closeModal()
    router.reload({ only: ['workExperiences'] })
  } catch (error) {
    if (error.response && error.response.data.errors) {
      formErrors.value = error.response.data.errors
    } else {
      console.error('Form submission error:', error)
      alert('Failed to save work experience. Please try again.')
    }
  } finally {
    isProcessing.value = false
  }
}

// Delete work experience
const deleteWork = async (id) => {
  if (confirm('Are you sure you want to delete this work experience?')) {
    try {
      await axios.delete(route('api.profile.work-experience.destroy', id))
      router.reload({ only: ['workExperiences'] })
    } catch (error) {
      console.error('Delete error:', error)
      alert('Failed to delete work experience. Please try again.')
    }
  }
}

// Calculate duration
const calculateDuration = (startDate, endDate, isCurrent) => {
  if (!startDate) return ''
  
  const start = new Date(startDate)
  const end = isCurrent ? new Date() : new Date(endDate || new Date())
  
  const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth())
  const years = Math.floor(months / 12)
  const remainingMonths = months % 12
  
  if (years > 0 && remainingMonths > 0) {
    return `${years} year${years > 1 ? 's' : ''}, ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}`
  } else if (years > 0) {
    return `${years} year${years > 1 ? 's' : ''}`
  } else {
    return `${remainingMonths} month${remainingMonths > 1 ? 's' : ''}`
  }
}

// Format date for display
const { formatDate } = useBangladeshFormat();

// Sort by start date (most recent first), filtering out any null/undefined entries
const sortedWorkList = computed(() => {
  return [...workList.value].filter(w => w && w.id).sort((a, b) => {
    if (a.is_current_employment) return -1
    if (b.is_current_employment) return 1
    return new Date(b.start_date || 0) - new Date(a.start_date || 0)
  })
})
</script>

<template>
  <section class="space-y-6">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-md">
          <BriefcaseIcon class="w-6 h-6 text-white" />
        </div>
        <div>
          <h2 class="font-semibold text-lg text-gray-900">Work Experience</h2>
          <p class="text-sm text-gray-500">Employment history and professional background</p>
        </div>
      </div>
      <button
        @click="openModal()"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm rounded-xl shadow-sm hover:from-indigo-700 hover:to-purple-700 transition-all"
      >
        <PlusIcon class="w-4 h-4" />
        <span class="hidden sm:inline">Add Experience</span>
        <span class="sm:hidden">Add</span>
      </button>
    </div>

    <!-- Work Experience List -->
    <div v-if="isLoading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      <p class="mt-2 text-sm text-gray-600">Loading work experiences...</p>
    </div>

    <div v-else-if="sortedWorkList.length === 0" class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
      <BriefcaseIcon class="mx-auto h-16 w-16 md:h-20 md:w-20 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">No work experience</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by adding your first work experience.</p>
      <div class="mt-6">
        <SecondaryButton @click="openModal()" class="w-full sm:w-auto py-3 px-6 text-base touch-manipulation justify-center" style="min-height: 48px">
          <PlusIcon class="h-5 w-5 md:h-6 md:w-6 mr-2" />
          Add Work Experience
        </SecondaryButton>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="(work, index) in sortedWorkList"
        :key="work?.id || index"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
      >
        <!-- Card Header -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <BriefcaseIcon class="w-6 h-6 md:w-7 md:h-7 text-growth-600 dark:text-indigo-400 flex-shrink-0" />
                <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ work?.position || 'Position' }}</h3>
              </div>
              <p class="text-sm font-semibold text-growth-600 dark:text-indigo-400">{{ work?.company_name || 'Company' }}</p>
            </div>
            <span
              v-if="work.is_current_employment"
              class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 text-xs font-semibold rounded-full flex-shrink-0"
            >
              <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1 animate-pulse"></span>
              Current
            </span>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-4 space-y-3">
          <!-- Location & Date Info -->
          <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ work.city }}, {{ work.country }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ formatDate(work.start_date) }} - {{ work.is_current_employment ? 'Present' : formatDate(work.end_date) }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm font-medium text-growth-600 dark:text-indigo-400">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ calculateDuration(work.start_date, work.end_date, work.is_current_employment) }}</span>
            </div>
          </div>
          
          <!-- Tags -->
          <div class="flex flex-wrap gap-2">
            <span
              v-if="work.employment_type"
              class="inline-flex items-center px-2.5 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-full"
            >
              {{ getEmploymentTypeLabel(work.employment_type) }}
            </span>
            <span
              v-if="work.salary"
              class="inline-flex items-center px-2.5 py-1 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-medium rounded-full"
            >
              {{ work.currency }} {{ Number(work.salary).toLocaleString() }}/{{ work.salary_period }}
            </span>
          </div>
          
          <!-- Job Description -->
          <div v-if="work.job_description" class="pt-2">
            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">Job Description</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed">{{ work.job_description }}</p>
          </div>
          
          <!-- Supervisor Info -->
          <div v-if="work.supervisor_name" class="pt-2">
            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">Supervisor</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ work.supervisor_name }}
              <span v-if="work.supervisor_email" class="text-gray-500"> • {{ work.supervisor_email }}</span>
            </p>
          </div>
        </div>

        <!-- Card Footer with Actions -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
          <div class="flex gap-2">
            <button
              @click="openModal(work)"
              class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg font-medium text-sm transition-colors"
            >
              <PencilSquareIcon class="h-4 w-4" />
              <span>Edit</span>
            </button>
            <button
              @click="deleteWork(work.id)"
              class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg font-medium text-sm transition-colors"
            >
              <TrashIcon class="h-4 w-4" />
              <span>Delete</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Add More Button -->
      <button
        @click="openModal()"
        class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 bg-growth-600 hover:bg-growth-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-base"
      >
        <PlusIcon class="h-5 w-5" />
        <span>ADD MORE EXPERIENCE</span>
      </button>
    </div>

    <!-- Modal -->
    <Modal :show="showModal" @close="closeModal" max-width="2xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ isEditMode ? 'Edit Work Experience' : 'Add Work Experience' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Company Name & Job Title -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="company_name" value="Company Name *" />
              <TextInput
                id="company_name"
                v-model="form.company_name"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError class="mt-2" :message="formErrors.company_name" />
            </div>

            <div>
              <InputLabel for="position" value="Job Title / Position *" />
              <TextInput
                id="position"
                v-model="form.position"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError class="mt-2" :message="formErrors.position" />
            </div>
          </div>

          <!-- Employment Type -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="employment_type" value="Employment Type" />
              <select
                id="employment_type"
                v-model="form.employment_type"
                class="mt-1 block w-full border-gray-300 focus:border-growth-600 focus:ring-growth-600 rounded-md shadow-sm"
              >
                <option value="">Select Type</option>
                <option v-for="type in employmentTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
              <InputError class="mt-2" :message="formErrors.employment_type" />
            </div>
          </div>

          <!-- Start Date & End Date -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="start_date" value="Start Date *" />
              <DateInput
                id="start_date"
                v-model="form.start_date"
                class="mt-1 block w-full"
                required
              />
              <InputError class="mt-2" :message="formErrors.start_date" />
            </div>

            <div>
              <InputLabel for="end_date" :value="form.is_current_employment ? 'End Date (Optional)' : 'End Date *'" />
              <DateInput
                id="end_date"
                v-model="form.end_date"
                class="mt-1 block w-full"
                :disabled="form.is_current_employment"
                :required="!form.is_current_employment"
              />
              <InputError class="mt-2" :message="formErrors.end_date" />
            </div>
          </div>

          <!-- Currently Working Here -->
          <div class="flex items-center">
            <Checkbox
              id="is_current_employment"
              v-model:checked="form.is_current_employment"
              @update:checked="val => { if (val) form.end_date = '' }"
            />
            <InputLabel for="is_current_employment" value="I currently work here" class="ml-2" />
          </div>

          <!-- Country & City -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="country" value="Country *" />
              <select
                id="country"
                v-model="form.country"
                class="mt-1 block w-full border-gray-300 focus:border-growth-600 focus:ring-growth-600 rounded-md shadow-sm"
                required
              >
                <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
              </select>
              <InputError class="mt-2" :message="formErrors.country" />
            </div>

            <div>
              <InputLabel for="city" value="City" />
              <TextInput
                id="city"
                v-model="form.city"
                type="text"
                class="mt-1 block w-full"
              />
              <InputError class="mt-2" :message="formErrors.city" />
            </div>
          </div>

          <!-- Job Description -->
          <div>
            <InputLabel for="job_description" value="Job Description & Responsibilities" />
            <textarea
              id="job_description"
              v-model="form.job_description"
              rows="3"
              class="mt-1 block w-full border-gray-300 focus:border-growth-600 focus:ring-growth-600 rounded-md shadow-sm"
              placeholder="Describe your main responsibilities and duties..."
            ></textarea>
            <InputError class="mt-2" :message="formErrors.job_description" />
          </div>

          <!-- Salary Information -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel for="salary" value="Salary (Optional)" />
              <TextInput
                id="salary"
                v-model="form.salary"
                type="number"
                class="mt-1 block w-full"
                placeholder="e.g., 50000"
              />
              <InputError class="mt-2" :message="formErrors.salary" />
            </div>

            <div>
              <InputLabel for="currency" value="Currency" />
              <select
                id="currency"
                v-model="form.currency"
                class="mt-1 block w-full border-gray-300 focus:border-growth-600 focus:ring-growth-600 rounded-md shadow-sm"
              >
                <option v-for="curr in currencies" :key="curr" :value="curr">{{ curr }}</option>
              </select>
              <InputError class="mt-2" :message="formErrors.currency" />
            </div>

            <div>
              <InputLabel for="salary_period" value="Period" />
              <select
                id="salary_period"
                v-model="form.salary_period"
                class="mt-1 block w-full border-gray-300 focus:border-growth-600 focus:ring-growth-600 rounded-md shadow-sm"
              >
                <option v-for="period in salaryPeriods" :key="period.value" :value="period.value">{{ period.label }}</option>
              </select>
              <InputError class="mt-2" :message="formErrors.salary_period" />
            </div>
          </div>

          <!-- Supervisor Information -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel for="supervisor_name" value="Supervisor Name" />
              <TextInput
                id="supervisor_name"
                v-model="form.supervisor_name"
                type="text"
                class="mt-1 block w-full"
                placeholder="e.g., John Smith"
              />
              <InputError class="mt-2" :message="formErrors.supervisor_name" />
            </div>

            <div>
              <InputLabel for="supervisor_email" value="Supervisor Email" />
              <TextInput
                id="supervisor_email"
                v-model="form.supervisor_email"
                type="email"
                class="mt-1 block w-full"
                placeholder="e.g., john@company.com"
              />
              <InputError class="mt-2" :message="formErrors.supervisor_email" />
            </div>

            <div>
              <InputLabel for="supervisor_phone" value="Supervisor Phone" />
              <TextInput
                id="supervisor_phone"
                v-model="form.supervisor_phone"
                type="tel"
                class="mt-1 block w-full"
                placeholder="e.g., +880 1712-345678"
              />
              <InputError class="mt-2" :message="formErrors.supervisor_phone" />
            </div>
          </div>

          <!-- Reason for Leaving -->
          <div v-if="!form.is_current_employment">
            <InputLabel for="reason_for_leaving" value="Reason for Leaving" />
            <TextInput
              id="reason_for_leaving"
              v-model="form.reason_for_leaving"
              type="text"
              class="mt-1 block w-full"
              placeholder="e.g., Career growth, better opportunity"
            />
            <InputError class="mt-2" :message="formErrors.reason_for_leaving" />
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-4 mt-6 pt-4 border-t">
            <SecondaryButton @click="closeModal" type="button" class="w-full sm:w-auto py-3 px-6 text-base touch-manipulation justify-center" style="min-height: 48px">
              Cancel
            </SecondaryButton>
            <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="isProcessing" class="w-full sm:w-auto py-3 px-6 text-base touch-manipulation justify-center" style="min-height: 48px">
              {{ editingId ? 'Update' : 'Save' }} Experience
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </section>
</template>

