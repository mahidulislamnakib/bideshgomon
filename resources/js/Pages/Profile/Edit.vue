<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { useProfileCompletion } from '@/Composables/useProfileCompletion';
import RhythmicCard from '@/Components/Rhythmic/RhythmicCard.vue';
import ProgressWave from '@/Components/Rhythmic/ProgressWave.vue';
import { 
    CheckCircleIcon, 
    ExclamationCircleIcon,
    UserCircleIcon,
    DocumentTextIcon,
    AcademicCapIcon,
    BriefcaseIcon,
    SparklesIcon,
    GlobeAltIcon,
    UsersIcon,
    BanknotesIcon,
    LanguageIcon,
    ShieldCheckIcon,
    LockClosedIcon,
    TrashIcon,
    ChevronRightIcon,
    PhoneIcon,
    Cog6ToothIcon,
    HeartIcon,
    ClipboardDocumentCheckIcon,
    BuildingLibraryIcon,
    ChatBubbleLeftRightIcon,
    DocumentDuplicateIcon,
    FolderOpenIcon,
    KeyIcon,
    EyeIcon,
    AdjustmentsHorizontalIcon
} from '@heroicons/vue/24/solid';

// Import existing components
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateProfileDetailsForm from './Partials/UpdateProfileDetailsForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import FamilySection from './Partials/FamilySection.vue';
import FinancialSection from './Partials/FinancialSection.vue';
import LanguagesSection from './Partials/LanguagesSection.vue';
import SecuritySection from './Partials/SecuritySection.vue';
import EducationSection from './Partials/EducationSection.vue';
import WorkExperienceSection from './Partials/WorkExperienceSection.vue';
import SkillsSection from './Partials/SkillsSection.vue';
import TravelHistorySection from './Partials/TravelHistorySection.vue';
import PhoneNumbersSection from './Partials/PhoneNumbersSection.vue';
import SocialLinksSection from '@/Components/Profile/SocialLinksSection.vue';
import EmergencyContactSection from '@/Components/Profile/EmergencyContactSection.vue';
import MedicalInformationSection from '@/Components/Profile/MedicalInformationSection.vue';
import ReferencesSection from '@/Components/Profile/ReferencesSection.vue';
import CertificationsSection from '@/Components/Profile/CertificationsSection.vue';
import ProfileCompletenessTracker from '@/Components/Profile/ProfileCompletenessTracker.vue';
import PrivacyDataControl from '@/Components/Profile/PrivacyDataControl.vue';
import PreferencesSettings from '@/Components/Profile/PreferencesSettings.vue';
import DocumentsManagement from '@/Components/Profile/DocumentsManagement.vue';
import PassportManagement from '@/Components/Profile/PassportManagement.vue';
import VisaHistoryManagement from '@/Components/Profile/VisaHistoryManagement.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
    userProfile: Object,
    familyMembers: { type: Array, default: () => [] },
    userLanguages: { type: Array, default: () => [] },
    languages: { type: Array, default: () => [] },
    languageTests: { type: Array, default: () => [] },
    securityInformation: Object,
    educations: { type: Array, default: () => [] },
    workExperiences: { type: Array, default: () => [] },
    skills: { type: Array, default: () => [] },
    travelHistory: { type: Array, default: () => [] },
    phoneNumbers: { type: Array, default: () => [] },
    passports: { type: Array, default: () => [] },
    visaHistory: { type: Array, default: () => [] },
    userDocuments: { type: Array, default: () => [] },
    divisions: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    degrees: { type: Array, default: () => [] },
    serviceCategories: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
    profileCompletion: { type: Object, default: () => ({ overall: 0, sections: {} }) },
    section: String, // Section from URL query parameter
});

// Active section management - null means card view, otherwise editing a specific section
const activeSection = ref(null);

// Mobile detection
const isMobile = ref(false);
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Auto-open section from URL on desktop, keep collapsed on mobile
    if (props.section && !isMobile.value) {
        activeSection.value = props.section;
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const sectionParam = urlParams.get('section');
        
        if (sectionParam && sections.find(s => s.id === sectionParam)) {
            activeSection.value = sectionParam;
        } else {
            activeSection.value = null;
        }
    });
});

