import { ref, computed } from 'vue'

/**
 * Composable for managing bulk selection in admin tables
 * @param {Object} options - Configuration options
 * @param {Function} options.getItems - Function that returns the current items array
 * @param {Function} options.getId - Function to get ID from an item (default: item => item.id)
 */
export function useBulkSelection(options = {}) {
    const { getItems = () => [], getId = (item) => item.id } = options
    
    const selectedItems = ref([])
    
    /**
     * Check if all items are selected
     */
    const allSelected = computed(() => {
        const items = getItems()
        return items.length > 0 && selectedItems.value.length === items.length
    })
    
    /**
     * Check if some (but not all) items are selected
     */
    const someSelected = computed(() => {
        const items = getItems()
        return selectedItems.value.length > 0 && selectedItems.value.length < items.length
    })
    
    /**
     * Check if any items are selected
     */
    const hasSelection = computed(() => selectedItems.value.length > 0)
    
    /**
     * Get count of selected items
     */
    const selectionCount = computed(() => selectedItems.value.length)
    
    /**
     * Toggle selection of a single item
     * @param {number|string} id - The ID of the item to toggle
     */
    function toggleItem(id) {
        const index = selectedItems.value.indexOf(id)
        if (index > -1) {
            selectedItems.value.splice(index, 1)
        } else {
            selectedItems.value.push(id)
        }
    }
    
    /**
     * Check if an item is selected
     * @param {number|string} id - The ID to check
     * @returns {boolean}
     */
    function isSelected(id) {
        return selectedItems.value.includes(id)
    }
    
    /**
     * Toggle all items selection
     */
    function toggleAll() {
        const items = getItems()
        if (allSelected.value) {
            selectedItems.value = []
        } else {
            selectedItems.value = items.map(getId)
        }
    }
    
    /**
     * Clear all selections
     */
    function clearSelection() {
        selectedItems.value = []
    }
    
    /**
     * Select specific items
     * @param {Array} ids - Array of IDs to select
     */
    function selectItems(ids) {
        selectedItems.value = [...ids]
    }
    
    return {
        selectedItems,
        allSelected,
        someSelected,
        hasSelection,
        selectionCount,
        toggleItem,
        isSelected,
        toggleAll,
        clearSelection,
        selectItems,
    }
}
