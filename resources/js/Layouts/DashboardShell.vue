<template>
    <div class="dashboard-shell min-h-screen bg-neutral-50">
        <!-- Mobile Overlay -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden transition-opacity"
        />

        <!-- Sidebar -->
        <SidebarMenu
            :open="sidebarOpen"
            :collapsed="sidebarCollapsed"
            @close="sidebarOpen = false"
            @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
        />

        <!-- Main Content Area -->
        <div
            :class="[
                'transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-70'
            ]"
        >
            <!-- Top Navigation -->
            <TopNav
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
                @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
                :collapsed="sidebarCollapsed"
            />

            <!-- Page Content -->
            <main class="dashboard-content">
                <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer v-if="showFooter" class="dashboard-footer bg-white border-t border-neutral-200 mt-12">
                <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-neutral-600">
                        <div>
                            © {{ currentYear }} <span class="font-semibold text-primary-600">BideshGomon</span>. All rights reserved.
                        </div>
                        <div class="flex gap-6">
                            <a href="#" class="hover:text-primary-600 transition-colors">Privacy</a>
                            <a href="#" class="hover:text-primary-600 transition-colors">Terms</a>
                            <a href="#" class="hover:text-primary-600 transition-colors">Support</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Mobile Bottom Navigation (Optional) -->
        <div v-if="showMobileNav" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-neutral-200 z-30">
            <div class="grid grid-cols-5 gap-1 px-2 py-2">
                <Link
                    :href="route('admin.dashboard')"
                    class="flex flex-col items-center justify-center py-2 text-xs rounded-lg transition-colors"
                    :class="route().current('admin.dashboard') ? 'text-primary-600 bg-primary-50' : 'text-neutral-600'"
                >
                    <HomeIcon class="h-6 w-6 mb-1" />
                    <span>Home</span>
                </Link>
                <Link
                    :href="route('admin.users.index')"
                    class="flex flex-col items-center justify-center py-2 text-xs rounded-lg transition-colors"
                    :class="route().current('admin.users.*') ? 'text-primary-600 bg-primary-50' : 'text-neutral-600'"
                >
                    <UsersIcon class="h-6 w-6 mb-1" />
                    <span>Users</span>
                </Link>
                <button
                    @click="sidebarOpen = true"
                    class="flex flex-col items-center justify-center py-2 text-xs rounded-lg transition-colors text-neutral-600"
                >
                    <Bars3Icon class="h-6 w-6 mb-1" />
                    <span>Menu</span>
                </button>
                <Link
                    href="#"
                    class="flex flex-col items-center justify-center py-2 text-xs rounded-lg transition-colors text-neutral-600"
                >
                    <BellIcon class="h-6 w-6 mb-1" />
                    <span>Alerts</span>
                </Link>
                <Link
                    href="#"
                    class="flex flex-col items-center justify-center py-2 text-xs rounded-lg transition-colors text-neutral-600"
                >
                    <Cog6ToothIcon class="h-6 w-6 mb-1" />
                    <span>Settings</span>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import SidebarMenu from './SidebarMenu.vue';
import TopNav from './TopNav.vue';
import {
    HomeIcon,
    UsersIcon,
    Bars3Icon,
    BellIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    showFooter: {
        type: Boolean,
        default: true
    },
    showMobileNav: {
        type: Boolean,
        default: false
    }
});

// Sidebar state
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

// Current year for footer
const currentYear = computed(() => new Date().getFullYear());

// Load sidebar state from localStorage
if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved !== null) {
        sidebarCollapsed.value = saved === 'true';
    }
}

// Save sidebar state
const saveSidebarState = () => {
    localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
};

// Watch for changes
import { watch } from 'vue';
watch(sidebarCollapsed, () => {
    saveSidebarState();
});
</script>

