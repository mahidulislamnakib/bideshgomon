import { watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { toast } from './useToast'

/**
 * Composable to watch Laravel flash messages and show toasts
 * Place this in your layout component to handle flash messages globally
 */
export function useFlashMessages() {
    const page = usePage()
    
    const flash = computed(() => page.props.flash || {})
    
    // Watch for flash message changes
    watch(
        () => ({
            success: page.props.flash?.success,
            error: page.props.flash?.error,
            warning: page.props.flash?.warning,
            info: page.props.flash?.info,
            message: page.props.flash?.message,
        }),
        (newFlash, oldFlash) => {
            // Show success toast
            if (newFlash.success && newFlash.success !== oldFlash?.success) {
                toast.success(newFlash.success)
            }
            
            // Show error toast
            if (newFlash.error && newFlash.error !== oldFlash?.error) {
                toast.error(newFlash.error)
            }
            
            // Show warning toast
            if (newFlash.warning && newFlash.warning !== oldFlash?.warning) {
                toast.warning(newFlash.warning)
            }
            
            // Show info toast
            if (newFlash.info && newFlash.info !== oldFlash?.info) {
                toast.info(newFlash.info)
            }
            
            // Show generic message as info
            if (newFlash.message && newFlash.message !== oldFlash?.message) {
                toast.info(newFlash.message)
            }
        },
        { immediate: true, deep: true }
    )
    
    return {
        flash,
    }
}