// Watch for section prop changes (when URL changes)
watch(() => props.section, (newSection) => {
    if (newSection && sections.find(s => s.id === newSection)) {
        activeSection.value = newSection;
    } else if (!newSection) {
        activeSection.value = null;
    }
});

// Profile completion tracking - use backend calculation for accuracy
const completion = computed(() => {
    const backendCompletion = props.profileCompletion?.overall || 0;
    const sections = props.profileCompletion?.sections || {};
    
    // Count completed sections (those marked as completed in backend)
    const completedCount = Object.values(sections).filter(s => s.completed).length;
    const totalSections = Object.keys(sections).length;
    
    return {
        percentage: backendCompletion,
        completed: completedCount,
        total: totalSections,
        sections: sections,
        isComplete: backendCompletion === 100,
    };
});

const getCompletionColor = (percentage) => {
    if (percentage >= 80) return 'text-green-600';
    if (percentage >= 50) return 'text-yellow-600';
    return 'text-orange-600';
};

const getCompletionBgColor = (percentage) => {
    if (percentage >= 80) return 'bg-green-600';
    if (percentage >= 50) return 'bg-yellow-600';
    return 'bg-orange-600';
};

// Calculate completion per section for dynamic card colors
const getSectionCompletion = (sectionId) => {
    const user = props.user;
    const profile = props.userProfile;
    
    const completionMap = {
        'basic': () => {
            let completed = 0;
            let total = 4;
            if (profile?.first_name) completed++;
            if (profile?.last_name) completed++;
            if (profile?.name_as_per_passport) completed++;
            if (user?.email) completed++;
            return Math.round((completed / total) * 100);
        },
        'profile': () => {
            let completed = 0;
            let total = 5;
            if (profile?.date_of_birth) completed++;
            if (profile?.gender) completed++;
            if (profile?.nationality) completed++;
            if (profile?.nid) completed++;
            if (profile?.present_address_line && profile?.present_division) completed++;
            return Math.round((completed / total) * 100);
        },
        'phone': () => {
            return props.phoneNumbers?.length > 0 ? 100 : 0;
        },
        'social': () => {
            let completed = 0;
            let total = 4;
            if (profile?.facebook_url) completed++;
            if (profile?.linkedin_url) completed++;
            if (profile?.whatsapp_number) completed++;
            if (profile?.telegram_username) completed++;
            return Math.round((completed / total) * 100);
        },
        'education': () => {
            return props.educations?.length > 0 ? 100 : 0;
        },
        'experience': () => {
            return props.workExperiences?.length > 0 ? 100 : 0;
        },
        'skills': () => {
            return props.skills?.length > 0 ? 100 : 0;
        },
        'languages': () => {
            return props.userLanguages?.length > 0 ? 100 : 0;
        },
        'certifications': () => {
            // Check if certifications data exists in profile
            return profile?.certifications?.length > 0 ? 100 : 0;
        },
        'references': () => {
            // Check if references data exists in profile
            return profile?.references?.length > 0 ? 100 : 0;
        },
        'emergency': () => {
            let completed = 0;
            let total = 3;
            if (profile?.emergency_contact_name) completed++;
            if (profile?.emergency_contact_phone) completed++;
            if (profile?.emergency_contact_relationship) completed++;
            return Math.round((completed / total) * 100);
        },
        'medical': () => {
            let completed = 0;
            let total = 4;
            if (profile?.blood_group) completed++;
            if (profile?.medical_conditions) completed++;
            if (profile?.vaccinations) completed++;
            if (profile?.health_insurance_number) completed++;
            return Math.round((completed / total) * 100);
        },
        'travel': () => {
            return props.travelHistory?.length > 0 ? 100 : 0;
        },
        'documents': () => {
            return props.userDocuments?.length > 0 ? 100 : 0;
        },
        'family': () => {
            return props.familyMembers?.length > 0 ? 100 : 0;
        },
        'financial': () => {
            let completed = 0;
            let total = 3;
            if (profile?.monthly_income_bdt) completed++;
            if (profile?.bank_account_number) completed++;
            if (profile?.bank_name) completed++;
            return Math.round((completed / total) * 100);
        },
        'security': () => {
            return props.securityInformation ? 100 : 0;
        },
        'passports': () => {
            return props.passports?.length > 0 ? 100 : 0;
        },
        'visa-history': () => {
            return props.visaHistory?.length > 0 ? 100 : 0;
        },
        'completeness': () => completion.value.percentage,
        'privacy': () => 50, // Default for settings sections
        'preferences': () => 50,
        'password': () => 100, // Always complete if account exists
        'delete': () => 100
    };
    
    const calculator = completionMap[sectionId];
    return calculator ? calculator() : 50;
};

