<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    UserCircleIcon,
    CalendarIcon,
    ClockIcon,
    EyeIcon,
    DocumentTextIcon,
    ArrowRightIcon,
    TagIcon,
    SparklesIcon,
    ChevronRightIcon,
    FunnelIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { FireIcon, StarIcon } from '@heroicons/vue/24/solid';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';

const props = defineProps({
    posts: Object,
    categories: Array,
    popularTags: Array,
    featuredPosts: Array,
    filters: Object,
});

const { formatDate } = useBangladeshFormat();
const searchQuery = ref(props.filters.search || '');
const showMobileFilters = ref(false);
let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('blog.index'), { search: searchQuery.value }, {
            preserveState: true,
            replace: true,
        });
    }, 500);
};

const filterByTag = (tagSlug) => {
    router.get(route('blog.index', { tag: tagSlug }));
};

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num;
};

const clearFilters = () => {
    searchQuery.value = '';
    router.get(route('blog.index'));
};

const hasActiveFilters = computed(() => {
    return props.filters.category || props.filters.tag || props.filters.search;
});

const getCategoryColor = (category) => {
    if (!category) return { bg: 'bg-gray-100', text: 'text-gray-700' };
    const colors = {
        '#3B82F6': { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-700 dark:text-blue-400' },
        '#10B981': { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400' },
        '#F59E0B': { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-700 dark:text-amber-400' },
        '#EF4444': { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-400' },
        '#8B5CF6': { bg: 'bg-violet-100 dark:bg-violet-900/30', text: 'text-violet-700 dark:text-violet-400' },
        '#EC4899': { bg: 'bg-pink-100 dark:bg-pink-900/30', text: 'text-pink-700 dark:text-pink-400' },
    };
    return colors[category.color] || { bg: 'bg-gray-100 dark:bg-gray-800', text: 'text-gray-700 dark:text-gray-300' };
};

// Placeholder images based on category
const getPlaceholderImage = (category) => {
    const images = {
        'visa-guides': 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=800&q=80',
        'study-abroad': 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80',
        'travel-tips': 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&q=80',
        'work-abroad': 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80',
        'immigration-news': 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800&q=80',
        'success-stories': 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80',
    };
    return images[category?.slug] || 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80';
};
</script>

<template>
    <Head title="Blog - Travel Guides & Tips" />

    <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
        <!-- Compact Hero with Search -->
        <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-xl md:text-2xl font-bold text-white">Blog</h1>
                <p class="text-sm text-white/80 mt-0.5">Visa guides, travel tips, study abroad advice & immigration news</p>
                
                <!-- Search in Hero -->
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search articles..."
                        class="w-48 md:w-64 px-4 py-2 text-sm border-0 bg-white/95 dark:bg-neutral-800 rounded-lg focus:ring-2 focus:ring-white/50 placeholder-gray-500"
                        @input="handleSearch"
                    />
                    <button 
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors"
                    >
                        <XMarkIcon class="h-4 w-4" />
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Pills Row -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 bg-gray-50 dark:bg-neutral-900">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <Link
                    :href="route('blog.index')"
                    class="flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap"
                    :class="!filters.category ? 
                        'bg-growth-600 text-white' : 
                        'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 border border-gray-200 dark:border-neutral-700'"
                >
                    All
                </Link>
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('blog.index', { category: category.slug })"
                    class="flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap"
                    :class="filters.category === category.slug ? 
                        'bg-growth-600 text-white' : 
                        'bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 border border-gray-200 dark:border-neutral-700'"
                >
                    {{ category.name }} ({{ category.published_posts_count }})
                </Link>
            </div>
        </div>

        <!-- Results Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                {{ posts.total }} articles found
                <span v-if="filters.category" class="ml-1">in "{{ filters.category }}"</span>
                <span v-if="filters.tag" class="ml-1">tagged "{{ filters.tag }}"</span>
                <span v-if="filters.search" class="ml-1">matching "{{ filters.search }}"</span>
            </p>

            <!-- Featured Posts (only show when no filters) -->
            <div v-if="featuredPosts.length > 0 && !hasActiveFilters" class="mb-12">
                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center gap-2 px-4 py-2 bg-amber-100 dark:bg-amber-900/30 rounded-full">
                        <FireIcon class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        <span class="font-semibold text-amber-800 dark:text-amber-300">Featured</span>
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-r from-amber-200 to-transparent dark:from-amber-800"></div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Main Featured Post -->
                    <article
                        v-if="featuredPosts[0]"
                        class="group relative bg-white dark:bg-neutral-800 rounded-3xl shadow-xl overflow-hidden cursor-pointer hover:shadow-2xl transition-all duration-300"
                        @click="$inertia.visit(route('blog.show', featuredPosts[0].slug))"
                    >
                        <div class="aspect-[16/10] relative overflow-hidden">
                            <img 
                                :src="featuredPosts[0].featured_image || getPlaceholderImage(featuredPosts[0].category)"
                                :alt="featuredPosts[0].title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute top-4 left-4">
                                <span :class="[getCategoryColor(featuredPosts[0].category).bg, getCategoryColor(featuredPosts[0].category).text]" 
                                    class="px-3 py-1.5 rounded-full text-sm font-medium backdrop-blur-sm">
                                    {{ featuredPosts[0].category?.name }}
                                </span>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h2 class="text-2xl md:text-3xl font-bold text-white mb-3 line-clamp-2 group-hover:underline decoration-2 underline-offset-4">
                                    {{ featuredPosts[0].title }}
                                </h2>
                                <p class="text-gray-200 mb-4 line-clamp-2">{{ featuredPosts[0].excerpt }}</p>
                                <div class="flex items-center gap-4 text-sm text-gray-300">
                                    <span class="flex items-center gap-1">
                                        <CalendarIcon class="h-4 w-4" />
                                        {{ formatDate(featuredPosts[0].published_at) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <ClockIcon class="h-4 w-4" />
                                        {{ featuredPosts[0].reading_time }} min read
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- Secondary Featured Posts -->
                    <div class="grid grid-cols-1 gap-6">
                        <article
                            v-for="post in featuredPosts.slice(1, 3)"
                            :key="post.id"
                            class="group flex gap-4 bg-white dark:bg-neutral-800 rounded-2xl p-4 shadow-lg cursor-pointer hover:shadow-xl transition-all duration-300"
                            @click="$inertia.visit(route('blog.show', post.slug))"
                        >
                            <div class="w-32 h-32 flex-shrink-0 rounded-xl overflow-hidden">
                                <img 
                                    :src="post.featured_image || getPlaceholderImage(post.category)"
                                    :alt="post.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <span :class="[getCategoryColor(post.category).bg, getCategoryColor(post.category).text]" 
                                    class="inline-block px-2 py-1 rounded-full text-xs font-medium mb-2">
                                    {{ post.category?.name }}
                                </span>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ post.title }}
                                </h3>
                                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ formatDate(post.published_at) }}</span>
                                    <span>{{ post.reading_time }} min</span>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Posts Grid -->
                    <div v-if="posts.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <article
                            v-for="post in posts.data"
                            :key="post.id"
                            class="group bg-white dark:bg-neutral-800 rounded-2xl shadow-lg overflow-hidden cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                            @click="$inertia.visit(route('blog.show', post.slug))"
                        >
                            <div class="aspect-[16/9] relative overflow-hidden">
                                <img 
                                    :src="post.featured_image || getPlaceholderImage(post.category)"
                                    :alt="post.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div class="absolute top-3 left-3 flex items-center gap-2">
                                    <span :class="[getCategoryColor(post.category).bg, getCategoryColor(post.category).text]" 
                                        class="px-2.5 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
                                        {{ post.category?.name }}
                                    </span>
                                    <span v-if="post.is_featured" class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 backdrop-blur-sm">
                                        <StarIcon class="h-3 w-3 inline" /> Featured
                                    </span>
                                </div>
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ post.title }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">{{ post.excerpt }}</p>
                                
                                <!-- Meta Info -->
                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-neutral-700 pt-4">
                                    <div class="flex items-center gap-4">
                                        <span class="flex items-center gap-1">
                                            <CalendarIcon class="h-4 w-4" />
                                            {{ formatDate(post.published_at) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <ClockIcon class="h-4 w-4" />
                                            {{ post.reading_time }} min
                                        </span>
                                    </div>
                                    <span class="flex items-center gap-1">
                                        <EyeIcon class="h-4 w-4" />
                                        {{ formatNumber(post.views_count) }}
                                    </span>
                                </div>

                                <!-- Tags -->
                                <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-2 mt-4">
                                    <span
                                        v-for="tag in post.tags.slice(0, 3)"
                                        :key="tag.id"
                                        class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-300 rounded-full cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-400 transition-colors"
                                        @click.stop="filterByTag(tag.slug)"
                                    >
                                        #{{ tag.name }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 bg-white dark:bg-neutral-800 rounded-2xl">
                        <DocumentTextIcon class="mx-auto h-16 w-16 text-gray-300 dark:text-neutral-600" />
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No articles found</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Try adjusting your search or filters.</p>
                        <button @click="clearFilters" class="mt-4 px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                            Clear Filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="posts.last_page > 1" class="mt-10 flex justify-center">
                        <nav class="flex items-center gap-2">
                            <Link
                                v-if="posts.prev_page_url"
                                :href="posts.prev_page_url"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition"
                            >
                                ← Previous
                            </Link>
                            <span class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                Page {{ posts.current_page }} of {{ posts.last_page }}
                            </span>
                            <Link
                                v-if="posts.next_page_url"
                                :href="posts.next_page_url"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition"
                            >
                                Next →
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Categories Widget -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <FunnelIcon class="h-5 w-5 text-primary-600" />
                            Categories
                        </h3>
                        <div class="space-y-2">
                            <Link
                                v-for="category in categories"
                                :key="category.id"
                                :href="route('blog.index', { category: category.slug })"
                                class="flex items-center justify-between p-3 rounded-xl transition group"
                                :class="filters.category === category.slug ? 
                                    'bg-primary-50 dark:bg-primary-900/20' : 
                                    'hover:bg-gray-50 dark:hover:bg-neutral-700'"
                            >
                                <span class="font-medium" :class="filters.category === category.slug ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                    {{ category.name }}
                                </span>
                                <span class="text-xs bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                    {{ category.published_posts_count }}
                                </span>
                            </Link>
                        </div>
                    </div>

                    <!-- Popular Tags Widget -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <TagIcon class="h-5 w-5 text-primary-600" />
                            Popular Tags
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in popularTags"
                                :key="tag.id"
                                class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 rounded-full cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-400 transition-all"
                                :class="filters.tag === tag.slug ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 ring-2 ring-primary-300' : ''"
                                @click="filterByTag(tag.slug)"
                            >
                                #{{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Newsletter CTA -->
                    <div class="bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl shadow-lg p-6 text-white">
                        <h3 class="text-lg font-bold mb-2">Stay Updated</h3>
                        <p class="text-primary-100 text-sm mb-4">Get the latest travel tips and visa guides delivered to your inbox.</p>
                        <Link :href="route('contact')" class="block w-full text-center py-2.5 bg-white text-primary-700 font-medium rounded-lg hover:bg-primary-50 transition">
                            Subscribe Now
                        </Link>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Links</h3>
                        <div class="space-y-3">
                            <Link href="/visas" class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition group">
                                <ChevronRightIcon class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                                <span>Visa Services</span>
                            </Link>
                            <Link href="/jobs" class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition group">
                                <ChevronRightIcon class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                                <span>Job Opportunities</span>
                            </Link>
                            <Link href="/services/cv-builder" class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition group">
                                <ChevronRightIcon class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                                <span>CV Builder</span>
                            </Link>
                            <Link href="/help/faq" class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition group">
                                <ChevronRightIcon class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                                <span>FAQs</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
