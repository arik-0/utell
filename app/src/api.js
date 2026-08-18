import axios from 'axios'

export const API_BASE_URL = 'http://localhost/utell/api/public'

const api = axios.create({
  baseURL: API_BASE_URL
})

export default api
