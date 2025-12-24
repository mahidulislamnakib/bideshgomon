<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    MapPinIcon, PhoneIcon, EnvelopeIcon, ClockIcon,
    ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline';

const contactInfo = [
    { icon: MapPinIcon, label: 'Address', value: 'House 45, Road 12, Banani, Dhaka 1213, Bangladesh' },
    { icon: PhoneIcon, label: 'Phone', value: '+880 1712-345678' },
    { icon: EnvelopeIcon, label: 'Email', value: 'support@bideshgomon.com' },
    { icon: ClockIcon, label: 'Hours', value: 'Sun - Thu: 9:00 AM - 6:00 PM' },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const subjects = [
    'General Inquiry',
    'Visa Services',
    'Job Placement',
    'Document Services',
    'Technical Support',
    'Partnership',
    'Other',
];

const submit = () => {
    form.post('/contact', {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Contact Us" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-xl md:text-2xl font-bold text-white">Contact Us</h1>
                    <p class="text-sm text-white/80 mt-0.5">We're here to help with your international journey</p>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Contact Info -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Get in Touch</h2>
                            
                            <div class="space-y-6">
                                <div v-for="item in contactInfo" :key="item.label" class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                                        <component :is="item.icon" class="h-5 w-5 text-primary-600" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.label }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ item.value }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Links -->
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Quick Support</h3>
                                <div class="space-y-2">
                                    <a href="/help/faq" class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700">
                                        <ChatBubbleLeftRightIcon class="h-4 w-4" />
                                        FAQs
                                    </a>
                                    <a href="/help" class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700">
                                        <ChatBubbleLeftRightIcon class="h-4 w-4" />
                                        Help Center
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Send us a Message</h2>
                            
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Full Name *
                                        </label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                            placeholder="Your name"
                                        />
                                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Email *
                                        </label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                            placeholder="your@email.com"
                                        />
                                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Phone
                                        </label>
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                            placeholder="+880 1XXX-XXXXXX"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Subject *
                                        </label>
                                        <select
                                            v-model="form.subject"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                        >
                                            <option value="">Select a subject</option>
                                            <option v-for="subject in subjects" :key="subject" :value="subject">
                                                {{ subject }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Message *
                                    </label>
                                    <textarea
                                        v-model="form.message"
                                        rows="5"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                        placeholder="How can we help you?"
                                    ></textarea>
                                    <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 disabled:opacity-50 transition-colors"
                                    >
                                        {{ form.processing ? 'Sending...' : 'Send Message' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="mt-12 bg-gray-200 dark:bg-neutral-800 rounded-xl h-64 flex items-center justify-center">
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <MapPinIcon class="h-12 w-12 mx-auto mb-2" />
                        <p>Banani, Dhaka 1213, Bangladesh</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
