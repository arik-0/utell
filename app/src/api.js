import axios from 'axios'

export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost/utell/api/public'

const api = axios.create({
  baseURL: API_BASE_URL
})

// Request Interceptor: Attach JWT Token automatically if user is logged in
api.interceptors.request.use(
  (config) => {
    try {
      const userRaw = localStorage.getItem('utell_user')
      if (userRaw) {
        const user = JSON.parse(userRaw)
        if (user && user.token) {
          config.headers.Authorization = `Bearer ${user.token}`
        }
      }
    } catch (_) {}
    return config
  },
  (error) => Promise.reject(error)
)

// Response Interceptor: Handle unauthenticated responses gracefully
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('utell_user')
      if (window.location.pathname !== '/' && window.location.pathname !== '/register') {
        window.location.href = '/'
      }
    }
    return Promise.reject(error)
  }
)

export default api

