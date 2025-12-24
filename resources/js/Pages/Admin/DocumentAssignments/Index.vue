<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    MagnifyingGlassIcon,
    GlobeAltIcon,
    DocumentTextIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    countries: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

const searchCountries = () => {
    router.get(route('admin.document-assignments.index'), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Document Assignments" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Country Document Assignments</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Assign visa documents from the master library to countries
                    </p>
                </div>

                <!-- Search -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-4 mb-6">
                    <input
                        v-model="search"
                        @keyup.enter="searchCountries"
                        type="text"
                        placeholder="Search countries..."
                        class="w-full px-4 py-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:border-growth-600 focus:ring-growth-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                    />
                </div>

                <!-- Countries Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="country in countries.data"
                        :key="country.id"
                        :href="route('admin.document-assignments.show', country.id)"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition-shadow p-6 cursor-pointer group"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <span class="text-4xl mr-4">{{ country.flag_emoji }}</span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-growth-600">
                                        {{ country.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ country.iso_code_2 }}</p>
                                </div>
                            </div>
                            <ArrowRightIcon class="w-5 h-5 text-gray-400 group-hover:text-growth-600 transition-colors" />
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <DocumentTextIcon class="w-4 h-4 mr-1" />
                                    <span>{{ country.document_requirements ? country.document_requirements.length : 0 }} documents</span>
                                </div>
                                <div class="text-growth-600 font-medium group-hover:underline">
                                    Manage ?
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="countries.links && countries.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="flex space-x-2">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in countries.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-xl',
                                link.active
                                    ? 'bg-growth-600 text-white'
                                    : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 cursor-pointer'
                                        : 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>

                <!-- Quick Links -->
                <div class="mt-8 bg-red-50 dark:bg-gray-800 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Link
                            :href="route('admin.master-documents.index')"
                            class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-2xl hover:shadow-md transition-shadow"
                        >
                            <DocumentTextIcon class="w-6 h-6 text-growth-600 mr-3" />
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Master Documents</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Manage document library</div>
                            </div>
                        </Link>
                        <Link
                            :href="route('admin.document-categories.index')"
                            class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-2xl hover:shadow-md transition-shadow"
                        >
                            <GlobeAltIcon class="w-6 h-6 text-growth-600 mr-3" />
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Document Categories</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Manage categories</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