// Get dynamic solid color based on completion
const getSectionGradient = (sectionId) => {
    const percentage = getSectionCompletion(sectionId);
    
    if (percentage >= 80) {
        return 'bg-green-600'; // Complete
    } else if (percentage >= 50) {
        return 'bg-yellow-600'; // Partial
    } else if (percentage > 0) {
        return 'bg-orange-600'; // Started but incomplete
    } else {
        return 'bg-gray-500'; // Not started
    }
};

// Get dynamic hover border color
const getSectionBorderColor = (sectionId) => {
    const percentage = getSectionCompletion(sectionId);
    
    if (percentage >= 80) {
        return 'hover:border-green-500 dark:hover:border-green-400';
    } else if (percentage >= 50) {
        return 'hover:border-yellow-500 dark:hover:border-yellow-400';
    } else if (percentage > 0) {
        return 'hover:border-orange-500 dark:hover:border-orange-400';
    } else {
        return 'hover:border-gray-500 dark:hover:border-gray-400';
    }
};

// Get dynamic text hover color
const getSectionTextColor = (sectionId) => {
    const percentage = getSectionCompletion(sectionId);
    
    if (percentage >= 80) {
        return 'group-hover:text-green-600 dark:group-hover:text-green-400';
    } else if (percentage >= 50) {
        return 'group-hover:text-yellow-600 dark:group-hover:text-yellow-400';
    } else if (percentage > 0) {
        return 'group-hover:text-orange-600 dark:group-hover:text-orange-400';
    } else {
        return 'group-hover:text-gray-600 dark:group-hover:text-gray-400';
    }
};

