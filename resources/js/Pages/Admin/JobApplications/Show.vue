<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    ArrowLeftIcon, UserCircleIcon, BriefcaseIcon, DocumentTextIcon,
    EnvelopeIcon, PhoneIcon, MapPinIcon, AcademicCapIcon,
    ClockIcon, CheckCircleIcon, XCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    application: Object,
});

const form = useForm({
    status: props.application.status,
    admin_notes: props.application.admin_notes || '',
});

const updateStatus = () => {
    form.post(route('admin.applications.update-status', props.application.id), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Application status updated successfully!');
        },
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-yellow-100 text-yellow-700',
        'reviewed': 'bg-red-100 text-blue-700',
        'shortlisted': 'bg-purple-100 text-purple-700',
        'rejected': 'bg-red-100 text-red-700',
        'hired': 'bg-green-100 text-green-700',
    };
    return badges[status] || 'bg-gray-100 text-gray-700';
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Application from ${application.user?.name}`" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link
                    :href="route('admin.applications.index')"
                    class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6"
                >
                    <ArrowLeftIcon class="h-5 w-5 mr-2" />
                    Back to Applications
                </Link>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Application Header -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ application.user?.name }}</h1>
                                    <span :class="getStatusBadge(application.status)" class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ application.status }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <EnvelopeIcon class="h-5 w-5 mr-3 text-gray-400 dark:text-gray-500" />
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Email</div>
                                        <div class="font-medium">{{ application.user?.email }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <PhoneIcon class="h-5 w-5 mr-3 text-gray-400 dark:text-gray-500" />
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Phone</div>
                                        <div class="font-medium">{{ application.user?.user_profile?.phone || 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <MapPinIcon class="h-5 w-5 mr-3 text-gray-400 dark:text-gray-500" />
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Location</div>
                                        <div class="font-medium">{{ application.user?.user_profile?.city || 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <ClockIcon class="h-5 w-5 mr-3 text-gray-400 dark:text-gray-500" />
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Applied</div>
                                        <div class="font-medium">{{ formatDate(application.submitted_at || application.created_at) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <BriefcaseIcon class="h-5 w-5 mr-2 text-growth-600" />
                                Applied Position
                            </h2>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Job Title</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ application.job_posting?.title }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Company</div>
                                        <div class="font-medium">{{ application.job_posting?.company_name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Location</div>
                                        <div class="font-medium">{{ application.job_posting?.city }}, {{ application.job_posting?.country?.name }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Salary Range</div>
                                        <div class="font-medium">{{ application.job_posting?.salary_currency }} {{ application.job_posting?.salary_min?.toLocaleString() }} - {{ application.job_posting?.salary_max?.toLocaleString() }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Application Fee</div>
                                        <div class="font-medium">?{{ application.application_fee_paid?.toLocaleString() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cover Letter -->
                        <div v-if="application.cover_letter" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <DocumentTextIcon class="h-5 w-5 mr-2 text-growth-600" />
                                Cover Letter
                            </h2>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-4 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                {{ application.cover_letter }}
                            </div>
                        </div>

                        <!-- Education -->
                        <div v-if="application.user?.educations?.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <AcademicCapIcon class="h-5 w-5 mr-2 text-growth-600" />
                                Education
                            </h2>
                            <div class="space-y-4">
                                <div
                                    v-for="edu in application.user.educations"
                                    :key="edu.id"
                                    class="border-l-4 border-growth-600 pl-4"
                                >
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ edu.degree }} in {{ edu.field_of_study }}</div>
                                    <div class="text-gray-600 dark:text-gray-400">{{ edu.institution_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(edu.start_date) }} - {{ edu.end_date ? formatDate(edu.end_date) : 'Present' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Experience -->
                        <div v-if="application.user?.work_experiences?.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <BriefcaseIcon class="h-5 w-5 mr-2 text-growth-600" />
                                Work Experience
                            </h2>
                            <div class="space-y-4">
                                <div
                                    v-for="exp in application.user.work_experiences"
                                    :key="exp.id"
                                    class="border-l-4 border-purple-500 pl-4"
                                >
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ exp.position }}</div>
                                    <div class="text-gray-600 dark:text-gray-400">{{ exp.company_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(exp.start_date) }} - {{ exp.end_date ? formatDate(exp.end_date) : 'Present' }}</div>
                                    <div v-if="exp.job_description" class="text-sm text-gray-700 dark:text-gray-300 mt-2">{{ exp.job_description }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Update Status -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Update Status</h2>
                            <form @submit.prevent="updateStatus" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-growth-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="reviewed">Reviewed</option>
                                        <option value="shortlisted">Shortlisted</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="hired">Hired</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes</label>
                                    <textarea
                                        v-model="form.admin_notes"
                                        rows="4"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-growth-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Add internal notes about this application..."
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full px-6 py-3 bg-growth-600 text-white rounded-2xl font-semibold hover:bg-growth-700 transition-colors disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Updating...' : 'Update Application' }}
                                </button>
                            </form>
                        </div>

                        <!-- Application Timeline -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Timeline</h2>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="p-2 bg-green-100 rounded-full mr-3">
                                        <CheckCircleIcon class="h-4 w-4 text-green-600" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">Application Submitted</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(application.submitted_at || application.created_at) }}</div>
                                    </div>
                                </div>
                                <div v-if="application.reviewed_at" class="flex items-start">
                                    <div class="p-2 bg-red-100 rounded-full mr-3">
                                        <CheckCircleIcon class="h-4 w-4 text-growth-600" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">Reviewed by Admin</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(application.reviewed_at) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Payment Details</h2>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Application Fee</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">?{{ application.application_fee_paid?.toLocaleString() }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Payment Status</div>
                                    <span :class="application.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" class="inline-block px-3 py-1 text-sm font-semibold rounded-full mt-1">
                                        {{ application.payment_status }}
                                    </span>
                                </div>
                                <div v-if="application.wallet_transaction_id">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Transaction ID</div>
                                    <div class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ application.wallet_transaction_id }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- CV -->
                        <div v-if="application.user_cv" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Attached CV</h2>
                            <div class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/30 rounded-2xl">
                                <DocumentTextIcon class="h-8 w-8 text-purple-600 mr-3" />
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ application.user_cv.title || 'Resume' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">CV Template</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
