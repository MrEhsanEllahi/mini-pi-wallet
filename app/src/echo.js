import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

// Get API base URL from environment
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
// Remove '/api' suffix if present to get the base domain
const baseUrl = apiBaseUrl.replace(/\/api$/, '')

// Initialize Laravel Echo
window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: false,
  authEndpoint: `${baseUrl}/broadcasting/auth`,
  auth: {
    headers: {
      Accept: 'application/json',
      ...(localStorage.getItem('token')
        ? { Authorization: `Bearer ${localStorage.getItem('token')}` }
        : {}),
    },
  },
})

export default window.Echo
