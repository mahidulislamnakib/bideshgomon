<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from '@/Composables/useToast';
import { useInertiaFormAutoSave } from '@/Composables/useFormAutoSave';
import AutoSaveIndicator from '@/Components/ui/AutoSaveIndicator.vue';
import { 
    ArrowLeftIcon, CheckIcon, SparklesIcon, DocumentTextIcon,
    PhotoIcon, TagIcon, Cog6ToothIcon, MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
    tags: Array,
});

const toast = useToast();

const appUrl = window.location.origin;
const imageError = ref(false);
const activeTab = ref('content');
const aiPrompt = ref('');
const aiLoading = ref(false);
const aiSuggestions = ref([]);

const form = useForm({
    category_id: '',
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    featured_image: '',
    image_credit: '',
    status: 'draft',
    published_at: '',
    is_featured: false,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    tags: [],
});

// Auto-save setup
const {
    lastSaved,
    hasRestoredData,
    isSaving,
    getTimeSinceLastSave,
    onSubmitSuccess,
    clearStorage,
} = useInertiaFormAutoSave('blog_post_create', form, {
    debounceMs: 1500,
    excludeFields: ['featured_image'], // Don't save file inputs
});

const tabs = [
    { id: 'content', label: 'Content', icon: DocumentTextIcon },
    { id: 'media', label: 'Media', icon: PhotoIcon },
    { id: 'seo', label: 'SEO', icon: MagnifyingGlassIcon },
];

const generateSlug = () => {
    form.slug = form.title.toString().toLowerCase().trim()
        .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-');
};

watch(() => form.title, (newTitle) => {
    if (!form.slug) generateSlug();
});

const submitForm = () => {
    form.post(route('admin.blog.posts.store'), { 
        preserveScroll: true,
        onSuccess: () => {
            onSubmitSuccess(); // Clear auto-saved data on success
        },
    });
};

// AI Writing Assistant
const aiTemplates = [
    { label: 'Blog intro', prompt: 'Write an engaging introduction for a blog post about: ' },
    { label: 'Expand section', prompt: 'Expand this section with more details: ' },
    { label: 'Conclusion', prompt: 'Write a compelling conclusion for: ' },
    { label: 'SEO description', prompt: 'Write an SEO-friendly meta description for: ' },
    { label: 'Social post', prompt: 'Write a social media post to promote: ' },
];

const generateWithAI = async (template) => {
    if (!form.title && !aiPrompt.value) {
        toast.warning('Please enter a title or prompt first');
        return;
    }
    
    aiLoading.value = true;
    const prompt = template ? template.prompt + (form.title || aiPrompt.value) : aiPrompt.value;
    
    // Simulate AI response (in real app, call your AI API)
    setTimeout(() => {
        const suggestions = [
            `Here's AI-generated content based on "${form.title || aiPrompt.value}":\n\n`,
            `## Introduction\n\nIn today's rapidly evolving landscape, understanding ${form.title || 'this topic'} has become increasingly important. Whether you're a beginner or an experienced professional, this guide will provide valuable insights.\n\n`,
            `## Key Points\n\n- First important aspect to consider\n- Second crucial element\n- Third essential component\n\n`,
            `## Conclusion\n\nBy following these guidelines, you'll be well-equipped to navigate ${form.title || 'this subject'} effectively.`
        ];
        
        aiSuggestions.value = [{
            text: suggestions.join(''),
            type: template?.label || 'Custom'
        }];
        aiLoading.value = false;
    }, 1500);
};

const insertAISuggestion = (suggestion) => {
    form.content += (form.content ? '\n\n' : '') + suggestion.text;
    aiSuggestions.value = [];
};

const wordCount = computed(() => {
    return form.content.trim().split(/\s+/).filter(w => w).length;
});

const readingTime = computed(() => {
    return Math.max(1, Math.ceil(wordCount.value / 200));
});
</script>

