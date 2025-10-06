import axios from 'axios'

let apiInstance

export function useApi() {
  if (apiInstance) return apiInstance

  const baseURL = import.meta.env.VITE_API_BASE_URL || '/api'

  apiInstance = axios.create({
    baseURL,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })

  apiInstance.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  })

  return apiInstance
}

