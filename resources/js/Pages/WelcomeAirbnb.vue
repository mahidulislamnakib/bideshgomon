<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'
import SearchBar from '@/Components/Airbnb/SearchBar.vue'
import CategoryPills from '@/Components/Airbnb/CategoryPills.vue'
import {
    GlobeAltIcon,
    AcademicCapIcon,
    BriefcaseIcon,
    MapPinIcon,
    ClockIcon,
    CheckCircleIcon,
    StarIcon,
} from '@heroicons/vue/24/outline'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const { formatCurrency } = useBangladeshFormat()

// Categories for the pills
const categories = [
    { id: 'all', label: 'All Services', icon: '🌍' },
    { id: 'visa', label: 'Visa Services', icon: '✈️' },
    { id: 'education', label: 'Study Abroad', icon: '🎓' },
    { id: 'work', label: 'Work Permits', icon: '💼' },
    { id: 'tourist', label: 'Tourist Visa', icon: '🏖️' },
    { id: 'migration', label: 'Migration', icon: '🏠' },
]

// Featured services (8 services)
const featuredServices = [
    {
        id: 1,
        title: 'USA Student Visa (F-1)',
        location: 'United States',
        images: [],
        price: 45000,
        rating: 4.9,
        reviews: 342,
        duration: '3-6 months',
        type: 'Study Visa',
        badge: 'Bestseller',
        features: ['University admission help', 'I-20 processing', 'Interview prep'],
        isWishlisted: false,
    },
    {
        id: 2,
        title: 'Canada PR (Express Entry)',
        location: 'Canada',
        images: [],
        price: 95000,
        rating: 4.8,
        reviews: 256,
        duration: '6-12 months',
        type: 'Permanent Residency',
        badge: 'High Success Rate',
        features: ['Points calculator', 'Job search support', 'Settlement services'],
        isWishlisted: false,
    },
    {
        id: 3,
        title: 'Australia Work Visa (482)',
        location: 'Australia',
        images: [],
        price: 75000,
        rating: 4.7,
        reviews: 189,
        duration: '2-4 months',
        type: 'Work Visa',
        badge: null,
        features: ['Employer nomination', 'Skills assessment', 'Family inclusion'],
        isWishlisted: false,
    },
    {
        id: 4,
        title: 'UK Tourist Visa',
        location: 'United Kingdom',
        images: [],
        price: 25000,
        rating: 4.9,
        reviews: 421,
        duration: '2-4 weeks',
        type: 'Tourist Visa',
        badge: 'Fast Processing',
        features: ['Itinerary planning', 'Hotel bookings', '24/7 support'],
        isWishlisted: false,
    },
    {
        id: 5,
        title: 'Germany Student Visa',
        location: 'Germany',
        images: [],
        price: 55000,
        rating: 4.8,
        reviews: 167,
        duration: '2-3 months',
        type: 'Study Visa',
        badge: null,
        features: ['University selection', 'Blocked account', 'Accommodation help'],
        isWishlisted: false,
    },
    {
        id: 6,
        title: 'Dubai Work Permit',
        location: 'United Arab Emirates',
        images: [],
        price: 35000,
        rating: 4.6,
        reviews: 298,
        duration: '1-2 months',
        type: 'Work Permit',
        badge: null,
        features: ['Job placement', 'Medical test', 'Emirates ID'],
        isWishlisted: false,
    },
    {
        id: 7,
        title: 'Singapore PR Application',
        location: 'Singapore',
        images: [],
        price: 85000,
        rating: 4.7,
        reviews: 134,
        duration: '4-6 months',
        type: 'Permanent Residency',
        badge: null,
        features: ['Eligibility check', 'Document preparation', 'Follow-up service'],
        isWishlisted: false,
    },
    {
        id: 8,
        title: 'Malaysia Student Visa (eVAL)',
        location: 'Malaysia',
        images: [],
        price: 28000,
        rating: 4.8,
        reviews: 203,
        duration: '1-2 months',
        type: 'Study Visa',
        badge: null,
        features: ['University enrollment', 'EMGS processing', 'SEV sticker'],
        isWishlisted: false,
    },
]

const showFiltersModal = ref(false)
</script>