<template>
    <Head title="Create Blog Post" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-4 sm:px-6">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="route('admin.blog.posts.index')" 
                            class="p-1.5 text-white/70 hover:text-white hover:bg-white/10 rounded-lg">
                            <ArrowLeftIcon class="h-5 w-5" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-bold text-white">Create Post</h1>
                            <p class="text-xs text-white/70">Write and publish content</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <AutoSaveIndicator 
                            :is-saving="isSaving"
                            :last-saved="lastSaved"
                            :time-since-last-save="getTimeSinceLastSave()"
                            :has-restored-data="hasRestoredData"
                            :show-restore-banner="false"
                            @discard="clearStorage"
                        />
                        <span class="text-xs text-white/70">{{ wordCount }} words · {{ readingTime }} min read</span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="max-w-6xl mx-auto px-4 sm:px-6 py-5">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Title -->
                        <div class="bg-white rounded-lg border p-4">
                            <input v-model="form.title" type="text" placeholder="Post title..."
                                class="w-full text-xl font-bold border-0 focus:ring-0 p-0 placeholder-gray-300"
                                @input="generateSlug" />
                            <div class="flex items-center gap-1 mt-2 text-xs text-gray-400">
                                <span>{{ appUrl }}/blog/</span>
                                <input v-model="form.slug" type="text" placeholder="slug"
                                    class="flex-1 border-0 focus:ring-0 p-0 text-xs text-gray-600" />
                            </div>
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Tabs -->
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <div class="flex border-b">
                                <button v-for="tab in tabs" :key="tab.id" type="button"
                                    @click="activeTab = tab.id"
                                    :class="['flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors',
                                        activeTab === tab.id 
                                            ? 'border-purple-600 text-purple-600' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700']">
                                    <component :is="tab.icon" class="h-4 w-4" />
                                    {{ tab.label }}
                                </button>
                            </div>

                            <div class="p-4">
                                <!-- Content Tab -->
                                <div v-show="activeTab === 'content'" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Excerpt</label>
                                        <textarea v-model="form.excerpt" rows="2" placeholder="Brief summary..."
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500 focus:border-purple-500"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Content</label>
                                        <textarea v-model="form.content" rows="16" placeholder="Write your post..."
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 font-mono"></textarea>
                                        <p class="mt-1 text-xs text-gray-400">Supports Markdown</p>
                                    </div>
                                </div>

                                <!-- Media Tab -->
                                <div v-show="activeTab === 'media'" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Featured Image URL</label>
                                        <input v-model="form.featured_image" type="text" placeholder="https://..."
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500" />
                                    </div>
                                    <div v-if="form.featured_image" class="rounded-lg overflow-hidden bg-gray-100">
                                        <img :src="form.featured_image" class="w-full h-48 object-cover" @error="imageError = true" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Image Credit</label>
                                        <input v-model="form.image_credit" type="text" placeholder="Photo by..."
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500" />
                                    </div>
                                </div>

                                <!-- SEO Tab -->
                                <div v-show="activeTab === 'seo'" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Meta Title</label>
                                        <input v-model="form.meta_title" type="text" :placeholder="form.title || 'SEO title'"
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500" />
                                        <p class="mt-1 text-xs text-gray-400">{{ (form.meta_title || form.title || '').length }}/60</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Meta Description</label>
                                        <textarea v-model="form.meta_description" rows="2" placeholder="SEO description..."
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500"></textarea>
                                        <p class="mt-1 text-xs text-gray-400">{{ (form.meta_description || '').length }}/160</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Keywords</label>
                                        <input v-model="form.meta_keywords" type="text" placeholder="keyword1, keyword2"
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Writing Assistant -->
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <SparklesIcon class="h-5 w-5 text-purple-600" />
                                <h3 class="font-semibold text-sm text-purple-900">AI Writing Assistant</h3>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 mb-3">
                                <button v-for="template in aiTemplates" :key="template.label" type="button"
                                    @click="generateWithAI(template)"
                                    :disabled="aiLoading"
                                    class="px-3 py-1.5 text-xs bg-white border border-purple-200 rounded-full text-purple-700 hover:bg-purple-100 disabled:opacity-50">
                                    {{ template.label }}
                                </button>
                            </div>

                            <div class="flex gap-2">
                                <input v-model="aiPrompt" type="text" placeholder="Or type custom prompt..."
                                    class="flex-1 text-sm border-purple-200 rounded-lg focus:ring-purple-500" />
                                <button type="button" @click="generateWithAI(null)" :disabled="aiLoading || !aiPrompt"
                                    class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50">
                                    {{ aiLoading ? 'Generating...' : 'Generate' }}
                                </button>
                            </div>

                            <!-- AI Suggestions -->
                            <div v-if="aiSuggestions.length > 0" class="mt-3 space-y-2">
                                <div v-for="(suggestion, i) in aiSuggestions" :key="i" 
                                    class="bg-white rounded-lg p-3 border border-purple-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-medium text-purple-600">{{ suggestion.type }}</span>
                                        <button type="button" @click="insertAISuggestion(suggestion)"
                                            class="text-xs text-purple-600 hover:text-purple-700 font-medium">
                                            Insert →
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap line-clamp-4">{{ suggestion.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-4">
                        <!-- Publish Settings -->
                        <div class="bg-white rounded-lg border p-4">
                            <h3 class="font-semibold text-sm text-gray-900 mb-3 flex items-center gap-2">
                                <Cog6ToothIcon class="h-4 w-4" />
                                Settings
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                    <select v-model="form.status"
                                        class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="scheduled">Scheduled</option>
                                    </select>
                                </div>
                                <div v-if="form.status !== 'draft'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Publish Date</label>
                                    <input v-model="form.published_at" type="datetime-local"
                                        class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500" />
                                </div>
                                <label class="flex items-center gap-2">
                                    <input v-model="form.is_featured" type="checkbox"
                                        class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                                    <span class="text-sm text-gray-700">Featured post</span>
                                </label>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="bg-white rounded-lg border p-4">
                            <label class="block text-xs font-medium text-gray-700 mb-2">Category *</label>
                            <select v-model="form.category_id"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500">
                                <option value="">Select category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Tags -->
                        <div class="bg-white rounded-lg border p-4">
                            <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center gap-1">
                                <TagIcon class="h-3.5 w-3.5" />
                                Tags
                            </label>
                            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                                <label v-for="tag in tags" :key="tag.id" 
                                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs cursor-pointer transition-colors"
                                    :class="form.tags.includes(tag.id) 
                                        ? 'bg-purple-100 text-purple-700' 
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                    <input type="checkbox" v-model="form.tags" :value="tag.id" class="hidden" />
                                    {{ tag.name }}
                                </label>
                            </div>
                            <p v-if="!tags?.length" class="text-xs text-gray-400 italic">No tags</p>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-2">
                            <button type="submit" :disabled="form.processing"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700 disabled:opacity-50">
                                <CheckIcon v-if="!form.processing" class="h-4 w-4" />
                                <svg v-else class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ form.processing ? 'Creating...' : 'Create Post' }}
                            </button>
                            <Link :href="route('admin.blog.posts.index')"
                                class="w-full flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-200">
                                Cancel
                            </Link>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
