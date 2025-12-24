<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    medicalInfo: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    blood_group: props.medicalInfo?.blood_group || '',
    allergies: props.medicalInfo?.allergies || '',
    medical_conditions: props.medicalInfo?.medical_conditions || '',
    vaccinations: props.medicalInfo?.vaccinations || [],
    health_insurance_provider: props.medicalInfo?.health_insurance_provider || '',
    health_insurance_policy_number: props.medicalInfo?.health_insurance_policy_number || '',
    health_insurance_expiry_date: props.medicalInfo?.health_insurance_expiry_date || '',
});

const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

const commonVaccinations = [
    { id: 'covid19', label: 'COVID-19', description: 'Required for most international travel' },
    { id: 'yellow_fever', label: 'Yellow Fever', description: 'Required for certain African/South American countries' },
    { id: 'hepatitis_a', label: 'Hepatitis A', description: 'Recommended for most travelers' },
    { id: 'hepatitis_b', label: 'Hepatitis B', description: 'Recommended for healthcare workers' },
    { id: 'typhoid', label: 'Typhoid', description: 'Recommended for South Asia travel' },
    { id: 'cholera', label: 'Cholera', description: 'Required for some countries' },
    { id: 'meningitis', label: 'Meningitis', description: 'Required for Hajj/Umrah' },
    { id: 'polio', label: 'Polio', description: 'Required for some travelers' },
];

const toggleVaccination = (vaccId) => {
    const index = form.vaccinations.findIndex(v => v.id === vaccId);
    if (index > -1) {
        form.vaccinations.splice(index, 1);
    } else {
        const vaccine = commonVaccinations.find(v => v.id === vaccId);
        form.vaccinations.push({
            id: vaccId,
            name: vaccine.label,
            date: new Date().toISOString().split('T')[0]
        });
    }
};

const isVaccinated = (vaccId) => {
    return form.vaccinations.some(v => v.id === vaccId);
};

const getVaccinationDate = (vaccId) => {
    const vacc = form.vaccinations.find(v => v.id === vaccId);
    return vacc?.date || '';
};

const updateVaccinationDate = (vaccId, date) => {
    const vacc = form.vaccinations.find(v => v.id === vaccId);
    if (vacc) {
        vacc.date = date;
    }
};

const submit = () => {
    form.post(route('profile.medical-info.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Medical & Health Information</h3>
                    <p class="text-sm text-gray-500">For emergencies and visa applications</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Privacy Notice -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-growth-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900">Privacy & Confidentiality</h4>
                        <p class="mt-1 text-sm text-blue-800">
                            Your medical information is confidential and will only be shared with authorized parties (medical professionals, immigration authorities) when required and with your consent.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Blood Group -->
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <InputLabel for="blood_group" value="Blood Group *" />
                    <select
                        id="blood_group"
                        v-model="form.blood_group"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                        required
                    >
                        <option value="">Select blood group</option>
                        <option v-for="group in bloodGroups" :key="group" :value="group">
                            {{ group }}
                        </option>
                    </select>
                    <p v-if="form.errors.blood_group" class="mt-1 text-sm text-red-600">
                        {{ form.errors.blood_group }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Critical for emergency medical treatment
                    </p>
                </div>
            </div>

            <!-- Allergies -->
            <div>
                <InputLabel for="allergies" value="Allergies" />
                <textarea
                    id="allergies"
                    v-model="form.allergies"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                    placeholder="List any drug, food, or environmental allergies (e.g., Penicillin, Peanuts, Latex)"
                ></textarea>
                <p v-if="form.errors.allergies" class="mt-1 text-sm text-red-600">
                    {{ form.errors.allergies }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Important for medical treatment abroad
                </p>
            </div>

            <!-- Medical Conditions -->
            <div>
                <InputLabel for="medical_conditions" value="Medical Conditions" />
                <textarea
                    id="medical_conditions"
                    v-model="form.medical_conditions"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600"
                    placeholder="List any chronic conditions, disabilities, or ongoing treatments (e.g., Diabetes, Asthma, Hypertension)"
                ></textarea>
                <p v-if="form.errors.medical_conditions" class="mt-1 text-sm text-red-600">
                    {{ form.errors.medical_conditions }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    May be required for travel insurance and visa applications
                </p>
            </div>

            <!-- Vaccinations -->
            <div>
                <InputLabel value="Vaccination Records" class="mb-3" />
                <div class="space-y-3 rounded-lg border border-gray-200 p-4">
                    <div v-for="vaccine in commonVaccinations" :key="vaccine.id" class="space-y-2">
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input
                                    :id="`vaccine-${vaccine.id}`"
                                    type="checkbox"
                                    :checked="isVaccinated(vaccine.id)"
                                    @change="toggleVaccination(vaccine.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-growth-600 focus:ring-growth-600"
                                />
                            </div>
                            <div class="ml-3 flex-1">
                                <label :for="`vaccine-${vaccine.id}`" class="font-medium text-gray-700">
                                    {{ vaccine.label }}
                                </label>
                                <p class="text-xs text-gray-500">{{ vaccine.description }}</p>
                                
                                <div v-if="isVaccinated(vaccine.id)" class="mt-2">
                                    <input
                                        type="date"
                                        :value="getVaccinationDate(vaccine.id)"
                                        @input="updateVaccinationDate(vaccine.id, $event.target.value)"
                                        class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-growth-600 focus:ring-growth-600 text-sm"
                                        placeholder="Vaccination date"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Select vaccinations you have received and provide dates
                </p>
            </div>

            <!-- Health Insurance -->
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h4 class="mb-4 text-sm font-medium text-gray-900">Health Insurance (Optional)</h4>
                
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <InputLabel for="health_insurance_provider" value="Insurance Provider" />
                        <TextInput
                            id="health_insurance_provider"
                            v-model="form.health_insurance_provider"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g., BRAC Insurance, Delta Life"
                        />
                    </div>

                    <div>
                        <InputLabel for="health_insurance_policy_number" value="Policy Number" />
                        <TextInput
                            id="health_insurance_policy_number"
                            v-model="form.health_insurance_policy_number"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Policy/Certificate number"
                        />
                    </div>

                    <div>
                        <InputLabel for="health_insurance_expiry_date" value="Expiry Date" />
                        <TextInput
                            id="health_insurance_expiry_date"
                            v-model="form.health_insurance_expiry_date"
                            type="date"
                            class="mt-1 block w-full"
                        />
                    </div>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <h4 class="mb-2 flex items-center gap-2 text-sm font-medium text-amber-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Important Reminders
                </h4>
                <ul class="space-y-1 text-sm text-amber-800">
                    <li>• Keep vaccination certificates handy when traveling</li>
                    <li>• Some countries require specific vaccinations (check destination requirements)</li>
                    <li>• Travel insurance often requires disclosure of pre-existing conditions</li>
                    <li>• Medical records may be requested during visa application</li>
                    <li>• Update this information regularly, especially vaccination dates</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                    ✓ Medical information saved successfully!
                </p>
                <div class="ml-auto">
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Medical Information' }}
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </div>
</template>
