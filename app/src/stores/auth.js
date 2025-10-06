import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '../composables/useApi'

export const useAuthStore = defineStore('auth', () => {
  const api = useApi()

  const token = ref(localStorage.getItem('token') || null)
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  function setToken(newToken) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('token', newToken)
    } else {
      localStorage.removeItem('token')
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    try {
      const { data } = await api.get('/user')
      user.value = data
      return data
    } catch (e) {
      // token invalid
      setToken(null)
      user.value = null
      return null
    }
  }

  async function register(payload) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/auth/register', payload)
      setToken(data.token)
      user.value = data.user
      return data
    } catch (e) {
      error.value = e.response?.data?.message || 'Registration failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function login(payload) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/auth/login', payload)
      setToken(data.token)
      user.value = data.user
      return data
    } catch (e) {
      error.value = e.response?.data?.message || 'Login failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (_) {
      // ignore
    } finally {
      setToken(null)
      user.value = null
    }
  }

  return { token, user, loading, error, setToken, fetchUser, register, login, logout }
})

