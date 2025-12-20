import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useNotificationStore = defineStore('notifications', () => {
    // State
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)
    const error = ref(null)

    // Getters
    const unreadNotifications = computed(() => {
        return notifications.value.filter(n => !n.read_at)
    })

    const readNotifications = computed(() => {
        return notifications.value.filter(n => n.read_at)
    })

    const hasUnread = computed(() => unreadCount.value > 0)

    // Actions
    function setNotifications(data) {
        notifications.value = data || []
        unreadCount.value = data?.filter(n => !n.read_at).length || 0
    }

    function setUnreadCount(count) {
        unreadCount.value = count
    }

    async function fetchNotifications(params = {}) {
        loading.value = true
        error.value = null
        
        try {
            const response = await axios.get('/api/notifications', { params })
            setNotifications(response.data.data)
            return response.data
        } catch (err) {
            error.value = err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    async function markAsRead(notificationId) {
        try {
            await axios.post(`/api/notifications/${notificationId}/read`)
            
            // Update local state
            const notification = notifications.value.find(n => n.id === notificationId)
            if (notification) {
                notification.read_at = new Date().toISOString()
                unreadCount.value = Math.max(0, unreadCount.value - 1)
            }
        } catch (err) {
            error.value = err.message
            throw err
        }
    }

    async function markAllAsRead() {
        try {
            await axios.post('/api/notifications/mark-all-read')
            
            // Update local state
            notifications.value.forEach(n => {
                if (!n.read_at) {
                    n.read_at = new Date().toISOString()
                }
            })
            unreadCount.value = 0
        } catch (err) {
            error.value = err.message
            throw err
        }
    }

    async function deleteNotification(notificationId) {
        try {
            await axios.delete(`/api/notifications/${notificationId}`)
            
            // Remove from local state
            const index = notifications.value.findIndex(n => n.id === notificationId)
            if (index > -1) {
                const notification = notifications.value[index]
                if (!notification.read_at) {
                    unreadCount.value = Math.max(0, unreadCount.value - 1)
                }
                notifications.value.splice(index, 1)
            }
        } catch (err) {
            error.value = err.message
            throw err
        }
    }

    function addNotification(notification) {
        notifications.value.unshift(notification)
        if (!notification.read_at) {
            unreadCount.value++
        }
    }

    function reset() {
        notifications.value = []
        unreadCount.value = 0
        loading.value = false
        error.value = null
    }

    // Initialize from page props
    function initFromPageProps(pageProps) {
        if (pageProps?.notifications) {
            setNotifications(pageProps.notifications)
        }
        if (pageProps?.unreadNotificationsCount !== undefined) {
            setUnreadCount(pageProps.unreadNotificationsCount)
        }
    }

    return {
        // State
        notifications,
        unreadCount,
        loading,
        error,
        
        // Getters
        unreadNotifications,
        readNotifications,
        hasUnread,
        
        // Actions
        setNotifications,
        setUnreadCount,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        addNotification,
        reset,
        initFromPageProps,
    }
})
