import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useWalletStore = defineStore('wallet', () => {
  const balance = ref(0)
  const transactions = ref([])
  const loading = ref(false)
  const error = ref(null)

  const formattedBalance = computed(() => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(balance.value)
  })

  function setBalance(newBalance) {
    balance.value = parseFloat(newBalance) || 0
  }

  function setTransactions(newTransactions) {
    transactions.value = newTransactions
  }

  function addTransaction(transaction) {
    transactions.value.unshift(transaction)
  }

  function setLoading(value) {
    loading.value = value
  }

  function setError(errorMessage) {
    error.value = errorMessage
  }

  function clearError() {
    error.value = null
  }

  return {
    balance,
    transactions,
    loading,
    error,
    formattedBalance,
    setBalance,
    setTransactions,
    addTransaction,
    setLoading,
    setError,
    clearError,
  }
})
