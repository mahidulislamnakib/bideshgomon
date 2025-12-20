<script setup>
import { ref, onErrorCaptured, computed } from 'vue'
import { ExclamationTriangleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

const error = ref(null)
const errorInfo = ref(null)
const isDev = computed(() => import.meta.env.DEV)

onErrorCaptured((err, instance, info) => {
    error.value = err
    errorInfo.value = info
    
    // Log to console in development
    if (import.meta.env.DEV) {
        console.error('Error caught by boundary:', err)
        console.error('Component stack:', info)
    }
    
    // Return false to prevent error from propagating
    return false
})

function reset() {
    error.value = null
    errorInfo.value = null
}

function reload() {
    window.location.reload()
}
</script>

<template>
    <!-- Error UI -->
    <div v-if="error" class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header with Gradient -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                    <div class="flex items-center justify-center">
                        <ExclamationTriangleIcon class="h-16 w-16 text-white" />
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-white text-center">
                        Something went wrong
                    </h1>
                </div>

                <!-- Error Content -->
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 text-center">
                        We're sorry, but something unexpected happened. Our team has been notified.
                    </p>

                    <!-- Error Details (Development Only) -->
                    <div v-if="isDev" class="mt-4 bg-gray-50 rounded-lg p-4">
                        <details>
                            <summary class="cursor-pointer font-medium text-gray-700 hover:text-gray-900 select-none">
                                Error Details (Dev Only)
                            </summary>
                            <div class="mt-3 space-y-2">
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-700">Message:</span>
                                    <p class="text-red-600 mt-1 font-mono text-xs">{{ error.message }}</p>
                                </div>
                                <div v-if="error.stack" class="text-sm">
                                    <span class="font-semibold text-gray-700">Stack:</span>
                                    <pre class="mt-1 text-xs bg-white p-2 rounded overflow-x-auto">{{ error.stack }}</pre>
                                </div>
                                <div v-if="errorInfo" class="text-sm">
                                    <span class="font-semibold text-gray-700">Component:</span>
                                    <p class="text-gray-600 mt-1 font-mono text-xs">{{ errorInfo }}</p>
                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-center pt-4">
                        <button
                            @click="reset"
                            class="inline-flex items-center px-4 py-2 bg-brand-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-green-700 focus:bg-brand-green-700 active:bg-brand-green-900 focus:outline-none focus:ring-2 focus:ring-brand-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <ArrowPathIcon class="h-4 w-4 mr-2" />
                            Try Again
                        </button>
                        <button
                            @click="reload"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Reload Page
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <p class="mt-4 text-center text-sm text-gray-500">
                Error Code: {{ Date.now() }}
            </p>
        </div>
    </div>

    <!-- Normal Content -->
    <slot v-else />
</template>
