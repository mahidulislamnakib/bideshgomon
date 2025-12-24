<template>
    <header
        :class="[
            'top-nav sticky top-0 z-30 bg-white border-b border-neutral-200 shadow-sm',
            'transition-all duration-300',
            collapsed ? 'lg:ml-20' : 'lg:ml-70'
        ]"
    >
        <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
            <!-- Left: Mobile Menu + Search -->
            <div class="flex items-center gap-4 flex-1">
                <!-- Mobile Menu Button -->
                <button
                    @click="$emit('toggle-sidebar')"
                    class="lg:hidden p-2 rounded-lg hover:bg-neutral-100 transition-colors"
                >
                    <Bars3Icon class="h-6 w-6 text-neutral-700" />
                </button>

                <!-- Desktop Collapse Button -->
                <button
                    @click="$emit('toggle-collapse')"
                    class="hidden lg:flex p-2 rounded-lg hover:bg-neutral-100 transition-colors"
                >
                    <Bars3Icon class="h-6 w-6 text-neutral-700" />
                </button>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 max-w-xl">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search users, services, applications..."
                        class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        @keydown.enter="handleSearch"
                    />
                </div>
            </div>

            <!-- Right: Actions + User Menu -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Mobile Search Button -->
                <button
                    @click="showMobileSearch = true"
                    class="md:hidden p-2 rounded-lg hover:bg-neutral-100 transition-colors"
                >
                    <MagnifyingGlassIcon class="h-6 w-6 text-neutral-700" />
                </button>

                <!-- Quick Actions Dropdown -->
                <div class="hidden sm:block relative">
                    <button
                        @click="showQuickActions = !showQuickActions"
                        class="p-2 rounded-lg hover:bg-neutral-100 transition-colors relative"
                    >
                        <PlusCircleIcon class="h-6 w-6 text-neutral-700" />
                    </button>
                    
                    <!-- Quick Actions Menu -->
                    <div
                        v-if="showQuickActions"
                        v-click-outside="() => showQuickActions = false"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-neutral-200 py-2 z-50"
                    >
                        <Link
                            :href="route('admin.users.create')"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-neutral-50 transition-colors"
                        >
                            <UserPlusIcon class="h-5 w-5 text-neutral-500" />
                            <span class="text-sm text-neutral-700">Add User</span>
                        </Link>
                        <Link
                            :href="route('admin.blog.posts.create')"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-neutral-50 transition-colors"
                        >
                            <DocumentPlusIcon class="h-5 w-5 text-neutral-500" />
                            <span class="text-sm text-neutral-700">New Blog Post</span>
                        </Link>
                        <Link
                            :href="route('admin.ads.create')"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-neutral-50 transition-colors"
                        >
                            <MegaphoneIcon class="h-5 w-5 text-neutral-500" />
                            <span class="text-sm text-neutral-700">Create Ad</span>
                        </Link>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="relative">
                    <button
                        @click="showNotifications = !showNotifications"
                        class="p-2 rounded-lg hover:bg-neutral-100 transition-colors relative"
                    >
                        <BellIcon class="h-6 w-6 text-neutral-700" />
                        <span v-if="unreadCount > 0" class="absolute top-1 right-1 h-2 w-2 bg-primary-600 rounded-full"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div
                        v-if="showNotifications"
                        v-click-outside="() => showNotifications = false"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-neutral-200 z-50"
                    >
                        <div class="p-4 border-b border-neutral-200">
                            <h3 class="text-sm font-semibold text-neutral-900">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div v-if="notifications.length === 0" class="p-8 text-center">
                                <BellIcon class="h-12 w-12 text-neutral-300 mx-auto mb-2" />
                                <p class="text-sm text-neutral-500">No notifications yet</p>
                            </div>
                            <div v-else class="divide-y divide-neutral-100">
                                <div
                                    v-for="notification in notifications"
                                    :key="notification.id"
                                    class="p-4 hover:bg-neutral-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                <BellIcon class="h-5 w-5 text-primary-600" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-neutral-900">{{ notification.title }}</p>
                                            <p class="text-sm text-neutral-500 mt-1">{{ notification.message }}</p>
                                            <p class="text-xs text-neutral-400 mt-1">{{ notification.time }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 border-t border-neutral-200 text-center">
                            <Link href="#" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                View all notifications
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="relative">
                    <button
                        @click="showUserMenu = !showUserMenu"
                        class="flex items-center gap-2 p-1 rounded-lg hover:bg-neutral-100 transition-colors"
                    >
                        <img
                            :src="user.avatar || '/images/default-avatar.png'"
                            :alt="user.name"
                            class="h-8 w-8 rounded-full object-cover ring-2 ring-neutral-200"
                        />
                        <ChevronDownIcon class="hidden sm:block h-4 w-4 text-neutral-500" />
                    </button>

                    <!-- User Menu Dropdown -->
                    <div
                        v-if="showUserMenu"
                        v-click-outside="() => showUserMenu = false"
                        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-neutral-200 py-2 z-50"
                    >
                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-neutral-200">
                            <p class="text-sm font-semibold text-neutral-900">{{ user.name }}</p>
                            <p class="text-xs text-neutral-500 mt-1">{{ user.email }}</p>
                        </div>

                        <!-- Menu Items -->
                        <Link
                            href="#"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-neutral-50 transition-colors"
                        >
                            <UserCircleIcon class="h-5 w-5 text-neutral-500" />
                            <span class="text-sm text-neutral-700">My Profile</span>
                        </Link>
                        <Link
                            href="#"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-neutral-50 transition-colors"
                        >
                            <Cog6ToothIcon class="h-5 w-5 text-neutral-500" />
                            <span class="text-sm text-neutral-700">Settings</span>
                        </Link>
                        <Link
                            href="#"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-neutral-50 transition-colors"
                        >
                            <QuestionMarkCircleIcon class="h-5 w-5 text-neutral-500" />
                            <span class="text-sm text-neutral-700">Help & Support</span>
                        </Link>

                        <div class="border-t border-neutral-200 my-2"></div>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full flex items-center gap-3 px-4 py-2 hover:bg-red-50 transition-colors text-left"
                        >
                            <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-600" />
                            <span class="text-sm text-red-600 font-medium">Sign Out</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Search Modal -->
        <div
            v-if="showMobileSearch"
            class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-50 p-4"
            @click="showMobileSearch = false"
        >
            <div @click.stop class="bg-white rounded-lg p-4 mt-16">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search..."
                    class="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    @keydown.enter="handleSearch"
                    autofocus
                />
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bars3Icon,
    MagnifyingGlassIcon,
    BellIcon,
    PlusCircleIcon,
    ChevronDownIcon,
    UserCircleIcon,
    Cog6ToothIcon,
    QuestionMarkCircleIcon,
    ArrowRightOnRectangleIcon,
    UserPlusIcon,
    DocumentPlusIcon,
    MegaphoneIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    collapsed: Boolean
});

defineEmits(['toggle-sidebar', 'toggle-collapse']);

const page = usePage();
const user = computed(() => page.props.auth.user);

// State
const searchQuery = ref('');
const showMobileSearch = ref(false);
const showQuickActions = ref(false);
const showNotifications = ref(false);
const showUserMenu = ref(false);

// Mock notifications (replace with real data)
const notifications = ref([]);
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

// Search handler
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        // Implement search logic
        console.log('Searching for:', searchQuery.value);
    }
};

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    }
};
</script>

