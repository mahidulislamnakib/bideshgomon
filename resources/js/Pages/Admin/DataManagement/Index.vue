<template>
    <Head title="Data Management" />
    
    <AdminLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-neutral-900">
            <!-- Compact Hero -->
            <div class="bg-gradient-to-r from-growth-600 to-teal-600 px-4 py-6 sm:px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white">Data Management</h1>
                            <p class="text-sm text-white/80 mt-0.5">Manage reference data with CRUD operations and CSV import/export</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-2">
                                <span class="text-lg font-bold text-white">{{ stats.geographic }}</span>
                                <span class="text-xs text-white/80 ml-1">Geographic</span>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-2">
                                <span class="text-lg font-bold text-white">{{ stats.professional }}</span>
                                <span class="text-xs text-white/80 ml-1">Professional</span>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-2">
                                <span class="text-lg font-bold text-white">{{ stats.content }}</span>
                                <span class="text-xs text-white/80 ml-1">Content</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Data Management Sections -->
                <div class="space-y-8">
                    <!-- Geographic Data -->
                    <section>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <GlobeAltIcon class="h-5 w-5 text-blue-500" />
                            Geographic & Location Data
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <DataCard
                                title="Countries"
                                :count="stats.countries || 0"
                                icon="globe"
                                description="Manage countries with ISO codes, flags, and regions"
                                :route="route('admin.data.countries.index')"
                                color="blue"
                            />
                            <DataCard
                                title="Cities"
                                :count="stats.cities || 0"
                                icon="building"
                                description="Major cities with coordinates and timezones"
                                :route="route('admin.data.cities.index')"
                                color="blue"
                            />
                            <DataCard
                                title="Airports"
                                :count="stats.airports || 0"
                                icon="plane"
                                description="International airports with IATA codes"
                                :route="route('admin.data.airports.index')"
                                color="blue"
                            />
                            <DataCard
                                title="Currencies"
                                :count="stats.currencies || 0"
                                icon="currency"
                                description="World currencies with BDT exchange rates"
                                :route="route('admin.data.currencies.index')"
                                color="blue"
                            />
                        </div>
                    </section>

                    <!-- Professional & Education Data -->
                    <section>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <BriefcaseIcon class="h-5 w-5 text-green-500" />
                            Professional & Education Data
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <DataCard
                                title="Skills"
                                :count="stats.skills || 0"
                                icon="star"
                                description="Professional skills database"
                                :route="route('admin.data.skills.index')"
                                color="green"
                            />
                            <DataCard
                                title="Skill Categories"
                                :count="stats.skillCategories || 0"
                                icon="folder"
                                description="Skill categorization hierarchy"
                                :route="route('admin.data.skill-categories.index')"
                                color="green"
                            />
                            <DataCard
                                title="Job Categories"
                                :count="stats.jobCategories || 0"
                                icon="briefcase"
                                description="Hierarchical job classifications"
                                :route="route('admin.data.job-categories.index')"
                                color="green"
                            />
                            <DataCard
                                title="Degrees"
                                :count="stats.degrees || 0"
                                icon="academic"
                                description="Educational qualifications"
                                :route="route('admin.data.degrees.index')"
                                color="green"
                            />
                            <DataCard
                                title="Languages"
                                :count="stats.languages || 0"
                                icon="language"
                                description="World languages with proficiency levels"
                                :route="route('admin.data.languages.index')"
                                color="green"
                            />
                            <DataCard
                                title="Language Tests"
                                :count="stats.languageTests || 0"
                                icon="clipboard"
                                description="IELTS, TOEFL, and other test types"
                                :route="route('admin.data.language-tests.index')"
                                color="green"
                            />
                            <DataCard
                                title="Institution Types"
                                :count="stats.institutionTypes || 0"
                                icon="building"
                                description="University, college, and school types"
                                :route="route('admin.data.institution-types.index')"
                                color="green"
                            />
                        </div>
                    </section>

                    <!-- System & Content Data -->
                    <section>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <CogIcon class="h-5 w-5 text-purple-500" />
                            System & Content Data
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <DataCard
                                title="Service Categories"
                                :count="stats.serviceCategories || 0"
                                icon="grid"
                                description="Main service classification"
                                :route="route('admin.data.service-categories.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Blog Categories"
                                :count="stats.blogCategories || 0"
                                icon="book"
                                description="Blog content categories"
                                :route="route('admin.data.blog-categories.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Blog Tags"
                                :count="stats.blogTags || 0"
                                icon="tag"
                                description="Content tagging system"
                                :route="route('admin.data.blog-tags.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Document Types"
                                :count="stats.documentTypes || 0"
                                icon="document"
                                description="Required document classifications"
                                :route="route('admin.data.document-types.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Bank Names"
                                :count="stats.bankNames || 0"
                                icon="bank"
                                description="Bangladeshi bank directory"
                                :route="route('admin.data.bank-names.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Visa Types"
                                :count="stats.visaTypes || 0"
                                icon="passport"
                                description="Visa category classifications"
                                :route="route('admin.data.visa-types.index')"
                                color="purple"
                            />
                            <DataCard
                                title="Relationship Types"
                                :count="stats.relationshipTypes || 0"
                                icon="users"
                                description="Family relationship categories"
                                :route="route('admin.data.relationship-types.index')"
                                color="purple"
                            />
                        </div>
                    </section>

                    <!-- Templates & Advanced -->
                    <section>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <DocumentDuplicateIcon class="h-5 w-5 text-indigo-500" />
                            Templates & Advanced
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <DataCard
                                title="CV Templates"
                                :count="stats.cvTemplates || 0"
                                icon="template"
                                description="Resume/CV templates for builder"
                                :route="route('admin.data.cv-templates.index')"
                                color="indigo"
                            />
                            <DataCard
                                title="Email Templates"
                                :count="stats.emailTemplates || 0"
                                icon="email"
                                description="System email templates"
                                :route="route('admin.data.email-templates.index')"
                                color="indigo"
                            />
                            <DataCard
                                title="SEO Settings"
                                :count="stats.seoSettings || 0"
                                icon="search"
                                description="Page SEO configurations"
                                :route="route('admin.data.seo-settings.index')"
                                color="indigo"
                            />
                            <DataCard
                                title="Smart Suggestions"
                                :count="stats.smartSuggestions || 0"
                                icon="alert"
                                description="AI-powered suggestions system"
                                :route="route('admin.data.smart-suggestions.index')"
                                color="indigo"
                            />
                            <DataCard
                                title="System Events"
                                :count="stats.systemEvents || 0"
                                icon="cog"
                                description="Event triggers and notifications"
                                :route="route('admin.data.system-events.index')"
                                color="indigo"
                            />
                        </div>
                    </section>
                </div>

                <!-- Bulk Operations -->
                <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <CloudArrowUpIcon class="h-8 w-8 text-growth-600" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bulk Import/Export</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Each data section supports CSV bulk upload and export. Download templates, fill in your data, and import hundreds of records at once.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="#" class="inline-flex items-center px-4 py-2 bg-growth-600 hover:bg-growth-700 text-white text-sm font-medium rounded-2xl transition-colors">
                                    <DocumentTextIcon class="mr-2 h-4 w-4" />
                                    View Documentation
                                </a>
                                <a href="#" class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-600 text-sm font-medium rounded-2xl transition-colors">
                                    <ArrowDownTrayIcon class="mr-2 h-4 w-4" />
                                    Download All Templates
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataCard from '@/Components/Admin/DataManagement/DataCard.vue';
import { 
    GlobeAltIcon, BriefcaseIcon, CogIcon, DocumentDuplicateIcon,
    CloudArrowUpIcon, DocumentTextIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            geographic: 227,
            professional: 283,
            content: 72,
            countries: 60,
            cities: 70,
            airports: 55,
            currencies: 42,
            skills: 100,
            skillCategories: 15,
            jobCategories: 73,
            degrees: 40,
            languages: 35,
            languageTests: 25,
            institutionTypes: 10,
            serviceCategories: 8,
            blogCategories: 10,
            blogTags: 44,
            documentTypes: 10,
            bankNames: 50,
            visaTypes: 20,
            relationshipTypes: 8,
            cvTemplates: 12,
            emailTemplates: 15,
            seoSettings: 20,
            smartSuggestions: 5,
            systemEvents: 10
        })
    }
});
</script>
