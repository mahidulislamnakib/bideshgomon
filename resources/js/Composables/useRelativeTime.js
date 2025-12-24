import { ref, computed, onMounted, onUnmounted } from 'vue';

/**
 * Composable for relative time formatting ("2 hours ago", "yesterday", etc.)
 * Follows Bangladesh timezone (BST +06:00)
 */
export function useRelativeTime() {
    const now = ref(new Date());
    let intervalId = null;
    
    // Update "now" every minute for live updates
    function startLiveUpdates(intervalMs = 60000) {
        if (intervalId) return;
        intervalId = setInterval(() => {
            now.value = new Date();
        }, intervalMs);
    }
    
    function stopLiveUpdates() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }
    
    /**
     * Format a date as relative time
     * @param {Date|string|number} date - The date to format
     * @param {object} options - Formatting options
     * @returns {string} Formatted relative time string
     */
    function formatRelative(date, options = {}) {
        const {
            addSuffix = true,
            includeSeconds = false,
            shortFormat = false,
            maxDays = 7, // After this many days, show full date
            timezone = 'Asia/Dhaka'
        } = options;
        
        if (!date) return '';
        
        const targetDate = toDate(date);
        if (!isValidDate(targetDate)) return '';
        
        const currentDate = now.value;
        const diffMs = currentDate.getTime() - targetDate.getTime();
        const diffSeconds = Math.floor(diffMs / 1000);
        const diffMinutes = Math.floor(diffSeconds / 60);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);
        const diffWeeks = Math.floor(diffDays / 7);
        const diffMonths = Math.floor(diffDays / 30);
        const diffYears = Math.floor(diffDays / 365);
        
        const isFuture = diffMs < 0;
        const absDiffSeconds = Math.abs(diffSeconds);
        const absDiffMinutes = Math.abs(diffMinutes);
        const absDiffHours = Math.abs(diffHours);
        const absDiffDays = Math.abs(diffDays);
        const absDiffWeeks = Math.abs(diffWeeks);
        const absDiffMonths = Math.abs(diffMonths);
        const absDiffYears = Math.abs(diffYears);
        
        let result = '';
        
        // Show full date if beyond maxDays
        if (absDiffDays > maxDays) {
            return formatFullDate(targetDate, timezone);
        }
        
        // Format based on time difference
        if (absDiffSeconds < 30) {
            result = shortFormat ? 'now' : 'just now';
        } else if (absDiffSeconds < 60) {
            if (includeSeconds) {
                result = shortFormat 
                    ? `${absDiffSeconds}s` 
                    : `${absDiffSeconds} second${absDiffSeconds !== 1 ? 's' : ''}`;
            } else {
                result = shortFormat ? '1m' : '1 minute';
            }
        } else if (absDiffMinutes < 60) {
            result = shortFormat 
                ? `${absDiffMinutes}m` 
                : `${absDiffMinutes} minute${absDiffMinutes !== 1 ? 's' : ''}`;
        } else if (absDiffHours < 24) {
            result = shortFormat 
                ? `${absDiffHours}h` 
                : `${absDiffHours} hour${absDiffHours !== 1 ? 's' : ''}`;
        } else if (absDiffDays === 1) {
            result = isFuture ? 'tomorrow' : 'yesterday';
            return result; // No suffix for yesterday/tomorrow
        } else if (absDiffDays < 7) {
            result = shortFormat 
                ? `${absDiffDays}d` 
                : `${absDiffDays} day${absDiffDays !== 1 ? 's' : ''}`;
        } else if (absDiffWeeks < 4) {
            result = shortFormat 
                ? `${absDiffWeeks}w` 
                : `${absDiffWeeks} week${absDiffWeeks !== 1 ? 's' : ''}`;
        } else if (absDiffMonths < 12) {
            result = shortFormat 
                ? `${absDiffMonths}mo` 
                : `${absDiffMonths} month${absDiffMonths !== 1 ? 's' : ''}`;
        } else {
            result = shortFormat 
                ? `${absDiffYears}y` 
                : `${absDiffYears} year${absDiffYears !== 1 ? 's' : ''}`;
        }
        
        // Add suffix
        if (addSuffix && result !== 'just now' && result !== 'now') {
            if (isFuture) {
                result = shortFormat ? `in ${result}` : `in ${result}`;
            } else {
                result = shortFormat ? `${result} ago` : `${result} ago`;
            }
        }
        
        return result;
    }
    
    /**
     * Format full date in Bangladesh format (DD/MM/YYYY)
     */
    function formatFullDate(date, timezone = 'Asia/Dhaka') {
        const targetDate = toDate(date);
        if (!isValidDate(targetDate)) return '';
        
        try {
            return targetDate.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                timeZone: timezone
            });
        } catch {
            // Fallback without timezone
            return targetDate.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    }
    
    /**
     * Format date and time in Bangladesh format
     */
    function formatDateTime(date, timezone = 'Asia/Dhaka') {
        const targetDate = toDate(date);
        if (!isValidDate(targetDate)) return '';
        
        try {
            return targetDate.toLocaleString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
                timeZone: timezone
            });
        } catch {
            // Fallback without timezone
            return targetDate.toLocaleString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }
    }
    
    /**
     * Get time of day greeting
     */
    function getGreeting(timezone = 'Asia/Dhaka') {
        let hour;
        try {
            const formatter = new Intl.DateTimeFormat('en-US', {
                hour: 'numeric',
                hour12: false,
                timeZone: timezone
            });
            hour = parseInt(formatter.format(now.value));
        } catch {
            hour = now.value.getHours();
        }
        
        if (hour >= 5 && hour < 12) return 'Good morning';
        if (hour >= 12 && hour < 17) return 'Good afternoon';
        if (hour >= 17 && hour < 21) return 'Good evening';
        return 'Good night';
    }
    
    /**
     * Check if date is today
     */
    function isToday(date) {
        const targetDate = toDate(date);
        if (!isValidDate(targetDate)) return false;
        
        const today = now.value;
        return targetDate.toDateString() === today.toDateString();
    }
    
    /**
     * Check if date is yesterday
     */
    function isYesterday(date) {
        const targetDate = toDate(date);
        if (!isValidDate(targetDate)) return false;
        
        const yesterday = new Date(now.value);
        yesterday.setDate(yesterday.getDate() - 1);
        return targetDate.toDateString() === yesterday.toDateString();
    }
    
    /**
     * Check if date is this week
     */
    function isThisWeek(date) {
        const targetDate = toDate(date);
        if (!isValidDate(targetDate)) return false;
        
        const today = now.value;
        const weekStart = new Date(today);
        weekStart.setDate(today.getDate() - today.getDay());
        weekStart.setHours(0, 0, 0, 0);
        
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 7);
        
        return targetDate >= weekStart && targetDate < weekEnd;
    }
    
    // Helper functions
    function toDate(input) {
        if (input instanceof Date) return input;
        if (typeof input === 'number') return new Date(input);
        if (typeof input === 'string') return new Date(input);
        return new Date();
    }
    
    function isValidDate(date) {
        return date instanceof Date && !isNaN(date.getTime());
    }
    
    return {
        now,
        formatRelative,
        formatFullDate,
        formatDateTime,
        getGreeting,
        isToday,
        isYesterday,
        isThisWeek,
        startLiveUpdates,
        stopLiveUpdates
    };
}

/**
 * Create a reactive relative time value that updates automatically
 */
export function useAutoRelativeTime(date, options = {}) {
    const { formatRelative, startLiveUpdates, stopLiveUpdates, now } = useRelativeTime();
    
    const relativeTime = computed(() => formatRelative(date, options));
    
    onMounted(() => {
        startLiveUpdates(options.updateInterval || 60000);
    });
    
    onUnmounted(() => {
        stopLiveUpdates();
    });
    
    return {
        relativeTime,
        now
    };
}

export default useRelativeTime;
