import { defineStore } from 'pinia'
import api from '../api'

export const useAuthStore = defineStore('auth', {
  state: () => {
    let storedUser = null
    try {
      const raw = localStorage.getItem('utell_user')
      if (raw) storedUser = JSON.parse(raw)
    } catch (e) {}

    return {
      user: storedUser,
    }
  },

  getters: {
    isStaffOrInstitution(state) {
      if (!state.user) return false
      const rol = Number(state.user.idRol || 0)
      const perfil = state.user.tipoPerfil || ''
      return [1, 3, 5, 6].includes(rol) || ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'].includes(perfil)
    },
    isModeratorOrAdmin(state) {
      if (!state.user) return false
      const rol = Number(state.user.idRol || 0)
      const perfil = state.user.tipoPerfil || ''
      return [1, 3].includes(rol) || ['Administrador', 'Moderador'].includes(perfil)
    }
  },

  actions: {
    // ─── Persist user to state + localStorage ─────────────────────────────
    _setUser(data) {
      this.user = data
      localStorage.setItem('utell_user', JSON.stringify(data))
    },

    // ─── Extract readable message from Axios / API errors ─────────────────
    _extractErrorMessage(err) {
      const respData = err?.response?.data
      if (respData) {
        if (typeof respData.error === 'object' && respData.error?.text) return respData.error.text
        if (typeof respData.mensaje === 'string' && respData.mensaje) return respData.mensaje
        if (typeof respData.error === 'string' && respData.error) return respData.error
      }
      return err?.message || 'Credenciales incorrectas'
    },

    // ─── Parse the API response and extract the user object ───────────────
    _parseUserResponse(raw) {
      let data = raw
      if (typeof data === 'string') {
        try { data = JSON.parse(data) } catch (e) {}
      }

      if (data && data.error) {
        const msg = typeof data.error === 'object' ? (data.error.text || data.mensaje) : (data.mensaje || data.error)
        throw new Error(msg || 'Credenciales incorrectas')
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

      try {
        const response = await api.post('/login', fd)
        const user     = this._parseUserResponse(response.data)
        this._setUser(user)
        return true
      } catch (err) {
        throw new Error(this._extractErrorMessage(err))
      }
    },

    // ─── Google OAuth login ───────────────────────────────────────────────
    // Sends the Google JWT credential to the backend for verification.
    // The backend decodes it, finds or creates the user, and returns the user object.
    async loginWithGoogle(credential) {
      const fd = new FormData()
      fd.append('credential', credential)

      try {
        const response = await api.post('/login/google', fd)
        const user     = this._parseUserResponse(response.data)
        this._setUser(user)
        return true
      } catch (err) {
        throw new Error(this._extractErrorMessage(err))
      }
    },

    // ─── Update partial user data in state + storage ──────────────────────
    updateUser(partial) {
      if (this.user) {
        this.user = { ...this.user, ...partial }
        localStorage.setItem('utell_user', JSON.stringify(this.user))
      }
    },

    // ─── Logout ───────────────────────────────────────────────────────────
    logout() {
      this.user = null
      localStorage.removeItem('utell_user')
    }
  }
})
