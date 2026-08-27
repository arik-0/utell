/**
 * U-Tell Shared Utilities
 * Centralized helper functions used across multiple views.
 */

/**
 * Converts a date+time from the DB into a relative time string.
 * e.g. "Hace 5m", "Hace 2h", "Hace 3d", or a full date if older.
 * @param {string} fecha - "YYYY-MM-DD"
 * @param {string} hora  - "HH:MM:SS"
 * @returns {string}
 */
export function timeAgo(fecha, hora) {
  if (!fecha) return 'Reciente'

  const isoStr = hora ? `${fecha}T${hora}` : `${fecha}T00:00:00`
  const past = new Date(isoStr)
  if (isNaN(past.getTime())) return fecha

  const now = new Date()
  const diffMs = now - past
  const diffSec = Math.floor(diffMs / 1000)
  const diffMin = Math.floor(diffSec / 60)
  const diffH   = Math.floor(diffMin / 60)
  const diffD   = Math.floor(diffH / 24)

  if (diffSec < 60)  return 'Ahora mismo'
  if (diffMin < 60)  return `Hace ${diffMin}m`
  if (diffH < 24)    return `Hace ${diffH}h`
  if (diffD < 7)     return `Hace ${diffD}d`

  // Older than a week: show localized date
  return past.toLocaleDateString('es-AR', { day: '2-digit', month: 'short' })
}

/**
 * Resolves the full URL for a profile picture with a fallback.
 * @param {string|null} pic
 * @returns {string}
 */
export function getProfilePic(pic) {
  if (!pic || pic.trim() === '') return '/img/Perfil.svg'
  if (pic.startsWith('http://') || pic.startsWith('https://')) return pic
  return `/img/${pic}`
}

/**
 * Theme Management Utilities (Light / Dark Mode)
 */
export function initTheme() {
  const saved = localStorage.getItem('utell_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
  setTheme(saved)
  return saved
}

export function setTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme)
  localStorage.setItem('utell_theme', theme)
}

export function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'
  const next = current === 'dark' ? 'light' : 'dark'
  setTheme(next)
  return next
}

/**
 * Screen Size & Responsive Composable
 */
import { ref, onMounted, onUnmounted } from 'vue'

export function useScreenSize() {
  const width = ref(typeof window !== 'undefined' ? window.innerWidth : 1200)
  const isMobile = ref(width.value < 768)
  const isTablet = ref(width.value >= 768 && width.value < 1024)
  const isDesktop = ref(width.value >= 768)
  const isWide = ref(width.value >= 1200)

  const handleResize = () => {
    width.value = window.innerWidth
    isMobile.value = width.value < 768
    isTablet.value = width.value >= 768 && width.value < 1024
    isDesktop.value = width.value >= 768
    isWide.value = width.value >= 1200
  }

  onMounted(() => {
    window.addEventListener('resize', handleResize, { passive: true })
    handleResize()
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
  })

  return {
    width,
    isMobile,
    isTablet,
    isDesktop,
    isWide
  }
}

