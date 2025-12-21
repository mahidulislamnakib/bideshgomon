<script setup>
import InputError from '@/Components/InputError.vue';
import { DocumentTextIcon } from '@heroicons/vue/24/solid';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
import {
    GENDER_OPTIONS,
    BANGLADESH_DIVISIONS,
    BANGLADESH_DISTRICTS
} from '@/Constants/profileData';

const props = defineProps({
    userProfile: Object,
    divisions: Array,
    countries: Array,
});

const { formatDateFromISO, parseDateToISO } = useBangladeshFormat();

// Display format for date (DD/MM/YYYY)
const displayDob = ref(props.userProfile?.dob ? formatDateFromISO(props.userProfile.dob) : '');

// Format dates to YYYY-MM-DD for date inputs
const formatDateForInput = (dateStr) => {
    if (!dateStr) return '';
    // If it's already in YYYY-MM-DD format, return as is
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
    // If it's a full datetime, extract the date part
    return (dateStr || '').split(' ')[0];
};

const sameAsPresent = ref(false)

const form = useForm({
    bio: props.userProfile?.bio || '',
    dob: props.userProfile?.dob || '',
    gender: props.userProfile?.gender || '',
    nationality: props.userProfile?.nationality || 'Bangladeshi',
    present_address_line: props.userProfile?.present_address_line || '',
    present_country: props.userProfile?.present_country || 'Bangladesh',
    present_division: props.userProfile?.present_division || '',
    present_district: props.userProfile?.present_district || '',
    present_city: props.userProfile?.present_city || '',
    present_postal_code: props.userProfile?.present_postal_code || '',
    permanent_address_line: props.userProfile?.permanent_address_line || '',
    permanent_country: props.userProfile?.permanent_country || 'Bangladesh',
    permanent_division: props.userProfile?.permanent_division || '',
    permanent_district: props.userProfile?.permanent_district || '',
    permanent_city: props.userProfile?.permanent_city || '',
    permanent_postal_code: props.userProfile?.permanent_postal_code || '',
    nid: props.userProfile?.nid || '',
});

watch(sameAsPresent, (newValue) => {
    if (newValue) {
        form.permanent_address_line = form.present_address_line
        form.permanent_country = form.present_country
        form.permanent_division = form.present_division
        form.permanent_district = form.present_district
        form.permanent_city = form.present_city
        form.permanent_postal_code = form.present_postal_code
    }
})

// Auto-sync permanent address when "same as present" is checked
watch(
    () => [form.present_address_line, form.present_country, form.present_division, form.present_district, form.present_city, form.present_postal_code],
    () => {
        if (sameAsPresent.value) {
            form.permanent_address_line = form.present_address_line
            form.permanent_country = form.present_country
            form.permanent_division = form.present_division
            form.permanent_district = form.present_district
            form.permanent_city = form.present_city
            form.permanent_postal_code = form.present_postal_code
        }
    }
)

// Get districts based on selected division
const presentDistricts = computed(() => {
    return form.present_division ? BANGLADESH_DISTRICTS[form.present_division] || [] : [];
});

const permanentDistricts = computed(() => {
    return form.permanent_division ? BANGLADESH_DISTRICTS[form.permanent_division] || [] : [];
});

// Update form.dob when display format changes
const updateDob = (value) => {
    displayDob.value = value;
    form.dob = parseDateToISO(value);
};

const saveError = ref('');

const submit = () => {
    saveError.value = '';
    
    // Validation: Check required fields
    if (!form.gender) {
        saveError.value = 'Please select your gender.';
        return;
    }
    
    if (!form.nationality) {
        saveError.value = 'Please enter your nationality.';
        return;
    }
    
    form.post(route('profile.update.details'), {
        preserveScroll: true,
        onSuccess: () => {
            if (form.dob) {
                displayDob.value = formatDateFromISO(form.dob);
            }
        },
        onError: (errors) => {
            saveError.value = 'Failed to save profile details. Please check the form and try again.';
            console.error('Save error:', errors);
        }
    });
};
</script>

