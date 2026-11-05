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
          if (config.headers && typeof config.headers.set === 'function') {
            config.headers.set('Authorization', `Bearer ${user.token}`)
          } else {
            config.headers = config.headers || {}
            config.headers.Authorization = `Bearer ${user.token}`
          }
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
      const reqUrl = String(error.config?.url || '')
      const isLoginRequest = reqUrl.includes('/login')
      const currentPath = window.location.pathname
      const isAuthPage = currentPath === '/' || currentPath === '/login' || currentPath === '/register'
      if (!isLoginRequest && !isAuthPage) {
        localStorage.removeItem('utell_user')
        window.location.href = '/'
      }
    }
    return Promise.reject(error)
  }
)

export default api
