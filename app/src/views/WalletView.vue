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
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import { getPusher } from '../pusher'

const transactionHistoryRef = ref(null)
const walletStore = useWalletStore()
const authStore = useAuthStore()
const notifications = useNotificationStore()
let pusherChannel = null

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

onMounted(async () => {
  await initializeRealtime()
})

onUnmounted(() => {
  // Clean up Pusher channel subscription
  if (pusherChannel) {
    pusherChannel.unbind_all()
    pusherChannel = null
  }
})

const initializeRealtime = async () => {
  try {
    const user = await authStore.fetchUser()
    if (!user) {
      console.warn('⚠️ No user found, skipping Pusher setup')
      return
    }

    const pusher = getPusher()
    const channelName = `private-user.${user.id}`

    // Subscribe to the user's private channel
    pusherChannel = pusher.subscribe(channelName)

    pusherChannel.bind('pusher:subscription_error', (status) => {
      console.error(`❌ Subscription error for ${channelName}:`, status)
    })

    // Listen for transaction.created event
    pusherChannel.bind('transaction.created', (event) => {
      console.log('💰 Transaction received:', event)

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

        const senderName = event.transaction.sender?.name || `User ${event.transaction.sender_id}`
        notifications.addToast({
          message: `Received ${formatCurrency(event.transaction.amount)} from ${senderName}`,
          type: 'success',
        })
      }

      // Optionally refresh the transaction list
      if (transactionHistoryRef.value) {
        transactionHistoryRef.value.fetchTransactions()
      }
    })
  } catch (error) {
    console.error('❌ Failed to initialize real-time updates:', error)
  }
}
</script>
