<template>
  <div class="flex items-center justify-center px-4">
    <div class="w-full max-w-md card">
      <h1 class="card-header text-center">{{ mode === 'login' ? 'Login' : 'Register' }}</h1>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div v-if="mode === 'register'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input v-model="form.name" type="text" class="input" placeholder="Your name" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" class="input" placeholder="you@example.com" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="form.password" type="password" class="input" placeholder="********" required />
        </div>

        <p v-if="auth.error" class="text-sm text-red-600">{{ auth.error }}</p>

        <button :disabled="auth.loading" class="btn-primary w-full">
          {{ auth.loading ? 'Please wait...' : (mode === 'login' ? 'Login' : 'Register') }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-4">
        <button class="underline" @click="toggleMode">
          {{ mode === 'login' ? 'Need an account? Register' : 'Have an account? Login' }}
        </button>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const mode = ref('login')
const form = reactive({ name: '', email: '', password: '' })

function toggleMode() {
  mode.value = mode.value === 'login' ? 'register' : 'login'
}

async function handleSubmit() {
  try {
    if (mode.value === 'login') {
      await auth.login({ email: form.email, password: form.password })
    } else {
      await auth.register({ name: form.name, email: form.email, password: form.password })
    }
    await auth.fetchUser()
    router.replace({ name: 'wallet' })
  } catch (_) {
    // error handled by store
  }
}
</script>
