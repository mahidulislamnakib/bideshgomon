<template>
    <Head title="BideshGomon - Your Global Migration Partner" />

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <div class="max-w-[1760px] mx-auto px-6 lg:px-20">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <Link :href="route('welcome')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-10 w-auto" />
                    <span class="text-2xl font-bold text-emerald-600 hidden sm:block">BideshGomon</span>
                </Link>

                <!-- Desktop Search Bar -->
                <div class="hidden md:block flex-1 max-w-2xl mx-12">
                    <SearchBar />
                </div>

                <!-- Right Menu -->
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('register')"
                        class="hidden lg:block text-sm font-semibold text-gray-700 hover:text-gray-900 px-4 py-2 rounded-full hover:bg-gray-100 transition-colors"
                    >
                        Become a Partner
                    </Link>

                    <button class="p-2.5 hover:bg-gray-100 rounded-full transition-colors">
                        <GlobeAltIcon class="h-5 w-5 text-gray-700" />
                    </button>

                    <!-- User Menu -->
                    <div v-if="$page.props.auth.user" class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-full hover:shadow-md transition-all"
                        >
                            <Bars3Icon class="h-4 w-4 text-gray-700" />
                            <UserCircleIcon class="h-7 w-7 text-gray-700" />
                        </button>

                        <!-- Dropdown -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div
                                v-if="showUserMenu"
                                v-click-outside="() => showUserMenu = false"
                                class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-gray-200 py-2"
                            >
                                <Link :href="route('dashboard')" class="menu-item">
                                    <span class="font-semibold">Dashboard</span>
                                </Link>
                                <Link :href="route('profile.edit')" class="menu-item">
                                    Profile
                                </Link>
                                <Link :href="route('user.applications.index')" class="menu-item">
                                    My Applications
                                </Link>
                                <Link :href="route('wallet.index')" class="menu-item">
                                    Wallet
                                </Link>
                                <div class="border-t border-gray-200 my-2"></div>
                                <Link :href="route('logout')" method="post" as="button" class="menu-item w-full text-left">
                                    Log out
                                </Link>
                            </div>
                        </Transition>
                    </div>

                    <!-- Auth Buttons -->
                    <div v-else class="flex items-center gap-2">
                        <Link
                            :href="route('login')"
                            class="px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 rounded-full transition-colors"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-4 py-2 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-full transition-colors"
                        >
                            Sign up
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div class="md:hidden px-6 pb-4">
            <SearchBar />
        </div>
    </header>

    <!-- Category Pills -->
    <CategoryPills
        :categories="categories"
        @category-change="handleCategoryChange"
        @filters-open="showFiltersModal = true"
    />

    <!-- Hero Section -->
    <section class="relative h-[70vh] min-h-[600px] bg-gradient-to-br from-emerald-500 to-emerald-700">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/10"></div>

        <!-- Hero Content -->
        <div class="relative h-full flex items-center justify-center text-center px-6">
            <div class="max-w-4xl">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
                    Your Journey Abroad<br />Starts Here
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-2xl mx-auto">
                    Trusted by 50,000+ Bangladeshis. Expert visa assistance for 100+ countries.
                </p>
                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <Link
                        :href="route('services.index')"
                        class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105"
                    >
                        Explore Services
                    </Link>
                    <button
                        @click="playVideo"
                        class="px-8 py-4 bg-white/90 hover:bg-white text-gray-900 text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105 flex items-center gap-2"
                    >
                        <PlayIcon class="h-5 w-5" />
                        Watch Success Stories
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Indicators -->
    <section class="py-12 bg-gray-50 border-b border-gray-200">
        <div class="max-w-[1760px] mx-auto px-6 lg:px-20">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-emerald-600 mb-2">50,000+</div>
                    <div class="text-sm text-gray-600">Happy Clients</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-emerald-600 mb-2">100+</div>
                    <div class="text-sm text-gray-600">Countries Covered</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-emerald-600 mb-2">95%</div>
                    <div class="text-sm text-gray-600">Success Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-emerald-600 mb-2">24/7</div>
                    <div class="text-sm text-gray-600">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Services Grid -->
    <section class="py-16 bg-white">
        <div class="max-w-[1760px] mx-auto px-6 lg:px-20">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Popular Services</h2>
                    <p class="text-gray-600">Handpicked services with verified success rates</p>
                </div>
                <Link
                    :href="route('services.index')"
                    class="hidden md:flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-full transition-colors"
                >
                    View All
                    <ArrowRightIcon class="h-4 w-4" />
                </Link>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <ServiceCard
                    v-for="service in featuredServices"
                    :key="service.id"
                    :service="service"
                    @wishlist-toggle="handleWishlistToggle"
                />
            </div>

            <!-- Mobile View All Button -->
            <div class="md:hidden mt-8 text-center">
                <Link
                    :href="route('services.index')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-full transition-colors"
                >
                    View All Services
                    <ArrowRightIcon class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </section>

    <!-- Destinations Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-[1760px] mx-auto px-6 lg:px-20">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Top Destinations</h2>
                <p class="text-gray-600">Explore visa services for popular countries</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button
                    v-for="destination in topDestinations"
                    :key="destination.id"
                    @click="exploreDestination(destination.slug)"
                    class="relative h-64 rounded-2xl overflow-hidden group cursor-pointer bg-gradient-to-br from-emerald-50 to-emerald-100 hover:from-emerald-100 hover:to-emerald-200 transition-all"
                >
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-7xl font-bold text-emerald-500/10">{{ destination.name.charAt(0) }}</span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/20 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 text-left">
                        <p class="text-2xl font-bold text-gray-900 mb-1">{{ destination.name }}</p>
                        <p class="text-sm text-gray-700">{{ destination.services }} services</p>
                    </div>
                </button>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16 bg-white">
        <div class="max-w-5xl mx-auto px-6 lg:px-20 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
            <p class="text-gray-600 mb-12 text-lg">Simple steps to your dream destination</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="relative">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-emerald-600">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Choose Service</h3>
                    <p class="text-gray-600">Browse and select from our verified visa services</p>
                </div>

                <div class="relative">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-emerald-600">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Submit Documents</h3>
                    <p class="text-gray-600">Upload required documents through our secure platform</p>
                </div>

                <div class="relative">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-emerald-600">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Get Your Visa</h3>
                    <p class="text-gray-600">Track progress and receive your approved visa</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-[1760px] mx-auto px-6 lg:px-20">
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Success Stories</h2>
                <p class="text-gray-600">Hear from our satisfied clients</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="testimonial in testimonials"
                    :key="testimonial.id"
                    class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-shadow"
                >
                    <div class="flex items-center gap-1 mb-4">
                        <StarIcon v-for="i in 5" :key="i" class="h-5 w-5 fill-emerald-500 text-emerald-500" />
                    </div>
                    <p class="text-gray-700 mb-4 italic">"{{ testimonial.text }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                            <span class="text-emerald-700 font-bold text-lg">{{ testimonial.name.split(' ').map(n => n[0]).join('') }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ testimonial.name }}</p>
                            <p class="text-sm text-gray-500">{{ testimonial.destination }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters Modal -->
    <FiltersModal
        :is-open="showFiltersModal"
        :initial-filters="currentFilters"
        @close="showFiltersModal = false"
        @apply="handleFiltersApply"
    />

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-emerald-500 to-emerald-700">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Start Your Journey?</h2>
            <p class="text-xl text-white/90 mb-8">Join thousands who trusted us with their migration dreams</p>
            <div class="flex items-center justify-center gap-4 flex-wrap">
                <Link
                    :href="route('register')"
                    class="px-8 py-4 bg-white text-emerald-600 text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105"
                >
                    Get Started Free
                </Link>
                <Link
                    :href="route('services.index')"
                    class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all border-2 border-white"
                >
                    Browse Services
                </Link>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <Footer />
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import SearchBar from '@/Components/Airbnb/SearchBar.vue'
import CategoryPills from '@/Components/Airbnb/CategoryPills.vue'
import ServiceCard from '@/Components/Airbnb/ServiceCard.vue'
import FiltersModal from '@/Components/Airbnb/FiltersModal.vue'
import Footer from '@/Components/Footer.vue'
import { usePlaceholderImages } from '@/Composables/usePlaceholderImages'
import {
    Bars3Icon,
    UserCircleIcon,
    GlobeAltIcon,
    PlayIcon,
    ArrowRightIcon,
    StarIcon
} from '@heroicons/vue/24/outline'

const { destinations: destImages, services: serviceImages, hero: heroImages, avatars } = usePlaceholderImages()

const showUserMenu = ref(false)
const showFiltersModal = ref(false)
const selectedCategory = ref('all')
const currentFilters = ref({})

// Categories for pills
const categories = [
    { id: 'all', name: 'All Services', icon: '🌍' },
    { id: 'visa', name: 'Visa', icon: '✈️' },
    { id: 'education', name: 'Study Abroad', icon: '🎓' },
    { id: 'work', name: 'Work Visa', icon: '💼' },
    { id: 'tourist', name: 'Tourist', icon: '🏖️' },
    { id: 'migration', name: 'Migration', icon: '🏠' },
    { id: 'business', name: 'Business', icon: '📊' },
    { id: 'medical', name: 'Medical', icon: '🏥' },
    { id: 'attestation', name: 'Attestation', icon: '📋' },
]

// Featured services (using placeholder images)
const featuredServices = [
    {
        id: 1,
        title: 'USA Student Visa - Complete Package',
        location: 'United States',
        images: [serviceImages.education, destImages.usa],
        price: 45000,
        rating: 4.9,
        reviews: 234,
        duration: '4-6 weeks',
        type: 'Study Visa',
        badge: 'Bestseller',
        features: ['Document Review', 'Interview Prep', 'Embassy Support'],
        isWishlisted: false,
    },
    {
        id: 2,
        title: 'Canada PR - Express Entry Assistance',
        location: 'Canada',
        images: [serviceImages.migration, destImages.canada],
        price: 95000,
        rating: 4.8,
        reviews: 189,
        duration: '6-8 months',
        type: 'Permanent Residence',
        badge: 'High Success',
        features: ['Profile Assessment', 'Documentation', 'Application Filing'],
        isWishlisted: false,
    },
    {
        id: 3,
        title: 'Australia Work Visa - Skilled Migration',
        location: 'Australia',
        images: [serviceImages.work, destImages.australia],
        price: 75000,
        rating: 4.7,
        reviews: 156,
        duration: '8-12 weeks',
        type: 'Work Visa',
        features: ['Skills Assessment', 'Job Search Support', 'Visa Lodgement'],
        isWishlisted: false,
    },
    {
        id: 4,
        title: 'UK Tourist Visa - Family Visit',
        location: 'United Kingdom',
        images: [serviceImages.tourist, destImages.uk],
        price: 25000,
        rating: 4.9,
        reviews: 312,
        duration: '2-3 weeks',
        type: 'Tourist Visa',
        badge: 'Fast Processing',
        features: ['Document Checklist', 'Application Review', 'Appointment Booking'],
        isWishlisted: false,
    },
    {
        id: 5,
        title: 'Germany Student Visa - University Admission',
        location: 'Germany',
        images: [serviceImages.education, destImages.germany],
        price: 55000,
        rating: 4.8,
        reviews: 198,
        duration: '5-7 weeks',
        type: 'Study Visa',
        features: ['University Application', 'Blocked Account', 'Embassy Support'],
        isWishlisted: false,
    },
    {
        id: 6,
        title: 'Dubai Work Permit - Employment Visa',
        location: 'UAE',
        images: [serviceImages.business, destImages.uae],
        price: 35000,
        rating: 4.6,
        reviews: 243,
        duration: '3-4 weeks',
        type: 'Work Visa',
        features: ['Job Contract Review', 'Medical Test', 'Emirates ID'],
        isWishlisted: false,
    },
    {
        id: 7,
        title: 'Singapore PR - Permanent Residence',
        location: 'Singapore',
        images: [serviceImages.migration, destImages.singapore],
        price: 85000,
        rating: 4.7,
        reviews: 134,
        duration: '4-6 months',
        type: 'Permanent Residence',
        features: ['Eligibility Check', 'Document Preparation', 'ICA Submission'],
        isWishlisted: false,
    },
    {
        id: 8,
        title: 'Malaysia Student Visa - EMGS Processing',
        location: 'Malaysia',
        images: [serviceImages.education, destImages.malaysia],
        price: 28000,
        rating: 4.8,
        reviews: 287,
        duration: '6-8 weeks',
        type: 'Study Visa',
        features: ['University Letter', 'EMGS Application', 'SEV Collection'],
        isWishlisted: false,
    },
]

// Top destinations (using placeholder images)
const topDestinations = [
    { id: 1, name: 'United States', slug: 'usa', image: destImages.usa, services: 45 },
    { id: 2, name: 'Canada', slug: 'canada', image: destImages.canada, services: 38 },
    { id: 3, name: 'Australia', slug: 'australia', image: destImages.australia, services: 32 },
    { id: 4, name: 'United Kingdom', slug: 'uk', image: destImages.uk, services: 28 },
]

// Testimonials (using placeholder avatars)
const testimonials = [
    {
        id: 1,
        name: 'Rafiqul Islam',
        destination: 'Moved to Canada',
        avatar: avatars.male1,
        text: 'BideshGomon made my Canada PR process seamless. Their team guided me through every step. Highly recommended!'
    },
    {
        id: 2,
        name: 'Nusrat Jahan',
        destination: 'Studying in USA',
        avatar: avatars.female1,
        text: 'Got my USA student visa in just 5 weeks! The interview preparation was exceptional. Thank you BideshGomon!'
    },
    {
        id: 3,
        name: 'Kamal Hossain',
        destination: 'Working in Dubai',
        avatar: avatars.male2,
        text: 'Professional service from start to finish. They handled all my work visa documentation perfectly.'
    },
]

const handleCategoryChange = (categoryId) => {
    selectedCategory.value = categoryId
    // Fetch services for this category
}

const handleWishlistToggle = (serviceId, isWishlisted) => {
    // API call to toggle wishlist
    console.log('Wishlist toggle', serviceId, isWishlisted)
}

const handleFiltersApply = (filters) => {
    currentFilters.value = filters
    // Fetch filtered services
    console.log('Filters applied:', filters)
}

const exploreDestination = (slug) => {
    router.visit(route('destinations.show', slug))
}

const playVideo = () => {
    // Open video modal
    console.log('Play video')
}

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value()
            }
        }
        document.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent)
    },
}
</script>

<!-- Temporarily disabled for debugging
<style scoped>
.menu-item {
    display: block;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: rgb(55 65 81);
    transition: background-color 200ms;
}

.menu-item:hover {
    background-color: rgb(249 250 251);
}
</style>
-->
