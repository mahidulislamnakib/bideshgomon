import { ref, computed } from 'vue';

/**
 * Composable for drag and drop list reordering
 * Uses native HTML5 drag and drop API
 */
export function useDragAndDrop(options = {}) {
    const {
        onReorder = null,
        handleClass = null, // If set, only elements with this class can initiate drag
        disabled = false
    } = options;
    
    const draggedItem = ref(null);
    const draggedIndex = ref(-1);
    const dragOverIndex = ref(-1);
    const isDragging = ref(false);
    
    /**
     * Handle drag start
     */
    function handleDragStart(event, item, index) {
        if (disabled) {
            event.preventDefault();
            return;
        }
        
        // Check if drag started from handle
        if (handleClass && !event.target.closest(`.${handleClass}`)) {
            event.preventDefault();
            return;
        }
        
        draggedItem.value = item;
        draggedIndex.value = index;
        isDragging.value = true;
        
        // Set drag image and data
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', index.toString());
        
        // Add visual feedback
        requestAnimationFrame(() => {
            if (event.target.classList) {
                event.target.classList.add('opacity-50', 'scale-95');
            }
        });
    }
    
    /**
     * Handle drag end
     */
    function handleDragEnd(event) {
        isDragging.value = false;
        draggedItem.value = null;
        draggedIndex.value = -1;
        dragOverIndex.value = -1;
        
        // Remove visual feedback
        if (event.target.classList) {
            event.target.classList.remove('opacity-50', 'scale-95');
        }
    }
    
    /**
     * Handle drag over
     */
    function handleDragOver(event, index) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        dragOverIndex.value = index;
    }
    
    /**
     * Handle drag leave
     */
    function handleDragLeave(event) {
        // Only reset if leaving the actual element, not a child
        if (!event.currentTarget.contains(event.relatedTarget)) {
            dragOverIndex.value = -1;
        }
    }
    
    /**
     * Handle drop
     */
    function handleDrop(event, items, targetIndex) {
        event.preventDefault();
        
        const sourceIndex = draggedIndex.value;
        
        if (sourceIndex === -1 || sourceIndex === targetIndex) {
            return items;
        }
        
        // Reorder the array
        const newItems = [...items];
        const [removed] = newItems.splice(sourceIndex, 1);
        newItems.splice(targetIndex, 0, removed);
        
        // Call callback if provided
        if (onReorder) {
            onReorder({
                items: newItems,
                from: sourceIndex,
                to: targetIndex,
                item: removed
            });
        }
        
        // Reset state
        dragOverIndex.value = -1;
        
        return newItems;
    }
    
    /**
     * Get drag handlers for an item
     */
    function getDragHandlers(item, index) {
        return {
            draggable: !disabled,
            onDragstart: (e) => handleDragStart(e, item, index),
            onDragend: handleDragEnd,
            onDragover: (e) => handleDragOver(e, index),
            onDragleave: handleDragLeave,
            onDrop: (e) => handleDrop(e, [], index)
        };
    }
    
    /**
     * Check if item is being dragged over
     */
    function isDraggedOver(index) {
        return dragOverIndex.value === index && draggedIndex.value !== index;
    }
    
    /**
     * Check if item is being dragged
     */
    function isBeingDragged(index) {
        return draggedIndex.value === index;
    }
    
    /**
     * Get drop indicator position
     */
    function getDropPosition(index) {
        if (dragOverIndex.value !== index || draggedIndex.value === -1) {
            return null;
        }
        return draggedIndex.value < index ? 'after' : 'before';
    }
    
    return {
        // State
        draggedItem,
        draggedIndex,
        dragOverIndex,
        isDragging,
        
        // Handlers
        handleDragStart,
        handleDragEnd,
        handleDragOver,
        handleDragLeave,
        handleDrop,
        getDragHandlers,
        
        // Helpers
        isDraggedOver,
        isBeingDragged,
        getDropPosition
    };
}

/**
 * Reorder array helper
 */
export function reorderArray(array, fromIndex, toIndex) {
    const result = [...array];
    const [removed] = result.splice(fromIndex, 1);
    result.splice(toIndex, 0, removed);
    return result;
}

/**
 * Move item in array and update position/order field
 */
export function reorderWithPosition(items, fromIndex, toIndex, positionField = 'position') {
    const reordered = reorderArray(items, fromIndex, toIndex);
    return reordered.map((item, index) => ({
        ...item,
        [positionField]: index + 1
    }));
}

export default useDragAndDrop;
