<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { MagnifyingGlassIcon, FunnelIcon, AcademicCapIcon, GlobeAltIcon, CurrencyDollarIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    universities: Object,
    countries: Array,
    filters: Object,
    stats: Object,
})

// Filter state
const filters = ref({
    search: props.filters.search || '',
    country_id: props.filters.country_id || '',
    ranking_min: props.filters.ranking_min || '',
    ranking_max: props.filters.ranking_max || '',
    tuition_max: props.filters.tuition_max || '',
    type: props.filters.type || '',
    scholarships: props.filters.scholarships || false,
})

// Comparison state
const comparisonList = ref([])
const showComparisonModal = ref(false)
const comparisonData = ref(null)

// Apply filters
const applyFilters = () => {
    router.get(route('education.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Reset filters
const resetFilters = () => {
    filters.value = {
        search: '',
        country_id: '',
        ranking_min: '',
        ranking_max: '',
        tuition_max: '',
        type: '',
        scholarships: false,
    }
    applyFilters()
}

// Toggle university in comparison list
const toggleComparison = (university) => {
    const index = comparisonList.value.findIndex(u => u.id === university.id)
    if (index > -1) {
        comparisonList.value.splice(index, 1)
    } else {
        if (comparisonList.value.length < 3) {
            comparisonList.value.push(university)
        } else {
            alert('You can compare up to 3 universities at a time')
        }
    }
}

// Check if university is in comparison list
const isInComparison = (universityId) => {
    return comparisonList.value.some(u => u.id === universityId)
}

// Compare universities
const compareUniversities = async () => {
    if (comparisonList.value.length < 2) {
        alert('Please select at least 2 universities to compare')
        return
    }

    try {
        const response = await axios.post(route('education.compare'), {
            ids: comparisonList.value.map(u => u.id)
        })
        comparisonData.value = response.data.universities
        showComparisonModal.value = true
    } catch (error) {
        console.error('Comparison failed:', error)
        alert('Failed to compare universities')
    }
}

// Format currency
const formatCurrency = (value) => {
    if (!value) return 'N/A'
    return '$' + new Intl.NumberFormat('en-US').format(value)
}

// Get ranking badge color
const getRankingColor = (ranking) => {
    if (!ranking) return 'bg-gray-100 text-gray-700'
    if (ranking <= 10) return 'bg-amber-100 text-amber-800'
    if (ranking <= 50) return 'bg-blue-100 text-blue-800'
    if (ranking <= 100) return 'bg-green-100 text-green-800'
    return 'bg-gray-100 text-gray-700'
}
</script>

<template>
    <Head title="University Search - Find Your Dream University" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">University Search</h1>
                    <p class="mt-2 text-gray-600">Explore {{ stats.total }} universities across {{ stats.countries }} countries</p>
                    
                    <!-- Stats -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg shadow px-5 py-4">
                            <div class="flex items-center">
                                <AcademicCapIcon class="h-8 w-8 text-brand-red-600" />
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Total Universities</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow px-5 py-4">
                            <div class="flex items-center">
                                <GlobeAltIcon class="h-8 w-8 text-brand-red-600" />
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Countries</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.countries }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow px-5 py-4">
                            <div class="flex items-center">
                                <CurrencyDollarIcon class="h-8 w-8 text-green-600" />
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">With Scholarships</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.with_scholarships }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Filters Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <FunnelIcon class="h-5 w-5 mr-2" />
                                    Filters
                                </h3>
                                <button
                                    @click="resetFilters"
                                    class="text-sm text-brand-red-600 hover:text-ocean-700"
                                >
                                    Reset
                                </button>
                            </div>

                            <!-- Search -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                                <div class="relative">
                                    <input
                                        v-model="filters.search"
                                        @keyup.enter="applyFilters"
                                        type="text"
                                        placeholder="University name, city..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-brand-red-600 focus:border-ocean-500"
                                    />
                                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                </div>
                            </div>

                            <!-- Country Filter -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                <select
                                    v-model="filters.country_id"
                                    @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-red-600 focus:border-ocean-500"
                                >
                                    <option value="">All Countries</option>
                                    <option v-for="country in countries" :key="country.id" :value="country.id">
                                        {{ country.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Ranking Range -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">World Ranking</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input
                                        v-model="filters.ranking_min"
                                        @blur="applyFilters"
                                        type="number"
                                        placeholder="Min"
                                        min="1"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-red-600 focus:border-ocean-500"
                                    />
                                    <input
                                        v-model="filters.ranking_max"
                                        @blur="applyFilters"
                                        type="number"
                                        placeholder="Max"
                                        min="1"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-red-600 focus:border-ocean-500"
                                    />
                                </div>
                            </div>

                            <!-- Tuition Range -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Tuition (USD/year)</label>
                                <input
                                    v-model="filters.tuition_max"
                                    @blur="applyFilters"
                                    type="number"
                                    placeholder="e.g., 50000"
                                    min="0"
                                    step="1000"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-red-600 focus:border-ocean-500"
                                />
                            </div>

                            <!-- University Type -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <select
                                    v-model="filters.type"
                                    @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-red-600 focus:border-ocean-500"
                                >
                                    <option value="">All Types</option>
                                    <option value="public">Public</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>

                            <!-- Scholarships Available -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="filters.scholarships"
                                        @change="applyFilters"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-brand-red-600 focus:ring-brand-red-600"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Scholarships Available</span>
                                </label>
                            </div>

                            <button
                                @click="applyFilters"
                                class="w-full bg-brand-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>

                    <!-- Universities Grid -->
                    <div class="lg:col-span-3">
                        <!-- Results Header -->
                        <div class="flex items-center justify-between mb-6">
                            <p class="text-gray-600">
                                Showing {{ universities.data.length }} of {{ universities.total }} universities
                            </p>
                            
                            <!-- Comparison Bar -->
                            <div v-if="comparisonList.length > 0" class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ comparisonList.length }} selected</span>
                                <button
                                    @click="compareUniversities"
                                    :disabled="comparisonList.length < 2"
                                    class="bg-brand-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Compare
                                </button>
                                <button
                                    @click="comparisonList = []"
                                    class="text-sm text-gray-600 hover:text-gray-800"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="universities.data.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
                            <AcademicCapIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No universities found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
                            <button
                                @click="resetFilters"
                                class="mt-4 text-brand-red-600 hover:text-ocean-700 text-sm font-medium"
                            >
                                Reset all filters
                            </button>
                        </div>

                        <!-- Universities Grid -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div
                                v-for="university in universities.data"
                                :key="university.id"
                                class="bg-white rounded-lg shadow hover:shadow-lg transition relative"
                            >
                                <!-- Comparison Checkbox -->
                                <div class="absolute top-4 right-4 z-10">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="isInComparison(university.id)"
                                            @change="toggleComparison(university)"
                                            class="rounded border-gray-300 text-brand-red-600 focus:ring-brand-red-600"
                                        />
                                        <span class="ml-2 text-xs text-gray-600">Compare</span>
                                    </label>
                                </div>

                                <Link :href="route('education.show', university.id)" class="block p-6">
                                    <!-- Header -->
                                    <div class="flex items-start gap-4 mb-4">
                                        <div v-if="university.logo" class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <img :src="university.logo" :alt="university.name" class="w-12 h-12 object-contain" />
                                        </div>
                                        <div v-else class="flex-shrink-0 w-16 h-16 bg-ocean-50 rounded-lg flex items-center justify-center">
                                            <AcademicCapIcon class="h-8 w-8 text-brand-red-600" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ university.name }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ university.city }}, {{ university.country.name }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Rankings & Stats -->
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        <div v-if="university.world_ranking">
                                            <p class="text-xs text-gray-500">World Ranking</p>
                                            <p :class="['text-lg font-bold inline-block px-3 py-1 rounded-full', getRankingColor(university.world_ranking)]">
                                                #{{ university.world_ranking }}
                                            </p>
                                        </div>
                                        <div v-if="university.acceptance_rate">
                                            <p class="text-xs text-gray-500">Acceptance Rate</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ university.acceptance_rate }}%</p>
                                        </div>
                                    </div>

                                    <!-- Tuition -->
                                    <div v-if="university.tuition_min || university.tuition_max" class="mb-4">
                                        <p class="text-xs text-gray-500">Annual Tuition</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            <template v-if="university.tuition_min && university.tuition_max">
                                                {{ formatCurrency(university.tuition_min) }} - {{ formatCurrency(university.tuition_max) }}
                                            </template>
                                            <template v-else>
                                                {{ formatCurrency(university.tuition_max || university.tuition_min) }}
                                            </template>
                                        </p>
                                    </div>

                                    <!-- Badges -->
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ university.type === 'public' ? 'Public' : 'Private' }}
                                        </span>
                                        <span v-if="university.scholarships_available" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <CheckBadgeIcon class="h-3 w-3 mr-1" />
                                            Scholarships
                                        </span>
                                        <span v-if="university.is_featured" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            ⭐ Featured
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="universities.links && universities.links.length > 3" class="mt-6">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <Link
                                    v-for="link in universities.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition',
                                        link.active
                                            ? 'bg-brand-red-600 text-white'
                                            : link.url
                                            ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Modal -->
        <Teleport to="body">
            <div v-if="showComparisonModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showComparisonModal = false">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50" @click="showComparisonModal = false"></div>

                    <div class="relative inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-xl">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h3 class="text-xl font-semibold text-gray-900">University Comparison</h3>
                            <button @click="showComparisonModal = false" class="text-gray-400 hover:text-gray-600">
                                <span class="text-2xl">&times;</span>
                            </button>
                        </div>

                        <!-- Comparison Table -->
                        <div v-if="comparisonData" class="p-6 overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Criteria</th>
                                        <th
                                            v-for="uni in comparisonData"
                                            :key="uni.id"
                                            class="py-3 px-4 text-left text-sm font-semibold text-gray-700"
                                        >
                                            {{ uni.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Country</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.country.name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">World Ranking</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.world_ranking ? `#${uni.world_ranking}` : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Acceptance Rate</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.acceptance_rate ? `${uni.acceptance_rate}%` : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Annual Tuition</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            <template v-if="uni.tuition_min && uni.tuition_max">
                                                {{ formatCurrency(uni.tuition_min) }} - {{ formatCurrency(uni.tuition_max) }}
                                            </template>
                                            <template v-else>
                                                {{ formatCurrency(uni.tuition_max || uni.tuition_min) }}
                                            </template>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Type</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.type === 'public' ? 'Public' : 'Private' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Student Count</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.student_count ? uni.student_count.toLocaleString() : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">International Students</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.international_students ? uni.international_students.toLocaleString() : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Scholarships Available</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ uni.scholarships_available ? '✅ Yes' : '❌ No' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">Application Fee</td>
                                        <td v-for="uni in comparisonData" :key="uni.id" class="py-3 px-4 text-sm text-gray-700">
                                            {{ formatCurrency(uni.application_fee) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                            <button
                                @click="showComparisonModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
