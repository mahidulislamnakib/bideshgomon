import { toast as sonnerToast } from 'vue-sonner'

/**
 * Toast notification composable using Vue Sonner
 * Provides consistent notification UI across the application
 */
export function useToast() {
    return {
        /**
         * Show success notification
         */
        success(message, options = {}) {
            return sonnerToast.success(message, {
                duration: 4000,
                ...options,
            })
        },

        /**
         * Show error notification
         */
        error(message, options = {}) {
            return sonnerToast.error(message, {
                duration: 5000,
                ...options,
            })
        },

        /**
         * Show info notification
         */
        info(message, options = {}) {
            return sonnerToast.info(message, {
                duration: 4000,
                ...options,
            })
        },

        /**
         * Show warning notification
         */
        warning(message, options = {}) {
            return sonnerToast.warning(message, {
                duration: 4000,
                ...options,
            })
        },

        /**
         * Show loading notification
         */
        loading(message, options = {}) {
            return sonnerToast.loading(message, options)
        },

        /**
         * Show promise notification (automatically shows loading, success, error)
         */
        promise(promise, messages = {}) {
            return sonnerToast.promise(promise, {
                loading: messages.loading || 'Loading...',
                success: messages.success || 'Success!',
                error: messages.error || 'Something went wrong',
            })
        },

        /**
         * Custom notification with action button
         */
        custom(message, options = {}) {
            return sonnerToast(message, options)
        },

        /**
         * Dismiss specific toast by ID
         */
        dismiss(toastId) {
            sonnerToast.dismiss(toastId)
        },

        /**
         * Dismiss all toasts
         */
        dismissAll() {
            sonnerToast.dismiss()
        },

        /**
         * Show form validation errors
         */
        formErrors(errors) {
            const errorMessages = Object.values(errors).flat()
            if (errorMessages.length > 0) {
                return sonnerToast.error(errorMessages[0], {
                    description: errorMessages.length > 1 
                        ? `${errorMessages.length - 1} more error(s)` 
                        : undefined,
                    duration: 5000,
                })
            }
        },

        /**
         * Show Bangladesh-specific currency notification
         */
        walletSuccess(amount, description) {
            const formatted = `৳${parseFloat(amount).toLocaleString('en-BD', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`
            
            return sonnerToast.success(`${formatted} ${description}`, {
                duration: 4000,
            })
        },

        /**
         * Show application submission success
         */
        applicationSubmitted(applicationId) {
            return sonnerToast.success('Application submitted successfully!', {
                description: `Application ID: #${applicationId}`,
                duration: 6000,
                action: {
                    label: 'View',
                    onClick: () => window.location.href = `/applications/${applicationId}`,
                },
            })
        },
    }
}

// Export default for convenience
export const toast = useToast()
