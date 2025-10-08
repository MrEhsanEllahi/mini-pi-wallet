import { defineStore } from 'pinia'
import { ref } from 'vue'

let idCounter = 0

export const useNotificationStore = defineStore('notifications', () => {
  const toasts = ref([])

  const removeToast = (id) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  const addToast = ({ message, type = 'info', timeout = 4000 }) => {
    const id = ++idCounter
    toasts.value.push({ id, message, type })

    if (timeout > 0) {
      setTimeout(() => removeToast(id), timeout)
    }
  }

  return {
    toasts,
    addToast,
    removeToast,
  }
})
