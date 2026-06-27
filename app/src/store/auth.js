import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => {
    let storedUser = null
    try {
      const u = localStorage.getItem('utell_user')
      if (u) storedUser = JSON.parse(u)
    } catch(e) {}

    return {
      user: storedUser,
      apiUrl: 'http://localhost/utell/api/public',
    }
  },
  actions: {
    async login(email, password) {
      try {
        const formData = new FormData()
        formData.append('email', email)
        formData.append('password', password)

        const response = await axios.post(`${this.apiUrl}/login`, formData)
        
        // El backend anterior devolvía un string o un JSON: var datos = JSON.parse(data);
        // axios parsea automáticamente el JSON, pero si devuelve string, lo parseamos
        let data = response.data
        if (typeof data === 'string') {
          try {
            data = JSON.parse(data)
          } catch (e) {
            // Might be an error string
            throw new Error(data)
          }
        }
        
        if (data && data[0] && data[0].idUsuario > 0) {
          this.user = data[0]
          localStorage.setItem('utell_user', JSON.stringify(data[0]))
          return true
        } else {
          throw new Error('Credenciales incorrectas')
        }
      } catch (error) {
        console.error('Error in login action:', error)
        throw error
      }
    },
    logout() {
      this.user = null
      localStorage.removeItem('utell_user')
    }
  }
})
