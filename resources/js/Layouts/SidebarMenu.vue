<template>
    <aside
        :class="[
            'sidebar fixed top-0 bottom-0 left-0 z-50 transition-all duration-300',
            'bg-white border-r border-neutral-200 shadow-lg',
            {
                'translate-x-0': open,
                '-translate-x-full lg:translate-x-0': !open,
                'w-70': !collapsed,
                'w-20': collapsed
            }
        ]"
    >
        <!-- Logo Header -->
        <div class="sidebar-header h-16 flex items-center justify-between px-4 border-b border-neutral-200">
            <Link
                :href="route('admin.dashboard')"
                class="flex items-center gap-3 transition-opacity"
                :class="{ 'opacity-0 lg:opacity-100': collapsed }"
            >
                <img src="/images/logo.png" alt="BideshGomon" class="h-8 w-auto" />
                <span v-if="!collapsed" class="text-xl font-bold text-primary-600">BideshGomon</span>
            </Link>
            <button
                @click="$emit('toggle-collapse')"
                class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-neutral-100 transition-colors"
            >
                <ChevronLeftIcon v-if="!collapsed" class="h-5 w-5 text-neutral-600" />
                <ChevronRightIcon v-else class="h-5 w-5 text-neutral-600" />
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav overflow-y-auto h-[calc(100vh-64px)] py-4">
            <div class="px-3 space-y-1">
                <!-- Dashboard Overview -->
                <NavItem
                    :href="route('admin.dashboard')"
                    :icon="HomeIcon"
                    label="Dashboard"
                    :collapsed="collapsed"
                    :active="route().current('admin.dashboard')"
                />

                <!-- Agency Management -->
                <NavGroup
                    :icon="BuildingOfficeIcon"
                    label="Agency Management"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.agencies.*') || route().current('admin.agency-*')"
                >
                    <NavItem
                        :href="route('admin.agencies.index')"
                        label="All Agencies"
                        :collapsed="collapsed"
                        :active="route().current('admin.agencies.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.agency-verification.dashboard')"
                        label="Verification Requests"
                        :collapsed="collapsed"
                        :active="route().current('admin.agency-verification.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.agency-resources.index')"
                        label="Resources"
                        :collapsed="collapsed"
                        :active="route().current('admin.agency-resources.*')"
                        nested
                    />
                </NavGroup>

                <!-- Travel Services -->
                <NavGroup
                    :icon="GlobeAltIcon"
                    label="Travel Services"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.visa-*') || route().current('admin.hotels.*') || route().current('admin.flight-*')"
                >
                    <NavItem
                        :href="route('admin.visa-applications.index')"
                        label="Visa Applications"
                        :collapsed="collapsed"
                        :active="route().current('admin.visa-applications.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.hotels.index')"
                        label="Hotel Bookings"
                        :collapsed="collapsed"
                        :active="route('admin.hotels.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.flight-requests.index')"
                        label="Flight Requests"
                        :collapsed="collapsed"
                        :active="route().current('admin.flight-requests.*')"
                        nested
                    />
                </NavGroup>

                <!-- Jobs Module -->
                <NavGroup
                    :icon="BriefcaseIcon"
                    label="Jobs Module"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.jobs.*')"
                >
                    <NavItem
                        :href="route('admin.jobs.index')"
                        label="All Jobs"
                        :collapsed="collapsed"
                        :active="route().current('admin.jobs.index')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.job-applications.index')"
                        label="Applications"
                        :collapsed="collapsed"
                        :active="route().current('admin.job-applications.*')"
                        nested
                    />
                </NavGroup>

                <!-- Users Module -->
                <NavGroup
                    :icon="UsersIcon"
                    label="Users"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.users.*')"
                >
                    <NavItem
                        :href="route('admin.users.index')"
                        label="All Users"
                        :collapsed="collapsed"
                        :active="route().current('admin.users.index')"
                        nested
                    />
                    <NavItem
                        href="#"
                        label="Roles & Permissions"
                        :collapsed="collapsed"
                        nested
                    />
                    <NavItem
                        :href="route('admin.impersonations.index')"
                        label="Impersonation Logs"
                        :collapsed="collapsed"
                        :active="route().current('admin.impersonations.*')"
                        nested
                    />
                </NavGroup>

                <!-- Financial / Billing -->
                <NavGroup
                    :icon="CurrencyDollarIcon"
                    label="Financial"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.wallets.*') || route().current('admin.rewards.*')"
                >
                    <NavItem
                        :href="route('admin.wallets.index')"
                        label="Wallets"
                        :collapsed="collapsed"
                        :active="route().current('admin.wallets.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.rewards.index')"
                        label="Referral Rewards"
                        :collapsed="collapsed"
                        :active="route().current('admin.rewards.*')"
                        nested
                    />
                    <NavItem
                        href="#"
                        label="Transactions"
                        :collapsed="collapsed"
                        nested
                    />
                </NavGroup>

                <!-- Analytics -->
                <NavGroup
                    :icon="ChartBarIcon"
                    label="Analytics"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.analytics.*')"
                >
                    <NavItem
                        :href="route('admin.analytics.dashboard')"
                        label="Overview"
                        :collapsed="collapsed"
                        :active="route().current('admin.analytics.dashboard')"
                        nested
                    />
                    <NavItem
                        href="#"
                        label="User Activity"
                        :collapsed="collapsed"
                        nested
                    />
                    <NavItem
                        href="#"
                        label="Service Performance"
                        :collapsed="collapsed"
                        nested
                    />
                </NavGroup>

                <!-- Support -->
                <NavGroup
                    :icon="LifebuoyIcon"
                    label="Support"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.support-*') || route().current('admin.faqs.*')"
                >
                    <NavItem
                        :href="route('admin.support-tickets.index')"
                        label="Support Tickets"
                        :collapsed="collapsed"
                        :active="route().current('admin.support-tickets.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.faqs.index')"
                        label="FAQs"
                        :collapsed="collapsed"
                        :active="route().current('admin.faqs.*')"
                        nested
                    />
                </NavGroup>

                <!-- Settings (Unified) -->
                <NavGroup
                    :icon="Cog6ToothIcon"
                    label="Settings"
                    :collapsed="collapsed"
                    :defaultOpen="route().current('admin.settings.*') || route().current('admin.service-*') || route().current('admin.blog.*')"
                >
                    <NavItem
                        href="#"
                        label="General Settings"
                        :collapsed="collapsed"
                        nested
                    />
                    <NavItem
                        :href="route('admin.service-modules.index')"
                        label="Service Modules"
                        :collapsed="collapsed"
                        :active="route().current('admin.service-modules.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.blog.posts.index')"
                        label="Blog Management"
                        :collapsed="collapsed"
                        :active="route().current('admin.blog.*')"
                        nested
                    />
                    <NavItem
                        :href="route('admin.ads.index')"
                        label="Ads Management"
                        :collapsed="collapsed"
                        :active="route().current('admin.ads.*')"
                        nested
                    />
                    <NavItem
                        href="#"
                        label="SEO & Marketing"
                        :collapsed="collapsed"
                        nested
                    />
                    <NavItem
                        href="#"
                        label="Email Templates"
                        :collapsed="collapsed"
                        nested
                    />
                </NavGroup>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import NavItem from '@/Components/Navigation/NavItem.vue';
import NavGroup from '@/Components/Navigation/NavGroup.vue';
import {
    HomeIcon,
    BuildingOfficeIcon,
    GlobeAltIcon,
    BriefcaseIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    LifebuoyIcon,
    Cog6ToothIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

defineProps({
    open: Boolean,
    collapsed: Boolean
});

defineEmits(['close', 'toggle-collapse']);
</script>