<template>
    <Head title="Welcome to BideshGomon" />

    <div class="min-h-screen bg-white">
        <!-- Header -->
        <Header />

        <!-- Category Pills (Sticky) -->
        <div class="sticky top-0 z-40 bg-white border-b border-gray-200">
            <CategoryPills
                :categories="categories"
                initial-category="all"
                @category-change="(id) => console.log('Category:', id)"
                @filters-open="showFiltersModal = true"
            />
        </div>

        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-emerald-500 to-emerald-700 py-20">
            <div class="absolute inset-0 bg-black/10"></div>
            
            <div class="relative max-w-7xl mx-auto px-6 lg:px-20 text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                    Go Abroad with <span class="text-emerald-100">Confidence</span>
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-3xl mx-auto">
                    All-in-one platform for visa applications, job search, travel booking, and document services. 
                    Simplify your journey abroad with expert guidance.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex items-center justify-center gap-4 flex-wrap mb-12">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-8 py-4 bg-white text-emerald-600 text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105"
                    >
                        Get Started Free
                    </Link>
                    <Link
                        :href="route('services.index')"
                        class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105"
                    >
                        Explore Services
                    </Link>
                </div>

                <!-- Search Bar -->
                <div class="max-w-4xl mx-auto">
                    <SearchBar />
                </div>
            </div>
        </section>

        <!-- Trust Indicators -->
        <section class="py-12 bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-6 lg:px-20">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold text-emerald-600 mb-2">50,000+</div>
                        <div class="text-sm text-gray-600">Happy Clients</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-emerald-600 mb-2">100+</div>
                        <div class="text-sm text-gray-600">Countries Covered</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-emerald-600 mb-2">95%</div>
                        <div class="text-sm text-gray-600">Success Rate</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-emerald-600 mb-2">24/7</div>
                        <div class="text-sm text-gray-600">Support Available</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Services -->
        <section class="py-16">
            <div class="max-w-[1760px] mx-auto px-6 lg:px-20">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Popular Services</h2>
                    <p class="text-gray-600">Most requested visa and migration services</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="service in featuredServices"
                        :key="service.id"
                        class="group cursor-pointer rounded-2xl border border-gray-200 hover:shadow-lg transition-all"
                    >
                        <!-- Card Header -->
                        <div class="relative aspect-[4/3] rounded-t-2xl overflow-hidden bg-gradient-to-br from-emerald-50 to-emerald-100 group-hover:from-emerald-100 group-hover:to-emerald-200 transition-all">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-8xl font-bold text-emerald-500/10">{{ service.title.charAt(0) }}</span>
                            </div>
                            <div v-if="service.badge" class="absolute top-3 left-3">
                                <span class="px-3 py-1 bg-emerald-500 text-white text-xs font-semibold rounded-full shadow-md">
                                    {{ service.badge }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="flex items-center gap-1 text-gray-600">
                                    <MapPinIcon class="h-4 w-4" />
                                    {{ service.location }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <StarIcon class="h-4 w-4 fill-emerald-500 text-emerald-500" />
                                    <span class="font-semibold text-gray-900">{{ service.rating }}</span>
                                    <span class="text-gray-500">({{ service.reviews }})</span>
                                </span>
                            </div>
                            
                            <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ service.title }}</h3>
                            <p class="text-sm text-gray-600 mb-3">{{ service.duration }} • {{ service.type }}</p>
                            
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(service.price) }}</span>
                                <span class="text-sm text-gray-600">total</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <Link
                        :href="route('services.index')"
                        class="inline-block px-8 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-full transition-colors"
                    >
                        View All Services →
                    </Link>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-20">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">How It Works</h2>
                    <p class="text-gray-600">Three simple steps to start your journey</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                            <span class="text-2xl font-bold text-emerald-600">1</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Choose Your Service</h3>
                        <p class="text-gray-600">Browse our comprehensive visa and migration services</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                            <span class="text-2xl font-bold text-emerald-600">2</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Submit Documents</h3>
                        <p class="text-gray-600">Upload required documents securely through our platform</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                            <span class="text-2xl font-bold text-emerald-600">3</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Track Progress</h3>
                        <p class="text-gray-600">Monitor your application status in real-time</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-20">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Why Choose BideshGomon</h2>
                    <p class="text-gray-600">Your trusted partner for international opportunities</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-2xl border border-gray-200 hover:shadow-lg transition-shadow">
                        <CheckCircleIcon class="h-12 w-12 text-emerald-600 mb-4" />
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Expert Guidance</h3>
                        <p class="text-gray-600">Professional consultants with years of experience in visa processing</p>
                    </div>
                    <div class="p-6 rounded-2xl border border-gray-200 hover:shadow-lg transition-shadow">
                        <ClockIcon class="h-12 w-12 text-emerald-600 mb-4" />
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Fast Processing</h3>
                        <p class="text-gray-600">Streamlined workflow ensures quick turnaround for your applications</p>
                    </div>
                    <div class="p-6 rounded-2xl border border-gray-200 hover:shadow-lg transition-shadow">
                        <StarIcon class="h-12 w-12 text-emerald-600 mb-4" />
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">95% Success Rate</h3>
                        <p class="text-gray-600">Proven track record of successful visa approvals</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-r from-emerald-600 to-emerald-700">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Ready to Start Your Journey?
                </h2>
                <p class="text-xl text-white/90 mb-8">
                    Join thousands of satisfied clients who achieved their dreams with BideshGomon
                </p>
                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-8 py-4 bg-white text-emerald-600 text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105"
                    >
                        Create Free Account
                    </Link>
                    <Link
                        :href="route('services.index')"
                        class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white text-lg font-semibold rounded-full shadow-xl hover:shadow-2xl transition-all hover:scale-105"
                    >
                        Browse All Services
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <Footer />
    </div>
</template>
