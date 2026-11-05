/**
 * U-Tell Shared Utilities
 * Centralized helper functions used across multiple views.
 */
import api, { API_BASE_URL } from './api'

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
  if (!pic || typeof pic !== 'string' || pic.trim() === '') return '/img/Perfil.svg'
  if (pic.startsWith('http://') || pic.startsWith('https://') || pic.startsWith('data:image/')) return pic
  if (pic.startsWith('/uploads/') || pic.startsWith('uploads/')) {
    const cleanPath = pic.startsWith('/') ? pic : `/${pic}`
    const base = API_BASE_URL.endsWith('/') ? API_BASE_URL.slice(0, -1) : API_BASE_URL
    return `${base}${cleanPath}`
  }
  if (pic.startsWith('/')) return pic
  return `/img/${pic}`
}

/**
 * Compresses an image file client-side to ensure fast uploads and low bandwidth.
 * Returns a Promise that resolves with { dataUrl, blob, file }.
 * @param {File} file
 * @param {number} maxWidth
 * @param {number} maxHeight
 * @param {number} quality
 * @returns {Promise<{ dataUrl: string, blob: Blob, file: File }>}
 */
export function compressImage(file, maxWidth = 1200, maxHeight = 1200, quality = 0.85) {
  return new Promise((resolve, reject) => {
    if (!file || !file.type.startsWith('image/')) {
      return reject(new Error('El archivo seleccionado no es una imagen válida.'))
    }

    const reader = new FileReader()
    reader.onerror = () => reject(new Error('Error al leer el archivo de imagen.'))
    reader.onload = (e) => {
      const img = new Image()
      img.onerror = () => reject(new Error('No se pudo cargar la imagen para procesarla.'))
      img.onload = () => {
        let width = img.width
        let height = img.height

        if (width > maxWidth || height > maxHeight) {
          const ratio = Math.min(maxWidth / width, maxHeight / height)
          width = Math.round(width * ratio)
          height = Math.round(height * ratio)
        }

        const canvas = document.createElement('canvas')
        canvas.width = width
        canvas.height = height

        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)

        const mimeType = file.type === 'image/png' ? 'image/png' : 'image/jpeg'
        const dataUrl = canvas.toDataURL(mimeType, quality)

        canvas.toBlob(
          (blob) => {
            if (!blob) {
              return resolve({ dataUrl, blob: file, file })
            }
            const ext = mimeType === 'image/png' ? 'png' : 'jpg'
            const compressedFile = new File([blob], `upload_${Date.now()}.${ext}`, { type: mimeType })
            resolve({ dataUrl, blob, file: compressedFile })
          },
          mimeType,
          quality
        )
      }
      img.src = e.target.result
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Uploads an image file to the backend /upload endpoint.
 * Falls back cleanly to dataUrl if upload endpoint is unavailable.
 * @param {File} file
 * @param {object} [options]
 * @returns {Promise<string>} returns full image URL
 */
export async function uploadImageFile(file, options = {}) {
  const { maxWidth = 1200, maxHeight = 1200, quality = 0.85, idUsuario = null, tipo = '' } = options
  const compressed = await compressImage(file, maxWidth, maxHeight, quality)

  try {
    const formData = new FormData()
    formData.append('file', compressed.file)
    if (idUsuario) formData.append('idUsuario', idUsuario)
    if (tipo) formData.append('tipo', tipo)

    const res = await api.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (res.data && res.data.url) {
      if (res.data.url.startsWith('http')) return res.data.url
      const base = API_BASE_URL.endsWith('/') ? API_BASE_URL.slice(0, -1) : API_BASE_URL
      return `${base}${res.data.url.startsWith('/') ? '' : '/'}${res.data.url}`
    }
    if (res.data && res.data.fullUrl) return res.data.fullUrl
  } catch (err) {
    console.warn('Backend /upload no disponible o devolvió error, usando respaldo comprimido:', err)
  }

  // Graceful fallback: return the high-efficiency compressed dataUrl
  return compressed.dataUrl
}

/**
 * Parses post text to extract attached images: [img:URL] or ![alt](URL).
 * @param {string} texto
 * @returns {{ text: string, image: string|null }}
 */
export function parsePostContent(texto) {
  if (!texto) return { text: '', image: null }

  // [img:URL]
  const tagRegex = /\[img:(https?:\/\/[^\s\]]+|data:image\/[a-zA-Z]+;base64,[^\s\]]+|\/uploads\/[^\s\]]+)\]/
  const match = texto.match(tagRegex)
  if (match) {
    let imgUrl = match[1]
    if (imgUrl.startsWith('/uploads/')) {
      const base = API_BASE_URL.endsWith('/') ? API_BASE_URL.slice(0, -1) : API_BASE_URL
      imgUrl = `${base}${imgUrl}`
    }
    return {
      text: texto.replace(match[0], '').trim(),
      image: imgUrl
    }
  }

  // Standard markdown ![alt](URL)
  const mdRegex = /!\[.*?\]\((https?:\/\/[^\s\)]+|data:image\/[a-zA-Z]+;base64,[^\s\)]+|\/uploads\/[^\s\)]+)\)/
  const mdMatch = texto.match(mdRegex)
  if (mdMatch) {
    let imgUrl = mdMatch[1]
    if (imgUrl.startsWith('/uploads/')) {
      const base = API_BASE_URL.endsWith('/') ? API_BASE_URL.slice(0, -1) : API_BASE_URL
      imgUrl = `${base}${imgUrl}`
    }
    return {
      text: texto.replace(mdMatch[0], '').trim(),
      image: imgUrl
    }
  }

  return { text: texto, image: null }
}

/**
 * Theme Management Utilities (Light / Dark Mode)
 */
export function initTheme() {
  let saved = 'light'
  try {
    saved = localStorage.getItem('utell_theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
  } catch (_) {}
  setTheme(saved)
  return saved
}

export function setTheme(theme) {
  if (typeof document !== 'undefined') {
    document.documentElement.setAttribute('data-theme', theme)
    if (theme === 'dark') {
      document.documentElement.classList.add('dark')
      if (document.body) document.body.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
      if (document.body) document.body.classList.remove('dark')
    }
  }
  try {
    localStorage.setItem('utell_theme', theme)
  } catch (_) {}
}

export function toggleTheme() {
  const current = (typeof document !== 'undefined' && document.documentElement.getAttribute('data-theme') === 'dark') ? 'dark' : 'light'
  const next = current === 'dark' ? 'light' : 'dark'
  setTheme(next)
  return next
}

