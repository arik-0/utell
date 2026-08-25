import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])

  const addToast = (message, type = 'info', duration = 3500) => {
    const id = Date.now() + Math.random().toString(36).substr(2, 4)
    toasts.value.push({ id, message, type })

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }
  }

  const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const success = (msg, dur) => addToast(msg, 'success', dur)
  const error   = (msg, dur) => addToast(msg, 'error', dur)
  const info    = (msg, dur) => addToast(msg, 'info', dur)
  const warning = (msg, dur) => addToast(msg, 'warning', dur)

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    info,
    warning
  }
})
