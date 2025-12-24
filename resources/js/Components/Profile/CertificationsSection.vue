<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    PlusIcon,
    TrashIcon,
    CheckBadgeIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    LinkIcon,
    CalendarDaysIcon,
    BuildingOfficeIcon,
    AcademicCapIcon,
    TruckIcon,
    ComputerDesktopIcon,
    LanguageIcon,
    DocumentTextIcon,
    SparklesIcon,
    ShieldCheckIcon,
    ArrowTopRightOnSquareIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon, StarIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    certifications: {
        type: [Object, Array],
        default: () => ({})
    }
});

// Properly extract certifications array from props
const getCertificationsArray = () => {
    // Handle different possible structures
    if (Array.isArray(props.certifications)) {
        return props.certifications;
    }
    if (props.certifications?.certifications && Array.isArray(props.certifications.certifications)) {
        return props.certifications.certifications;
    }
    if (props.certifications && typeof props.certifications === 'object') {
        // It's userProfile - check for certifications property
        const certs = props.certifications.certifications;
        if (Array.isArray(certs)) {
            return certs;
        }
    }
    return [];
};

const form = useForm({
    certifications: getCertificationsArray()
});

// Watch for prop changes
watch(() => props.certifications, () => {
    form.certifications = getCertificationsArray();
}, { deep: true });

const certificationTypes = [
    { 
        value: 'professional', 
        label: 'Professional Certification', 
        icon: AcademicCapIcon,
        color: 'indigo',
        gradient: 'from-indigo-500 to-purple-600',
        description: 'Industry certifications (PMP, CPA, etc.)' 
    },
    { 
        value: 'trade', 
        label: 'Trade License', 
        icon: BuildingOfficeIcon,
        color: 'emerald',
        gradient: 'from-emerald-500 to-teal-600',
        description: 'Business or trade licenses' 
    },
    { 
        value: 'driving', 
        label: 'Driving License', 
        icon: TruckIcon,
        color: 'blue',
        gradient: 'from-blue-500 to-cyan-600',
        description: 'Vehicle operation permits' 
    },
    { 
        value: 'technical', 
        label: 'Technical Certification', 
        icon: ComputerDesktopIcon,
        color: 'violet',
        gradient: 'from-violet-500 to-purple-600',
        description: 'IT, software, technical skills' 
    },
    { 
        value: 'language', 
        label: 'Language Proficiency', 
        icon: LanguageIcon,
        color: 'rose',
        gradient: 'from-rose-500 to-pink-600',
        description: 'IELTS, TOEFL, etc.' 
    },
    { 
        value: 'other', 
        label: 'Other License/Permit', 
        icon: DocumentTextIcon,
        color: 'gray',
        gradient: 'from-gray-500 to-gray-600',
        description: 'Any other certification' 
    },
];

const showTypeSelector = ref(false);

const addCertification = (type = 'professional') => {
    form.certifications.push({
        id: Date.now(),
        type: type,
        name: '',
        issuing_organization: '',
        issue_date: '',
        expiry_date: '',
        credential_id: '',
        credential_url: '',
        never_expires: false
    });
    showTypeSelector.value = false;
};

const removeCertification = (index) => {
    form.certifications.splice(index, 1);
};

const submit = () => {
    form.post(route('profile.certifications.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Form will show success message via recentlySuccessful
        },
        onError: (errors) => {
            console.error('Certification save errors:', errors);
        }
    });
};

const getTypeInfo = (type) => {
    return certificationTypes.find(t => t.value === type) || certificationTypes[0];
};

const isExpired = (cert) => {
    if (cert.never_expires || !cert.expiry_date) return false;
    return new Date(cert.expiry_date) < new Date();
};

const isExpiringSoon = (cert) => {
    if (cert.never_expires || !cert.expiry_date) return false;
    const thirtyDaysFromNow = new Date();
    thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
    const expiryDate = new Date(cert.expiry_date);
    return expiryDate > new Date() && expiryDate <= thirtyDaysFromNow;
};

