<template>
    <Head title="Settings" />

    <DashboardShell>
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Platform Settings</h1>
                <p class="text-neutral-600 mt-2">Configure your platform settings and preferences</p>
            </div>
            <div class="flex items-center gap-3">
                <ActionButton
                    label="Clear Cache"
                    :href="route('admin.settings.clear-cache')"
                    variant="secondary"
                    size="md"
                    :icon="ArrowPathIcon"
                />
                <ActionButton
                    label="Reset to Defaults"
                    :href="route('admin.settings.seed')"
                    variant="ghost"
                    size="md"
                    :icon="ArrowPathIcon"
                />
            </div>
        </div>

        <!-- Settings Accordion -->
        <div class="space-y-4">
            <!-- General Settings -->
            <SettingsSection
                :icon="CogIcon"
                title="General Settings"
                :description="`${getGroupSettingsCount('general')} settings`"
                :expanded="expandedSections.general"
                @toggle="toggleSection('general')"
                color="primary"
            >
                <form @submit.prevent="saveGroup('general')" class="space-y-6">
                    <div
                        v-for="setting in getGroupSettings('general')"
                        :key="setting.key"
                        class="pb-6 border-b border-neutral-100 last:border-0"
                    >
                        <SettingInput
                            :setting="setting"
                            :value="form.settings.find(s => s.key === setting.key)?.value"
                            @update="updateSetting"
                        />
                    </div>

                    <div class="flex justify-end pt-4">
                        <ActionButton
                            label="Save General Settings"
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :icon="CheckIcon"
                        />
                    </div>
                </form>
            </SettingsSection>

            <!-- Service Modules -->
            <SettingsSection
                :icon="BriefcaseIcon"
                title="Service Modules"
                :description="`${getGroupSettingsCount('modules')} modules`"
                :expanded="expandedSections.modules"
                @toggle="toggleSection('modules')"
                color="success"
            >
                <form @submit.prevent="saveGroup('modules')" class="space-y-6">
                    <div
                        v-for="setting in getGroupSettings('modules')"
                        :key="setting.key"
                        class="pb-6 border-b border-neutral-100 last:border-0"
                    >
                        <SettingInput
                            :setting="setting"
                            :value="form.settings.find(s => s.key === setting.key)?.value"
                            @update="updateSetting"
                        />
                    </div>

                    <div class="flex justify-end pt-4">
                        <ActionButton
                            label="Save Service Modules"
                            type="submit"
                            variant="success"
                            :loading="form.processing"
                            :icon="CheckIcon"
                        />
                    </div>
                </form>
            </SettingsSection>

            <!-- Blog Configuration -->
            <SettingsSection
                :icon="DocumentTextIcon"
                title="Blog Configuration"
                description="Manage blog posts and categories"
                :expanded="expandedSections.blog"
                @toggle="toggleSection('blog')"
                color="primary"
            >
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <DocumentTextIcon class="h-8 w-8 text-growth-600 flex-shrink-0" />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Blog Management</h3>
                            <p class="text-sm text-neutral-600 mb-4">
                                Create and manage blog posts, categories, and tags for your platform.
                            </p>
                            <div class="flex gap-3">
                                <Link
                                    :href="route('admin.blog.posts.index')"
                                    class="inline-flex items-center px-4 py-2 bg-growth-600 text-white text-sm font-medium rounded-2xl hover:bg-growth-700 transition-colors shadow-sm"
                                >
                                    <DocumentTextIcon class="h-5 w-5 mr-2" />
                                    Manage Posts
                                </Link>
                                <Link
                                    :href="route('admin.blog.categories.index')"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-blue-300 text-blue-700 text-sm font-medium rounded-2xl hover:bg-blue-50 transition-colors"
                                >
                                    Categories
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </SettingsSection>

            <!-- Ads Management -->
            <SettingsSection
                :icon="MegaphoneIcon"
                title="Ads Management"
                description="Configure advertisement placements"
                :expanded="expandedSections.ads"
                @toggle="toggleSection('ads')"
                color="warning"
            >
                <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <MegaphoneIcon class="h-8 w-8 text-purple-600 flex-shrink-0" />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Advertisement System</h3>
                            <p class="text-sm text-neutral-600 mb-4">
                                Manage ads across different placements: sidebar, header, footer, blog pages, and homepage.
                            </p>
                            <Link
                                :href="route('admin.ads.index')"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-2xl hover:bg-purple-700 transition-colors shadow-sm"
                            >
                                <MegaphoneIcon class="h-5 w-5 mr-2" />
                                Manage Ads
                            </Link>
                        </div>
                    </div>
                </div>
            </SettingsSection>

            <!-- SEO Settings -->
            <SettingsSection
                :icon="ShieldCheckIcon"
                title="SEO & Meta Settings"
                description="Configure SEO and meta tags"
                :expanded="expandedSections.seo"
                @toggle="toggleSection('seo')"
                color="success"
            >
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <ShieldCheckIcon class="h-8 w-8 text-green-600 flex-shrink-0" />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-neutral-900 mb-2">SEO Configuration</h3>
                            <p class="text-sm text-neutral-600 mb-4">
                                Configure page-specific SEO settings including meta titles, descriptions, keywords, Open Graph tags, and canonical URLs.
                            </p>
                            <Link
                                :href="route('seo-settings.index')"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-2xl hover:bg-green-700 transition-colors shadow-sm"
                            >
                                <ShieldCheckIcon class="h-5 w-5 mr-2" />
                                Manage SEO Settings
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- General SEO Settings (if any) -->
                <form v-if="getGroupSettings('seo').length > 0" @submit.prevent="saveGroup('seo')" class="space-y-6">
                    <div
                        v-for="setting in getGroupSettings('seo')"
                        :key="setting.key"
                        class="pb-6 border-b border-neutral-100 last:border-0"
                    >
                        <SettingInput
                            :setting="setting"
                            :value="form.settings.find(s => s.key === setting.key)?.value"
                            @update="updateSetting"
                        />
                    </div>

                    <div class="flex justify-end pt-4">
                        <ActionButton
                            label="Save SEO Settings"
                            type="submit"
                            variant="success"
                            :loading="form.processing"
                            :icon="CheckIcon"
                        />
                    </div>
                </form>
            </SettingsSection>

            <!-- Email Configuration -->
            <SettingsSection
                :icon="EnvelopeIcon"
                title="Email Configuration"
                :description="`${getGroupSettingsCount('email')} email settings`"
                :expanded="expandedSections.email"
                @toggle="toggleSection('email')"
                color="primary"
            >
                <form @submit.prevent="saveGroup('email')" class="space-y-6">
                    <div
                        v-for="setting in getGroupSettings('email')"
                        :key="setting.key"
                        class="pb-6 border-b border-neutral-100 last:border-0"
                    >
                        <SettingInput
                            :setting="setting"
                            :value="form.settings.find(s => s.key === setting.key)?.value"
                            @update="updateSetting"
                        />
                    </div>

                    <div class="flex justify-end pt-4">
                        <ActionButton
                            label="Save Email Settings"
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :icon="CheckIcon"
                        />
                    </div>
                </form>
            </SettingsSection>

            <!-- API Keys -->
            <SettingsSection
                :icon="KeyIcon"
                title="API Keys & Integrations"
                :description="`${getGroupSettingsCount('api')} API services`"
                :expanded="expandedSections.api"
                @toggle="toggleSection('api')"
                color="danger"
            >
                <!-- Security Warning -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="flex">
                        <ExclamationTriangleIcon class="h-5 w-5 text-amber-400 flex-shrink-0" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Security Notice</h3>
                            <p class="mt-1 text-sm text-amber-700">
                                API keys are sensitive credentials. Never share them publicly or commit them to version control.
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="saveGroup('api')" class="space-y-6">
                    <div
                        v-for="setting in getGroupSettings('api')"
                        :key="setting.key"
                        class="pb-6 border-b border-neutral-100 last:border-0"
                    >
                        <SettingInput
                            :setting="setting"
                            :value="form.settings.find(s => s.key === setting.key)?.value"
                            @update="updateSetting"
                            :is-secret="true"
                        />
                    </div>

                    <div class="flex justify-end pt-4">
                        <ActionButton
                            label="Save API Keys"
                            type="submit"
                            variant="danger"
                            :loading="form.processing"
                            :icon="CheckIcon"
                        />
                    </div>
                </form>
            </SettingsSection>
        </div>

        <!-- Info Panel -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <CogIcon class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Settings Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Settings marked as "Public" can be accessed by the frontend application</li>
                            <li>Changes are cached for performance - clear cache if needed</li>
                            <li>Use "Reset to Defaults" to restore original platform settings</li>
                            <li>Each section can be saved independently</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </DashboardShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardShell from '@/Layouts/DashboardShell.vue';
