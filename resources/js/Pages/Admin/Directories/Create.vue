<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    ArrowLeftIcon, BuildingOffice2Icon, PhotoIcon,
    GlobeAltIcon, MapPinIcon, CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
    countries: Array
});

const form = useForm({
    directory_category_id: '',
    country_id: '',
    name: '',
    name_bn: '',
    description: '',
    description_bn: '',
    email: '',
    phone: '',
    website: '',
    address: '',
    city: '',
    latitude: '',
    longitude: '',
    logo: null,
    image: null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    operating_hours: {
        sunday: '',
        monday: '',
        tuesday: '',
        wednesday: '',
        thursday: '',
        friday: '',
        saturday: ''
    },
    is_published: false,
    is_verified: false,
    is_featured: false
});

// Quick fill helpers for operating hours
const fillWeekdays = () => {
    const hours = '9:00 AM - 6:00 PM';
    form.operating_hours.sunday = hours;
    form.operating_hours.monday = hours;
    form.operating_hours.tuesday = hours;
    form.operating_hours.wednesday = hours;
    form.operating_hours.thursday = hours;
    form.operating_hours.friday = 'Closed';
    form.operating_hours.saturday = 'Closed';
};

const fillAll = () => {
    const hours = '9:00 AM - 6:00 PM';
    Object.keys(form.operating_hours).forEach(day => {
        form.operating_hours[day] = hours;
    });
};

const clearAll = () => {
    Object.keys(form.operating_hours).forEach(day => {
        form.operating_hours[day] = '';
    });
};

const logoPreview = ref(null);
const imagePreview = ref(null);

const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
};

