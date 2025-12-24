<script setup>
import InputError from '@/Components/InputError.vue';
import { UserCircleIcon } from '@heroicons/vue/24/solid';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    userProfile: {
        type: Object,
        default: () => ({}),
    },
});

const user = usePage().props.auth.user;
const saveError = ref('');

// Debug: Check what we're receiving
console.log('UpdateProfileInformationForm - userProfile prop:', props.userProfile);
console.log('UpdateProfileInformationForm - first_name:', props.userProfile?.first_name);
console.log('UpdateProfileInformationForm - last_name:', props.userProfile?.last_name);

// Passport-standard name fields
const form = useForm({
    first_name: props.userProfile?.first_name || '',
    middle_name: props.userProfile?.middle_name || '',
    last_name: props.userProfile?.last_name || '',
    name_as_per_passport: props.userProfile?.name_as_per_passport || '',
    email: user.email,
});

console.log('UpdateProfileInformationForm - form initialized:', {
    first_name: form.first_name,
    middle_name: form.middle_name,
    last_name: form.last_name,
    name_as_per_passport: form.name_as_per_passport
});

// Auto-generate passport name format when typing
const autoGeneratePassportName = () => {
    const parts = [form.first_name, form.middle_name, form.last_name].filter(p => p && p.trim());
    form.name_as_per_passport = parts.join(' ').toUpperCase();
};

// Hydrate form fields on mount if they are still empty but props have data (guards against race or cache issues)
onMounted(() => {
    if (!form.first_name && props.userProfile?.first_name) {
        form.first_name = props.userProfile.first_name || '';
        form.middle_name = props.userProfile.middle_name || '';
        form.last_name = props.userProfile.last_name || '';
        form.name_as_per_passport = props.userProfile.name_as_per_passport || '';
        console.log('Form hydrated from userProfile onMounted:', {
            first_name: form.first_name,
            middle_name: form.middle_name,
            last_name: form.last_name,
            name_as_per_passport: form.name_as_per_passport
        });
    }
});

const submit = () => {
    console.log('=== SUBMIT FUNCTION CALLED ===');
    console.log('Form data:', {
        first_name: form.first_name,
        middle_name: form.middle_name,
        last_name: form.last_name,
        name_as_per_passport: form.name_as_per_passport,
        email: form.email
    });
    console.log('Route URL:', route('profile.update'));
    console.log('Form processing:', form.processing);
    
    saveError.value = '';
    
    try {
        console.log('Calling form.patch...');
        const result = form.patch(route('profile.update'), {
            preserveScroll: true,
            onBefore: () => {
                console.log('onBefore callback triggered');
            },
            onStart: () => {
                console.log('onStart callback triggered');
            },
            onProgress: (progress) => {
                console.log('onProgress:', progress);
            },
            onSuccess: (page) => {
                console.log('onSuccess callback triggered', page);
                saveError.value = '';
            },
            onError: (errors) => {
                console.error('onError callback triggered', errors);
                saveError.value = 'Failed to update profile information. Please check the form and try again.';
            },
            onCancel: () => {
                console.log('onCancel callback triggered');
            },
            onFinish: () => {
                console.log('onFinish callback triggered');
            }
        });
        console.log('form.patch() returned:', result);
    } catch (error) {
        console.error('Exception in form.patch():', error);
    }
};
</script>

