import { defineStore } from 'pinia'
import axios from 'axios'
import { API_BASE_URL } from '../api'

export const useAuthStore = defineStore('auth', {
  state: () => {
    let storedUser = null
    try {
      const raw = localStorage.getItem('utell_user')
      if (raw) storedUser = JSON.parse(raw)
    } catch (e) {}

    return {
      user:   storedUser,
      apiUrl: API_BASE_URL,
    }
  },

  actions: {
    // ─── Persist user to state + localStorage ─────────────────────────────
    _setUser(data) {
      this.user = data
      localStorage.setItem('utell_user', JSON.stringify(data))
    },

    // ─── Parse the API response and extract the user object ───────────────
    _parseUserResponse(raw) {
      let data = raw
      if (typeof data === 'string') {
        try { data = JSON.parse(data) } catch (e) {}
      }

      if (data && data.error) {
        const msg = typeof data.error === 'object' ? data.error.text : data.error
        throw new Error(msg)
      }

      if (typeof data === 'string') throw new Error(data)

      if (Array.isArray(data) && data.length > 0 && data[0].idUsuario) return data[0]
      if (data && data.idUsuario) return data

      throw new Error('Credenciales incorrectas')
    },

    // ─── Classic email/password login ─────────────────────────────────────
    async login(email, password) {
      const fd = new FormData()
      fd.append('email', email)
      fd.append('password', password)

      const response = await axios.post(`${this.apiUrl}/login`, fd)
      const user     = this._parseUserResponse(response.data)
      this._setUser(user)
      return true
    },

    // ─── Google OAuth login ───────────────────────────────────────────────
    // Sends the Google JWT credential to the backend for verification.
    // The backend decodes it, finds or creates the user, and returns the user object.
    async loginWithGoogle(credential) {
      const fd = new FormData()
      fd.append('credential', credential)

      const response = await axios.post(`${this.apiUrl}/login/google`, fd)
      const user     = this._parseUserResponse(response.data)
      this._setUser(user)
      return true
    },

    // ─── Logout ───────────────────────────────────────────────────────────
    logout() {
      this.user = null
      localStorage.removeItem('utell_user')
    }
  }
})
