<template>
    <AdminLayout>
        <Head title="Service Modules Management" />

        <div class="page-container">
            <!-- Page Header -->
            <PageHeader
                title="Service Modules"
                description="Manage all 39 services across 6 categories with comprehensive analytics"
                :primaryAction="{
                    label: 'Add Service',
                    onClick: () => router.visit(route('admin.service-modules.create')),
                    icon: PlusIcon
                }"
                :secondaryActions="[
                    { label: 'Export Report', onClick: exportReport, icon: DocumentArrowDownIcon },
                    { label: 'Settings', onClick: () => {}, icon: Cog6ToothIcon }
                ]"
            />

            <!-- Statistics Grid -->
            <div class="grid-stats mb-8">
                <div class="stat-card">
                    <div class="stat-card-icon bg-red-100">
                        <ServerIcon class="h-6 w-6 text-red-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Services</p>
                        <p class="stat-card-value">{{ stats.total_services }}</p>
                        <p class="stat-card-change text-gray-600">Across 6 categories</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-green-100">
                        <CheckCircleIcon class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Active Services</p>
                        <p class="stat-card-value">{{ stats.active_services }}</p>
                        <p class="stat-card-change text-green-600">
                            {{ Math.round((stats.active_services / stats.total_services) * 100) }}% active
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-yellow-100">
                        <ClockIcon class="h-6 w-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Coming Soon</p>
                        <p class="stat-card-value">{{ stats.coming_soon }}</p>
                        <p class="stat-card-change text-yellow-600">In development</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon bg-purple-100">
                        <ClipboardDocumentListIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="stat-card-label">Total Applications</p>
                        <p class="stat-card-value">{{ stats.total_applications }}</p>
                        <p class="stat-card-change text-purple-600">All time</p>
                    </div>
                </div>
            </div>

            <!-- View Toggle & Search -->
            <div class="card-base mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1 w-full sm:w-auto">
                        <FormInput
                            v-model="searchQuery"
                            placeholder="Search services..."
                            :icon="MagnifyingGlassIcon"
                            class="flex-1"
                            @input="handleSearch"
                        />
                        <select v-model="filterStatus" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all bg-white">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="coming_soon">Coming Soon</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button
                            @click="viewMode = 'grid'"
                            class="p-2 rounded-2xl border transition-colors"
                            :class="viewMode === 'grid' ? 'bg-red-50 border-red-200 text-red-600' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'"
                        >
                            <Squares2X2Icon class="h-5 w-5" />
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            class="p-2 rounded-2xl border transition-colors"
                            :class="viewMode === 'list' ? 'bg-red-50 border-red-200 text-red-600' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'"
                        >
                            <ListBulletIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Service Categories -->
            <div class="space-y-6">
                <div 
                    v-for="category in filteredCategories" 
                    :key="category.id"
                    class="card-base"
                >
                    <!-- Category Header -->
                    <div 
                        class="p-6 flex items-center justify-between cursor-pointer border-l-4 hover:bg-gray-50 transition-colors"
                        :style="{ borderLeftColor: category.color }"
                        @click="toggleCategory(category.id)"
                    >
                        <div class="flex items-center space-x-4">
                            <div 
                                class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-2xl shadow-md"
                                :style="{ backgroundColor: category.color }"
                            >
                                <i :class="`fa ${category.icon}`"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ category.name }}</h2>
                                <p class="text-sm text-gray-600 mt-1">{{ category.description }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ category.modules_count }}</div>
                                <div class="text-xs text-gray-500">Total</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ category.active_modules_count }}</div>
                                <div class="text-xs text-gray-500">Active</div>
                            </div>
                            <ChevronDownIcon 
                                class="w-6 h-6 text-gray-400 transition-transform duration-200"
                                :class="{ 'rotate-180': expandedCategories.includes(category.id) }"
                            />
                        </div>
                    </div>

                    <!-- Services Grid/List -->
                    <div v-show="expandedCategories.includes(category.id)" class="px-6 pb-6">
                        <!-- Grid View -->
                        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                            <div 
                                v-for="service in getFilteredServices(category.modules)" 
                                :key="service.id"
                                class="card-hover p-5 relative"
                            >
                                <!-- Status Badges -->
                                <div class="absolute top-3 right-3 flex flex-col gap-1">
                                    <StatusBadge
                                        v-if="service.is_featured"
                                        status="featured"
                                        size="sm"
                                        customLabel="⭐ Featured"
                                    />
                                    <StatusBadge
                                        v-if="service.coming_soon"
                                        status="coming_soon"
                                        size="sm"
                                        customLabel="🕐 Coming Soon"
                                    />
                                    <StatusBadge
                                        v-else
                                        :status="service.is_active ? 'active' : 'inactive'"
                                        size="sm"
                                    />
                                </div>

                                <!-- Service Info -->
                                <div class="mt-8">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 pr-20">{{ service.name }}</h3>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ service.short_description }}</p>

                                    <!-- Price & Applications -->
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-xl font-bold" :style="{ color: category.color }">
                                            {{ service.formatted_price }}
                                        </span>
                                        <span class="text-sm text-gray-500 flex items-center gap-1">
                                            <ClipboardDocumentListIcon class="h-4 w-4" />
                                            {{ service.applications_count }}
                                        </span>
                                    </div>

                                    <!-- Completion Progress -->
                                    <div v-if="service.applications_count > 0" class="mb-4">
                                        <div class="flex justify-between text-xs text-gray-600 mb-2">
                                            <span class="font-medium">Completion Rate</span>
                                            <span class="font-semibold">{{ service.completion_rate }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div 
                                                class="h-2 rounded-full transition-all duration-500"
                                                :style="{ 
                                                    width: service.completion_rate + '%',
                                                    backgroundColor: category.color
                                                }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('admin.service-modules.show', service.id)"
                                            class="flex-1 btn-primary text-center"
                                            :style="{ backgroundColor: category.color, borderColor: category.color }"
                                        >
                                            View Details
                                        </Link>
                                        <button
                                            @click.stop="toggleServiceActive(service)"
                                            class="px-4 py-2 text-sm font-medium rounded-2xl border transition-colors"
                                            :class="service.is_active 
                                                ? 'border-red-200 text-red-700 bg-red-50 hover:bg-red-100' 
                                                : 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100'"
                                        >
                                            {{ service.is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </div>

                                    <!-- Technical Info -->
                                    <div v-if="service.controller" class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                                            <CodeBracketIcon class="h-4 w-4" />
                                            <span class="font-medium">Route:</span>
                                            <code class="bg-gray-100 px-2 py-0.5 rounded">{{ service.route_prefix }}</code>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <CubeIcon class="h-4 w-4" />
                                            <span class="font-medium">Controller:</span>
                                            <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ service.controller }}</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div v-else class="mt-4 space-y-3">
                            <div 
                                v-for="service in getFilteredServices(category.modules)" 
                                :key="service.id"
                                class="card-hover p-4 flex items-center justify-between"
                            >
                                <div class="flex items-center gap-4 flex-1">
                                    <div 
                                        class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-lg flex-shrink-0"
                                        :style="{ backgroundColor: category.color }"
                                    >
                                        <i :class="`fa ${category.icon}`"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ service.name }}</h3>
                                            <StatusBadge
                                                v-if="service.is_featured"
                                                status="featured"
                                                size="sm"
                                                customLabel="⭐"
                                            />
                                            <StatusBadge
                                                v-if="service.coming_soon"
                                                status="coming_soon"
                                                size="sm"
                                            />
                                            <StatusBadge
                                                v-else
                                                :status="service.is_active ? 'active' : 'inactive'"
                                                size="sm"
                                            />
                                        </div>
                                        <p class="text-sm text-gray-600 line-clamp-1">{{ service.short_description }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div class="text-center">
                                        <div class="text-lg font-bold" :style="{ color: category.color }">
                                            {{ service.formatted_price }}
                                        </div>
                                        <div class="text-xs text-gray-500">Price</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ service.applications_count }}</div>
                                        <div class="text-xs text-gray-500">Applications</div>
                                    </div>
                                    <div v-if="service.applications_count > 0" class="text-center">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ service.completion_rate }}%</div>
                                        <div class="text-xs text-gray-500">Completion</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('admin.service-modules.show', service.id)"
                                            class="p-2 text-growth-600 hover:bg-blue-50 rounded-2xl transition-colors"
                                            title="View Details"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click.stop="toggleServiceActive(service)"
                                            class="p-2 rounded-2xl transition-colors"
                                            :class="service.is_active 
                                                ? 'text-red-600 hover:bg-red-50' 
                                                : 'text-green-600 hover:bg-green-50'"
                                            :title="service.is_active ? 'Deactivate' : 'Activate'"
                                        >
                                            <PowerIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State for Category -->
                        <EmptyState
                            v-if="getFilteredServices(category.modules).length === 0"
                            icon="MagnifyingGlassIcon"
                            title="No services match your filters"
                            description="Try adjusting your search or filter criteria"
                            size="sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State for All Categories -->
            <EmptyState
                v-if="filteredCategories.length === 0"
                icon="ServerIcon"
                title="No service categories found"
                description="Start by creating your first service category"
                :action="{ label: 'Create Category', onClick: () => {} }"
            />
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/Base/PageHeader.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import EmptyState from '@/Components/Base/EmptyState.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    ServerIcon,
    CheckCircleIcon,
    ClockIcon,
    ClipboardDocumentListIcon,
    ChevronDownIcon,
    Squares2X2Icon,
    ListBulletIcon,
    EyeIcon,
    PowerIcon,
    CodeBracketIcon,
    CubeIcon,
    DocumentArrowDownIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
    stats: Object,
});

