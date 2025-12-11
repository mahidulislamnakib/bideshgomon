<template>
    <div :class="[utilityClasses.card, 'quick-actions-widget']">
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-neutral-900">Quick Actions</h3>
            <p class="text-sm text-neutral-500 mt-1">Common administrative tasks</p>
        </div>

        <!-- Actions Grid -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Add User -->
            <Link
                :href="route('admin.users.create')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <UserPlusIcon class="h-6 w-6 text-primary-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Add User</span>
            </Link>

            <!-- Create Blog Post -->
            <Link
                :href="route('admin.blog.posts.create')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <DocumentPlusIcon class="h-6 w-6 text-blue-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">New Post</span>
            </Link>

            <!-- Create Ad -->
            <Link
                :href="route('admin.ads.create')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <MegaphoneIcon class="h-6 w-6 text-purple-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Create Ad</span>
            </Link>

            <!-- View Reports -->
            <Link
                :href="route('admin.analytics.dashboard')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-success-50 to-success-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <ChartBarIcon class="h-6 w-6 text-success-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Reports</span>
            </Link>

            <!-- Manage Agencies -->
            <Link
                :href="route('admin.agencies.index')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <BuildingOfficeIcon class="h-6 w-6 text-orange-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Agencies</span>
            </Link>

            <!-- Support Tickets -->
            <Link
                :href="route('admin.support-tickets.index')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <LifebuoyIcon class="h-6 w-6 text-red-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Support</span>
            </Link>

            <!-- Wallets -->
            <Link
                :href="route('admin.wallets.index')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <CurrencyDollarIcon class="h-6 w-6 text-yellow-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Wallets</span>
            </Link>

            <!-- Settings -->
            <Link
                href="#"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-neutral-50 to-neutral-100 rounded-lg hover:shadow-md transition-all group"
            >
                <div class="p-3 bg-white rounded-full shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <Cog6ToothIcon class="h-6 w-6 text-neutral-600" />
                </div>
                <span class="text-sm font-medium text-neutral-900">Settings</span>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import {
    UserPlusIcon,
    DocumentPlusIcon,
    MegaphoneIcon,
    ChartBarIcon,
    BuildingOfficeIcon,
    LifebuoyIcon,
    CurrencyDollarIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline';
import { utilityClasses } from '@/Config/designSystem';
</script>
