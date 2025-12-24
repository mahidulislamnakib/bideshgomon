<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    DocumentTextIcon,
    GlobeAltIcon,
    CheckCircleIcon,
    BriefcaseIcon,
    PlusIcon,
    EyeIcon,
    PencilIcon,
    FunnelIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    visaRequirements: Object,
    stats: Object,
    countries: Array,
    visaTypes: Array,
    filters: Object,
});

const showFilters = ref(false);

const filterForm = ref({
    country: props.filters?.country || '',
    visa_type: props.filters?.visa_type || '',
    is_active: props.filters?.is_active || '',
});

const applyFilters = () => {
    router.get(route('admin.visa-requirements.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        country: '',
        visa_type: '',
        is_active: '',
    };
    applyFilters();
};

const toggleStatus = (requirement) => {
    router.post(
        route('admin.visa-requirements.toggle-active', requirement.id),
        {},
        {
            preserveScroll: true,
        }
    );
};

const hasActiveFilters = computed(() => {
    return filterForm.value.country || filterForm.value.visa_type || filterForm.value.is_active;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-BD', {
        minimumFractionDigits: 0,
    }).format(amount || 0);
};
</script>

<template>
    <Head title="Visa Requirements Management" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <!-- SVG Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="visaReqGrid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M0 32V0h32" fill="none" stroke="currentColor" stroke-opacity="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#visaReqGrid)" />
                    </svg>
                </div>

                <!-- Gradient Orbs -->
                <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-gradient-to-br from-cyan-500/20 to-teal-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-gradient-to-br from-emerald-500/20 to-cyan-500/20 rounded-full blur-3xl"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-2xl shadow-lg">
                                <DocumentTextIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Visa Requirements</h1>
                                <p class="mt-1 text-gray-300">Manage visa requirements for different countries and professions</p>
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
                                :href="route('admin.visa-requirements.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-teal-600 text-white rounded-xl font-semibold hover:from-cyan-600 hover:to-teal-700 transition-all shadow-lg"
                            >
                                <PlusIcon class="h-5 w-5" />
                                Add Requirement
                            </Link>
                        </div>
                    </div>

                    <!-- Stats Cards in Hero -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-cyan-500/20 rounded-2xl">
                                    <DocumentTextIcon class="h-5 w-5 text-cyan-300" />
                                </div>
                                <div>
                                    <p class="text-cyan-300 text-xs font-medium">Total</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total_requirements || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-500/20 rounded-2xl">
                                    <GlobeAltIcon class="h-5 w-5 text-emerald-300" />
                                </div>
                                <div>
                                    <p class="text-emerald-300 text-xs font-medium">Countries</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total_countries || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500/20 rounded-2xl">
                                    <CheckCircleIcon class="h-5 w-5 text-green-300" />
                                </div>
                                <div>
                                    <p class="text-green-300 text-xs font-medium">Active</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.active_requirements || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/20 rounded-2xl">
                                    <DocumentTextIcon class="h-5 w-5 text-purple-300" />
                                </div>
                                <div>
                                    <p class="text-purple-300 text-xs font-medium">Documents</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.total_documents || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-orange-500/20 rounded-2xl">
                                    <BriefcaseIcon class="h-5 w-5 text-orange-300" />
                                </div>
                                <div>
                                    <p class="text-orange-300 text-xs font-medium">Professions</p>
                                    <p class="text-2xl font-bold text-white">{{ stats?.profession_variants || 0 }}</p>
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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country</label>
                                <select 
                                    v-model="filterForm.country" 
                                    @change="applyFilters"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-cyan-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Countries</option>
                                    <option v-for="country in countries" :key="country.value" :value="country.value">
                                        {{ country.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visa Type</label>
                                <select 
                                    v-model="filterForm.visa_type" 
                                    @change="applyFilters"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-cyan-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in visaTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select 
                                    v-model="filterForm.is_active" 
                                    @change="applyFilters"
                                    class="w-full px-4 py-3 border-0 ring-1 ring-gray-300 dark:ring-neutral-600 rounded-xl focus:ring-2 focus:ring-cyan-500 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button 
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    class="w-full px-5 py-3 border border-gray-300 dark:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors flex items-center justify-center gap-2"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Requirements Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Country</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Visa Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Documents</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Professions</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Fees</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="requirement in visaRequirements.data" :key="requirement.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-xl flex items-center justify-center">
                                                <GlobeAltIcon class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ requirement.country }}</div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">({{ requirement.country_code }})</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ requirement.visa_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ requirement.visa_category || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ requirement.documents?.length || 0 }} docs
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                            {{ requirement.profession_requirements?.length || 0 }} professions
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        ৳{{ formatCurrency((requirement.government_fee || 0) + (requirement.service_fee || 0)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button 
                                            @click="toggleStatus(requirement)"
                                            :class="[
                                                'px-3 py-1 text-xs font-semibold rounded-full transition-colors',
                                                requirement.is_active 
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 hover:bg-green-200' 
                                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-200'
                                            ]"
                                        >
                                            {{ requirement.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end gap-1">
                                            <Link 
                                                :href="route('admin.visa-requirements.show', requirement.id)"
                                                class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-2xl transition-colors"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <Link 
                                                :href="route('admin.visa-requirements.edit', requirement.id)"
                                                class="p-2 text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300 hover:bg-cyan-50 dark:hover:bg-cyan-900/20 rounded-2xl transition-colors"
                                            >
                                                <PencilIcon class="h-5 w-5" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="!visaRequirements.data || visaRequirements.data.length === 0">
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 bg-cyan-100 dark:bg-cyan-900/30 rounded-full mb-4">
                                                <DocumentTextIcon class="h-8 w-8 text-cyan-600 dark:text-cyan-400" />
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No visa requirements found</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a new visa requirement.</p>
                                            <Link
                                                :href="route('admin.visa-requirements.create')"
                                                class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl font-medium transition-colors"
                                            >
                                                <PlusIcon class="h-5 w-5" />
                                                Add Requirement
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="visaRequirements.data && visaRequirements.data.length > 0" class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-medium text-gray-900 dark:text-white">{{ visaRequirements.from }}</span>
                                to <span class="font-medium text-gray-900 dark:text-white">{{ visaRequirements.to }}</span>
                                of <span class="font-medium text-gray-900 dark:text-white">{{ visaRequirements.total }}</span> requirements
                            </p>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="visaRequirements.prev_page_url"
                                    :href="visaRequirements.prev_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-4 w-4" />
                                    Previous
                                </Link>
                                <Link
                                    v-if="visaRequirements.next_page_url"
                                    :href="visaRequirements.next_page_url"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Next
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
