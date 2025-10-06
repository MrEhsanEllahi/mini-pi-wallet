<template>
  <div class="transaction-history card">
    <div class="mb-6">
      <h2 class="text-2xl font-bold mb-2">Current Balance</h2>
      <p class="text-4xl font-bold text-green-600">{{ walletStore.formattedBalance }}</p>
    </div>

    <div>
      <h2 class="card-header">Transaction History</h2>

      <div v-if="walletStore.loading" class="text-center py-8">
        <p class="text-gray-500">Loading transactions...</p>
      </div>

      <div v-else-if="walletStore.error" class="p-4 bg-red-50 border border-red-200 rounded-md">
        <p class="text-sm text-red-600">{{ walletStore.error }}</p>
      </div>

      <div v-else-if="walletStore.transactions.length === 0" class="text-center py-8">
        <p class="text-gray-500">No transactions yet</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="transaction in walletStore.transactions"
          :key="transaction.id"
          class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span
                  :class="
                    transaction.sender_id === currentUserId
                      ? 'badge-red'
                      : 'badge-green'
                  "
                >
                  {{ transaction.sender_id === currentUserId ? 'Sent' : 'Received' }}
                </span>
                <span class="text-gray-400">•</span>
                <span class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</span>
              </div>

              <div class="text-sm text-gray-600">
                <p v-if="transaction.sender_id === currentUserId">
                  To:
                  <span class="font-medium">{{ transaction.receiver?.name || 'Unknown' }}</span>
                  <span class="font-mono text-xs text-gray-500">(ID: {{ formatUserId(transaction.receiver_id) }})</span>
                </p>
                <p v-else>
                  From:
                  <span class="font-medium">{{ transaction.sender?.name || 'Unknown' }}</span>
                  <span class="font-mono text-xs text-gray-500">(ID: {{ formatUserId(transaction.sender_id) }})</span>
                </p>

                <p v-if="transaction.sender_id === currentUserId" class="mt-1 text-xs">
                  Commission: {{ formatCurrency(transaction.commission_fee) }}
                </p>
              </div>
            </div>

            <div class="text-right">
              <p
                :class="
                  transaction.sender_id === currentUserId
                    ? 'text-red-600 font-bold text-lg'
                    : 'text-green-600 font-bold text-lg'
                "
              >
                {{ transaction.sender_id === currentUserId ? '-' : '+' }}
                {{ formatCurrency(transaction.amount) }}
              </p>
              <p v-if="transaction.sender_id === currentUserId" class="text-xs text-gray-500 mt-1">
                Total: -{{
                  formatCurrency(
                    parseFloat(transaction.amount) + parseFloat(transaction.commission_fee)
                  )
                }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWalletStore } from '../stores/wallet'
import { useApi } from '../composables/useApi'

const walletStore = useWalletStore()
const currentUserId = ref(null)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const formatUserId = (id) => {
  return String(id).padStart(6, '0')
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

const api = useApi()

const fetchTransactions = async () => {
  walletStore.setLoading(true)
  walletStore.clearError()

  try {
    const { data } = await api.get('/transactions')

    walletStore.setBalance(data.balance ?? 0)

    // Handle pagination - get the data array from paginated response
    const transactionData = data.transactions.data || data.transactions
    walletStore.setTransactions(transactionData)
  } catch (err) {
    walletStore.setError(err.response?.data?.message || err.message)
  } finally {
    walletStore.setLoading(false)
  }
}

const fetchCurrentUser = async () => {
  try {
    const { data } = await api.get('/user')
    if (data?.id) currentUserId.value = data.id
  } catch (err) {
    console.error('Failed to fetch current user:', err)
  }
}

onMounted(() => {
  fetchCurrentUser()
  fetchTransactions()
})

defineExpose({
  fetchTransactions,
})
</script>
