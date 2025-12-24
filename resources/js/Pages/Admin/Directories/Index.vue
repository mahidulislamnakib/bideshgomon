<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import { 
    PlusIcon, FunnelIcon, ArrowPathIcon,
    PencilSquareIcon, TrashIcon, BuildingOffice2Icon,
    CheckCircleIcon, XCircleIcon, StarIcon, EyeIcon,
    PhoneIcon, EnvelopeIcon
} from '@heroicons/vue/24/outline';
import { 
    CheckBadgeIcon as CheckBadgeSolidIcon, 
    StarIcon as StarSolidIcon 
} from '@heroicons/vue/24/solid';

const props = defineProps({
    directories: Object,
    categories: Array,
    countries: Array,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    country: props.filters?.country || '',
    status: props.filters?.status || '',
    verified: props.filters?.verified || false,
    featured: props.filters?.featured || false
});

const deleteModal = reactive({
    show: false,
    directory: null
});

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterDirectories();
    }, 300);
};

const filterDirectories = () => {
    router.get(route('admin.directories.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.category = '';
    filters.country = '';
    filters.status = '';
    filters.verified = false;
    filters.featured = false;
    filterDirectories();
};

const togglePublished = (directory) => {
    router.post(
        route('admin.directories.toggle-published', directory.id),
        {},
        { preserveState: true, preserveScroll: true }
    );
};

const toggleVerified = (directory) => {
    router.post(
        route('admin.directories.toggle-verified', directory.id),
        {},
        { preserveState: true, preserveScroll: true }
    );
};

const toggleFeatured = (directory) => {
    router.post(
        route('admin.directories.toggle-featured', directory.id),
        {},
        { preserveState: true, preserveScroll: true }
    );
};

const confirmDelete = (directory) => {
    deleteModal.directory = directory;
    deleteModal.show = true;
};

const closeDeleteModal = () => {
    deleteModal.show = false;
    deleteModal.directory = null;
};

const deleteDirectory = () => {
    router.delete(route('admin.directories.destroy', deleteModal.directory.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => closeDeleteModal()
    });
};

const stats = computed(() => [
    { label: 'Total Directories', value: props.directories?.total || 0, icon: BuildingOffice2Icon, color: 'blue' },
    { label: 'Published', value: props.directories?.data?.filter(d => d.is_published).length || 0, icon: CheckCircleIcon, color: 'green' },
    { label: 'Featured', value: props.directories?.data?.filter(d => d.is_featured).length || 0, icon: StarIcon, color: 'yellow' },
    { label: 'Verified', value: props.directories?.data?.filter(d => d.is_verified).length || 0, icon: CheckBadgeSolidIcon, color: 'purple' },
]);
</script>

<template>
    <Head title="Directories Management" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
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
                                    Directories Management
                                </h1>
                                <p class="text-gray-400 mt-2">Manage embassies, airlines, training centers, and other directories</p>
                            </div>
                            
                            <Link :href="route('admin.directories.create')" 
                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-growth-500 to-teal-600 text-white rounded-xl font-semibold hover:from-growth-600 hover:to-teal-700 transition-all shadow-lg shadow-growth-500/25">
                                <PlusIcon class="h-5 w-5 mr-2" />
                                Add Directory
                            </Link>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                            <div v-for="stat in stats" :key="stat.label" 
                                class="bg-white/10 backdrop-blur-lg rounded-xl p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-2xl" :class="{
                                        'bg-blue-500/20': stat.color === 'blue',
                                        'bg-green-500/20': stat.color === 'green',
                                        'bg-yellow-500/20': stat.color === 'yellow',
                                        'bg-purple-500/20': stat.color === 'purple'
                                    }">
                                        <component :is="stat.icon" class="h-5 w-5" :class="{
                                            'text-blue-400': stat.color === 'blue',
                                            'text-green-400': stat.color === 'green',
                                            'text-yellow-400': stat.color === 'yellow',
                                            'text-purple-400': stat.color === 'purple'
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
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-2">
                            <input 
                                v-model="filters.search" 
                                @input="debouncedSearch"
                                type="text" 
                                placeholder="Search by name, email, phone..." 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <select v-model="filters.category" @change="filterDirectories" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="filters.country" @change="filterDirectories" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                <option value="">All Countries</option>
                                <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="filters.status" @change="filterDirectories" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Additional Filters -->
                    <div class="mt-4 flex flex-wrap items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="filters.verified" @change="filterDirectories" type="checkbox" 
                                class="w-4 h-4 rounded border-gray-300 text-growth-600 focus:ring-growth-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Verified Only</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="filters.featured" @change="filterDirectories" type="checkbox" 
                                class="w-4 h-4 rounded border-gray-300 text-growth-600 focus:ring-growth-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Featured Only</span>
                        </label>
                        <button @click="resetFilters"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors text-sm">
                            <ArrowPathIcon class="h-4 w-4 mr-2" />
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Directories Table -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <!-- Empty State -->
                    <EmptyState
                        v-if="directories.data.length === 0"
                        icon="FolderIcon"
                        title="No directories found"
                        description="Create directories to organize and categorize your content."
                        :action="{
                            label: 'Create Directory',
                            onClick: () => router.visit(route('admin.directories.create')),
                        }"
                    />

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Directory</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Views</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="directory in directories.data" :key="directory.id" class="hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <!-- Directory Info -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 flex items-center justify-center bg-gray-100 dark:bg-neutral-700 rounded-xl overflow-hidden flex-shrink-0">
                                                <img v-if="directory.logo_url" :src="directory.logo_url" :alt="directory.name" class="h-full w-full object-cover"/>
                                                <BuildingOffice2Icon v-else class="h-6 w-6 text-gray-400" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ directory.name }}</p>
                                                    <CheckBadgeSolidIcon v-if="directory.is_verified" class="h-4 w-4 text-blue-500 flex-shrink-0" title="Verified" />
                                                    <StarSolidIcon v-if="directory.is_featured" class="h-4 w-4 text-yellow-500 flex-shrink-0" title="Featured" />
                                                </div>
                                                <p v-if="directory.name_bn" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ directory.name_bn }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Category -->
                                    <td class="px-6 py-4">
                                        <span v-if="directory.category" 
                                            class="inline-flex items-center px-2.5 py-1 rounded-2xl text-xs font-medium"
                                            :style="{ 
                                                backgroundColor: (directory.category.color || '#6b7280') + '20',
                                                color: directory.category.color || '#6b7280'
                                            }">
                                            {{ directory.category.name }}
                                        </span>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                    
                                    <!-- Contact -->
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div v-if="directory.email" class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                                <EnvelopeIcon class="h-3.5 w-3.5" />
                                                <span class="truncate max-w-32">{{ directory.email }}</span>
                                            </div>
                                            <div v-if="directory.phone" class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                                <PhoneIcon class="h-3.5 w-3.5" />
                                                <span>{{ directory.phone }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Views -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                            <EyeIcon class="h-4 w-4" />
                                            <span>{{ directory.views_count || 0 }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="px-6 py-4 text-center">
                                        <button @click="togglePublished(directory)" class="transition-colors">
                                            <span v-if="directory.is_published" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50">
                                                <CheckCircleIcon class="h-3.5 w-3.5" />
                                                Published
                                            </span>
                                            <span v-else 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-neutral-600">
                                                <XCircleIcon class="h-3.5 w-3.5" />
                                                Draft
                                            </span>
                                        </button>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click="toggleVerified(directory)" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-2xl transition-colors"
                                                :class="directory.is_verified 
                                                    ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200' 
                                                    : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 hover:bg-gray-200'"
                                                :title="directory.is_verified ? 'Verified' : 'Not Verified'">
                                                <CheckBadgeSolidIcon class="h-4 w-4" />
                                            </button>
                                            <button @click="toggleFeatured(directory)" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-2xl transition-colors"
                                                :class="directory.is_featured 
                                                    ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-200' 
                                                    : 'bg-gray-100 dark:bg-neutral-700 text-gray-400 hover:bg-gray-200'"
                                                :title="directory.is_featured ? 'Featured' : 'Not Featured'">
                                                <StarSolidIcon class="h-4 w-4" />
                                            </button>
                                            <Link :href="route('admin.directories.edit', directory.id)" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-growth-50 dark:bg-growth-900/20 text-growth-600 dark:text-growth-400 hover:bg-growth-100 dark:hover:bg-growth-900/40 transition-colors">
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </Link>
                                            <button @click="confirmDelete(directory)" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="directories.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing <span class="font-semibold text-gray-900 dark:text-white">{{ directories.from }}</span> 
                                to <span class="font-semibold text-gray-900 dark:text-white">{{ directories.to }}</span> 
                                of <span class="font-semibold text-gray-900 dark:text-white">{{ directories.total }}</span> directories
                            </p>
                            <div class="flex items-center gap-2">
                                <Link v-if="directories.prev_page_url" :href="directories.prev_page_url" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-2xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                                    Previous
                                </Link>
                                <span v-else class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl cursor-not-allowed">
                                    Previous
                                </span>
                                <Link v-if="directories.next_page_url" :href="directories.next_page_url" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-2xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                                    Next
                                </Link>
                                <span v-else class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl cursor-not-allowed">
                                    Next
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="deleteModal.show" @close="closeDeleteModal">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <TrashIcon class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Directory</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Are you sure you want to delete "<strong>{{ deleteModal.directory?.name }}</strong>"?
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="closeDeleteModal" type="button" 
                        class="px-4 py-2 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors">
                        Cancel
                    </button>
                    <button @click="deleteDirectory" type="button" 
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
