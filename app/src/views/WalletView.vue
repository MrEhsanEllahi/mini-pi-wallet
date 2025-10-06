<template>
  <div class="wallet-view">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <TransferForm />
        </div>

        <div>
          <TransactionHistory ref="transactionHistoryRef" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import TransferForm from '../components/TransferForm.vue'
import TransactionHistory from '../components/TransactionHistory.vue'
import { useWalletStore } from '../stores/wallet'
import { useApi } from '../composables/useApi'

const transactionHistoryRef = ref(null)
const walletStore = useWalletStore()
let echo = null
const api = useApi()

onMounted(async () => {
  await initializeRealtime()
})

onUnmounted(() => {
  // Clean up Pusher connections
  if (echo) {
    echo.disconnect()
  }
})

const initializeRealtime = async () => {
  try {
    // Get current user to subscribe to their channel
    const { data: user } = await api.get('/user')

    // Initialize Laravel Echo with Pusher
    // Note: This requires pusher-js and laravel-echo packages
    if (window.Echo) {
      echo = window.Echo

      console.log(echo);

      // Subscribe to private channel for this user
      echo
        .private(`user.${user.id}`)
        .listen('.transaction.created', (event) => {
          console.log('Transaction received:', event)

          // Add the transaction to the list
          walletStore.addTransaction(event.transaction)

          // Update balance based on transaction type
          const isSender = event.transaction.sender_id === user.id
          const isReceiver = event.transaction.receiver_id === user.id

          if (isSender) {
            // Deduct amount + commission from balance
            const totalDebit =
              parseFloat(event.transaction.amount) +
              parseFloat(event.transaction.commission_fee)
            walletStore.setBalance(walletStore.balance - totalDebit)
          } else if (isReceiver) {
            // Add amount to balance
            walletStore.setBalance(walletStore.balance + parseFloat(event.transaction.amount))
          }

          // Optionally refresh the transaction list
          if (transactionHistoryRef.value) {
            transactionHistoryRef.value.fetchTransactions()
          }
        })
    }
  } catch (error) {
    console.error('Failed to initialize real-time updates:', error)
  }
}
</script>
