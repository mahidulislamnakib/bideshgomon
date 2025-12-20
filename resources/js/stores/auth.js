import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref(null)
    const isAuthenticated = ref(false)
    const token = ref(null)

    // Getters
    const userRole = computed(() => user.value?.role || null)
    const isAdmin = computed(() => user.value?.role === 'admin')
    const isAgency = computed(() => user.value?.role === 'agency')
    const isConsultant = computed(() => user.value?.role === 'consultant')
    const userName = computed(() => user.value?.name || 'Guest')
    const userEmail = computed(() => user.value?.email || '')

    // Actions
    function setUser(userData) {
        user.value = userData
        isAuthenticated.value = !!userData
    }

    function setToken(tokenValue) {
        token.value = tokenValue
        if (tokenValue) {
            localStorage.setItem('auth_token', tokenValue)
        } else {
            localStorage.removeItem('auth_token')
        }
    }

    function logout() {
        user.value = null
        isAuthenticated.value = false
        token.value = null
        localStorage.removeItem('auth_token')
    }

    function updateUser(userData) {
        if (user.value) {
            user.value = { ...user.value, ...userData }
        }
    }

    function hasRole(role) {
        return user.value?.role === role
    }

    function hasPermission(permission) {
        return user.value?.permissions?.includes(permission) || false
    }

    // Initialize from Inertia page props or localStorage
    function initFromPageProps(pageProps) {
        if (pageProps?.auth?.user) {
            setUser(pageProps.auth.user)
        }
    }

    return {
        // State
        user,
        isAuthenticated,
        token,
        
        // Getters
        userRole,
        isAdmin,
        isAgency,
        isConsultant,
        userName,
        userEmail,
        
        // Actions
        setUser,
        setToken,
        logout,
        updateUser,
        hasRole,
        hasPermission,
        initFromPageProps,
    }
})
