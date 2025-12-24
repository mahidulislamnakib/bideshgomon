import { ref, shallowRef } from 'vue';

/**
 * Composable for managing quick view modals
 * Provides state management for opening/closing modals with data
 */
export function useQuickView() {
    const isOpen = ref(false);
    const loading = ref(false);
    const data = shallowRef(null);
    const error = ref(null);

    /**
     * Open the quick view with data
     * @param {Object|Function} dataOrFetcher - Data object or async function to fetch data
     */
    async function open(dataOrFetcher) {
        isOpen.value = true;
        error.value = null;
        
        if (typeof dataOrFetcher === 'function') {
            // Async data fetcher
            loading.value = true;
            try {
                data.value = await dataOrFetcher();
            } catch (e) {
                error.value = e.message || 'Failed to load data';
                console.error('[QuickView] Fetch error:', e);
            } finally {
                loading.value = false;
            }
        } else {
            // Direct data object
            data.value = dataOrFetcher;
        }
    }

    /**
     * Close the quick view and clear data
     */
    function close() {
        isOpen.value = false;
        // Keep data for exit animation, clear after
        setTimeout(() => {
            data.value = null;
            error.value = null;
        }, 300);
    }

    /**
     * Toggle quick view
     */
    function toggle(dataOrFetcher) {
        if (isOpen.value) {
            close();
        } else {
            open(dataOrFetcher);
        }
    }

    return {
        isOpen,
        loading,
        data,
        error,
        open,
        close,
        toggle,
    };
}

/**
 * Pre-configured quick view for common entity types
 * Includes formatters for displaying data nicely
 */
export function useEntityQuickView(entityType) {
    const quickView = useQuickView();

    const formatters = {
        user: (user) => ({
            title: user.name,
            subtitle: user.email,
            fields: [
                { label: 'Email', value: user.email },
                { label: 'Phone', value: user.phone || 'N/A' },
                { label: 'Role', value: user.role?.name || 'User' },
                { label: 'Status', value: user.suspended_at ? 'Suspended' : 'Active', badge: true },
                { label: 'Verified', value: user.email_verified_at ? 'Yes' : 'No', badge: true },
                { label: 'Registered', value: new Date(user.created_at).toLocaleDateString() },
            ],
        }),
        
        job: (job) => ({
            title: job.title,
            subtitle: job.company_name,
            fields: [
                { label: 'Company', value: job.company_name },
                { label: 'Location', value: job.location || 'Remote' },
                { label: 'Type', value: job.job_type || 'Full-time' },
                { label: 'Salary', value: job.salary_range || 'Competitive' },
                { label: 'Status', value: job.status, badge: true },
                { label: 'Posted', value: new Date(job.created_at).toLocaleDateString() },
            ],
        }),
        
        booking: (booking) => ({
            title: `Booking #${booking.id}`,
            subtitle: booking.user?.name || 'Guest',
            fields: [
                { label: 'Customer', value: booking.user?.name || 'Guest' },
                { label: 'Service', value: booking.service?.name || 'N/A' },
                { label: 'Date', value: new Date(booking.date).toLocaleDateString() },
                { label: 'Status', value: booking.status, badge: true },
                { label: 'Amount', value: `৳${booking.amount?.toLocaleString() || 0}` },
                { label: 'Created', value: new Date(booking.created_at).toLocaleDateString() },
            ],
        }),
    };

    function openWithFormat(entity) {
        const formatter = formatters[entityType];
        const formatted = formatter ? formatter(entity) : { title: 'Details', fields: [] };
        quickView.open({ raw: entity, ...formatted });
    }

    return {
        ...quickView,
        openWithFormat,
    };
}

export default useQuickView;
