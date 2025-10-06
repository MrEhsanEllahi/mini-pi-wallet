<template>
  <header class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="h-8 w-8 rounded-lg bg-blue-600" />
        <h1 class="text-lg sm:text-xl font-bold text-gray-800">Mini Wallet</h1>
      </div>

      <div class="flex items-center gap-3" v-if="auth.token">
        <div v-if="auth.user" class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
          <span class="text-sm text-gray-600">Hi, {{ auth.user.name }}</span>
          <span class="text-xs sm:text-sm font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
            ID: {{ formatUserId(auth.user.id) }}
          </span>
        </div>
        <button class="btn-secondary" @click="onLogout">Logout</button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const formatUserId = (id) => {
  return String(id).padStart(6, '0')
}

async function onLogout() {
  await auth.logout()
  router.replace({ name: 'login' })
}
</script>

