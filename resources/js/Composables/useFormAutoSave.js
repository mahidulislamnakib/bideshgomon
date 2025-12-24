import { ref, watch, onUnmounted } from 'vue';
import { useDebounceFn } from '@vueuse/core';

/**
 * Auto-save composable for forms
 * Saves form data to localStorage with debouncing
 * Restores data on page load and clears on successful submit
 */
export function useFormAutoSave(formId, formData, options = {}) {
    const {
        debounceMs = 1000,          // Debounce delay in ms
        enabled = true,             // Enable/disable auto-save
        onRestore = null,           // Callback when data is restored
        excludeFields = [],         // Fields to exclude from saving
        showNotification = true,    // Show save notification
    } = options;

    const STORAGE_KEY = `form_autosave_${formId}`;
    const lastSaved = ref(null);
    const hasRestoredData = ref(false);
    const isSaving = ref(false);

    /**
     * Get saveable data (excluding specified fields and files)
     */
    function getSaveableData() {
        const data = {};
        const formValue = typeof formData.value !== 'undefined' ? formData.value : formData;
        
        for (const [key, value] of Object.entries(formValue)) {
            // Skip excluded fields
            if (excludeFields.includes(key)) continue;
            
            // Skip file objects (can't be serialized)
            if (value instanceof File || value instanceof FileList) continue;
            
            // Skip null/undefined
            if (value === null || value === undefined) continue;
            
            data[key] = value;
        }
        
        return data;
    }

    /**
     * Save form data to localStorage
     */
    function saveToStorage() {
        if (!enabled) return;
        
        try {
            isSaving.value = true;
            const data = getSaveableData();
            const saveData = {
                formId,
                data,
                timestamp: Date.now(),
                version: 1,
            };
            
            localStorage.setItem(STORAGE_KEY, JSON.stringify(saveData));
            lastSaved.value = new Date();
            
            if (showNotification) {
                // Could integrate with toast system
                console.debug(`[AutoSave] Form "${formId}" saved at ${lastSaved.value.toLocaleTimeString()}`);
            }
        } catch (error) {
            console.error('[AutoSave] Failed to save:', error);
        } finally {
            isSaving.value = false;
        }
    }

    /**
     * Debounced save function
     */
    const debouncedSave = useDebounceFn(saveToStorage, debounceMs);

    /**
     * Restore form data from localStorage
     */
    function restoreFromStorage() {
        if (!enabled) return null;
        
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (!stored) return null;
            
            const parsed = JSON.parse(stored);
            
            // Validate stored data
            if (!parsed.data || parsed.formId !== formId) {
                clearStorage();
                return null;
            }
            
            // Check if data is too old (24 hours)
            const maxAge = 24 * 60 * 60 * 1000;
            if (Date.now() - parsed.timestamp > maxAge) {
                clearStorage();
                return null;
            }
            
            hasRestoredData.value = true;
            lastSaved.value = new Date(parsed.timestamp);
            
            if (onRestore) {
                onRestore(parsed.data);
            }
            
            return parsed.data;
        } catch (error) {
            console.error('[AutoSave] Failed to restore:', error);
            clearStorage();
            return null;
        }
    }

    /**
     * Clear saved data from localStorage
     */
    function clearStorage() {
        try {
            localStorage.removeItem(STORAGE_KEY);
            lastSaved.value = null;
            hasRestoredData.value = false;
        } catch (error) {
            console.error('[AutoSave] Failed to clear storage:', error);
        }
    }

    /**
     * Apply restored data to form
     */
    function applyRestoredData(form, data) {
        if (!data) return;
        
        for (const [key, value] of Object.entries(data)) {
            if (key in form) {
                form[key] = value;
            }
        }
    }

    /**
     * Get time since last save
     */
    function getTimeSinceLastSave() {
        if (!lastSaved.value) return null;
        
        const seconds = Math.floor((Date.now() - lastSaved.value.getTime()) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
        return `${Math.floor(seconds / 86400)} days ago`;
    }

    /**
     * Setup auto-save watcher
     */
    function setupWatcher(watchTarget) {
        const stopWatch = watch(
            watchTarget,
            () => {
                debouncedSave();
            },
            { deep: true }
        );
        
        return stopWatch;
    }

    return {
        // State
        lastSaved,
        hasRestoredData,
        isSaving,
        
        // Methods
        saveToStorage,
        restoreFromStorage,
        clearStorage,
        applyRestoredData,
        getTimeSinceLastSave,
        setupWatcher,
        debouncedSave,
    };
}

/**
 * Simplified hook for Inertia forms
 * Automatically watches and saves form data
 */
export function useInertiaFormAutoSave(formId, form, options = {}) {
    const autoSave = useFormAutoSave(formId, form, options);
    
    // Setup watcher for form data
    const stopWatch = autoSave.setupWatcher(() => form.data());
    
    // Restore on mount
    const restoredData = autoSave.restoreFromStorage();
    if (restoredData) {
        autoSave.applyRestoredData(form, restoredData);
    }
    
    // Clean up on unmount
    onUnmounted(() => {
        stopWatch();
    });
    
    /**
     * Call this on successful form submission
     */
    function onSubmitSuccess() {
        autoSave.clearStorage();
    }
    
    return {
        ...autoSave,
        restoredData,
        onSubmitSuccess,
    };
}

/**
 * Simple localStorage form persistence without debouncing
 */
export function useSimpleFormPersist(key, defaultValue = {}) {
    const STORAGE_KEY = `form_persist_${key}`;
    
    function load() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            return stored ? JSON.parse(stored) : defaultValue;
        } catch {
            return defaultValue;
        }
    }
    
    function save(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (error) {
            console.error('[FormPersist] Save failed:', error);
        }
    }
    
    function clear() {
        localStorage.removeItem(STORAGE_KEY);
    }
    
    return { load, save, clear };
}

export default useFormAutoSave;