const sections = [
    // Personal Information
    { 
        id: 'basic', 
        name: 'Basic Information', 
        icon: UserCircleIcon, 
        description: 'Name, email, and contact information',
        category: 'personal' 
    },
    { 
        id: 'profile', 
        name: 'Profile Details', 
        icon: ClipboardDocumentCheckIcon, 
        description: 'Address, NID, passport, and personal details',
        category: 'personal' 
    },
    { 
        id: 'phone', 
        name: 'Phone Numbers', 
        icon: PhoneIcon, 
        description: 'Manage and verify your phone numbers',
        category: 'personal' 
    },
    { 
        id: 'social', 
        name: 'Social Media & Contact', 
        icon: ChatBubbleLeftRightIcon, 
        description: 'Social profiles and additional contact methods',
        category: 'personal' 
    },
    
    // Professional Profile
    { 
        id: 'education', 
        name: 'Education & Qualifications', 
        icon: AcademicCapIcon, 
        description: 'Academic history and qualifications',
        category: 'professional' 
    },
    { 
        id: 'experience', 
        name: 'Work Experience', 
        icon: BriefcaseIcon, 
        description: 'Employment history and career details',
        category: 'professional' 
    },
    { 
        id: 'skills', 
        name: 'Skills & Expertise', 
        icon: SparklesIcon, 
        description: 'Professional skills and competencies',
        category: 'professional' 
    },
    { 
        id: 'languages', 
        name: 'Language Proficiency', 
        icon: LanguageIcon, 
        description: 'Languages and proficiency levels',
        category: 'professional' 
    },
    { 
        id: 'certifications', 
        name: 'Certifications & Licenses', 
        icon: ClipboardDocumentCheckIcon, 
        description: 'Professional certifications, licenses, and credentials',
        category: 'professional' 
    },
    { 
        id: 'references', 
        name: 'References', 
        icon: UsersIcon, 
        description: 'Professional, academic, and personal references',
        category: 'professional' 
    },
    
    // Safety & Health
    { 
        id: 'emergency', 
        name: 'Emergency Contact', 
        icon: HeartIcon, 
        description: 'Emergency contact person details',
        category: 'safety' 
    },
    { 
        id: 'medical', 
        name: 'Medical Information', 
        icon: ShieldCheckIcon, 
        description: 'Health records, vaccinations, and insurance',
        category: 'safety' 
    },
    
    // Immigration & Documents
    { 
        id: 'travel', 
        name: 'Travel History', 
        icon: GlobeAltIcon, 
        description: 'International travel records',
        category: 'immigration' 
    },
    { 
        id: 'passports', 
        name: 'Passport Management', 
        icon: DocumentTextIcon, 
        description: 'Manage passport details and validity',
        category: 'immigration' 
    },
    { 
        id: 'visa-history', 
        name: 'Visa History', 
        icon: DocumentDuplicateIcon, 
        description: 'Track visa applications and status',
        category: 'immigration' 
    },
    { 
        id: 'documents', 
        name: 'Documents Management', 
        icon: FolderOpenIcon, 
        description: 'Upload and manage important documents',
        category: 'immigration' 
    },
    
    // Family & Financial
    { 
        id: 'family', 
        name: 'Family Information', 
        icon: UsersIcon, 
        description: 'Family members and relationships',
        category: 'family-financial' 
    },
    { 
        id: 'financial', 
        name: 'Financial Information', 
        icon: BanknotesIcon, 
        description: 'Income, assets, and financial details',
        category: 'family-financial' 
    },
    
    // Background & Security
    { 
        id: 'security', 
        name: 'Background Check', 
        icon: BuildingLibraryIcon, 
        description: 'Criminal records and police clearance',
        category: 'background' 
    },
    
    // Account & Settings
    { 
        id: 'completeness', 
        name: 'Profile Completeness', 
        icon: CheckCircleIcon, 
        description: 'Track your profile completion progress',
        category: 'settings' 
    },
    { 
        id: 'public-profile', 
        name: 'Public Profile & Sharing', 
        icon: UserCircleIcon, 
        description: 'Share your profile and generate QR code',
        category: 'settings' 
    },
    { 
        id: 'privacy', 
        name: 'Privacy & Data Control', 
        icon: EyeIcon, 
        description: 'Privacy settings and data control',
        category: 'settings' 
    },
    { 
        id: 'preferences', 
        name: 'Preferences & Settings', 
        icon: AdjustmentsHorizontalIcon, 
        description: 'Personalize your experience',
        category: 'settings' 
    },
    { 
        id: 'password', 
        name: 'Change Password', 
        icon: KeyIcon, 
        description: 'Update your account password',
        category: 'settings' 
    },
    { 
        id: 'delete', 
        name: 'Delete Account', 
        icon: TrashIcon, 
        description: 'Permanently delete your account',
        category: 'settings' 
    },
];

const currentSection = computed(() => sections.find(s => s.id === activeSection.value));

