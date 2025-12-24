import { ref, watch, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Debounce a value with a specified delay
 * @param {any} initialValue - Initial value
 * @param {number} delay - Debounce delay in ms
 * @returns {Object} - { value, debouncedValue }
 */
export function useDebouncedRef(initialValue = '', delay = 300) {
    const value = ref(initialValue)
    const debouncedValue = ref(initialValue)
    let timeoutId = null

    watch(value, (newValue) => {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => {
            debouncedValue.value = newValue
        }, delay)
    })

    onUnmounted(() => {
        clearTimeout(timeoutId)
    })

    return {
        value,
        debouncedValue,
    }
}

/**
 * Composable for debounced search with Inertia router
 * @param {Object} options - Configuration options
 * @param {string} options.routeName - Route name to navigate to
 * @param {Object} options.initialFilters - Initial filter values
 * @param {number} options.delay - Debounce delay in ms (default: 400)
 * @param {boolean} options.preserveScroll - Preserve scroll position (default: true)
 * @param {boolean} options.preserveState - Preserve component state (default: true)
 */
export function useDebouncedSearch(options = {}) {
    const {
        routeName = '',
        initialFilters = {},
        delay = 400,
        preserveScroll = true,
        preserveState = true,
    } = options

    const search = ref(initialFilters.search || '')
    const filters = ref({ ...initialFilters })
    const isSearching = ref(false)
    let searchTimeout = null

    /**
     * Perform search with current filters
     */
    const performSearch = () => {
        if (!routeName) return

        isSearching.value = true

        router.get(route(routeName), {
            search: search.value || undefined,
            ...Object.fromEntries(
                Object.entries(filters.value).filter(([_, v]) => v !== '' && v !== null && v !== undefined)
            ),
        }, {
            preserveScroll,
            preserveState,
            replace: true,
            onFinish: () => {
                isSearching.value = false
            },
        })
    }

    /**
     * Debounced search - call this on input
     */
    const debouncedSearch = () => {
        clearTimeout(searchTimeout)
        searchTimeout = setTimeout(() => {
            performSearch()
        }, delay)
    }

    /**
     * Immediate search - bypasses debounce
     */
    const immediateSearch = () => {
        clearTimeout(searchTimeout)
        performSearch()
    }

    /**
     * Clear all filters
     */
    const clearFilters = () => {
        search.value = ''
        filters.value = Object.fromEntries(
            Object.keys(filters.value).map(key => [key, ''])
        )
        immediateSearch()
    }

    /**
     * Update a specific filter
     */
    const updateFilter = (key, value) => {
        filters.value[key] = value
        debouncedSearch()
    }

    // Cleanup on unmount
    onUnmounted(() => {
        clearTimeout(searchTimeout)
    })

    // Watch search for auto-debounce
    watch(search, () => {
        debouncedSearch()
    })

    return {
        search,
        filters,
        isSearching,
        debouncedSearch,
        immediateSearch,
        performSearch,
        clearFilters,
        updateFilter,
    }
}

/**
 * Simple debounce function
 * @param {Function} fn - Function to debounce
 * @param {number} delay - Delay in ms
 */
export function debounce(fn, delay = 300) {
    let timeoutId = null
    return function (...args) {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => fn.apply(this, args), delay)
    }
}
