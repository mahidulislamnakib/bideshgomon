<template>
    <AdminLayout>
        <Head title="Review Verification Request" />
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Button -->
            <Link
                :href="route('admin.agency-verification.index')"
                class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6"
            >
                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                Back to Verification Requests
            </Link>

            <!-- Agency Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 mb-8">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ agency.name }}</h1>
                        <p class="text-gray-600 dark:text-gray-300">{{ agency.company_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ agency.email }} � {{ agency.phone }}</p>
                    </div>
                    <span
                        :class="[
                            'px-4 py-2 rounded-full text-sm font-medium',
                            request.status === 'approved' ? 'bg-green-100 text-green-800' :
                            request.status === 'rejected' ? 'bg-red-100 text-red-800' :
                            request.status === 'under_review' ? 'bg-red-100 text-growth-600' :
                            request.status === 'requires_changes' ? 'bg-orange-100 text-orange-800' :
                            'bg-yellow-100 text-yellow-800'
                        ]"
                    >
                        {{ (request?.status || '').replace('_', ' ').toUpperCase() }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Business Type</p>
                        <p class="font-medium dark:text-white">{{ agency.business_type || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Established</p>
                        <p class="font-medium dark:text-white">{{ agency.established_year || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">License Number</p>
                        <p class="font-medium dark:text-white">{{ agency.license_number || 'N/A' }}</p>
                    </div>
                </div>

                <div v-if="request.message" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Agency Message:</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ request.message }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Submitted: {{ formatDate(request.submitted_at) }}</p>
                    <p v-if="request.reviewed_at" class="text-sm text-gray-500 dark:text-gray-400">
                        Reviewed: {{ formatDate(request.reviewed_at) }} by {{ request.reviewer.name }}
                    </p>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Submitted Documents</h2>
                <div class="space-y-4">
                    <div v-for="doc in documents" :key="doc.id" class="border dark:border-gray-700 rounded-2xl p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start flex-1">
                                <DocumentIcon class="h-6 w-6 text-gray-400 mr-3 mt-1" />
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="font-medium text-gray-900 dark:text-white">{{ doc.document_name }}</h3>
                                        <span
                                            :class="[
                                                'ml-3 px-2 py-1 text-xs rounded-full',
                                                doc.status === 'approved' ? 'bg-green-100 text-green-800' :
                                                doc.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                                'bg-yellow-100 text-yellow-800'
                                            ]"
                                        >
                                            {{ doc.status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ (doc?.document_type || '').replace('_', ' ') }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ formatFileSize(doc.file_size) }}</p>

                                    <div v-if="doc.status === 'rejected' && doc.rejection_reason" class="mt-2 p-3 bg-red-50 rounded">
                                        <p class="text-sm text-red-900">Rejection Reason:</p>
                                        <p class="text-sm text-red-700 mt-1">{{ doc.rejection_reason }}</p>
                                    </div>

                                    <div v-if="doc.notes" class="mt-2 p-3 bg-red-50 rounded">
                                        <p class="text-sm text-red-900">Notes:</p>
                                        <p class="text-sm text-blue-700 mt-1">{{ doc.notes }}</p>
                                    </div>

                                    <!-- Document Status Update Form -->
                                    <div v-if="doc.status === 'pending'" class="mt-4 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                            <textarea
                                                v-model="documentForms[doc.id].notes"
                                                rows="2"
                                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                                                placeholder="Any notes about this document..."
                                            ></textarea>
                                        </div>
                                        <div v-if="documentForms[doc.id].status === 'rejected'">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rejection Reason *</label>
                                            <textarea
                                                v-model="documentForms[doc.id].rejection_reason"
                                                rows="2"
                                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                                                placeholder="Explain why this document is being rejected..."
                                            ></textarea>
                                        </div>
                                        <div class="flex space-x-3">
                                            <button
                                                @click="updateDocumentStatus(doc.id, 'approved')"
                                                class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                @click="updateDocumentStatus(doc.id, 'rejected')"
                                                class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-growth-700"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a
                                :href="`/storage/${doc.file_path}`"
                                target="_blank"
                                class="ml-4 text-growth-600 hover:text-growth-600 text-sm font-medium"
                            >
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Request Status Form -->
            <div v-if="request.status !== 'approved'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Update Request Status</h2>
                <form @submit.prevent="updateRequestStatus" class="space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Status</label>
                        <select
                            id="status"
                            v-model="statusForm.status"
                            required
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                        >
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="requires_changes">Requires Changes</option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Notes</label>
                        <textarea
                            id="admin_notes"
                            v-model="statusForm.admin_notes"
                            rows="4"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                            placeholder="Internal notes about this verification..."
                        ></textarea>
                    </div>

                    <div v-if="statusForm.status === 'rejected'">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rejection Reason *</label>
                        <textarea
                            id="rejection_reason"
                            v-model="statusForm.rejection_reason"
                            rows="4"
                            required
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                            placeholder="Explain why the verification is being rejected..."
                        ></textarea>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-xl">
                        <p class="text-sm text-yellow-900">
                            <strong>Note:</strong> The agency will be notified via email about this status change.
                            {{ statusForm.status === 'approved' ? 'Approving will mark all submitted documents as approved and give the agency a verified badge.' : '' }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full px-4 py-3 bg-growth-600 text-white rounded-xl hover:bg-growth-700 disabled:opacity-50"
                    >
                        {{ processing ? 'Updating...' : 'Update Status' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DocumentIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    request: Object,
    documents: Array,
    agency: Object,
});

const statusForm = ref({
    status: props.request.status,
    admin_notes: props.request.admin_notes || '',
    rejection_reason: props.request.rejection_reason || '',
});

const documentForms = ref({});
props.documents.forEach(doc => {
    documentForms.value[doc.id] = {
        status: 'pending',
        notes: '',
        rejection_reason: '',
    };
});

const processing = ref(false);

const updateRequestStatus = () => {
    processing.value = true;
    router.post(
        route('admin.agency-verification.update-status', props.request.id),
        statusForm.value,
        {
            onFinish: () => processing.value = false,
        }
    );
};

const updateDocumentStatus = (documentId, status) => {
    if (status === 'rejected' && !documentForms.value[documentId].rejection_reason) {
        alert('Please provide a rejection reason');
        documentForms.value[documentId].status = 'rejected';
        return;
    }

    router.post(
        route('admin.agency-verification.update-document-status', documentId),
        {
            status,
            ...documentForms.value[documentId],
        }
    );
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatFileSize = (bytes) => {
    if (!bytes) return 'Unknown';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