<template>
    <section class="space-y-6">
        <!-- Section Header -->
        <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                <UserCircleIcon class="h-6 w-6 text-white" />
            </div>
            <div>
                <h2 class="font-semibold text-lg text-gray-900">
                    Basic Information
                </h2>
                <p class="text-sm text-gray-500">Your name and email as they appear on official documents</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Passport Name Notice -->
            <div class="flex items-start gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-growth-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-900">Important</p>
                    <p class="text-sm text-blue-700 mt-0.5">Enter your name exactly as it appears on your passport for visa applications.</p>
                </div>
            </div>

            <!-- Name Fields Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="first_name"
                        type="text"
                        v-model="form.first_name"
                        @input="autoGeneratePassportName"
                        required
                        placeholder="JOHN"
                        autocomplete="given-name"
                        class="w-full px-4 py-3 uppercase text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all placeholder:text-gray-400"
                    />
                    <InputError :message="form.errors.first_name" />
                </div>

                <div class="space-y-2">
                    <label for="middle_name" class="block text-sm font-medium text-gray-700">
                        Middle Name <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                    </label>
                    <input
                        id="middle_name"
                        type="text"
                        v-model="form.middle_name"
                        @input="autoGeneratePassportName"
                        placeholder="WILLIAM"
                        autocomplete="additional-name"
                        class="w-full px-4 py-3 uppercase text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all placeholder:text-gray-400"
                    />
                    <InputError :message="form.errors.middle_name" />
                </div>

                <div class="space-y-2">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="last_name"
                        type="text"
                        v-model="form.last_name"
                        @input="autoGeneratePassportName"
                        required
                        placeholder="DOE"
                        autocomplete="family-name"
                        class="w-full px-4 py-3 uppercase text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all placeholder:text-gray-400"
                    />
                    <InputError :message="form.errors.last_name" />
                </div>
            </div>

            <!-- Auto-generated Full Name -->
            <div class="space-y-2">
                <label for="name_as_per_passport" class="block text-sm font-medium text-gray-700">
                    Full Name (As Per Passport)
                </label>
                <input
                    id="name_as_per_passport"
                    type="text"
                    v-model="form.name_as_per_passport"
                    readonly
                    placeholder="AUTO-GENERATED"
                    class="w-full px-4 py-3 uppercase text-sm font-medium bg-gray-50 border border-gray-200 rounded-xl text-gray-600 cursor-not-allowed"
                />
                <p class="text-xs text-gray-500">This is auto-generated from the fields above</p>
                <InputError :message="form.errors.name_as_per_passport" />
            </div>

            <!-- Email Field -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="john.doe@example.com"
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-growth-600 focus:border-growth-600 transition-all placeholder:text-gray-400"
                />
                <InputError :message="form.errors.email" />
            </div>

            <!-- Email Verification Notice -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-4 bg-amber-50 border border-amber-100 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-amber-900">Email not verified</p>
                        <p class="text-sm text-amber-700 mt-0.5">
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="underline hover:text-amber-800 font-medium"
                            >
                                Click here to resend verification email
                            </Link>
                        </p>
                        <p v-if="status === 'verification-link-sent'" class="text-sm font-medium text-green-600 mt-2">
                            ✓ Verification link sent!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Save Error -->
            <div v-if="saveError" class="p-4 bg-red-50 border border-red-100 rounded-xl">
                <p class="text-sm text-red-700">{{ saveError }}</p>
            </div>

            <!-- Desktop Save Button -->
            <div class="hidden md:flex items-center gap-4 pt-4 border-t border-gray-100">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm rounded-xl shadow-sm hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-growth-600 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in duration-150"
                    leave-to-class="opacity-0"
                >
                    <span v-if="form.recentlySuccessful" class="inline-flex items-center gap-1.5 text-sm text-green-600 font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Saved successfully
                    </span>
                </Transition>
            </div>

            <!-- Mobile Sticky Save Button -->
            <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-gray-200 p-4 shadow-lg z-30">
                <button
                    @click="submit"
                    :disabled="form.processing"
                    type="button"
                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm rounded-xl shadow-sm disabled:opacity-50 transition-all active:scale-[0.98]"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Saving...' : (form.recentlySuccessful ? '✓ Saved!' : 'Save Changes') }}
                </button>
            </div>

            <!-- Mobile Bottom Spacer -->
            <div class="md:hidden h-20"></div>
        </form>
    </section>
</template>