// Function to change active section
const changeSection = (sectionId) => {
    activeSection.value = sectionId;
    
    // Update URL without full page reload (just update browser history)
    const url = route('profile.edit', { section: sectionId });
    window.history.replaceState(window.history.state, '', url);
    
    // Scroll to top on mobile
    if (window.innerWidth < 1024) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

// Function to go back to card view
const backToCards = () => {
    activeSection.value = null;
    
    // Update URL without full page reload
    const url = route('profile.edit');
    window.history.replaceState(window.history.state, '', url);
};


</script>

<template>
    <Head title="Edit Profile" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Hero Header Section -->
            <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-2xl backdrop-blur-sm flex items-center justify-center">
                                <UserCircleIcon class="h-8 w-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-white">
                                    {{ activeSection ? currentSection?.name : 'Profile Settings' }}
                                </h1>
                                <p class="text-white/80 text-sm mt-1">
                                    {{ activeSection ? currentSection?.description : 'Manage your complete profile across 25 sections' }}
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="activeSection && !isMobile"
                            @click="backToCards"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold text-sm backdrop-blur-sm transition-all border border-white/20"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back to Sections
                        </button>
                    </div>
                </div>
            </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
                <!-- Mobile Back Button (Sticky) -->
                <div 
                    v-if="activeSection && isMobile"
                    class="sticky top-0 z-20 bg-white border-b border-gray-200 -mx-4 px-4 py-3 mb-4 shadow-sm"
                >
                    <button
                        @click="backToCards"
                        class="flex items-center gap-2 text-indigo-600 font-semibold text-sm active:scale-95 transition-transform"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to All Sections
                    </button>
                </div>

                <!-- Profile Completion Progress -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div :class="[
                                    'w-11 h-11 rounded-xl flex items-center justify-center',
                                    completion.isComplete ? 'bg-green-100' : 'bg-amber-100'
                                ]">
                                    <CheckCircleIcon v-if="completion.isComplete" class="w-6 h-6 text-green-600" />
                                    <ExclamationCircleIcon v-else class="w-6 h-6 text-amber-600" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Profile Completion</h3>
                                    <p class="text-xs text-gray-500">{{ completion.completed }} of {{ completion.total }} sections completed</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span :class="[
                                    'text-3xl font-bold',
                                    completion.percentage >= 80 ? 'text-green-600' : completion.percentage >= 50 ? 'text-amber-600' : 'text-red-500'
                                ]">
                                    {{ completion.percentage }}%
                                </span>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div
                                    :class="[
                                        'h-2.5 rounded-full transition-all duration-700 ease-out',
                                        completion.percentage >= 80 ? 'bg-gradient-to-r from-green-400 to-green-600' : 
                                        completion.percentage >= 50 ? 'bg-gradient-to-r from-amber-400 to-amber-600' : 
                                        'bg-gradient-to-r from-red-400 to-red-500'
                                    ]"
                                    :style="{ width: completion.percentage + '%' }"
                                ></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                            <p v-if="!completion.isComplete" class="text-xs text-gray-500">
                                Complete all sections to unlock premium features
                            </p>
                            <p v-else class="text-xs text-green-600 font-medium">
                                ✓ Profile complete! Ready for AI assessment
                            </p>
                            <Link
                                :href="route('profile.assessment.show')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-sm hover:shadow-md"
                            >
                                <SparklesIcon class="w-4 h-4" />
                                AI Assessment
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Card View: Show when no section is active -->
                <div v-if="!activeSection" class="space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                    <UserCircleIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Personal Information</h3>
                                    <p class="text-xs text-gray-500">4 sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'personal')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    class="group flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition-all text-left"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" class="w-5 h-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-700">{{ section.name }}</h4>
                                            <span :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 flex-shrink-0" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Profile -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                    <BriefcaseIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Professional Profile</h3>
                                    <p class="text-xs text-gray-500">6 sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'professional')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    class="group flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-purple-200 hover:bg-purple-50/50 transition-all text-left"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" class="w-5 h-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate group-hover:text-purple-700">{{ section.name }}</h4>
                                            <span :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-purple-500 flex-shrink-0" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Safety & Health -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
                                    <HeartIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Safety & Health</h3>
                                    <p class="text-xs text-gray-500">2 sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'safety')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    class="group flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-rose-200 hover:bg-rose-50/50 transition-all text-left"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" class="w-5 h-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate group-hover:text-rose-700">{{ section.name }}</h4>
                                            <span :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-rose-500 flex-shrink-0" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Immigration & Documents -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center">
                                    <GlobeAltIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Immigration & Documents</h3>
                                    <p class="text-xs text-gray-500">4 sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'immigration')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    class="group flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-teal-200 hover:bg-teal-50/50 transition-all text-left"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" class="w-5 h-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate group-hover:text-teal-700">{{ section.name }}</h4>
                                            <span :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-teal-500 flex-shrink-0" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Family & Financial -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                                    <UsersIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Family & Financial</h3>
                                    <p class="text-xs text-gray-500">2 sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'family-financial')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    class="group flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-amber-200 hover:bg-amber-50/50 transition-all text-left"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" class="w-5 h-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate group-hover:text-amber-700">{{ section.name }}</h4>
                                            <span :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-amber-500 flex-shrink-0" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Background & Security -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center">
                                    <ShieldCheckIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Background & Security</h3>
                                    <p class="text-xs text-gray-500">1 section</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'background')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    class="group flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-slate-200 hover:bg-slate-50/50 transition-all text-left"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" class="w-5 h-5 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate group-hover:text-slate-700">{{ section.name }}</h4>
                                            <span :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon class="w-4 h-4 text-gray-300 group-hover:text-slate-500 flex-shrink-0" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Account & Settings -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center">
                                    <Cog6ToothIcon class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Account & Settings</h3>
                                    <p class="text-xs text-gray-500">6 sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button
                                    v-for="section in sections.filter(s => s.category === 'settings')"
                                    :key="section.id"
                                    @click="changeSection(section.id)"
                                    :class="[
                                        'group flex items-center gap-3 p-3 rounded-xl border transition-all text-left',
                                        section.id === 'delete' 
                                            ? 'border-red-200 bg-red-50/50 hover:bg-red-100/50 hover:border-red-300' 
                                            : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50/50'
                                    ]"
                                >
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105',
                                        section.id === 'delete' ? 'bg-red-100' : getSectionGradient(section.id)
                                    ]">
                                        <component :is="section.icon" :class="[
                                            'w-5 h-5',
                                            section.id === 'delete' ? 'text-red-600' : 'text-white'
                                        ]" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 :class="[
                                                'text-sm font-medium truncate',
                                                section.id === 'delete' ? 'text-red-700 group-hover:text-red-800' : 'text-gray-900 group-hover:text-gray-700'
                                            ]">
                                                {{ section.name }}
                                            </h4>
                                            <span v-if="section.id !== 'delete'" :class="[
                                                'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                                getSectionCompletion(section.id) >= 80 ? 'bg-green-100 text-green-700' : 
                                                getSectionCompletion(section.id) >= 50 ? 'bg-amber-100 text-amber-700' : 
                                                getSectionCompletion(section.id) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                {{ getSectionCompletion(section.id) }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ section.description }}</p>
                                    </div>
                                    <ChevronRightIcon :class="[
                                        'w-4 h-4 flex-shrink-0',
                                        section.id === 'delete' ? 'text-red-400 group-hover:text-red-600' : 'text-gray-300 group-hover:text-gray-500'
                                    ]" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Detail View: Show when a section is active -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Section Content -->
                    <div class="p-5 sm:p-6">
                        <!-- Basic Information -->
                        <UpdateProfileInformationForm
                            v-if="activeSection === 'basic'"
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            :user-profile="userProfile"
                        />

                        <!-- Phone Numbers -->
                        <PhoneNumbersSection
                            v-if="activeSection === 'phone'"
                        />

                        <!-- Social Media & Contact -->
                        <SocialLinksSection
                            v-if="activeSection === 'social'"
                            :social-links="userProfile?.social_links || {}"
                        />

                        <!-- Emergency Contact -->
                        <EmergencyContactSection
                            v-if="activeSection === 'emergency'"
                            :emergency-contact="userProfile"
                        />

                        <!-- Medical Information -->
                        <MedicalInformationSection
                            v-if="activeSection === 'medical'"
                            :medical-info="userProfile"
                        />

                        <!-- Profile Details -->
                        <UpdateProfileDetailsForm
                            v-if="activeSection === 'profile'"
                            :user-profile="userProfile"
                            :divisions="divisions"
                            :countries="countries"
                        />

                        <!-- Education Section -->
                        <EducationSection
                            v-if="activeSection === 'education'"
                            :user-profile="userProfile"
                            :educations="educations"
                            :degrees="degrees"
                            :countries="countries"
                        />

                        <!-- Work Experience Section -->
                        <WorkExperienceSection
                            v-if="activeSection === 'experience'"
                            :user-profile="userProfile"
                            :work-experiences="workExperiences"
                            :countries="countries"
                        />

                        <!-- Skills Section -->
                        <SkillsSection
                            v-if="activeSection === 'skills'"
                            :user-profile="userProfile"
                            :user-skills="skills"
                        />

                        <!-- Travel History Section -->
                        <TravelHistorySection
                            v-if="activeSection === 'travel'"
                            :travel-history="travelHistory"
                        />

                        <!-- References Section -->
                        <ReferencesSection
                            v-if="activeSection === 'references'"
                            :references="userProfile"
                        />

                        <!-- Certifications Section -->
                        <CertificationsSection
                            v-if="activeSection === 'certifications'"
                            :certifications="userProfile"
                        />

                        <!-- Profile Completeness Tracker -->
                        <ProfileCompletenessTracker
                            v-if="activeSection === 'completeness'"
                            :user="user"
                            :user-profile="userProfile"
                            :family-members="familyMembers"
                            :languages="languages"
                            :educations="educations"
                            :work-experiences="workExperiences"
                            :skills="skills"
                            :travel-history="travelHistory"
                            :phone-numbers="phoneNumbers"
                        />

                        <!-- Family Section -->
                        <FamilySection
                            v-if="activeSection === 'family'"
                            :user-profile="userProfile"
                        />

                        <!-- Financial Section -->
                        <FinancialSection
                            v-if="activeSection === 'financial'"
                            :user-profile="userProfile"
                        />

                        <!-- Languages Section -->
                        <LanguagesSection
                            v-if="activeSection === 'languages'"
                            :user-profile="userProfile"
                        />

                        <!-- Privacy & Data Control -->
                        <PrivacyDataControl
                            v-if="activeSection === 'privacy'"
                            :user-profile="userProfile"
                        />

                        <!-- Preferences & Settings -->
                        <PreferencesSettings
                            v-if="activeSection === 'preferences'"
                            :user-profile="userProfile"
                            :countries="countries"
                            :service-categories="serviceCategories"
                            :currencies="currencies"
                            :languages="languages"
                        />

                        <!-- Documents Management -->
                        <DocumentsManagement
                            v-if="activeSection === 'documents'"
                            :user-profile="userProfile"
                            :user-documents="userDocuments"
                        />

                        <!-- Passport Management -->
                        <PassportManagement
                            v-if="activeSection === 'passports'"
                            :user-profile="userProfile"
                        />

                        <!-- Visa History Management -->
                        <VisaHistoryManagement
                            v-if="activeSection === 'visa-history'"
                            :user-profile="userProfile"
                            :visa-history="visaHistory"
                            :passports="passports"
                        />

                        <!-- Security Section -->
                        <SecuritySection
                            v-if="activeSection === 'security'"
                            :user-profile="userProfile"
                        />

                        <!-- Public Profile & Sharing -->
                        <div v-if="activeSection === 'public-profile'" class="space-y-6">
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                        <UserCircleIcon class="w-6 h-6 text-white" />
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            Public Profile & Sharing
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600">
                                            Share your profile with others and generate a QR code for easy access.
                                        </p>
                                        <div class="mt-4">
                                            <a
                                                :href="route('profile.public.settings')"
                                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all"
                                            >
                                                Manage Public Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy & Data Control -->
                        <PrivacyDataControl
                            v-if="activeSection === 'privacy'"
                            :user="user"
                            :user-profile="userProfile"
                        />

                        <!-- Preferences & Settings -->
                        <PreferencesSettings
                            v-if="activeSection === 'preferences'"
                            :user-profile="userProfile"
                        />

                        <!-- Password -->
                        <UpdatePasswordForm
                            v-if="activeSection === 'password'"
                        />

                        <!-- Delete Account -->
                        <DeleteUserForm
                            v-if="activeSection === 'delete'"
                        />
                    </div>

                    <!-- Bottom Back Button (Mobile Friendly) -->
                    <div class="border-t border-gray-100 px-4 py-4 bg-gray-50 md:hidden">
                        <button
                            @click="backToCards"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back to All Sections
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>