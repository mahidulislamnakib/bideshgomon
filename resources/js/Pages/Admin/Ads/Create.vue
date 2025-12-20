<template>
    <AdminLayout>
        <Head title="Create Ad" />

        <!-- Modern Hero Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col gap-6">
                    <!-- Back Button -->
                    <Link :href="route('admin.ads.index')" class="inline-flex items-center gap-2 text-white/90 hover:text-white transition-colors w-fit group">
                        <ChevronLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
                        Back to Ads
                    </Link>
                    
                    <!-- Header Content -->
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                            <PlusCircleIcon class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">Create Ad Campaign</h1>
                            <p class="text-white/90 text-lg">Launch a new targeted advertisement</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-8">

            <!-- Modern Form Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden max-w-4xl">
                <form @submit.prevent="submit" class="p-8 space-y-8">
                    <!-- Basic Information -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg">
                                <DocumentTextIcon class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Basic Information</h3>
                        </div>
                        <div class="space-y-6">
                            <FormInput
                                v-model="form.title"
                                label="Ad Title"
                                placeholder="Enter ad title"
                                :error="form.errors.title"
                                required
                                description="Main headline for your ad (max 255 characters)"
                            />

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Ad Body <span class="text-gray-400 font-normal">(Optional)</span>
                                </label>
                                <div class="relative">
                                    <textarea
                                        v-model="form.body"
                                        rows="5"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.body }"
                                        placeholder="Enter detailed description for your ad campaign..."
                                    />
                                    <div class="absolute bottom-3 right-3 text-xs text-gray-400 bg-white px-2">
                                        {{ form.body ? form.body.length : 0 }} characters
                                    </div>
                                </div>
                                <p v-if="form.errors.body" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <XMarkIcon class="h-4 w-4" />
                                    {{ form.errors.body }}
                                </p>
                                <p class="mt-2 text-sm text-gray-500">Supporting text and detailed information for your advertisement</p>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg">
                                <PhotoIcon class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Ad Image</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Image <span class="text-gray-400">(Optional)</span>
                                </label>
                                <input
                                    type="file"
                                    @change="handleFileChange"
                                    accept="image/*"
                                    class="input-base"
                                    :class="{ 'border-red-500': form.errors.image_url }"
                                />
                                <p v-if="form.errors.image_url" class="mt-1 text-sm text-red-600">{{ form.errors.image_url }}</p>
                                <p class="mt-1 text-sm text-gray-500">Maximum file size: 2MB. Recommended: 400x300px</p>
                            </div>

                            <!-- Image Preview -->
                            <div v-if="imagePreview" class="relative">
                                <div class="w-full max-w-sm rounded-lg overflow-hidden border-2 border-gray-200">
                                    <img :src="imagePreview" alt="Preview" class="w-full h-auto" />
                                </div>
                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="absolute top-2 right-2 p-1.5 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors"
                                >
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Call to Action -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg">
                                <CursorArrowRaysIcon class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Call to Action</h3>
                        </div>
                        <div class="space-y-6">
                            <FormInput
                                v-model="form.cta_url"
                                label="CTA URL"
                                type="url"
                                placeholder="https://example.com"
                                :error="form.errors.cta_url"
                                description="Link users will visit when clicking the ad (Optional)"
                            />

                            <FormInput
                                v-model="form.cta_text"
                                label="CTA Button Text"
                                placeholder="Learn More"
                                :error="form.errors.cta_text"
                                description="Text shown on the call-to-action button (Optional, default: 'Learn More')"
                            />
                        </div>
                    </div>

                    <!-- Placement & Scheduling -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg">
                                <CalendarIcon class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Placement & Scheduling</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gradient-to-br from-gray-50 to-white p-5 rounded-xl border-2 border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <div class="p-1 bg-gradient-to-br from-indigo-500 to-purple-600 rounded">
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                        </svg>
                                    </div>
                                    Placement <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.placement"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white font-medium"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.placement }"
                                    required
                                >
                                    <option value="" disabled>🎯 Select ad placement</option>
                                    <option value="sidebar">📱 Sidebar (Desktop only)</option>
                                    <option value="inline">📄 Inline (All devices)</option>
                                    <option value="empty_state">🎴 Empty State (Large card)</option>
                                    <option value="banner">🎪 Banner (Top of page)</option>
                                    <option value="sticky_bottom">📍 Sticky Bottom (Mobile only)</option>
                                    <option value="modal">🪟 Modal (Popup)</option>
                                </select>
                                <p v-if="form.errors.placement" class="mt-2 text-sm text-red-600">{{ form.errors.placement }}</p>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-white p-5 rounded-xl border-2 border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <div class="p-1 bg-gradient-to-br from-amber-500 to-orange-600 rounded">
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    Priority
                                </label>
                                <input
                                    v-model.number="form.priority"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.priority }"
                                    placeholder="50"
                                />
                                <p v-if="form.errors.priority" class="mt-2 text-sm text-red-600">{{ form.errors.priority }}</p>
                                <p class="mt-2 text-xs text-gray-500">⚡ Higher priority ads shown first (0-100)</p>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-white p-5 rounded-xl border-2 border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <div class="p-1 bg-gradient-to-br from-green-500 to-emerald-600 rounded">
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    Start Date <span class="text-gray-400 font-normal">(Optional)</span>
                                </label>
                                <input
                                    v-model="form.start_at"
                                    type="datetime-local"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.start_at }"
                                />
                                <p v-if="form.errors.start_at" class="mt-2 text-sm text-red-600">{{ form.errors.start_at }}</p>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-white p-5 rounded-xl border-2 border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <div class="p-1 bg-gradient-to-br from-red-500 to-rose-600 rounded">
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    End Date <span class="text-gray-400 font-normal">(Optional)</span>
                                </label>
                                <input
                                    v-model="form.end_at"
                                    type="datetime-local"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.end_at }"
                                />
                                <p v-if="form.errors.end_at" class="mt-2 text-sm text-red-600">{{ form.errors.end_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg">
                                <CheckCircleIcon class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Status</h3>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-white p-5 rounded-xl border-2 border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <div class="p-1 bg-gradient-to-br from-green-500 to-emerald-600 rounded">
                                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                Initial Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white font-medium"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.status }"
                                required
                            >
                                <option value="" disabled>🎯 Select status</option>
                                <option value="draft">📝 Draft (Not visible)</option>
                                <option value="active">✅ Active (Live now)</option>
                                <option value="paused">⏸️ Paused (Temporarily hidden)</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</p>
                            <p class="mt-2 text-xs text-gray-500">💡 Set ad visibility status - can be changed later</p>
                        </div>
                    </div>

                    <!-- Targeting (Optional) -->
                    <div>
                        <button
                            type="button"
                            @click="showTargeting = !showTargeting"
                            class="flex items-center gap-2 text-lg font-semibold text-gray-900 hover:text-red-600 transition-colors"
                        >
                            <ChevronRightIcon class="h-5 w-5 transition-transform" :class="{ 'rotate-90': showTargeting }" />
                            Targeting Options
                            <span class="text-sm font-normal text-gray-500">(Optional - Leave empty for all)</span>
                        </button>

                        <div v-if="showTargeting" class="mt-6 space-y-6">
                            <!-- Pages -->
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-6 rounded-xl border-2 border-indigo-100">
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <div class="p-1.5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    Target Pages
                                </label>
                                <textarea
                                    v-model="targetingPages"
                                    rows="4"
                                    class="w-full px-4 py-3 border-2 border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none bg-white"
                                    placeholder="user_dashboard, blog_posts, jobs_list, services_index (comma-separated)"
                                />
                                <p class="mt-3 text-sm text-indigo-700 flex items-start gap-2">
                                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Specific pages where ad should appear. Leave empty to show on all pages. Use comma-separated page identifiers.</span>
                                </p>
                            </div>

                            <!-- Roles -->
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 p-6 rounded-xl border-2 border-blue-100">
                                <label class="block text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <div class="p-1.5 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    Target Roles
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer bg-white p-3 rounded-lg border-2 border-blue-100 hover:border-blue-300 transition-all group">
                                        <input type="checkbox" value="user" v-model="targetingRoles" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4" />
                                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">👤 User</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-white p-3 rounded-lg border-2 border-blue-100 hover:border-blue-300 transition-all group">
                                        <input type="checkbox" value="admin" v-model="targetingRoles" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4" />
                                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">👑 Admin</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-white p-3 rounded-lg border-2 border-blue-100 hover:border-blue-300 transition-all group">
                                        <input type="checkbox" value="agency" v-model="targetingRoles" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4" />
                                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">🏢 Agency</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-white p-3 rounded-lg border-2 border-blue-100 hover:border-blue-300 transition-all group">
                                        <input type="checkbox" value="consultant" v-model="targetingRoles" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4" />
                                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">💼 Consultant</span>
                                    </label>
                                </div>
                                <p class="mt-3 text-sm text-blue-700 flex items-start gap-2">
                                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Select user roles to target. Leave empty to target all roles.</span>
                                </p>
                            </div>

                            <!-- Devices -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl border-2 border-green-100">
                                <label class="block text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    Target Devices
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <label class="flex items-center gap-3 cursor-pointer bg-white p-4 rounded-lg border-2 border-green-100 hover:border-green-300 transition-all group">
                                        <input type="checkbox" value="desktop" v-model="targetingDevices" class="rounded text-green-600 focus:ring-green-500 w-4 h-4" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-700 group-hover:text-green-700">🖥️ Desktop</div>
                                            <div class="text-xs text-gray-500">Laptop & PC users</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer bg-white p-4 rounded-lg border-2 border-green-100 hover:border-green-300 transition-all group">
                                        <input type="checkbox" value="tablet" v-model="targetingDevices" class="rounded text-green-600 focus:ring-green-500 w-4 h-4" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-700 group-hover:text-green-700">📱 Tablet</div>
                                            <div class="text-xs text-gray-500">iPad & Android tablets</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer bg-white p-4 rounded-lg border-2 border-green-100 hover:border-green-300 transition-all group">
                                        <input type="checkbox" value="mobile" v-model="targetingDevices" class="rounded text-green-600 focus:ring-green-500 w-4 h-4" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-700 group-hover:text-green-700">📲 Mobile</div>
                                            <div class="text-xs text-gray-500">Smartphones</div>
                                        </div>
                                    </label>
                                </div>
                                <p class="mt-3 text-sm text-green-700 flex items-start gap-2">
                                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Select devices to target. Leave empty to target all devices.</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 pt-8 border-t border-gray-200">
                        <Link 
                            :href="route('admin.ads.index')" 
                            class="px-6 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all"
                        >
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Ad Campaign</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormInput from '@/Components/Base/FormInput.vue';
import { 
    XMarkIcon, 
    ChevronRightIcon,
    ChevronLeftIcon,
    PlusCircleIcon,
    DocumentTextIcon,
    PhotoIcon,
    CursorArrowRaysIcon,
    CalendarIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

// State
const showTargeting = ref(false);
const imagePreview = ref(null);
const targetingPages = ref('');
const targetingRoles = ref([]);
const targetingDevices = ref([]);

// Form
const form = useForm({
    title: '',
    body: '',
    image_url: null,
    cta_url: '',
    cta_text: 'Learn More',
    placement: '',
    start_at: '',
    end_at: '',
    priority: 50,
    status: 'draft',
    meta: {}
});

// Handle file change
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image_url = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Remove image
const removeImage = () => {
    form.image_url = null;
    imagePreview.value = null;
};

// Submit form
const submit = () => {
    // Build targeting meta
    const meta = {};
    
    if (targetingPages.value) {
        meta.pages = targetingPages.value.split(',').map(p => p.trim()).filter(p => p);
    }
    
    if (targetingRoles.value.length > 0) {
        meta.roles = targetingRoles.value;
    }
    
    if (targetingDevices.value.length > 0) {
        meta.devices = targetingDevices.value;
    }
    
    form.meta = meta;
    
    // Submit
    form.post(route('admin.ads.store'), {
        preserveScroll: true,
    });
};
</script>
