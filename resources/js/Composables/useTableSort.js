import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Table sorting composable for Inertia.js tables
 * @param {Object} options - Configuration options
 * @returns {Object} - Sort state and methods
 */
export function useTableSort(options = {}) {
    const {
        routeName,
        initialSort = '',
        initialDirection = 'asc',
        preserveState = true,
        preserveScroll = true,
        additionalParams = {},
    } = options

    const sortColumn = ref(initialSort)
    const sortDirection = ref(initialDirection)

    /**
     * Toggle sort for a column
     * @param {string} column - Column key to sort by
     */
    const sortBy = (column) => {
        if (sortColumn.value === column) {
            // Toggle direction if same column
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
        } else {
            // New column, default to ascending
            sortColumn.value = column
            sortDirection.value = 'asc'
        }

        // Navigate with sort params
        if (routeName) {
            router.get(route(routeName), {
                ...additionalParams,
                sort: sortColumn.value,
                direction: sortDirection.value,
            }, {
                preserveState,
                preserveScroll,
            })
        }
    }

    /**
     * Check if column is currently sorted
     * @param {string} column - Column key
     * @returns {boolean}
     */
    const isSorted = (column) => sortColumn.value === column

    /**
     * Get sort direction for a column
     * @param {string} column - Column key
     * @returns {string|null} - 'asc', 'desc', or null
     */
    const getSortDirection = (column) => {
        return isSorted(column) ? sortDirection.value : null
    }

    /**
     * Get CSS classes for sort indicator
     * @param {string} column - Column key
     * @returns {Object} - CSS class object
     */
    const getSortClasses = (column) => ({
        'text-primary-600 dark:text-primary-400': isSorted(column),
    })

    /**
     * Reset sorting
     */
    const resetSort = () => {
        sortColumn.value = ''
        sortDirection.value = 'asc'
    }

    return {
        sortColumn,
        sortDirection,
        sortBy,
        isSorted,
        getSortDirection,
        getSortClasses,
        resetSort,
    }
}

/**
 * Client-side sorting for small datasets
 * @param {Array} items - Array of items to sort
 * @param {string} column - Column key to sort by
 * @param {string} direction - 'asc' or 'desc'
 * @returns {Array} - Sorted array
 */
export function sortItems(items, column, direction = 'asc') {
    if (!column || !items?.length) return items

    return [...items].sort((a, b) => {
        let aVal = a[column]
        let bVal = b[column]

        // Handle null/undefined
        if (aVal == null) aVal = ''
        if (bVal == null) bVal = ''

        // Handle dates
        if (aVal instanceof Date) aVal = aVal.getTime()
        if (bVal instanceof Date) bVal = bVal.getTime()

        // Handle strings
        if (typeof aVal === 'string') aVal = aVal.toLowerCase()
        if (typeof bVal === 'string') bVal = bVal.toLowerCase()

        // Compare
        let result = 0
        if (aVal < bVal) result = -1
        if (aVal > bVal) result = 1

        return direction === 'desc' ? -result : result
    })
}
