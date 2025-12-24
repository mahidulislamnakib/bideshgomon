<template>
    <AuthenticatedLayout>
        <Head title="Create Support Ticket" />

        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-growth-50/30">
            <div class="py-8 sm:py-12">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <!-- Page Header -->
                    <div class="mb-8">
                        <Link :href="route('support.index')" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-growth-600 transition-colors mb-4 group">
                            <ArrowLeftIcon class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                            Back to Tickets
                        </Link>
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">
                            How can we <span class="text-growth-600">help</span> you?
                        </h1>
                        <p class="mt-2 text-lg text-gray-600">
                            Create a support ticket and our team will respond within 24 hours
                        </p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Main Form Card -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                                <!-- Card Header -->
                                <div class="bg-gradient-to-r from-growth-600 to-growth-700 px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                            <ChatBubbleBottomCenterTextIcon class="w-6 h-6 text-white" />
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-white">New Support Request</h2>
                                            <p class="text-growth-100 text-sm">Fill out the form below</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Body -->
                                <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-6">
                                    
                                    <!-- Category Selection (Visual Cards) -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-3">
                                            What do you need help with? <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <button
                                                v-for="cat in categories"
                                                :key="cat.value"
                                                type="button"
                                                @click="form.category = cat.value"
                                                :class="[
                                                    'relative p-4 rounded-xl border-2 transition-all duration-200 text-left group',
                                                    form.category === cat.value 
                                                        ? 'border-growth-500 bg-growth-50 ring-2 ring-growth-500/20' 
                                                        : 'border-gray-200 hover:border-growth-300 hover:bg-gray-50'
                                                ]"
                                            >
                                                <div :class="[
                                                    'w-10 h-10 rounded-lg flex items-center justify-center mb-2 transition-colors',
                                                    form.category === cat.value ? cat.bgActive : cat.bg
                                                ]">
                                                    <component :is="cat.icon" :class="['w-5 h-5', form.category === cat.value ? cat.textActive : cat.text]" />
                                                </div>
                                                <span :class="['text-sm font-medium', form.category === cat.value ? 'text-growth-700' : 'text-gray-700']">
                                                    {{ cat.label }}
                                                </span>
                                                <CheckCircleIcon 
                                                    v-if="form.category === cat.value"
                                                    class="absolute top-2 right-2 w-5 h-5 text-growth-500"
                                                />
                                            </button>
                                        </div>
                                        <p v-if="form.errors.category" class="mt-2 text-sm text-red-600">{{ form.errors.category }}</p>
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <label for="subject" class="block text-sm font-semibold text-gray-900 mb-2">
                                            Subject <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="subject"
                                            v-model="form.subject"
                                            type="text"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-growth-500 transition-all text-gray-900 placeholder-gray-400"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.subject }"
                                            placeholder="Brief summary of your issue"
                                            required
                                        />
                                        <p v-if="form.errors.subject" class="mt-2 text-sm text-red-600">{{ form.errors.subject }}</p>
                                    </div>

                                    <!-- Priority -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-3">
                                            Priority Level <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex flex-wrap gap-3">
                                            <button
                                                v-for="priority in priorities"
                                                :key="priority.value"
                                                type="button"
                                                @click="form.priority = priority.value"
                                                :class="[
                                                    'px-4 py-2.5 rounded-xl border-2 font-medium text-sm transition-all duration-200 flex items-center gap-2',
                                                    form.priority === priority.value 
                                                        ? `${priority.activeBg} ${priority.activeBorder} ${priority.activeText}` 
                                                        : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white'
                                                ]"
                                            >
                                                <span :class="['w-2 h-2 rounded-full', priority.dot]"></span>
                                                {{ priority.label }}
                                            </button>
                                        </div>
                                        <p v-if="form.errors.priority" class="mt-2 text-sm text-red-600">{{ form.errors.priority }}</p>
                                    </div>

                                    <!-- Message -->
                                    <div>
                                        <label for="message" class="block text-sm font-semibold text-gray-900 mb-2">
                                            Describe Your Issue <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            id="message"
                                            v-model="form.message"
                                            rows="6"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-500 focus:border-growth-500 transition-all text-gray-900 placeholder-gray-400 resize-none"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.message }"
                                            placeholder="Please provide as much detail as possible about your issue, including any error messages, steps you've taken, and expected vs actual behavior..."
                                            required
                                        ></textarea>
                                        <div class="flex justify-between mt-2">
                                            <p v-if="form.errors.message" class="text-sm text-red-600">{{ form.errors.message }}</p>
                                            <p class="text-xs text-gray-500 ml-auto">{{ form.message.length }} / 2000 characters</p>
                                        </div>
                                    </div>

                                    <!-- Attachments -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                                            Attachments <span class="text-gray-400 font-normal">(Optional)</span>
                                        </label>
                                        <div 
                                            class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-growth-400 hover:bg-growth-50/30 transition-all cursor-pointer"
                                            @click="$refs.fileInput.click()"
                                            @dragover.prevent="isDragging = true"
                                            @dragleave="isDragging = false"
                                            @drop.prevent="handleDrop"
                                            :class="{ 'border-growth-500 bg-growth-50': isDragging }"
                                        >
                                            <input
                                                ref="fileInput"
                                                type="file"
                                                @change="handleFileUpload"
                                                multiple
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                class="hidden"
                                            />
                                            <CloudArrowUpIcon class="w-10 h-10 mx-auto text-gray-400 mb-3" />
                                            <p class="text-sm font-medium text-gray-700">
                                                <span class="text-growth-600">Click to upload</span> or drag and drop
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                PDF, DOC, DOCX, JPG, PNG (max 10MB each, up to 5 files)
                                            </p>
                                        </div>

                                        <!-- File Preview -->
                                        <div v-if="form.attachments.length > 0" class="mt-4 space-y-2">
                                            <div 
                                                v-for="(file, index) in form.attachments" 
                                                :key="index"
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <DocumentIcon class="w-5 h-5 text-gray-400" />
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-700">{{ file.name }}</p>
                                                        <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="removeFile(index)" class="text-gray-400 hover:text-red-500 transition-colors">
                                                    <XCircleIcon class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.attachments" class="mt-2 text-sm text-red-600">{{ form.errors.attachments }}</p>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                        <Link :href="route('support.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                                            Cancel
                                        </Link>
                                        <button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="inline-flex items-center gap-2 px-6 py-3 bg-growth-600 text-white font-semibold rounded-xl hover:bg-growth-700 focus:outline-none focus:ring-2 focus:ring-growth-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-growth-500/25"
                                        >
                                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <PaperAirplaneIcon v-else class="w-5 h-5" />
                                            {{ form.processing ? 'Submitting...' : 'Submit Ticket' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="lg:col-span-1 space-y-6">
                            
                            <!-- Quick Tips Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                        <LightBulbIcon class="w-5 h-5 text-yellow-600" />
                                    </div>
                                    <h3 class="font-bold text-gray-900">Quick Tips</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Be specific about your issue</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Include any error messages</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Attach relevant screenshots</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-growth-500 flex-shrink-0 mt-0.5" />
                                        <span class="text-sm text-gray-600">Mention steps to reproduce</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Response Time Card -->
                            <div class="bg-gradient-to-br from-growth-600 to-growth-700 rounded-2xl shadow-lg p-6 text-white">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <ClockIcon class="w-5 h-5 text-white" />
                                    </div>
                                    <h3 class="font-bold">Response Time</h3>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-growth-100">Low Priority</span>
                                        <span class="font-semibold">24-48 hours</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-growth-100">Normal</span>
                                        <span class="font-semibold">12-24 hours</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-growth-100">High</span>
                                        <span class="font-semibold">4-12 hours</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-growth-100">Urgent</span>
                                        <span class="font-semibold">&lt; 4 hours</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Alternatives -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                <h3 class="font-bold text-gray-900 mb-4">Need Faster Help?</h3>
                                <div class="space-y-3">
                                    <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <ChatBubbleLeftRightIcon class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Live Chat</p>
                                            <p class="text-xs text-gray-500">Available 9 AM - 9 PM</p>
                                        </div>
                                    </a>
                                    <a href="tel:+8801910638075" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <PhoneIcon class="w-5 h-5 text-green-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Call Support</p>
                                            <p class="text-xs text-gray-500">+880 1910-638075</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowLeftIcon,
    ChatBubbleBottomCenterTextIcon,
    CheckCircleIcon,
    CloudArrowUpIcon,
    DocumentIcon,
    XCircleIcon,
    PaperAirplaneIcon,
    LightBulbIcon,
    CheckIcon,
    ClockIcon,
    ChatBubbleLeftRightIcon,
    PhoneIcon,
    GlobeAltIcon,
    BriefcaseIcon,
    UserCircleIcon,
    CreditCardIcon,
    WrenchScrewdriverIcon,
    QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';

const categories = [
    { value: 'visa', label: 'Visa & Immigration', icon: GlobeAltIcon, bg: 'bg-blue-100', text: 'text-blue-600', bgActive: 'bg-blue-500', textActive: 'text-white' },
    { value: 'jobs', label: 'Jobs & Work', icon: BriefcaseIcon, bg: 'bg-green-100', text: 'text-green-600', bgActive: 'bg-green-500', textActive: 'text-white' },
    { value: 'account', label: 'Account', icon: UserCircleIcon, bg: 'bg-purple-100', text: 'text-purple-600', bgActive: 'bg-purple-500', textActive: 'text-white' },
    { value: 'payment', label: 'Payment', icon: CreditCardIcon, bg: 'bg-orange-100', text: 'text-orange-600', bgActive: 'bg-orange-500', textActive: 'text-white' },
    { value: 'technical', label: 'Technical', icon: WrenchScrewdriverIcon, bg: 'bg-red-100', text: 'text-red-600', bgActive: 'bg-red-500', textActive: 'text-white' },
    { value: 'other', label: 'Other', icon: QuestionMarkCircleIcon, bg: 'bg-gray-100', text: 'text-gray-600', bgActive: 'bg-gray-500', textActive: 'text-white' },
];

const priorities = [
    { value: 'low', label: 'Low', dot: 'bg-gray-400', activeBg: 'bg-gray-100', activeBorder: 'border-gray-400', activeText: 'text-gray-700' },
    { value: 'normal', label: 'Normal', dot: 'bg-blue-500', activeBg: 'bg-blue-50', activeBorder: 'border-blue-500', activeText: 'text-blue-700' },
    { value: 'high', label: 'High', dot: 'bg-orange-500', activeBg: 'bg-orange-50', activeBorder: 'border-orange-500', activeText: 'text-orange-700' },
    { value: 'urgent', label: 'Urgent', dot: 'bg-red-500', activeBg: 'bg-red-50', activeBorder: 'border-red-500', activeText: 'text-red-700' },
];

const form = useForm({
    subject: '',
    category: '',
    priority: 'normal',
    message: '',
    attachments: [],
});

const isDragging = ref(false);

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

const handleDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

const addFiles = (files) => {
    const validFiles = files.filter(file => {
        const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        const validSize = file.size <= 10 * 1024 * 1024; // 10MB
        return validTypes.includes(file.type) && validSize;
    });

    if (form.attachments.length + validFiles.length > 5) {
        alert('Maximum 5 files allowed');
        return;
    }

    form.attachments = [...form.attachments, ...validFiles];
};

const removeFile = (index) => {
    form.attachments.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = () => {
    form.post(route('support.store'), {
        preserveScroll: true,
    });
};
</script>
