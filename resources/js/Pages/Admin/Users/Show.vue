<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    UserCircleIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    CalendarIcon,
    ShieldCheckIcon,
    BriefcaseIcon,
    DocumentTextIcon,
    CreditCardIcon,
    NoSymbolIcon,
    CheckCircleIcon,
    TrashIcon,
    PencilIcon,
    ClockIcon,
    GlobeAltIcon,
    CheckBadgeIcon,
    XCircleIcon,
    CurrencyDollarIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object,
    profileCompletion: {
        type: Number,
        default: 0,
    },
});

const toast = useToast();

const activeTab = ref('overview');

const suspendForm = useForm({
    reason: '',
});

const roleForm = useForm({
    role_id: props.user.role_id,
});

const suspendUser = () => {
    if (!suspendForm.reason) {
        toast.warning('Please enter a suspension reason');
        return;
    }

    if (confirm(`Suspend ${props.user.name}?`)) {
        suspendForm.post(route('admin.users.suspend', props.user.id), {
            preserveScroll: true,
            onSuccess: () => {
                suspendForm.reset();
            }
        });
    }
};

const unsuspendUser = () => {
    if (confirm(`Unsuspend ${props.user.name}?`)) {
        router.post(route('admin.users.unsuspend', props.user.id), {}, {
            preserveScroll: true,
        });
    }
};

const updateRole = () => {
    if (confirm(`Change ${props.user.name}'s role?`)) {
        roleForm.post(route('admin.users.update-role', props.user.id), {
            preserveScroll: true,
        });
    }
};