const submit = () => {
    form.post(route('admin.directories.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Create Directory" />

    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900 pb-12">
            <!-- Hero Header -->
            <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 left-0 w-96 h-96 bg-growth-500 rounded-full filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl translate-x-1/2 translate-y-1/2"></div>
                </div>
                
                <div class="relative z-10 px-4 py-8 sm:px-6 lg:px-8">
                    <div class="max-w-5xl mx-auto">
                        <div class="flex items-center gap-4">
                            <Link :href="route('admin.directories.index')" 
                                class="p-2 bg-white/10 rounded-xl backdrop-blur hover:bg-white/20 transition-colors">
                                <ArrowLeftIcon class="h-5 w-5 text-white" />
                            </Link>
                            <div>
                                <h1 class="text-2xl lg:text-3xl font-bold text-white flex items-center gap-3">
                                    <div class="p-2 bg-white/10 rounded-xl backdrop-blur">
                                        <BuildingOffice2Icon class="h-7 w-7 text-white" />
                                    </div>
                                    Create Directory
                                </h1>
                                <p class="text-gray-400 mt-2">Add a new directory entry</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <BuildingOffice2Icon class="h-5 w-5 text-growth-600" />
                            Basic Information
                        </h3>
                        
                        <div class="space-y-5">
                            <!-- Category & Country -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.directory_category_id" required
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.directory_category_id }">
                                        <option value="">Select Category</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <p v-if="form.errors.directory_category_id" class="mt-1 text-sm text-red-600">{{ form.errors.directory_category_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country</label>
                                    <select v-model="form.country_id"
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent">
                                        <option value="">Select Country</option>
                                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Names -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Name (English) <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.name" type="text" required
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.name }" />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Name (Bengali)
                                    </label>
                                    <input v-model="form.name_bn" type="text" placeholder="বাংলায় নাম"
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                                </div>
                            </div>

                            <!-- Descriptions -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description (English) <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.description" rows="3" required
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.description }"></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description (Bengali)
                                </label>
                                <textarea v-model="form.description_bn" rows="3" placeholder="বাংলায় বিবরণ"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <GlobeAltIcon class="h-5 w-5 text-growth-600" />
                            Contact Information
                        </h3>
                        
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                    <input v-model="form.email" type="email"
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                                    <input v-model="form.phone" type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Website URL</label>
                                <input v-model="form.website" type="url" placeholder="https://"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City</label>
                                    <input v-model="form.city" type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                                </div>

                                <div class="md:col-span-1">
                                    <!-- Empty for alignment -->
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Address <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.address" rows="2" required
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.address }"></textarea>
                                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <MapPinIcon class="h-5 w-5 text-growth-600" />
                            GPS Coordinates
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitude</label>
                                <input v-model="form.latitude" type="text" placeholder="23.8103"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitude</label>
                                <input v-model="form.longitude" type="text" placeholder="90.4125"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <PhotoIcon class="h-5 w-5 text-growth-600" />
                            Images
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Logo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Logo</label>
                                <div class="border-2 border-dashed border-gray-300 dark:border-neutral-600 rounded-xl p-6 text-center hover:border-growth-500 transition-colors">
                                    <div v-if="logoPreview" class="mb-4">
                                        <img :src="logoPreview" alt="Logo preview" class="mx-auto h-24 w-24 object-cover rounded-xl"/>
                                        <button @click="removeLogo" type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium">
                                            Remove
                                        </button>
                                    </div>
                                    <template v-else>
                                        <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-4">
                                            <label class="cursor-pointer">
                                                <span class="text-sm font-medium text-growth-600 hover:text-growth-700">Upload logo</span>
                                                <input type="file" class="sr-only" accept="image/*" @change="handleLogoUpload" />
                                            </label>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                    </template>
                                </div>
                            </div>

                            <!-- Featured Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Featured Image</label>
                                <div class="border-2 border-dashed border-gray-300 dark:border-neutral-600 rounded-xl p-6 text-center hover:border-growth-500 transition-colors">
                                    <div v-if="imagePreview" class="mb-4">
                                        <img :src="imagePreview" alt="Image preview" class="mx-auto h-24 w-auto object-cover rounded-xl"/>
                                        <button @click="removeImage" type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium">
                                            Remove
                                        </button>
                                    </div>
                                    <template v-else>
                                        <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-4">
                                            <label class="cursor-pointer">
                                                <span class="text-sm font-medium text-growth-600 hover:text-growth-700">Upload image</span>
                                                <input type="file" class="sr-only" accept="image/*" @change="handleImageUpload" />
                                            </label>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 5MB</p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">SEO Settings</h3>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Title</label>
                                <input v-model="form.meta_title" type="text" maxlength="60"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_title?.length || 0 }}/60 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Description</label>
                                <textarea v-model="form.meta_description" rows="2" maxlength="160"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent"></textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_description?.length || 0 }}/160 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Keywords</label>
                                <input v-model="form.meta_keywords" type="text" placeholder="keyword1, keyword2, keyword3"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-transparent" />
                                <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="h-5 w-5 text-growth-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Operating Hours
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Set business hours for each day. Leave blank or type "Closed" for days off.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div v-for="day in ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']" :key="day">
                                <label :for="`hours_${day}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 capitalize">{{ day }}</label>
                                <input
                                    :id="`hours_${day}`"
                                    v-model="form.operating_hours[day]"
                                    type="text"
                                    class="w-full rounded-2xl border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-growth-500 focus:ring-growth-500 text-sm"
                                    placeholder="9:00 AM - 6:00 PM"
                                />
                            </div>
                        </div>
                        
                        <!-- Quick Fill Buttons -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" @click="fillWeekdays" class="px-3 py-1.5 text-xs font-medium bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-xl transition-colors">
                                Fill Weekdays (Sun-Thu)
                            </button>
                            <button type="button" @click="fillAll" class="px-3 py-1.5 text-xs font-medium bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-xl transition-colors">
                                Fill All Days
                            </button>
                            <button type="button" @click="clearAll" class="px-3 py-1.5 text-xs font-medium bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-xl transition-colors">
                                Clear All
                            </button>
                        </div>
                    </div>

                    <!-- Status & Settings -->
                    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-xl border border-neutral-100 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <CheckCircleIcon class="h-5 w-5 text-growth-600" />
                            Status & Settings
                        </h3>
                        
                        <div class="flex flex-wrap gap-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.is_published" type="checkbox" 
                                    class="w-5 h-5 rounded border-gray-300 text-growth-600 focus:ring-growth-500" />
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Published</span>
                                    <p class="text-xs text-gray-500">Visible to public</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.is_verified" type="checkbox" 
                                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Verified</span>
                                    <p class="text-xs text-gray-500">Show verified badge</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.is_featured" type="checkbox" 
                                    class="w-5 h-5 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500" />
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Featured</span>
                                    <p class="text-xs text-gray-500">Show in featured section</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-4">
                        <Link :href="route('admin.directories.index')" 
                            class="px-6 py-2.5 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-growth-500 to-teal-600 text-white rounded-xl font-semibold hover:from-growth-600 hover:to-teal-700 transition-all shadow-lg shadow-growth-500/25 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Directory</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