import ActionButton from '@/Components/UI/ActionButton.vue';
import SettingsSection from '@/Components/Settings/SettingsSection.vue';
import SettingInput from '@/Components/Settings/SettingInput.vue';
import {
    CogIcon,
    EnvelopeIcon,
    BriefcaseIcon,
    CheckIcon,
    ArrowPathIcon,
    KeyIcon,
    ShieldCheckIcon,
    DocumentTextIcon,
    MegaphoneIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Array,
    currentGroup: String,
    groups: Object
});

const expandedSections = ref({
    general: true,
    modules: false,
    blog: false,
    ads: false,
    seo: false,
    email: false,
    api: false
});

const form = useForm({
    settings: props.settings || []
});

const toggleSection = (section) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

const getGroupSettings = (group) => {
    return (props.settings || []).filter(s => s.group === group);
};

const getGroupSettingsCount = (group) => {
    return getGroupSettings(group).length;
};

const updateSetting = (key, value) => {
    const index = form.settings.findIndex(s => s.key === key);
    if (index !== -1) {
        form.settings[index].value = value;
    }
};

const saveGroup = (group) => {
    const groupSettings = form.settings.filter(s => s.group === group);
    
    const submitForm = useForm({
        settings: groupSettings
    });
    
    submitForm.post(route('admin.settings.update', { group }), {
        preserveScroll: true,
        onSuccess: () => {
            console.log(`${group} settings saved successfully`);
        },
        onError: (errors) => {
            console.error('Save errors:', errors);
        }
    });
};
</script>
