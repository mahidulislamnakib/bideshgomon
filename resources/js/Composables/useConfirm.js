import { ref, reactive } from 'vue'

/**
 * Confirmation modal state
 */
const state = reactive({
    show: false,
    type: 'danger',
    title: 'Confirm Action',
    message: 'Are you sure you want to proceed?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    processing: false,
    onConfirm: null,
    onCancel: null,
})

/**
 * Show a confirmation modal
 * @param {Object} options - Confirmation options
 * @param {string} [options.type='danger'] - Modal type: 'danger', 'warning', 'info', 'success'
 * @param {string} [options.title='Confirm Action'] - Modal title
 * @param {string} [options.message='Are you sure?'] - Modal message
 * @param {string} [options.confirmText='Confirm'] - Confirm button text
 * @param {string} [options.cancelText='Cancel'] - Cancel button text
 * @returns {Promise<boolean>} - Resolves true if confirmed, false if cancelled
 */
function confirm(options = {}) {
    return new Promise((resolve) => {
        state.show = true
        state.type = options.type || 'danger'
        state.title = options.title || 'Confirm Action'
        state.message = options.message || 'Are you sure you want to proceed?'
        state.confirmText = options.confirmText || 'Confirm'
        state.cancelText = options.cancelText || 'Cancel'
        state.processing = false
        
        state.onConfirm = () => {
            state.show = false
            resolve(true)
        }
        
        state.onCancel = () => {
            state.show = false
            resolve(false)
        }
    })
}

/**
 * Shorthand for delete confirmation
 */
function confirmDelete(itemName = 'this item') {
    return confirm({
        type: 'danger',
        title: 'Delete Confirmation',
        message: `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
        confirmText: 'Delete',
    })
}

/**
 * Shorthand for bulk action confirmation
 */
function confirmBulk(action, count, itemType = 'item') {
    const plural = count === 1 ? itemType : `${itemType}s`
    return confirm({
        type: action === 'delete' ? 'danger' : 'warning',
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} ${count} ${plural}`,
        message: `Are you sure you want to ${action} ${count} ${plural}?${action === 'delete' ? ' This action cannot be undone.' : ''}`,
        confirmText: action.charAt(0).toUpperCase() + action.slice(1),
    })
}

/**
 * Shorthand for status change confirmation
 */
function confirmStatusChange(action, itemName = 'this item') {
    return confirm({
        type: 'warning',
        title: 'Confirm Status Change',
        message: `Are you sure you want to ${action} ${itemName}?`,
        confirmText: action.charAt(0).toUpperCase() + action.slice(1),
    })
}

/**
 * Set processing state (shows spinner on confirm button)
 */
function setProcessing(processing) {
    state.processing = processing
}

/**
 * Close the modal programmatically
 */
function close() {
    state.show = false
}

/**
 * useConfirm composable
 */
export function useConfirm() {
    return {
        state,
        confirm,
        confirmDelete,
        confirmBulk,
        confirmStatusChange,
        setProcessing,
        close,
    }
}

// Export singleton for global access
export const confirmModal = {
    confirm,
    confirmDelete,
    confirmBulk,
    confirmStatusChange,
    setProcessing,
    close,
}