// State
const expandedCategories = ref(props.categories.map(c => c.id)); // Expand all by default
const viewMode = ref('grid'); // 'grid' or 'list'
const searchQuery = ref('');
const filterStatus = ref('');

// Toggle category expansion
const toggleCategory = (categoryId) => {
    const index = expandedCategories.value.indexOf(categoryId);
    if (index > -1) {
        expandedCategories.value.splice(index, 1);
    } else {
        expandedCategories.value.push(categoryId);
    }
};

// Filter services by search and status
const getFilteredServices = (services) => {
    return services.filter(service => {
        const matchesSearch = !searchQuery.value || 
            service.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            service.short_description.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesStatus = !filterStatus.value ||
            (filterStatus.value === 'active' && service.is_active && !service.coming_soon) ||
            (filterStatus.value === 'inactive' && !service.is_active && !service.coming_soon) ||
            (filterStatus.value === 'coming_soon' && service.coming_soon);
        
        return matchesSearch && matchesStatus;
    });
};

// Filter categories (hide empty after filtering)
const filteredCategories = computed(() => {
    return props.categories.filter(category => {
        return getFilteredServices(category.modules).length > 0;
    });
});

// Handle search with debounce
let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // Search is handled client-side for now
    }, 300);
};

// Toggle service active status
const toggleServiceActive = (service) => {
    if (confirm(`Are you sure you want to ${service.is_active ? 'deactivate' : 'activate'} ${service.name}?`)) {
        router.post(route('admin.service-modules.toggle-active', service.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success feedback handled by flash message
            }
        });
    }
};

// Export report
const exportReport = () => {
    router.get(route('admin.service-modules.export'), {}, {
        preserveScroll: true,
    });
};
</script>
