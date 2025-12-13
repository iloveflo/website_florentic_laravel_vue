import { createApp } from 'vue'
import App from './App.vue'
import router from './routes/index.js'
import axios from 'axios'

// Set base URL for API requests
axios.defaults.baseURL = '/api'
axios.defaults.withCredentials = true

// Add request interceptor to add auth token
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Add response interceptor to handle auth errors
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Clear token and redirect to login if unauthorized
      localStorage.removeItem('token')
      router.push('/')
    }
    return Promise.reject(error)
  }
)

createApp(App).use(router).mount('#app')