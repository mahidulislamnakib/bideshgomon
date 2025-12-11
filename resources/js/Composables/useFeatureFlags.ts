import { ref, computed } from 'vue'

/**
 * Feature Flags Composable for Vue
 * 
 * Client-side feature flag checking with SSR-safe evaluation
 * Always do server-side evaluation for security-critical features
 */

// Global feature flags state (hydrated from server)
const featureFlags = ref<Record<string, boolean>>({})

export function useFeatureFlags() {
    /**
     * Initialize feature flags from server
     * Call this in app setup with data from Laravel
     */
    function initialize(flags: Record<string, boolean>) {
        featureFlags.value = flags
    }

    /**
     * Check if a feature is enabled
     * 
     * @param feature Feature flag name
     * @returns boolean
     */
    function isEnabled(feature: string): boolean {
        return featureFlags.value[feature] ?? false
    }

    /**
     * Reactive computed property for feature check
     * 
     * @example
     * const showNewFlow = useFeature('visa_new_flow_20250101')
     */
    function useFeature(feature: string) {
        return computed(() => isEnabled(feature))
    }

    /**
     * Check multiple features (ANY)
     */
    function isAnyEnabled(...features: string[]): boolean {
        return features.some(feature => isEnabled(feature))
    }

    /**
     * Check multiple features (ALL)
     */
    function areAllEnabled(...features: string[]): boolean {
        return features.every(feature => isEnabled(feature))
    }

    /**
     * Get all enabled features
     */
    function getEnabledFeatures(): string[] {
        return Object.entries(featureFlags.value)
            .filter(([_, enabled]) => enabled)
            .map(([feature]) => feature)
    }

    /**
     * Debug helper - log current feature flags
     */
    function debug() {
        console.group('🚩 Feature Flags')
        Object.entries(featureFlags.value).forEach(([feature, enabled]) => {
            console.log(`${enabled ? '✅' : '❌'} ${feature}`)
        })
        console.groupEnd()
    }

    return {
        initialize,
        isEnabled,
        useFeature,
        isAnyEnabled,
        areAllEnabled,
        getEnabledFeatures,
        debug,
    }
}

/**
 * Vue directive for feature flags
 * 
 * Usage:
 * <div v-feature="'visa_new_flow_20250101'">New flow content</div>
 * <div v-feature:hide="'old_feature'">Hide when feature enabled</div>
 */
export const vFeature = {
    mounted(el: HTMLElement, binding: any) {
        const { isEnabled } = useFeatureFlags()
        const feature = binding.value
        const hide = binding.arg === 'hide'
        
        const enabled = isEnabled(feature)
        const shouldShow = hide ? !enabled : enabled
        
        if (!shouldShow) {
            el.style.display = 'none'
        }
    }
}
