<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    roles: Array,
    countries: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    role_id: '',
    country_id: '',
    email_verified: false,
});

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <Head title="Create User - Admin" />

    <AdminLayout>
        <!-- Modern Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #1f2937 100%);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-primary-500/30 to-primary-600/20 rounded-full blur-3xl"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/10 backdrop-blur-sm rounded-xl">
                                <UserPlusIcon class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-300 uppercase tracking-wider">Admin / Users</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Create New User</h1>
                        <p class="text-sm text-gray-300">Add a new user to the system</p>
                    </div>
                    <Link
                        :href="route('admin.users.index')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white rounded-xl transition-all border border-white/20 font-semibold"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Users
                    </Link>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Create Form -->
                <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-card border border-neutral-100 dark:border-neutral-700">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Full Name <span class="text-error-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="Enter full name"
                                        />
                                        <div v-if="form.errors.name" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Email Address <span class="text-error-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            required
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="user@example.com"
                                        />
                                        <div v-if="form.errors.email" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Phone Number
                                        </label>
                                        <input
                                            v-model="form.phone"
                                            type="text"
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="+880 1XXX-XXXXXX"
                                        />
                                        <div v-if="form.errors.phone" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.phone }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Country
                                        </label>
                                        <select
                                            v-model="form.country_id"
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                        >
                                            <option value="">Select Country</option>
                                            <option v-for="country in countries" :key="country.id" :value="country.id">
                                                {{ country.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.country_id" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.country_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Password</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Password <span class="text-error-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.password"
                                            type="password"
                                            required
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="Enter password"
                                        />
                                        <div v-if="form.errors.password" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.password }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Confirm Password <span class="text-error-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.password_confirmation"
                                            type="password"
                                            required
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="Confirm password"
                                        />
                                        <div v-if="form.errors.password_confirmation" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.password_confirmation }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Role & Permissions -->
                            <div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Role & Permissions</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            User Role <span class="text-error-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.role_id"
                                            required
                                            class="w-full rounded-2xl border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                                        >
                                            <option value="">Select Role</option>
                                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                                {{ role.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.role_id" class="text-error-600 dark:text-error-400 text-sm mt-1">
                                            {{ form.errors.role_id }}
                                        </div>
                                    </div>

                                    <div class="flex items-end">
                                        <label class="flex items-center gap-2">
                                            <input
                                                v-model="form.email_verified"
                                                type="checkbox"
                                                class="rounded border-neutral-300 dark:border-neutral-600 text-primary-600 focus:ring-primary-500"
                                            />
                                            <span class="text-sm text-neutral-700 dark:text-neutral-300">Mark email as verified</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                            <Link
                                :href="route('admin.users.index')"
                                class="px-6 py-2 border border-neutral-300 dark:border-neutral-600 rounded-2xl text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-primary-600 text-white rounded-2xl hover:bg-primary-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
