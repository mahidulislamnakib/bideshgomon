<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BuildingOffice2Icon, MapPinIcon, PhoneIcon,
    FunnelIcon, ArrowPathIcon, EyeIcon
} from '@heroicons/vue/24/outline';
import { 
    CheckBadgeIcon as CheckBadgeSolidIcon, 
    StarIcon as StarSolidIcon 
} from '@heroicons/vue/24/solid';

const props = defineProps({
    directories: Object,
    categories: Array,
    countries: Array,
    featuredDirectories: Array,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    country: props.filters?.country || '',
    verified: props.filters?.verified || false
});

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterDirectories();
    }, 300);
};

const filterDirectories = () => {
    router.get(route('directories.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.category = '';
    filters.country = '';
    filters.verified = false;
    filterDirectories();
};

const getCategoryColor = (color) => {
    const colors = {
        blue: '#3B82F6', indigo: '#6366F1', green: '#10B981',
        red: '#EF4444', purple: '#8B5CF6', yellow: '#F59E0B', gray: '#6B7286',
    };
    return colors[color] || '#6B7286';
};
</script>

<template>
    <Head title="Directories" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero with Filters -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Directory</h1>
                    <p class="text-sm text-white/80 mt-0.5">Embassies, airlines, training centers & more</p>
                    
                    <!-- Filters in Hero -->
                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <input v-model="filters.search" @input="debouncedSearch" type="text" 
                            placeholder="Search directories..."
                            class="w-48 md:w-64 px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50 placeholder-gray-500" />
                        <select v-model="filters.country" @change="filterDirectories()"
                            class="px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50">
                            <option value="">All Countries</option>
                            <option v-for="country in countries" :key="country.id" :value="country.id">
                                {{ country.name }}
                            </option>
                        </select>
                        <label class="flex items-center gap-2 text-sm text-white/90 whitespace-nowrap cursor-pointer">
                            <input v-model="filters.verified" @change="filterDirectories()" type="checkbox" 
                                class="w-4 h-4 rounded border-white/30 bg-white/20 text-white focus:ring-white/50" />
                            Verified Only
                        </label>
                    </div>
                </div>
            </div>

            <!-- Category Pills Row -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 bg-gray-50 dark:bg-neutral-900">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Category Pills -->
                    <div class="flex-1 overflow-x-auto no-scrollbar">
                        <div class="flex gap-2 min-w-max">
                            <button @click="filters.category = ''; filterDirectories()"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap',
                                    !filters.category 
                                        ? 'bg-growth-600 text-white' 
                                        : 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-neutral-700 hover:bg-gray-50'
                                ]">
                                All
                            </button>
                            <button v-for="cat in categories" :key="cat.id"
                                @click="filters.category = cat.slug; filterDirectories()"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap',
                                    filters.category === cat.slug 
                                        ? 'text-white' 
                                        : 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-neutral-700 hover:bg-gray-50'
                                ]"
                                :style="filters.category === cat.slug ? { backgroundColor: getCategoryColor(cat.color) } : {}">
                                {{ cat.name }} ({{ cat.directories_count || 0 }})
                            </button>
                        </div>
                    </div>
                    
                    <!-- Reset Button -->
                    <button v-if="filters.search || filters.category || filters.country || filters.verified" 
                        @click="resetFilters" 
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg transition-colors">
                        <ArrowPathIcon class="h-3.5 w-3.5" />
                        Reset
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    {{ directories.total }} results
                </p>

                <!-- Empty State -->
                <div v-if="directories.data.length === 0" class="bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-8 text-center">
                    <BuildingOffice2Icon class="mx-auto h-10 w-10 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500">No directories found</p>
                    <button @click="resetFilters" class="mt-3 text-xs text-growth-600 hover:text-growth-700 font-medium">
                        Reset filters
                    </button>
                </div>

                <!-- Directory Grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    <Link v-for="directory in directories.data" :key="directory.id"
                        :href="route('directories.show', directory.slug)"
                        class="group bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 p-3 hover:shadow-md hover:border-growth-300 dark:hover:border-growth-600 transition-all">
                        
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 flex items-center justify-center bg-gray-100 dark:bg-neutral-700 rounded-lg overflow-hidden flex-shrink-0">
                                <img v-if="directory.logo_url" :src="directory.logo_url" :alt="directory.name" class="h-full w-full object-cover"/>
                                <BuildingOffice2Icon v-else class="h-5 w-5 text-gray-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1">
                                    <h3 class="font-medium text-sm text-gray-900 dark:text-white group-hover:text-growth-600 truncate">
                                        {{ directory.name }}
                                    </h3>
                                    <CheckBadgeSolidIcon v-if="directory.is_verified" class="h-3.5 w-3.5 text-blue-500 flex-shrink-0" />
                                    <StarSolidIcon v-if="directory.is_featured" class="h-3.5 w-3.5 text-yellow-500 flex-shrink-0" />
                                </div>
                                <span v-if="directory.category" 
                                    class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium mt-0.5"
                                    :style="{ 
                                        backgroundColor: getCategoryColor(directory.category.color) + '15',
                                        color: getCategoryColor(directory.category.color)
                                    }">
                                    {{ directory.category.name }}
                                </span>
                            </div>
                        </div>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                            {{ directory.description }}
                        </p>

                        <div class="mt-2 flex items-center justify-between text-[10px] text-gray-400">
                            <div class="flex items-center gap-1">
                                <MapPinIcon v-if="directory.city" class="h-3 w-3" />
                                <span class="truncate max-w-[80px]">{{ directory.city }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <EyeIcon class="h-3 w-3" />
                                {{ directory.views_count || 0 }}
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="directories.last_page > 1" class="mt-5 flex items-center justify-center gap-2">
                    <Link v-if="directories.prev_page_url" :href="directories.prev_page_url" 
                        class="px-3 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded hover:bg-gray-50">
                        ←
                    </Link>
                    <span class="text-xs text-gray-500">{{ directories.current_page }} / {{ directories.last_page }}</span>
                    <Link v-if="directories.next_page_url" :href="directories.next_page_url" 
                        class="px-3 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded hover:bg-gray-50">
                        →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