const certificationsByType = computed(() => {
    return certificationTypes.map(type => ({
        ...type,
        count: form.certifications.filter(c => c.type === type.value).length
    })).filter(t => t.count > 0);
});

const expiredCount = computed(() => 
    form.certifications.filter(c => isExpired(c)).length
);

const expiringSoonCount = computed(() => 
    form.certifications.filter(c => isExpiringSoon(c)).length
);

const validCount = computed(() => 
    form.certifications.filter(c => !isExpired(c) && !isExpiringSoon(c)).length
);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Color classes mapping to avoid Tailwind purge issues
const colorClasses = {
    indigo: {
        bg: 'bg-indigo-50',
        border: 'border-indigo-500',
        text: 'text-indigo-700',
        icon: 'text-indigo-500',
        badge: 'bg-indigo-100 text-indigo-700'
    },
    emerald: {
        bg: 'bg-emerald-50',
        border: 'border-emerald-500',
        text: 'text-emerald-700',
        icon: 'text-emerald-500',
        badge: 'bg-emerald-100 text-emerald-700'
    },
    blue: {
        bg: 'bg-blue-50',
        border: 'border-blue-500',
        text: 'text-blue-700',
        icon: 'text-blue-500',
        badge: 'bg-blue-100 text-blue-700'
    },
    violet: {
        bg: 'bg-violet-50',
        border: 'border-violet-500',
        text: 'text-violet-700',
        icon: 'text-violet-500',
        badge: 'bg-violet-100 text-violet-700'
    },
    rose: {
        bg: 'bg-rose-50',
        border: 'border-rose-500',
        text: 'text-rose-700',
        icon: 'text-rose-500',
        badge: 'bg-rose-100 text-rose-700'
    },
    gray: {
        bg: 'bg-gray-50',
        border: 'border-gray-500',
        text: 'text-gray-700',
        icon: 'text-gray-500',
        badge: 'bg-gray-100 text-gray-700'
    }
};

const getColorClasses = (color) => colorClasses[color] || colorClasses.gray;
</script>

