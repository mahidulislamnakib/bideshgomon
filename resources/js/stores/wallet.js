import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useWalletStore = defineStore('wallet', () => {
    // State
    const balance = ref(0)
    const transactions = ref([])
    const loading = ref(false)
    const error = ref(null)

    // Getters
    const formattedBalance = computed(() => {
        return `৳${balance.value.toLocaleString('en-BD', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`
    })

    const recentTransactions = computed(() => {
        return transactions.value.slice(0, 5)
    })

    const totalCredits = computed(() => {
        return transactions.value
            .filter(t => t.transaction_type === 'credit')
            .reduce((sum, t) => sum + parseFloat(t.amount), 0)
    })

    const totalDebits = computed(() => {
        return transactions.value
            .filter(t => t.transaction_type === 'debit')
            .reduce((sum, t) => sum + parseFloat(t.amount), 0)
    })

    // Actions
    function setBalance(amount) {
        balance.value = parseFloat(amount) || 0
    }

    function setTransactions(data) {
        transactions.value = data || []
    }

    async function fetchBalance() {
        loading.value = true
        error.value = null
        
        try {
            const response = await axios.get('/api/wallet/balance')
            setBalance(response.data.balance)
            return response.data
        } catch (err) {
            error.value = err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    async function fetchTransactions(params = {}) {
        loading.value = true
        error.value = null
        
        try {
            const response = await axios.get('/api/wallet/transactions', { params })
            setTransactions(response.data.data)
            return response.data
        } catch (err) {
            error.value = err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    async function creditWallet(amount, description) {
        loading.value = true
        error.value = null
        
        try {
            const response = await axios.post('/api/wallet/credit', {
                amount,
                description
            })
            
            setBalance(response.data.balance)
            
            // Add transaction to local state
            if (response.data.transaction) {
                transactions.value.unshift(response.data.transaction)
            }
            
            return response.data
        } catch (err) {
            error.value = err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    async function debitWallet(amount, description) {
        loading.value = true
        error.value = null
        
        try {
            const response = await axios.post('/api/wallet/debit', {
                amount,
                description
            })
            
            setBalance(response.data.balance)
            
            // Add transaction to local state
            if (response.data.transaction) {
                transactions.value.unshift(response.data.transaction)
            }
            
            return response.data
        } catch (err) {
            error.value = err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    function reset() {
        balance.value = 0
        transactions.value = []
        loading.value = false
        error.value = null
    }

    // Initialize from page props
    function initFromPageProps(pageProps) {
        if (pageProps?.wallet?.balance !== undefined) {
            setBalance(pageProps.wallet.balance)
        }
        if (pageProps?.wallet?.transactions) {
            setTransactions(pageProps.wallet.transactions)
        }
    }

    return {
        // State
        balance,
        transactions,
        loading,
        error,
        
        // Getters
        formattedBalance,
        recentTransactions,
        totalCredits,
        totalDebits,
        
        // Actions
        setBalance,
        setTransactions,
        fetchBalance,
        fetchTransactions,
        creditWallet,
        debitWallet,
        reset,
        initFromPageProps,
    }
})