const deleteUser = () => {
    if (confirm(`Are you sure you want to delete ${props.user.name}? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
};

// Impersonation helpers
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role?.slug === 'admin');
const impersonating = computed(() => page.props.auth?.user?.impersonating);

const canImpersonate = computed(() => {
    if (!isAdmin.value) return false;
    if (impersonating.value) return false; // Already impersonating someone
    // Do not impersonate admins (policy)
    return props.user.role?.slug !== 'admin';
});

const impersonateUser = () => {
    if (!canImpersonate.value) return;
    
    const purpose = prompt(`Impersonate ${props.user.name}?\n\nPlease provide a reason (required for audit trail):`);
    if (!purpose || purpose.trim() === '') {
        return; // User cancelled or provided empty reason
    }
    
    router.post(route('admin.users.impersonate', props.user.id), {
        purpose: purpose.trim()
    }, {
        preserveScroll: false,
        onSuccess: () => {
            // Redirect to dashboard after successful impersonation
            router.visit(route('dashboard'));
        },
        onError: (errors) => {
            console.error('Impersonation failed:', errors);
            toast.error('Failed to impersonate user', errors.purpose || 'Please try again.');
        }
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount) => {
    return '\u09F3' + new Intl.NumberFormat('en-BD', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    reviewed: 'bg-red-100 text-growth-600 border-red-200',
    shortlisted: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
    hired: 'bg-purple-100 text-purple-800 border-purple-200',
};

const getStatusColor = (status) => {
    return statusColors[status?.toLowerCase()] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const tabs = [
    { id: 'overview', label: 'Overview', icon: UserCircleIcon },
    { id: 'applications', label: 'Job Applications', icon: BriefcaseIcon },
    { id: 'cvs', label: 'CVs & Documents', icon: DocumentTextIcon },
    { id: 'activity', label: 'Activity Log', icon: ClockIcon },
];

</script>

<template>
    <Head :title="`${user.name} - User Details`" />

    <AdminLayout>
        <!-- Modern Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-primary-500/30 to-primary-600/20 rounded-full blur-3xl"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header with Back Button -->
                <div class="flex items-center justify-between mb-6">
                    <Link
                        :href="route('admin.users.index')"
                        class="inline-flex items-center gap-2 text-gray-300 hover:text-white font-medium transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        Back to Users
                    </Link>
                    <div class="flex gap-2">
                        <button
                            v-if="canImpersonate"
                            @click="impersonateUser"
                            class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-2xl font-medium transition-colors" style="background: #14a800;"
                            :title="`Debug: isAdmin=${isAdmin}, impersonating=${impersonating}, userRole=${user.role?.slug}`"
                        >
                            <UserIcon class="h-4 w-4" />
                            Impersonate
                        </button>
                        <div v-else class="text-xs text-gray-400 flex items-center gap-1">
                            <span v-if="!isAdmin">Not admin</span>
                            <span v-else-if="impersonating">Already impersonating</span>
                            <span v-else-if="user.role?.slug === 'admin'">Can't impersonate admin</span>
                        </div>
                        <Link
                            :href="route('admin.users.edit', user.id)"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white rounded-2xl hover:bg-white/20 font-medium transition-colors"
                        >
                            <PencilIcon class="h-4 w-4" />
                            Edit User
                        </Link>
                        <button
                            @click="deleteUser"
                            class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-2xl font-medium transition-colors" style="background: #dc2626;"
                        >
                            <TrashIcon class="h-4 w-4" />
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Profile Header Card -->
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20">
                    <div class="px-6 py-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <!-- Avatar and Basic Info -->
                            <div class="flex items-center gap-4">
                                <div class="h-20 w-20 rounded-xl flex items-center justify-center text-white text-3xl font-bold" style="background: linear-gradient(135deg, #14a800 0%, #0f8a00 100%);">
                                    {{ (user.name || '').charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-white">{{ user.name }}</h1>
                                    <p class="text-gray-300 flex items-center gap-2 mt-1">
                                        <EnvelopeIcon class="h-4 w-4" />
                                        {{ user.email }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 mt-3">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5',
                                                user.role?.slug === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-400/30' : 'bg-primary-500/20 text-primary-300 border border-primary-400/30',
                                            ]"
                                        >
                                            <ShieldCheckIcon class="h-4 w-4" />
                                            {{ user.role?.name || 'User' }}
                                        </span>
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5',
                                                user.suspended_at ? 'bg-red-500/20 text-red-300 border border-red-400/30' : 'bg-green-500/20 text-green-300 border border-green-400/30',
                                            ]"
                                        >
                                            <component :is="user.suspended_at ? NoSymbolIcon : CheckCircleIcon" class="h-4 w-4" />
                                            {{ user.suspended_at ? 'Suspended' : 'Active' }}
                                        </span>
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5',
                                                user.email_verified_at ? 'bg-green-500/20 text-green-300 border border-green-400/30' : 'bg-amber-500/20 text-amber-300 border border-amber-400/30',
                                            ]"
                                        >
                                            <component :is="user.email_verified_at ? CheckBadgeIcon : XCircleIcon" class="h-4 w-4" />
                                            {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-if="!user.suspended_at"
                                    @click="suspendUser"
                                    class="px-4 py-2 text-white rounded-2xl font-medium transition-colors text-sm flex items-center gap-2" style="background: #dc2626;"
                                >
                                    <NoSymbolIcon class="h-4 w-4" />
                                    Suspend
                                </button>
                                <button
                                    v-else
                                    @click="unsuspendUser"
                                    class="px-4 py-2 text-white rounded-2xl font-medium hover:bg-green-600 transition-colors text-sm flex items-center gap-2" style="background: #14a800;"
                                >
                                    <CheckCircleIcon class="h-4 w-4" />
                                    Unsuspend
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Row -->
                    <div class="px-6 py-6 border-t border-white/10">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ profileCompletion }}%</div>
                                <div class="text-sm text-gray-400 mt-1">Profile Complete</div>
                                <div class="mt-2 w-full bg-white/20 rounded-full h-1.5">
                                    <div 
                                        class="h-1.5 rounded-full transition-all duration-500"
                                        :class="{
                                            'bg-green-400': profileCompletion >= 80,
                                            'bg-yellow-400': profileCompletion >= 50 && profileCompletion < 80,
                                            'bg-red-400': profileCompletion < 50
                                        }"
                                        :style="{ width: profileCompletion + '%' }"
                                    ></div>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ user.job_applications?.length || 0 }}</div>
                                <div class="text-sm text-gray-400 mt-1">Applications</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ user.cvs?.length || 0 }}</div>
                                <div class="text-sm text-gray-400 mt-1">CVs</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ formatCurrency(user.wallet?.balance || 0) }}</div>
                                <div class="text-sm text-gray-400 mt-1">Wallet Balance</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ Math.floor((new Date() - new Date(user.created_at)) / (1000 * 60 * 60 * 24)) }}</div>
                                <div class="text-sm text-gray-400 mt-1">Days Active</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 mb-6 overflow-hidden">
                    <div class="border-b border-neutral-200 dark:border-neutral-700">
                        <nav class="flex -mb-px">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    'flex items-center gap-2 px-6 py-4 font-medium text-sm border-b-2 transition-colors',
                                    activeTab === tab.id
                                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                        : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:border-neutral-300',
                                ]"
                            >
                                <component :is="tab.icon" class="h-5 w-5" />
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content Area -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'">
                            <!-- Contact Information -->
                            <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center gap-2">
                                    <EnvelopeIcon class="h-6 w-6 text-neutral-600 dark:text-neutral-400" />
                                    Contact Information
                                </h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <div class="p-2 bg-neutral-200 dark:bg-neutral-600 rounded-2xl">
                                            <EnvelopeIcon class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold">Email</p>
                                            <p class="font-medium text-neutral-900 dark:text-white mt-1">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <div class="p-2 bg-neutral-200 dark:bg-neutral-600 rounded-2xl">
                                            <PhoneIcon class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold">Phone</p>
                                            <p class="font-medium text-neutral-900 dark:text-white mt-1">{{ user.phone || 'Not provided' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <div class="p-2 bg-neutral-200 dark:bg-neutral-600 rounded-2xl">
                                            <MapPinIcon class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold">Country</p>
                                            <p class="font-medium text-neutral-900 dark:text-white mt-1">{{ user.country?.name || 'Not specified' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <div class="p-2 bg-neutral-200 dark:bg-neutral-600 rounded-2xl">
                                            <CalendarIcon class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold">Member Since</p>
                                            <p class="font-medium text-neutral-900 dark:text-white mt-1">{{ formatDate(user.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Details -->
                            <div v-if="user.profile" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center gap-2">
                                    <UserCircleIcon class="h-6 w-6 text-neutral-600 dark:text-neutral-400" />
                                    Profile Details
                                </h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold mb-1">Date of Birth</p>
                                        <p class="font-medium text-neutral-900 dark:text-white">{{ user.profile.date_of_birth ? formatDate(user.profile.date_of_birth) : 'Not provided' }}</p>
                                    </div>
                                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold mb-1">Gender</p>
                                        <p class="font-medium text-neutral-900 dark:text-white capitalize">{{ user.profile.gender || 'Not specified' }}</p>
                                    </div>
                                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold mb-1">Nationality</p>
                                        <p class="font-medium text-neutral-900 dark:text-white">{{ user.profile.nationality || 'Not specified' }}</p>
                                    </div>
                                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold mb-1">Marital Status</p>
                                        <p class="font-medium text-neutral-900 dark:text-white capitalize">{{ user.profile.marital_status || 'Not specified' }}</p>
                                    </div>
                                    <div class="md:col-span-2 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase font-semibold mb-1">Address</p>
                                        <p class="font-medium text-neutral-900 dark:text-white">{{ user.profile.address || 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Applications Tab -->
                        <div v-if="activeTab === 'applications'" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                            <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-6 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <BriefcaseIcon class="h-6 w-6" style="color: #14a800;" />
                                    Job Applications
                                </span>
                                <span class="text-sm font-medium px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-2xl">
                                    {{ user.job_applications?.length || 0 }}
                                </span>
                            </h2>
                            <div v-if="user.job_applications && user.job_applications.length > 0" class="space-y-4">
                                <div
                                    v-for="application in user.job_applications"
                                    :key="application.id"
                                    class="border border-neutral-200 dark:border-neutral-700 rounded-xl p-5 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm transition-all"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <h4 class="font-bold text-lg text-neutral-900 dark:text-white">{{ application.job_posting?.title }}</h4>
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1 flex items-center gap-2">
                                                <GlobeAltIcon class="h-4 w-4" />
                                                {{ application.job_posting?.company_name }}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-3 mt-4">
                                                <span
                                                    :class="['px-3 py-1.5 rounded-2xl text-xs font-medium', getStatusColor(application.status)]"
                                                >
                                                    {{ application.status }}
                                                </span>
                                                <span class="text-xs text-neutral-600 dark:text-neutral-400 flex items-center gap-1">
                                                    <ClockIcon class="h-4 w-4" />
                                                    {{ formatDate(application.created_at) }}
                                                </span>
                                            </div>
                                        </div>
                                        <Link
                                            :href="route('admin.applications.show', application.id)"
                                            class="px-5 py-2.5 text-white rounded-2xl transition-colors font-medium text-sm" style="background: #14a800;"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-neutral-100 dark:bg-neutral-700 mb-4">
                                    <BriefcaseIcon class="h-10 w-10 text-neutral-400" />
                                </div>
                                <p class="text-neutral-600 dark:text-neutral-300 font-medium">No job applications yet</p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">User hasn't applied to any jobs</p>
                            </div>
                        </div>

                        <!-- CVs Tab -->
                        <div v-if="activeTab === 'cvs'" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                            <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-6 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <DocumentTextIcon class="h-6 w-6" style="color: #14a800;" />
                                    CVs & Documents
                                </span>
                                <span class="text-sm font-medium px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-2xl">
                                    {{ user.cvs?.length || 0 }}
                                </span>
                            </h2>
                            <div v-if="user.cvs && user.cvs.length > 0" class="space-y-3">
                                <div
                                    v-for="cv in user.cvs"
                                    :key="cv.id"
                                    class="flex items-center justify-between p-5 border border-neutral-200 dark:border-neutral-700 rounded-xl hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm transition-all"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="p-3 rounded-2xl" style="background: rgba(20, 168, 0, 0.1);">
                                            <DocumentTextIcon class="h-6 w-6" style="color: #14a800;" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-neutral-900 dark:text-white">{{ cv.title || 'Untitled CV' }}</p>
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Created {{ formatDate(cv.created_at) }}</p>
                                        </div>
                                    </div>
                                    <span
                                        :class="[
                                            'px-4 py-2 rounded-2xl text-xs font-medium',
                                            cv.is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300',
                                        ]"
                                    >
                                        {{ cv.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-neutral-100 dark:bg-neutral-700 mb-4">
                                    <DocumentTextIcon class="h-10 w-10 text-neutral-400" />
                                </div>
                                <p class="text-neutral-600 dark:text-neutral-300 font-medium">No CVs created yet</p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">User hasn't uploaded any CVs</p>
                            </div>
                        </div>

                        <!-- Activity Tab -->
                        <div v-if="activeTab === 'activity'" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                            <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-6 flex items-center gap-2">
                                <ClockIcon class="h-6 w-6" style="color: #14a800;" />
                                Activity Log
                            </h2>
                            <div class="space-y-4">
                                <div class="flex gap-4 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                            <CheckCircleIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-neutral-900 dark:text-white">Account Created</p>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ formatDateTime(user.created_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="user.email_verified_at" class="flex gap-4 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background: rgba(20, 168, 0, 0.1);">
                                            <CheckBadgeIcon class="h-5 w-5" style="color: #14a800;" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-neutral-900 dark:text-white">Email Verified</p>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ formatDateTime(user.email_verified_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="user.last_login_at" class="flex gap-4 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background: rgba(59, 130, 246, 0.1);">
                                            <ClockIcon class="h-5 w-5" style="color: #3b82f6;" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-neutral-900 dark:text-white">Last Login</p>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ formatDateTime(user.last_login_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Wallet Information -->
                        <div class="bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 rounded-2xl shadow-card p-6">
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center gap-2">
                                <CreditCardIcon class="h-6 w-6" style="color: #14a800;" />
                                Wallet Balance
                            </h3>
                            <div class="space-y-4">
                                <div class="p-4 rounded-xl" style="background: rgba(20, 168, 0, 0.1);">
                                    <p class="text-sm font-medium mb-1" style="color: #14a800;">Main Balance</p>
                                    <p class="text-3xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(user.wallet?.balance || 0) }}</p>
                                </div>
                                <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Cashback</span>
                                        <span class="text-xl font-bold text-neutral-900 dark:text-white">{{ formatCurrency(user.wallet?.cashback_balance || 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Management -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                            <h3 class="text-md font-bold text-neutral-900 dark:text-white mb-4 flex items-center gap-2">
                                <ShieldCheckIcon class="h-6 w-6" style="color: #14a800;" />
                                Role Management
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Current Role</label>
                                    <div class="p-3 rounded-xl border" style="background: rgba(20, 168, 0, 0.1); border-color: rgba(20, 168, 0, 0.3);">
                                        <p class="font-bold text-neutral-900 dark:text-white">{{ user.role?.name || 'User' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Change To</label>
                                    <select
                                        v-model="roleForm.role_id"
                                        class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        <option value="1">Admin</option>
                                        <option value="2">User</option>
                                        <option value="3">Agency</option>
                                        <option value="4">Consultant</option>
                                    </select>
                                </div>
                                <button
                                    @click="updateRole"
                                    :disabled="roleForm.processing || roleForm.role_id === user.role_id"
                                    class="w-full px-4 py-2.5 text-white rounded-2xl font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed" style="background: #14a800;"
                                >
                                    <span v-if="roleForm.processing">Updating...</span>
                                    <span v-else>Update Role</span>
                                </button>
                            </div>
                        </div>

                        <!-- Suspension Management -->
                        <div v-if="!user.suspended_at" class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-error-200 dark:border-error-800 p-6">
                            <h3 class="text-md font-bold text-neutral-900 dark:text-white mb-4 flex items-center gap-2">
                                <NoSymbolIcon class="h-6 w-6" style="color: #dc2626;" />
                                Suspend User
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Suspension Reason</label>
                                    <textarea
                                        v-model="suspendForm.reason"
                                        rows="4"
                                        placeholder="Enter detailed reason for suspension..."
                                        class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-2xl focus:ring-2 focus:ring-error-500 focus:border-error-500"
                                    ></textarea>
                                </div>
                                <button
                                    @click="suspendUser"
                                    :disabled="suspendForm.processing || !suspendForm.reason"
                                    class="w-full px-4 py-2.5 text-white rounded-2xl font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed" style="background: #dc2626;"
                                >
                                    <span v-if="suspendForm.processing">Suspending...</span>
                                    <span v-else>Suspend User</span>
                                </button>
                            </div>
                        </div>

                        <!-- Suspension Info -->
                        <div v-else class="bg-error-50 dark:bg-error-900/20 border border-error-200 dark:border-error-800 rounded-2xl p-6">
                            <h3 class="text-md font-bold text-error-900 dark:text-error-300 mb-3 flex items-center gap-2">
                                <NoSymbolIcon class="h-6 w-6" />
                                Account Suspended
                            </h3>
                            <div class="space-y-3">
                                <div class="p-3 bg-error-100 dark:bg-error-900/30 rounded-xl">
                                    <p class="text-xs text-error-700 dark:text-error-400 font-bold uppercase mb-1">Reason</p>
                                    <p class="text-sm text-error-900 dark:text-error-200 font-medium">{{ user.suspension_reason }}</p>
                                </div>
                                <div class="p-3 bg-error-100 dark:bg-error-900/30 rounded-xl">
                                    <p class="text-xs text-error-700 dark:text-error-400 font-bold uppercase mb-1">Suspended On</p>
                                    <p class="text-sm text-error-900 dark:text-error-200 font-medium">{{ formatDateTime(user.suspended_at) }}</p>
                                </div>
                                <button
                                    @click="unsuspendUser"
                                    class="w-full px-4 py-2.5 text-white rounded-2xl font-medium transition-colors" style="background: #14a800;"
                                >
                                    Unsuspend User
                                </button>
                            </div>
                        </div>

                        <!-- Account Statistics -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                            <h3 class="text-md font-bold text-neutral-900 dark:text-white mb-4">Quick Stats</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 rounded-xl" style="background: rgba(20, 168, 0, 0.1);">
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300 font-medium">Applications</span>
                                    <span class="text-lg font-bold" style="color: #14a800;">{{ user.job_applications?.length || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 rounded-xl" style="background: rgba(59, 130, 246, 0.1);">
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300 font-medium">CVs Created</span>
                                    <span class="text-lg font-bold" style="color: #3b82f6;">{{ user.cvs?.length || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 rounded-xl bg-success-50 dark:bg-success-900/20">
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300 font-medium">Email Status</span>
                                    <span
                                        :class="[
                                            'text-sm font-bold',
                                            user.email_verified_at ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400',
                                        ]"
                                    >
                                        {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center p-3 rounded-xl bg-warning-50 dark:bg-warning-900/20">
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300 font-semibold">Account Age</span>
                                    <span class="text-sm font-bold text-warning-600 dark:text-warning-400">
                                        {{ Math.floor((new Date() - new Date(user.created_at)) / (1000 * 60 * 60 * 24)) }} days
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Details -->
                        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700 p-6">
                            <h3 class="text-md font-bold text-neutral-900 dark:text-white mb-4">Account Details</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-neutral-600 dark:text-neutral-400 font-medium">User ID</span>
                                    <span class="font-bold text-neutral-900 dark:text-white">#{{ user.id }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-neutral-600 dark:text-neutral-400 font-medium">Created</span>
                                    <span class="font-bold text-neutral-900 dark:text-white">{{ formatDate(user.created_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-neutral-600 dark:text-neutral-400 font-medium">Last Updated</span>
                                    <span class="font-bold text-neutral-900 dark:text-white">{{ formatDate(user.updated_at) }}</span>
                                </div>
                                <div v-if="user.last_login_at" class="flex justify-between items-center">
                                    <span class="text-neutral-600 dark:text-neutral-400 font-medium">Last Login</span>
                                    <span class="font-bold text-neutral-900 dark:text-white">{{ formatDate(user.last_login_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