<template>
    <section class="space-y-6">
        <!-- Section Header -->
        <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                <DocumentTextIcon class="h-6 w-6 text-white" />
            </div>
            <div>
                <h2 class="font-semibold text-lg text-gray-900">Profile Details</h2>
                <p class="text-sm text-gray-500">Personal information, addresses and identification</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Bio -->
            <div class="space-y-2">
                <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                <textarea
                    id="bio"
                    v-model="form.bio"
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none placeholder:text-gray-400"
                    rows="3"
                    placeholder="Tell us about yourself..."
                ></textarea>
                <InputError :message="form.errors.bio" />
            </div>

            <!-- Date of Birth, NID & Gender -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input
                        id="dob"
                        type="text"
                        v-model="displayDob"
                        @blur="updateDob(displayDob)"
                        class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-gray-400"
                        placeholder="DD/MM/YYYY"
                    />
                    <p class="text-xs text-gray-500">Format: DD/MM/YYYY</p>
                    <InputError :message="form.errors.dob" />
                </div>

                <div class="space-y-2">
                    <label for="nid" class="block text-sm font-medium text-gray-700">National ID (NID)</label>
                    <input
                        id="nid"
                        type="text"
                        v-model="form.nid"
                        class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-gray-400"
                        placeholder="10 or 17 digits"
                    />
                    <InputError :message="form.errors.nid" />
                </div>

                <div class="space-y-2">
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender <span class="text-red-500">*</span></label>
                    <select
                        id="gender"
                        v-model="form.gender"
                        class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                    >
                        <option value="">Select Gender</option>
                        <option v-for="gender in GENDER_OPTIONS" :key="gender" :value="gender.toLowerCase()">
                            {{ gender }}
                        </option>
                    </select>
                    <InputError :message="form.errors.gender" />
                </div>
            </div>

            <!-- Nationality -->
            <div class="space-y-2">
                <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality <span class="text-red-500">*</span></label>
                <input
                    id="nationality"
                    v-model="form.nationality"
                    list="nationalities-list"
                    type="text"
                    placeholder="Type to search nationality..."
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-gray-400"
                />
                <datalist id="nationalities-list">
                    <option v-for="country in countries" :key="country.id" :value="country.nationality">
                        {{ country.nationality }}
                    </option>
                </datalist>
                <InputError :message="form.errors.nationality" />
            </div>

            <!-- Present Address -->
            <div class="pt-6 border-t border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h3 class="text-base font-medium text-gray-900">Present Address</h3>
                </div>
                <div class="space-y-4">
                    <!-- Country Selector -->
                    <div class="space-y-2">
                        <label for="present_country" class="block text-sm font-medium text-gray-700">Country</label>
                        <select
                            id="present_country"
                            v-model="form.present_country"
                            @change="form.present_division = ''; form.present_district = ''"
                            class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                        >
                            <option value="">Select Country</option>
                            <option v-for="country in countries" :key="country.id" :value="country.name">
                                {{ country.flag_emoji }} {{ country.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.present_country" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel 
                                for="present_division" 
                                :value="form.present_country === 'Bangladesh' ? 'Division' : 'State/Province/Region'" 
                            />
                            <select
                                v-if="form.present_country === 'Bangladesh'"
                                id="present_division"
                                v-model="form.present_division"
                                @change="form.present_district = ''"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                            >
                                <option value="">Select Division</option>
                                <option v-for="division in BANGLADESH_DIVISIONS" :key="division" :value="division">
                                    {{ division }}
                                </option>
                            </select>
                            <input
                                v-else
                                type="text"
                                id="present_division"
                                v-model="form.present_division"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="Enter state/province/region"
                            />
                            <InputError class="mt-2" :message="form.errors.present_division" />
                        </div>

                        <div>
                            <InputLabel 
                                for="present_district" 
                                :value="form.present_country === 'Bangladesh' ? 'District' : 'City/District'" 
                            />
                            <select
                                v-if="form.present_country === 'Bangladesh' && form.present_division"
                                id="present_district"
                                v-model="form.present_district"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                            >
                                <option value="">Select District</option>
                                <option v-for="district in presentDistricts" :key="district" :value="district">
                                    {{ district }}
                                </option>
                            </select>
                            <input
                                v-else
                                type="text"
                                id="present_district"
                                v-model="form.present_district"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                :placeholder="form.present_country === 'Bangladesh' ? 'Select division first' : 'Enter city/district'"
                                :disabled="form.present_country === 'Bangladesh' && !form.present_division"
                            />
                            <InputError class="mt-2" :message="form.errors.present_district" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="present_city" value="City" />
                            <TextInput
                                id="present_city"
                                v-model="form.present_city"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Enter city"
                            />
                            <InputError class="mt-2" :message="form.errors.present_city" />
                        </div>

                        <div>
                            <InputLabel for="present_postal_code" value="Postal/ZIP Code" />
                            <TextInput
                                id="present_postal_code"
                                v-model="form.present_postal_code"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Enter postal code"
                            />
                            <InputError class="mt-2" :message="form.errors.present_postal_code" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="present_address_line" value="Street Address" class="block text-sm font-semibold text-gray-700 mb-2" />
                        <textarea
                            id="present_address_line"
                            v-model="form.present_address_line"
                            class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
                            rows="2"
                            placeholder="House/Apartment number, Street, Area"
                        />
                        <InputError class="mt-2" :message="form.errors.present_address_line" />
                    </div>
                </div>
            </div>

            <!-- Permanent Address -->
            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-medium text-gray-900">Permanent Address</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="sameAsPresent"
                            type="checkbox"
                            class="w-4 h-4 text-brand-red-600 border-gray-300 rounded focus:ring-brand-red-600"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Same as Present Address</span>
                    </label>
                </div>
                <div class="space-y-4">
                    <!-- Country Selector -->
                    <div>
                        <InputLabel for="permanent_country" value="Country" class="block text-sm font-semibold text-gray-700 mb-2" />
                        <select
                            id="permanent_country"
                            v-model="form.permanent_country"
                            @change="form.permanent_division = ''; form.permanent_district = ''"
                            class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                        >
                            <option value="">Select Country</option>
                            <option v-for="country in countries" :key="country.id" :value="country.name">
                                {{ country.flag_emoji }} {{ country.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.permanent_country" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel 
                                for="permanent_division" 
                                :value="form.permanent_country === 'Bangladesh' ? 'Division' : 'State/Province/Region'" 
                            />
                            <select
                                v-if="form.permanent_country === 'Bangladesh'"
                                id="permanent_division"
                                v-model="form.permanent_division"
                                @change="form.permanent_district = ''"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                            >
                                <option value="">Select Division</option>
                                <option v-for="division in BANGLADESH_DIVISIONS" :key="division" :value="division">
                                    {{ division }}
                                </option>
                            </select>
                            <input
                                v-else
                                type="text"
                                id="permanent_division"
                                v-model="form.permanent_division"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="Enter state/province/region"
                            />
                            <InputError class="mt-2" :message="form.errors.permanent_division" />
                        </div>

                        <div>
                            <InputLabel 
                                for="permanent_district" 
                                :value="form.permanent_country === 'Bangladesh' ? 'District' : 'City/District'" 
                            />
                            <select
                                v-if="form.permanent_country === 'Bangladesh' && form.permanent_division"
                                id="permanent_district"
                                v-model="form.permanent_district"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white"
                            >
                                <option value="">Select District</option>
                                <option v-for="district in permanentDistricts" :key="district" :value="district">
                                    {{ district }}
                                </option>
                            </select>
                            <input
                                v-else
                                type="text"
                                id="permanent_district"
                                v-model="form.permanent_district"
                                class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                :placeholder="form.permanent_country === 'Bangladesh' ? 'Select division first' : 'Enter city/district'"
                                :disabled="form.permanent_country === 'Bangladesh' && !form.permanent_division"
                            />
                            <InputError class="mt-2" :message="form.errors.permanent_district" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="permanent_city" value="City" />
                            <TextInput
                                id="permanent_city"
                                v-model="form.permanent_city"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Enter city"
                            />
                            <InputError class="mt-2" :message="form.errors.permanent_city" />
                        </div>

                        <div>
                            <InputLabel for="permanent_postal_code" value="Postal/ZIP Code" />
                            <TextInput
                                id="permanent_postal_code"
                                v-model="form.permanent_postal_code"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Enter postal code"
                            />
                            <InputError class="mt-2" :message="form.errors.permanent_postal_code" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="permanent_address_line" value="Street Address" class="block text-sm font-semibold text-gray-700 mb-2" />
                        <textarea
                            id="permanent_address_line"
                            v-model="form.permanent_address_line"
                            :disabled="sameAsPresent"
                            class="mt-1 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
                            rows="2"
                            placeholder="House/Apartment number, Street, Area"
                        />
                        <InputError class="mt-2" :message="form.errors.permanent_address_line" />
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Error Message -->
                <div v-if="saveError" class="rounded-md bg-red-50 p-3 md:p-4 text-sm md:text-base">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 md:h-5 md:w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xs md:text-sm font-medium text-red-800">{{ saveError }}</h3>
                        </div>
                        <div class="ml-auto pl-3">
                            <button @click="saveError = ''" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100 touch-manipulation">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Save Button -->
                <div class="hidden md:flex items-center gap-4 pt-4 border-t border-gray-100">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm rounded-xl shadow-sm hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
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
                </div>
        </form>
        
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
        <!-- Mobile Spacer -->
        <div class="md:hidden h-20"></div>
    </section>
</template>
