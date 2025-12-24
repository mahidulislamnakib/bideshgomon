<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageSkeleton from '@/Components/ui/PageSkeleton.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import { 
    PlusIcon, MagnifyingGlassIcon, FunnelIcon, ArrowPathIcon,
    PencilSquareIcon, TrashIcon, GlobeAltIcon, BuildingOffice2Icon,
    CheckCircleIcon, XCircleIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    partners: Object,
    filters: Object
});

const form = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || 'all'
});

let timeout = null;
const search = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.partners.index'), form, {
            preserveState: true,
            replace: true
        });
    }, 300);
};

const resetFilters = () => {
    form.search = '';
    form.status = 'all';
    search();
};

const toggleActive = (partner) => {
    if (confirm(`Are you sure you want to ${partner.is_active ? 'deactivate' : 'activate'} this partner?`)) {
        router.post(route('admin.partners.toggle-active', partner.id), {}, {
            preserveScroll: true
        });
    }
};

const confirmDelete = (partner) => {
    if (confirm(`Are you sure you want to delete "${partner.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.partners.destroy', partner.id));
    }
};

const stats = [
    { label: 'Total Partners', value: props.partners?.total || 0, icon: BuildingOffice2Icon, color: 'blue' },
    { label: 'Active', value: props.partners?.data?.filter(p => p.is_active).length || 0, icon: CheckCircleIcon, color: 'green' },
    { label: 'Inactive', value: props.partners?.data?.filter(p => !p.is_active).length || 0, icon: XCircleIcon, color: 'gray' },
];

const loading = ref(false);
</script>

<template>
    <Head title="Partners Management" />

    <AdminLayout>
        <!-- Loading Skeleton -->
        <PageSkeleton v-if="loading" />
        
        <!-- Main Content -->
        <div v-else class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-96 h-96 bg-growth-500 rounded-full filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl translate-x-1/2 translate-y-1/2"></div>
                </div>
                
                <div class="relative z-10 px-4 py-8 sm:px-6 lg:px-8">
                    <div class="max-w-7xl mx-auto">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h1 class="text-2xl lg:text-3xl font-bold text-white flex items-center gap-3">
                                    <div class="p-2 bg-white/10 rounded-xl backdrop-blur">
                                        <BuildingOffice2Icon class="h-7 w-7 text-white" />
                                    </div>
                                    Partners Management
                                </h1>
                                <p class="text-gray-400 mt-2">Manage your partner organizations and brands</p>
                            </div>
                            
                            <Link :href="route('admin.partners.create')" 
                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-growth-500 to-teal-600 text-white rounded-xl font-semibold hover:from-growth-600 hover:to-teal-700 transition-all shadow-lg shadow-growth-500/25">
                                <PlusIcon class="h-5 w-5 mr-2" />
                                Add Partner
                            </Link>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                            <div v-for="stat in stats" :key="stat.label" 
                                class="bg-white/10 backdrop-blur-lg rounded-xl p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl" :class="{
                                        'bg-blue-500/20': stat.color === 'blue',
                                        'bg-green-500/20': stat.color === 'green',
                                        'bg-gray-500/20': stat.color === 'gray'
                                    }">
                                        <component :is="stat.icon" class="h-5 w-5" :class="{
                                            'text-blue-400': stat.color === 'blue',
                                            'text-green-400': stat.color === 'green',
                                            'text-gray-400': stat.color === 'gray'
                                        }" />
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-white">{{ stat.value }}</p>
                                        <p class="text-sm text-gray-400">{{ stat.label }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <!-- Filters Card -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <FunnelIcon class="h-5 w-5 text-gray-500" />
                        <h3 class="font-semibold text-gray-900 dark:text-white">Filters</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <input 
                                v-model="form.search" 
                                @input="search"
                                type="text" 
                                placeholder="Search partners..." 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <select v-model="form.status" @change="search" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <button @click="resetFilters"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                                <ArrowPathIcon class="h-4 w-4 mr-2" />
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Partners Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="partners.data.length === 0"
                        icon="BuildingOfficeIcon"
                        title="No partners found"
                        description="Add trusted partners to showcase on your platform and build credibility."
                        :action="{
                            label: 'Add Partner',
                            onClick: () => router.visit(route('admin.partners.create')),
                        }"
                    />

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Partner</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="partner in partners.data" :key="partner.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-20 flex items-center justify-center bg-gray-100 dark:bg-neutral-700 rounded-xl overflow-hidden">
                                                <img v-if="partner.logo_url" :src="partner.logo_url" :alt="partner.name" class="max-h-10 w-auto object-contain"/>
                                                <BuildingOffice2Icon v-else class="h-6 w-6 text-gray-400" />
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ partner.name }}</p>
                                                <p v-if="partner.name_bn" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ partner.name_bn }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a v-if="partner.website_url" :href="partner.website_url" target="_blank" 
                                            class="inline-flex items-center gap-1.5 text-sm text-growth-600 hover:text-growth-700 dark:text-growth-400 dark:hover:text-growth-300 font-medium">
                                            <GlobeAltIcon class="h-4 w-4" />
                                            Visit Site
                                        </a>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                            {{ partner.partner_type || 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-100 dark:bg-neutral-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ partner.sort_order }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="toggleActive(partner)" class="inline-flex items-center gap-1.5 transition-colors">
                                            <span v-if="partner.is_active" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50">
                                                <CheckCircleSolidIcon class="h-3.5 w-3.5" />
                                                Active
                                            </span>
                                            <span v-else class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-neutral-600">
                                                <XCircleIcon class="h-3.5 w-3.5" />
                                                Inactive
                                            </span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.partners.edit', partner.id)" 
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </Link>
                                            <button @click="confirmDelete(partner)" 
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="partners.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-semibold text-gray-900 dark:text-white">{{ partners.from }}</span> 
                                to <span class="font-semibold text-gray-900 dark:text-white">{{ partners.to }}</span> 
                                of <span class="font-semibold text-gray-900 dark:text-white">{{ partners.total }}</span> partners
                            </p>
                            <div class="flex items-center gap-2">
                                <Link v-if="partners.prev_page_url" :href="partners.prev_page_url" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                                    Previous
                                </Link>
                                <span v-else class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl cursor-not-allowed">
                                    Previous
                                </span>
                                <Link v-if="partners.next_page_url" :href="partners.next_page_url" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                                    Next
                                </Link>
                                <span v-else class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl cursor-not-allowed">
                                    Next
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
