<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    BuildingLibraryIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    FunnelIcon,
    CheckCircleIcon,
    XCircleIcon,
    PencilIcon,
    TrashIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    resources: Object,
    filters: Object,
});

const showFilters = ref(false);
const statusFilter = ref(props.filters?.status || '');
const resourceTypeFilter = ref(props.filters?.resource_type || '');
const searchQuery = ref(props.filters?.search || '');

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showDeleteModal = ref(false);
const selectedResource = ref(null);

const approveForm = useForm({
    admin_notes: '',
});

const rejectForm = useForm({
    admin_notes: '',
});

const resourceTypeLabels = {
    university: 'University',
    school: 'School',
    language_center: 'Language Center',
    training_institute: 'Training Institute',
    job_portal: 'Job Portal',
    other: 'Other',
};

const applyFilters = () => {
    router.get(route('admin.agency-resources.index'), {
        status: statusFilter.value,
        resource_type: resourceTypeFilter.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    resourceTypeFilter.value = '';
    searchQuery.value = '';
    router.get(route('admin.agency-resources.index'));
};

const openApproveModal = (resource) => {
    selectedResource.value = resource;
    approveForm.admin_notes = '';
    showApproveModal.value = true;
};

const openRejectModal = (resource) => {
    selectedResource.value = resource;
    rejectForm.admin_notes = '';
    showRejectModal.value = true;
};

const openDeleteModal = (resource) => {
    selectedResource.value = resource;
    showDeleteModal.value = true;
};

const closeModals = () => {
    showApproveModal.value = false;
    showRejectModal.value = false;
    showDeleteModal.value = false;
    selectedResource.value = null;
};

const submitApproval = () => {
    approveForm.post(route('admin.agency-resources.approve', selectedResource.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModals(),
    });
};

const submitRejection = () => {
    rejectForm.post(route('admin.agency-resources.reject', selectedResource.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModals(),
    });
};

const confirmDelete = () => {
    router.delete(route('admin.agency-resources.destroy', selectedResource.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModals(),
    });
};

const getStatusBadgeClass = (resource) => {
    if (!resource.is_active) return 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300';
    if (resource.is_approved) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
    return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300';
};

const getStatusText = (resource) => {
    if (!resource.is_active) return 'Inactive';
    if (resource.is_approved) return 'Approved';
    return 'Pending';
};

const hasActiveFilters = computed(() => {
    return statusFilter.value || resourceTypeFilter.value || searchQuery.value;
});
</script>

<template>
    <Head title="Agency Resources" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- SVG Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="agencyResGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#agencyResGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-orange-500/20 to-amber-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-2xl shadow-lg">
                                <BuildingLibraryIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Agency Resources</h1>
                                <p class="mt-1 text-gray-300">Manage exclusive resource assignments (universities, schools, etc.)</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex gap-3">
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-medium transition-all backdrop-blur-sm"
                            >
                                <FunnelIcon class="h-5 w-5" />
                                {{ showFilters ? 'Hide' : 'Show' }} Filters
                            </button>
                            <Link
                                :href="route('admin.agency-resources.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-600 text-white rounded-xl font-semibold hover:from-amber-600 hover:to-yellow-700 transition-all shadow-lg"
                            >
                                <PlusIcon class="h-5 w-5" />
                                Add Resource
                            </Link>
                        </div>
                    </div>

                    <!-- Stats Cards in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-500/20 rounded-2xl">
                                    <BuildingLibraryIcon class="h-5 w-5 text-amber-300" />
                                </div>
                                <div>
                                    <p class="text-amber-300 text-xs font-medium">Total Resources</p>
                                    <p class="text-2xl font-bold text-white">{{ resources?.total || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500/20 rounded-2xl">
                                    <BuildingLibraryIcon class="h-5 w-5 text-yellow-300" />
                                </div>
                                <div>
                                    <p class="text-yellow-300 text-xs font-medium">Pending</p>
                                    <p class="text-2xl font-bold text-white">{{ resources?.data?.filter(r => !r.is_approved).length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-2xl">
                                    <CheckCircleIcon class="h-5 w-5 text-green-300" />
                                </div>
                                <div>
                                    <p class="text-green-300 text-xs font-medium">Approved</p>
                                    <p class="text-2xl font-bold text-white">{{ resources?.data?.filter(r => r.is_approved).length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/20 rounded-2xl">
                                    <BuildingLibraryIcon class="h-5 w-5 text-blue-300" />
                                </div>
                                <div>
                                    <p class="text-blue-300 text-xs font-medium">Primary Owners</p>
                                    <p class="text-2xl font-bold text-white">{{ resources?.data?.filter(r => r.is_primary_owner).length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <!-- Filters Panel -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <input
                                        v-model="searchQuery"
                                        @keyup.enter="applyFilters"
                                        type="text"
                                        placeholder="Resource name, agency..."
                                        class="w-full pl-10 pr-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-amber-500 dark:bg-neutral-700 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select
                                    v-model="statusFilter"
                                    @change="applyFilters"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-amber-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending Approval</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Resource Type</label>
                                <select
                                    v-model="resourceTypeFilter"
                                    @change="applyFilters"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-amber-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="(label, value) in resourceTypeLabels" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-end gap-3">
                                <button
                                    @click="applyFilters"
                                    class="flex-1 px-5 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium transition-colors"
                                >
                                    Apply
                                </button>
                                <button
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    class="px-5 py-3 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors"
                                >
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Resources Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Resource</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Agency</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Service</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Commission</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="resource in resources.data" :key="resource.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900/30 dark:to-yellow-900/30 rounded-xl flex items-center justify-center">
                                                <BuildingLibraryIcon class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ resource.resource_name }}</div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-gray-300">
                                                    {{ resourceTypeLabels[resource.resource_type] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ resource.agency?.name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ resource.agency?.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ resource.service_module?.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ resource.country?.name }}</div>
                                        <div v-if="resource.city" class="text-xs text-gray-500 dark:text-gray-400">{{ resource.city }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(resource)"
                                            class="px-3 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ getStatusText(resource) }}
                                        </span>
                                        <div v-if="resource.is_primary_owner" class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                Primary Owner
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ resource.special_commission_rate || '—' }}%</div>
                                        <div v-if="!resource.special_commission_rate" class="text-xs text-gray-500 dark:text-gray-400">Uses default</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button
                                                v-if="!resource.is_approved"
                                                @click="openApproveModal(resource)"
                                                class="p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-2xl transition-colors"
                                                title="Approve"
                                            >
                                                <CheckCircleIcon class="h-5 w-5" />
                                            </button>
                                            <button
                                                v-if="!resource.is_approved"
                                                @click="openRejectModal(resource)"
                                                class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-colors"
                                                title="Reject"
                                            >
                                                <XCircleIcon class="h-5 w-5" />
                                            </button>
                                            <Link
                                                :href="route('admin.agency-resources.edit', resource.id)"
                                                class="p-2 text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-2xl transition-colors"
                                                title="Edit"
                                            >
                                                <PencilIcon class="h-5 w-5" />
                                            </Link>
                                            <button
                                                @click="openDeleteModal(resource)"
                                                class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-colors"
                                                title="Delete"
                                            >
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="!resources.data || resources.data.length === 0">
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 bg-amber-100 dark:bg-amber-900/30 rounded-full mb-4">
                                                <BuildingLibraryIcon class="h-8 w-8 text-amber-600 dark:text-amber-400" />
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No resources found</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start by adding an exclusive resource assignment.</p>
                                            <Link
                                                :href="route('admin.agency-resources.create')"
                                                class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium transition-colors"
                                            >
                                                <PlusIcon class="h-5 w-5" />
                                                Add Resource
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="resources.data && resources.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-medium text-gray-900 dark:text-white">{{ resources.from }}</span>
                                to <span class="font-medium text-gray-900 dark:text-white">{{ resources.to }}</span>
                                of <span class="font-medium text-gray-900 dark:text-white">{{ resources.total }}</span> resources
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="resources.prev_page_url"
                                    :href="resources.prev_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <Link
                                    v-if="resources.next_page_url"
                                    :href="resources.next_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approve Modal -->
                <Modal :show="showApproveModal" @close="closeModals">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Approve Resource Assignment</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Approve <span class="font-medium text-gray-900 dark:text-white">{{ selectedResource?.resource_name }}</span> for 
                            <span class="font-medium text-gray-900 dark:text-white">{{ selectedResource?.agency?.name }}</span>?
                        </p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes (Optional)</label>
                            <textarea
                                v-model="approveForm.admin_notes"
                                rows="3"
                                class="w-full rounded-xl border-0 ring-1 ring-gray-300 dark:ring-neutral-600 focus:ring-2 focus:ring-green-500 dark:bg-neutral-700 dark:text-white"
                                placeholder="Add any notes about this approval..."
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <SecondaryButton @click="closeModals">Cancel</SecondaryButton>
                            <PrimaryButton @click="submitApproval" :disabled="approveForm.processing">Approve</PrimaryButton>
                        </div>
                    </div>
                </Modal>

                <!-- Reject Modal -->
                <Modal :show="showRejectModal" @close="closeModals">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reject Resource Assignment</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Reject <span class="font-medium text-gray-900 dark:text-white">{{ selectedResource?.resource_name }}</span> for 
                            <span class="font-medium text-gray-900 dark:text-white">{{ selectedResource?.agency?.name }}</span>?
                        </p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Rejection *</label>
                            <textarea
                                v-model="rejectForm.admin_notes"
                                rows="3"
                                class="w-full rounded-xl border-0 ring-1 ring-gray-300 dark:ring-neutral-600 focus:ring-2 focus:ring-red-500 dark:bg-neutral-700 dark:text-white"
                                placeholder="Explain why this resource is being rejected..."
                                required
                            ></textarea>
                            <p v-if="rejectForm.errors.admin_notes" class="mt-1 text-sm text-red-600">{{ rejectForm.errors.admin_notes }}</p>
                        </div>
                        <div class="flex justify-end gap-3">
                            <SecondaryButton @click="closeModals">Cancel</SecondaryButton>
                            <DangerButton @click="submitRejection" :disabled="rejectForm.processing || !rejectForm.admin_notes">Reject</DangerButton>
                        </div>
                    </div>
                </Modal>

                <!-- Delete Confirmation Modal -->
                <Modal :show="showDeleteModal" @close="closeModals">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delete Resource Assignment</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Are you sure you want to delete <span class="font-medium text-gray-900 dark:text-white">{{ selectedResource?.resource_name }}</span>? 
                            This action cannot be undone.
                        </p>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-4">
                            <p class="text-sm text-red-800 dark:text-red-300">
                                ⚠️ Warning: This will permanently remove the resource assignment.
                            </p>
                        </div>
                        <div class="flex justify-end gap-3">
                            <SecondaryButton @click="closeModals">Cancel</SecondaryButton>
                            <DangerButton @click="confirmDelete">Delete Permanently</DangerButton>
                        </div>
                    </div>
                </Modal>
            </div>
        </div>
    </AdminLayout>
</template>
