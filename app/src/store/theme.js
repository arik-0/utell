import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { initTheme, setTheme as utilSetTheme, toggleTheme as utilToggleTheme } from '../utils'

export const useThemeStore = defineStore('theme', () => {
  const currentTheme = ref(
    typeof document !== 'undefined'
      ? document.documentElement.getAttribute('data-theme') || initTheme()
      : 'light'
  )

  const isDark = computed(() => currentTheme.value === 'dark')

  const toggle = () => {
    const next = utilToggleTheme()
    currentTheme.value = next
    return next
  }

  const set = (theme) => {
    utilSetTheme(theme)
    currentTheme.value = theme
  }

  return {
    currentTheme,
    isDark,
    toggle,
    set
  }
})