<template>
    <div class="space-y-8">
        <!-- Modern Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 p-8 text-white">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 80 80">
                    <pattern id="cert-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="10" cy="10" r="1.5" fill="currentColor"/>
                    </pattern>
                    <rect fill="url(#cert-pattern)" width="100%" height="100%"/>
                </svg>
            </div>
            
            <!-- Floating badges -->
            <div class="absolute top-4 right-4 flex gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-white/20 backdrop-blur-sm px-3 py-1 text-xs font-medium">
                    <CheckBadgeIcon class="h-3.5 w-3.5" />
                    {{ form.certifications.length }} Total
                </span>
                <span v-if="validCount > 0" class="inline-flex items-center gap-1 rounded-full bg-green-400/30 backdrop-blur-sm px-3 py-1 text-xs font-medium">
                    <CheckCircleIcon class="h-3.5 w-3.5" />
                    {{ validCount }} Active
                </span>
            </div>
            
            <div class="relative flex items-start gap-5">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                    <CheckBadgeIcon class="h-8 w-8 text-white" />
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold">Certifications & Licenses</h2>
                    <p class="mt-1 text-white/80">Professional certifications, trade licenses, and credentials that strengthen your profile</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Alert Cards -->
            <div v-if="expiredCount > 0 || expiringSoonCount > 0" class="grid gap-4 sm:grid-cols-2">
                <div v-if="expiredCount > 0" class="rounded-2xl border-2 border-red-200 bg-gradient-to-br from-red-50 to-red-100 p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500 text-white shadow-lg shadow-red-500/30">
                            <ExclamationTriangleIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-red-900">{{ expiredCount }} Expired</h4>
                            <p class="mt-1 text-sm text-red-700">Renew to maintain compliance</p>
                        </div>
                    </div>
                </div>

                <div v-if="expiringSoonCount > 0" class="rounded-2xl border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-100 p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/30">
                            <ClockIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-amber-900">{{ expiringSoonCount }} Expiring Soon</h4>
                            <p class="mt-1 text-sm text-amber-700">Within the next 30 days</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats by Type -->
            <div v-if="certificationsByType.length > 0" class="flex flex-wrap gap-3">
                <div
                    v-for="type in certificationsByType"
                    :key="type.value"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-sm border border-gray-200"
                >
                    <component :is="type.icon" :class="[getColorClasses(type.color).icon, 'h-5 w-5']" />
                    <span class="text-sm font-medium text-gray-700">{{ type.label }}</span>
                    <span :class="[getColorClasses(type.color).badge, 'rounded-full px-2 py-0.5 text-xs font-bold']">
                        {{ type.count }}
                    </span>
                </div>
            </div>

            <!-- Certifications List -->
            <div class="space-y-4">
                <div
                    v-for="(cert, index) in form.certifications"
                    :key="cert.id || index"
                    class="group relative overflow-hidden rounded-2xl border-2 bg-white shadow-sm transition-all duration-300 hover:shadow-lg"
                    :class="{
                        'border-red-300': isExpired(cert),
                        'border-amber-300': isExpiringSoon(cert) && !isExpired(cert),
                        'border-gray-200 hover:border-growth-300': !isExpired(cert) && !isExpiringSoon(cert)
                    }"
                >
                    <!-- Status Banner -->
                    <div 
                        v-if="isExpired(cert) || isExpiringSoon(cert)"
                        :class="[
                            'px-4 py-2 text-sm font-semibold',
                            isExpired(cert) ? 'bg-red-500 text-white' : 'bg-amber-400 text-amber-900'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <ExclamationTriangleIcon v-if="isExpired(cert)" class="h-4 w-4" />
                            <ClockIcon v-else class="h-4 w-4" />
                            <span>{{ isExpired(cert) ? 'EXPIRED' : 'EXPIRING SOON' }}</span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <!-- Header with Type Icon -->
                        <div class="mb-6 flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="[
                                    'flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br shadow-lg',
                                    getTypeInfo(cert.type).gradient
                                ]">
                                    <component :is="getTypeInfo(cert.type).icon" class="h-7 w-7 text-white" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">
                                        {{ cert.name || `New ${getTypeInfo(cert.type).label}` }}
                                    </h4>
                                    <p class="text-sm text-gray-500">{{ getTypeInfo(cert.type).label }}</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="removeCertification(index)"
                                class="rounded-xl p-2.5 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-all"
                            >
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Form Fields -->
                        <div class="space-y-5">
                            <!-- Certification Type -->
                            <div>
                                <label :for="`type-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Certification Type <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <button
                                        v-for="type in certificationTypes"
                                        :key="type.value"
                                        type="button"
                                        @click="cert.type = type.value"
                                        :class="[
                                            'flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition-all border-2',
                                            cert.type === type.value 
                                                ? [getColorClasses(type.color).bg, getColorClasses(type.color).border, getColorClasses(type.color).text]
                                                : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'
                                        ]"
                                    >
                                        <component :is="type.icon" class="h-4 w-4" />
                                        <span class="truncate">{{ type.label.split(' ')[0] }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Name and Organization -->
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label :for="`name-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Certification Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        :id="`name-${index}`"
                                        v-model="cert.name"
                                        type="text"
                                        required
                                        placeholder="e.g., PMP, AWS Certified, IELTS"
                                        class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 transition-colors focus:border-growth-500 focus:ring-0"
                                    />
                                </div>

                                <div>
                                    <label :for="`org-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Issuing Organization <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        :id="`org-${index}`"
                                        v-model="cert.issuing_organization"
                                        type="text"
                                        required
                                        placeholder="e.g., PMI, AWS, British Council"
                                        class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 transition-colors focus:border-growth-500 focus:ring-0"
                                    />
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label :for="`issue-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <CalendarDaysIcon class="inline h-4 w-4 mr-1" />
                                        Issue Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        :id="`issue-${index}`"
                                        v-model="cert.issue_date"
                                        type="date"
                                        required
                                        class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-gray-900 transition-colors focus:border-growth-500 focus:ring-0"
                                    />
                                </div>

                                <div>
                                    <label :for="`expiry-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <ClockIcon class="inline h-4 w-4 mr-1" />
                                        Expiry Date
                                    </label>
                                    <input
                                        :id="`expiry-${index}`"
                                        v-model="cert.expiry_date"
                                        type="date"
                                        :disabled="cert.never_expires"
                                        :class="[
                                            'w-full rounded-xl border-2 px-4 py-3 transition-colors focus:ring-0',
                                            cert.never_expires 
                                                ? 'border-gray-100 bg-gray-50 text-gray-400 cursor-not-allowed' 
                                                : 'border-gray-200 text-gray-900 focus:border-growth-500'
                                        ]"
                                    />
                                </div>

                                <div class="flex items-end pb-1">
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input
                                            :id="`never-${index}`"
                                            v-model="cert.never_expires"
                                            type="checkbox"
                                            class="sr-only peer"
                                            @change="cert.never_expires ? cert.expiry_date = '' : null"
                                        />
                                        <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-growth-500 peer-focus:ring-4 peer-focus:ring-growth-100 transition-colors"></div>
                                        <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-700">Never expires</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Credential Details -->
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label :for="`credential-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <ShieldCheckIcon class="inline h-4 w-4 mr-1" />
                                        Credential ID/Number
                                    </label>
                                    <input
                                        :id="`credential-${index}`"
                                        v-model="cert.credential_id"
                                        type="text"
                                        placeholder="Certificate or license number"
                                        class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 transition-colors focus:border-growth-500 focus:ring-0"
                                    />
                                </div>

                                <div>
                                    <label :for="`url-${index}`" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <LinkIcon class="inline h-4 w-4 mr-1" />
                                        Verification URL
                                    </label>
                                    <div class="relative">
                                        <input
                                            :id="`url-${index}`"
                                            v-model="cert.credential_url"
                                            type="url"
                                            placeholder="https://verify.example.com/..."
                                            class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-growth-500 focus:ring-0"
                                        />
                                        <a
                                            v-if="cert.credential_url"
                                            :href="cert.credential_url"
                                            target="_blank"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-growth-600 hover:text-growth-700"
                                        >
                                            <ArrowTopRightOnSquareIcon class="h-5 w-5" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Certification Section -->
            <div v-if="showTypeSelector" class="rounded-2xl border-2 border-dashed border-growth-300 bg-growth-50/50 p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-4">Choose Certification Type</h4>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <button
                        v-for="type in certificationTypes"
                        :key="type.value"
                        type="button"
                        @click="addCertification(type.value)"
                        class="flex items-center gap-4 rounded-xl border-2 border-gray-200 bg-white p-4 text-left transition-all hover:border-growth-400 hover:shadow-md"
                    >
                        <div :class="['flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br shadow-md', type.gradient]">
                            <component :is="type.icon" class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">{{ type.label }}</div>
                            <div class="text-xs text-gray-500">{{ type.description }}</div>
                        </div>
                    </button>
                </div>
                <button
                    type="button"
                    @click="showTypeSelector = false"
                    class="mt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                >
                    Cancel
                </button>
            </div>

            <div v-else class="flex justify-center">
                <button
                    type="button"
                    @click="showTypeSelector = true"
                    class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-growth-600 to-emerald-600 px-6 py-4 text-white font-semibold shadow-lg shadow-growth-500/30 hover:shadow-xl hover:shadow-growth-500/40 transition-all"
                >
                    <PlusIcon class="h-5 w-5" />
                    Add Certification
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="form.certifications.length === 0" class="rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/50 p-12 text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-rose-100 to-pink-100">
                    <CheckBadgeIcon class="h-10 w-10 text-rose-500" />
                </div>
                <h3 class="mt-6 text-xl font-bold text-gray-900">No certifications added yet</h3>
                <p class="mt-2 text-gray-500 max-w-md mx-auto">
                    Add your professional certifications, licenses, and credentials to strengthen your profile and stand out to employers.
                </p>
                <button
                    type="button"
                    @click="showTypeSelector = true"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 px-6 py-3 text-white font-semibold shadow-lg shadow-rose-500/30 hover:shadow-xl transition-all"
                >
                    <PlusIcon class="h-5 w-5" />
                    Add Your First Certification
                </button>
            </div>

            <!-- Tips Section -->
            <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500 text-white shadow-lg">
                        <SparklesIcon class="h-6 w-6" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-blue-900">Pro Tips</h4>
                        <ul class="mt-3 space-y-2 text-sm text-blue-800">
                            <li class="flex items-start gap-2">
                                <CheckCircleIcon class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" />
                                <span>Include all relevant professional and technical certifications</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <CheckCircleIcon class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" />
                                <span>Keep expiry dates updated for time-sensitive credentials</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <CheckCircleIcon class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" />
                                <span>Trade licenses may be required for visa applications</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <CheckCircleIcon class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" />
                                <span>Add verification URLs for authenticity when available</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Popular Certifications -->
            <div class="rounded-2xl bg-white border border-gray-200 p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <StarIcon class="h-5 w-5 text-amber-500" />
                    Popular Certifications by Category
                </h4>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="type in certificationTypes.slice(0, 5)" :key="type.value" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <component :is="type.icon" :class="[getColorClasses(type.color).icon, 'h-5 w-5']" />
                            <h5 class="text-sm font-bold text-gray-900">{{ type.label }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 space-y-1 pl-7">
                            <li v-if="type.value === 'professional'">• PMP (Project Management)</li>
                            <li v-if="type.value === 'professional'">• CPA (Accounting)</li>
                            <li v-if="type.value === 'professional'">• Six Sigma Certifications</li>
                            <li v-if="type.value === 'trade'">• Business Trade License</li>
                            <li v-if="type.value === 'trade'">• Import/Export License</li>
                            <li v-if="type.value === 'trade'">• Industry-specific Permits</li>
                            <li v-if="type.value === 'driving'">• Driving License (Car)</li>
                            <li v-if="type.value === 'driving'">• International Driving Permit</li>
                            <li v-if="type.value === 'driving'">• Commercial Vehicle License</li>
                            <li v-if="type.value === 'technical'">• AWS/Azure/GCP Certified</li>
                            <li v-if="type.value === 'technical'">• CCNA/CCNP (Networking)</li>
                            <li v-if="type.value === 'technical'">• CISSP (Cybersecurity)</li>
                            <li v-if="type.value === 'language'">• IELTS (English)</li>
                            <li v-if="type.value === 'language'">• TOEFL (English)</li>
                            <li v-if="type.value === 'language'">• HSK (Chinese)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="form.certifications.length > 0" class="sticky bottom-0 -mx-4 -mb-4 bg-white/95 backdrop-blur-sm border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between max-w-3xl mx-auto">
                    <div class="flex items-center gap-3">
                        <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-green-600">
                            <CheckCircleIcon class="h-5 w-5" />
                            <span class="text-sm font-medium">Saved successfully!</span>
                        </div>
                        <div v-else-if="form.hasErrors" class="flex items-center gap-2 text-red-600">
                            <ExclamationTriangleIcon class="h-5 w-5" />
                            <span class="text-sm font-medium">Please check your input</span>
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-growth-600 to-emerald-600 px-8 py-3 text-white font-bold shadow-lg shadow-growth-500/30 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Saving...
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <CheckBadgeIcon class="h-5 w-5" />
                            Save Certifications
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
