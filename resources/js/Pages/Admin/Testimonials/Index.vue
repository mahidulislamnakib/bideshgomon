<template>
    <Head title="Testimonials" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-display-md font-bold text-gray-900">Testimonials</h1>
                    <p class="mt-1 text-gray-600">Manage customer testimonials and reviews</p>
                </div>
                <Link
                    :href="route('admin.testimonials.create')"
                    class="inline-flex items-center px-4 py-2 bg-brand-red-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-red-700 transition-colors"
                >
                    💬 Add Testimonial
                </Link>
            </div>

            <!-- Filters Card -->
            <BaseCard padding="lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <BaseInput
                            v-model="filters.search"
                            type="text"
                            placeholder="Search testimonials..."
                            @input="debouncedSearch"
                        />
                    </div>
                    <BaseSelect
                        v-model="filters.status"
                        :options="[
                            { value: '', label: 'All Status' },
                            { value: 'approved', label: 'Approved' },
                            { value: 'pending', label: 'Pending' },
                            { value: 'featured', label: 'Featured' }
                        ]"
                        @change="search"
                    />
                    <BaseSelect
                        v-model="filters.rating"
                        :options="[
                            { value: '', label: 'All Ratings' },
                            { value: '5', label: '5 Stars' },
                            { value: '4', label: '4 Stars' },
                            { value: '3', label: '3 Stars' },
                            { value: '2', label: '2 Stars' },
                            { value: '1', label: '1 Star' }
                        ]"
                        @change="search"
                    />
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="search" class="px-4 py-2 bg-brand-red-600 text-white rounded-md hover:bg-red-700 transition-colors">Search</button>
                    <button @click="resetFilters" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Reset</button>
                </div>
            </BaseCard>

            <!-- Testimonials Table -->
            <BaseCard padding="none">
                <div v-if="testimonials.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No testimonials</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new testimonial.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="testimonial in testimonials.data" :key="testimonial.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div v-if="testimonial.photo" class="flex-shrink-0 h-10 w-10">
                                    <img :src="`/storage/${testimonial.photo}`" :alt="testimonial.author_name" class="h-10 w-10 rounded-full object-cover"/>
                                </div>
                                <div v-else class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-gray-500 font-medium">{{ testimonial.author_name.charAt(0) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ testimonial.author_name }}</div>
                                    <div v-if="testimonial.position" class="text-sm text-gray-500">{{ testimonial.position }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ testimonial.company || '-' }}</div>
                            <div v-if="testimonial.location" class="text-sm text-gray-500">{{ testimonial.location }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 line-clamp-2">{{ testimonial.content }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg v-for="i in 5" :key="i" class="h-5 w-5" :class="i <= testimonial.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <span v-if="testimonial.is_approved" class="inline-flex px-2 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                                <span v-else class="inline-flex px-2 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                                <span v-if="testimonial.is_featured" class="inline-flex px-2 text-xs font-semibold rounded-full bg-red-100 text-brand-red-600">
                                    Featured
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <button
                                    @click="toggleApproval(testimonial)"
                                    :class="testimonial.is_approved ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'"
                                    class="font-medium"
                                    :title="testimonial.is_approved ? 'Unapprove' : 'Approve'"
                                >
                                    {{ testimonial.is_approved ? 'Unapprove' : 'Approve' }}
                                </button>
                                <button
                                    @click="toggleFeatured(testimonial)"
                                    :class="testimonial.is_featured ? 'text-gray-600 hover:text-gray-900' : 'text-brand-red-600 hover:text-red-900'"
                                    class="font-medium"
                                >
                                    {{ testimonial.is_featured ? 'Unfeature' : 'Feature' }}
                                </button>
                                <Link
                                    :href="route('admin.testimonials.edit', testimonial.id)"
                                    class="text-brand-red-600 hover:text-red-900 font-medium"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteTestimonial(testimonial)"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
                </div>
            </BaseCard>

            <!-- Pagination -->
            <div v-if="testimonials.links && testimonials.links.length > 3" class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Showing {{ testimonials.from }} to {{ testimonials.to }} of {{ testimonials.total }} results
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, index) in testimonials.links"
                        :key="index"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                            link.active
                                ? 'bg-brand-red-600 text-white'
                                : link.url
                                ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        ]"
                        :disabled="!link.url"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseCard from '@/Components/Base/BaseCard.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

const props = defineProps({
    testimonials: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    rating: props.filters.rating || '',
});

let searchTimeout = null;

const search = () => {
    router.get(route('admin.testimonials.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(search, 300);
};

const resetFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.rating = '';
    search();
};

const toggleApproval = (testimonial) => {
    const action = testimonial.is_approved ? 'unapprove' : 'approve';
    if (confirm(`Are you sure you want to ${action} this testimonial?`)) {
        router.post(route('admin.testimonials.toggle-approved', testimonial.id), {}, {
            preserveScroll: true,
        });
    }
};

const toggleFeatured = (testimonial) => {
    const action = testimonial.is_featured ? 'unfeature' : 'feature';
    if (confirm(`Are you sure you want to ${action} this testimonial?`)) {
        router.post(route('admin.testimonials.toggle-featured', testimonial.id), {}, {
            preserveScroll: true,
        });
    }
};

const deleteTestimonial = (testimonial) => {
    if (confirm(`Are you sure you want to delete the testimonial by ${testimonial.author_name}?`)) {
        router.delete(route('admin.testimonials.destroy', testimonial.id));
    }
};
</script>
