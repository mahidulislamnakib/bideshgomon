import { ref } from 'vue';

/**
 * Composable for clipboard operations with feedback
 */
export function useClipboard() {
    const copied = ref(false);
    const error = ref(null);
    const copiedText = ref('');
    
    let timeoutId = null;
    
    /**
     * Copy text to clipboard
     * @param {string} text - Text to copy
     * @param {number} resetDelay - Delay in ms before resetting copied state (default: 2000)
     * @returns {Promise<boolean>} - Success status
     */
    async function copy(text, resetDelay = 2000) {
        try {
            error.value = null;
            
            // Modern Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers or non-secure contexts
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                const success = document.execCommand('copy');
                document.body.removeChild(textArea);
                
                if (!success) {
                    throw new Error('Failed to copy text');
                }
            }
            
            copied.value = true;
            copiedText.value = text;
            
            // Reset after delay
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            timeoutId = setTimeout(() => {
                copied.value = false;
                copiedText.value = '';
            }, resetDelay);
            
            return true;
        } catch (err) {
            error.value = err.message || 'Failed to copy';
            copied.value = false;
            return false;
        }
    }
    
    /**
     * Copy with toast notification (requires toast system)
     * @param {string} text - Text to copy
     * @param {string} successMessage - Success message for toast
     * @param {Function} showToast - Toast function (optional)
     */
    async function copyWithToast(text, successMessage = 'Copied to clipboard!', showToast = null) {
        const success = await copy(text);
        
        if (showToast) {
            if (success) {
                showToast({ type: 'success', message: successMessage });
            } else {
                showToast({ type: 'error', message: error.value || 'Failed to copy' });
            }
        }
        
        return success;
    }
    
    /**
     * Format and copy (e.g., format phone number, add prefix)
     * @param {string} text - Text to format and copy
     * @param {Function} formatter - Formatter function
     */
    async function copyFormatted(text, formatter = (t) => t) {
        const formatted = formatter(text);
        return copy(formatted);
    }
    
    return {
        copied,
        error,
        copiedText,
        copy,
        copyWithToast,
        copyFormatted
    };
}

/**
 * Format helpers for common copy scenarios
 */
export const copyFormatters = {
    // Remove all whitespace
    compact: (text) => text.replace(/\s+/g, ''),
    
    // Lowercase
    lowercase: (text) => text.toLowerCase(),
    
    // Uppercase
    uppercase: (text) => text.toUpperCase(),
    
    // Format as URL
    asUrl: (text) => {
        if (!text.startsWith('http://') && !text.startsWith('https://')) {
            return `https://${text}`;
        }
        return text;
    },
    
    // Format phone for calling
    phoneCall: (text) => `tel:${text.replace(/[^\d+]/g, '')}`,
    
    // Format email for mailto
    emailLink: (text) => `mailto:${text}`,
    
    // JSON pretty print
    jsonPretty: (text) => {
        try {
            const obj = typeof text === 'string' ? JSON.parse(text) : text;
            return JSON.stringify(obj, null, 2);
        } catch {
            return text;
        }
    },
    
    // JSON compact
    jsonCompact: (text) => {
        try {
            const obj = typeof text === 'string' ? JSON.parse(text) : text;
            return JSON.stringify(obj);
        } catch {
            return text;
        }
    }
};

export default useClipboard;
