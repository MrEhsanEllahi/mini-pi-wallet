<template>
  <div class="transfer-form card">
    <h2 class="card-header">Send Money</h2>

    <form @submit.prevent="handleSubmit">
      <div class="mb-4">
        <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-2">
          Recipient User ID
        </label>
        <input id="receiver_id" v-model="form.receiver_id" type="text" required class="input font-mono" placeholder="e.g., 000001 or 1" :disabled="loading" maxlength="6" />
        <p class="help">Enter 6-digit ID (e.g., 000001) or plain number (e.g., 1)</p>
      </div>

      <div class="mb-4">
        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
          Amount
        </label>
        <input id="amount" v-model.number="form.amount" type="number" required min="0.01" step="0.01" class="input" placeholder="Enter amount" :disabled="loading" />
        <p v-if="commissionFee > 0" class="text-sm text-gray-500 mt-1">
          Commission fee (1.5%): {{ formatCurrency(commissionFee) }}
          <br />
          Total to be debited: {{ formatCurrency(totalAmount) }}
        </p>
      </div>

      <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>

      <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
        <p class="text-sm text-green-600">{{ success }}</p>
      </div>

      <button type="submit" :disabled="loading || !form.receiver_id || !form.amount" class="btn-primary bg-pimono-gradient w-full">
        {{ loading ? 'Processing...' : 'Send' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useWalletStore } from '../stores/wallet'
import { useApi } from '../composables/useApi'

const walletStore = useWalletStore()

const form = ref({
  receiver_id: '',
  amount: null,
})

const loading = ref(false)
const error = ref(null)
const success = ref(null)

const commissionFee = computed(() => {
  if (form.value.amount && form.value.amount > 0) {
    return parseFloat((form.value.amount * 0.015).toFixed(2))
  }
  return 0
})

const totalAmount = computed(() => {
  if (form.value.amount && form.value.amount > 0) {
    return parseFloat(form.value.amount) + commissionFee.value
  }
  return 0
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const api = useApi()

const handleSubmit = async () => {
  loading.value = true
  error.value = null
  success.value = null

  try {
    // Parse receiver_id to handle both formatted (000001) and plain (1) input
    const receiverId = parseInt(form.value.receiver_id, 10)

    if (isNaN(receiverId) || receiverId < 1) {
      error.value = 'Please enter a valid recipient ID'
      loading.value = false
      return
    }

    const { data } = await api.post('/transactions', {
      receiver_id: receiverId,
      amount: form.value.amount,
    })

    // Update balance
    walletStore.setBalance(data.new_balance ?? 0)

    // Add transaction to the list
    walletStore.addTransaction(data.transaction)

    // Show success message
    success.value = 'Transaction successful!'

    // Reset form
    form.value.receiver_id = ''
    form.value.amount = null

    // Clear success message after 3 seconds
    setTimeout(() => {
      success.value = null
    }, 3000)
  } catch (err) {
    error.value = err.response?.data?.message || err.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield;
}
</style>
