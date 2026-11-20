<template>
  <div class="login-page" :class="{ 'is-dark': themeStore.isDark }">
    <button
      class="auth-theme-btn"
      @click="themeStore.toggle()"
      :title="themeStore.isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
      type="button"
      aria-label="Cambiar tema"
    >
      <svg v-if="themeStore.isDark" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>

    <div class="login-shell">
      <div class="top-section">
        <div class="hero-brand-pill">
          <img src="/img/logo.png" alt="U-Tell Logo" class="logo-cap" />
          <span class="hero-brand-name">U-Tell</span>
        </div>

        <div class="hero-desktop-copy">
          <h2 class="hero-desktop-title">Tu comunidad universitaria en un solo lugar</h2>
          <p class="hero-desktop-desc">Explora experiencias reales, resuelve consultas de cursada y conecta con estudiantes de toda Argentina.</p>
          <div class="hero-desktop-tags">
            <span>Consultas</span>
            <span>Experiencias</span>
            <span>Universidades</span>
          </div>
        </div>
      </div>

      <div class="bottom-card">
        <div class="bottom-card-inner">
          <h1 class="welcome-title">Bienvenido</h1>
          <p class="welcome-sub">Ingresa a tu cuenta universitaria</p>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="field-group">
          <label class="field-label" for="email-input">Correo electrónico</label>
          <div class="input-wrap">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input
              id="email-input"
              type="email"
              v-model="email"
              placeholder="nombre@universidad.edu"
              autocomplete="email"
              required
            />
          </div>
        </div>

        <div class="field-group">
          <label class="field-label" for="password-input">Contraseña</label>
          <div class="input-wrap">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              id="password-input"
              :type="showPassword ? 'text' : 'password'"
              v-model="password"
              placeholder="Tu contraseña"
              autocomplete="current-password"
              required
            />
            <button type="button" class="toggle-eye" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Ocultar' : 'Mostrar'">
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>

        <div class="form-row">
          <label class="checkbox-label">
            <input type="checkbox" v-model="rememberMe" />
            <span>Recordar sesión</span>
          </label>
          <a href="#" class="forgot-link" @click.prevent="handleForgotPassword">¿Olvidaste tu contraseña?</a>
        </div>

        <p v-if="errorMessage" class="error-msg" role="alert">{{ errorMessage }}</p>

        <button type="submit" class="btn btn-primary" :disabled="isLoading">
          <span v-if="isLoading" class="btn-spinner"></span>
          <span>{{ isLoading ? 'Ingresando...' : 'Iniciar sesión' }}</span>
        </button>
      </form>

      <div class="divider-row">
        <span class="divider-line"></span>
        <span class="divider-text">o</span>
        <span class="divider-line"></span>
      </div>

      <button class="btn btn-google" @click="handleGoogleLogin" :disabled="isLoadingGoogle">
        <img src="/img/Vector.svg" alt="Google" class="google-icon" width="20" height="20" />
        <span>{{ isLoadingGoogle ? 'Conectando...' : 'Continuar con Google' }}</span>
      </button>

          <p class="register-prompt">
            ¿No tienes cuenta?
            <router-link to="/register" class="register-link">Crear cuenta</router-link>
          </p>
        </div>
      </div>
    </div>

    <!-- Modal Recuperar Contraseña (Puntos T02 y T03) -->
    <div v-if="showRecoverModal" class="recover-modal-backdrop" @click.self="showRecoverModal = false">
      <div class="recover-modal-card">
        <h3 class="recover-title">Recuperar Contraseña</h3>
        <p class="recover-sub">Ingresa tu correo registrado y tu fecha de nacimiento para restablecer tu clave de acceso.</p>

        <form @submit.prevent="submitRecoverPassword" class="recover-form">
          <div class="field-group">
            <label class="field-label">Correo electrónico</label>
            <div class="input-wrap">
              <input type="email" v-model="recoverEmail" placeholder="tu@universidad.edu.ar" required style="padding-left:14px;" />
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Fecha de nacimiento (verificación)</label>
            <div class="input-wrap">
              <input type="date" v-model="recoverFNac" style="padding-left:14px;" />
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Nueva contraseña (mín. 6 caracteres)</label>
            <div class="input-wrap">
              <input type="password" v-model="recoverNewPass" placeholder="Nueva contraseña segura" minlength="6" required style="padding-left:14px;" />
            </div>
          </div>

          <div class="recover-actions">
            <button type="button" class="btn btn-google" style="margin-bottom:0;" @click="showRecoverModal = false">Cancelar</button>
            <button type="submit" class="btn btn-primary" style="margin-bottom:0;" :disabled="isRecovering">
              {{ isRecovering ? 'Procesando...' : 'Restablecer Clave' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'
import { useThemeStore } from '../store/theme'
import api from '../api'

const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()
const themeStore = useThemeStore()

const email           = ref('')
const password        = ref('')
const showPassword    = ref(false)
const rememberMe      = ref(false)
const isLoading       = ref(false)
const isLoadingGoogle = ref(false)
const errorMessage    = ref('')

const showRecoverModal = ref(false)
const recoverEmail     = ref('')
const recoverFNac      = ref('')
const recoverNewPass   = ref('')
const isRecovering     = ref(false)

const handleForgotPassword = () => {
  recoverEmail.value   = email.value || ''
  recoverFNac.value    = ''
  recoverNewPass.value = ''
  showRecoverModal.value = true
}

const submitRecoverPassword = async () => {
  if (!recoverEmail.value || recoverNewPass.value.length < 6) {
    toastStore.warning('Ingresa tu correo y una nueva contraseña de al menos 6 caracteres.')
    return
  }
  isRecovering.value = true
  try {
    const res = await api.post('/recuperar-clave', {
      email: recoverEmail.value,
      fNac: recoverFNac.value,
      nuevaClave: recoverNewPass.value
    })
    toastStore.success(res.data?.mensaje || 'Contraseña restablecida con éxito.')
    email.value = recoverEmail.value
    showRecoverModal.value = false
  } catch (err) {
    const msg = err?.response?.data?.mensaje || err?.response?.data?.error || 'No fue posible recuperar la contraseña.'
    toastStore.error(msg)
  } finally {
    isRecovering.value = false
  }
}

// ─── Classic Login ───────────────────────────────────────────────────────────
const handleLogin = async () => {
  isLoading.value    = true
  errorMessage.value = ''
  try {
    await authStore.login(email.value, password.value)

    const user = authStore.user
    if (authStore.isStaffOrInstitution) {
      toastStore.success(`¡Bienvenido al Tablero de Control, ${user?.nombre || 'Autoridad'}!`, 3000)
      router.push('/dashboard')
    } else {
      toastStore.success(`¡Bienvenido de nuevo, ${user?.nombre || 'Estudiante'}!`, 2500)
      router.push('/feed')
    }
  } catch (error) {
    errorMessage.value = error.message || 'Credenciales incorrectas o error en el servidor.'
    toastStore.error(errorMessage.value)
  } finally {
    isLoading.value = false
  }
}

// ─── Google OAuth ─────────────────────────────────────────────────────────────
const GOOGLE_CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID || ''
let gsiScriptEl = null

const handleGoogleCallback = async (response) => {
  isLoadingGoogle.value = true
  errorMessage.value    = ''
  try {
    await authStore.loginWithGoogle(response.credential)

    if (authStore.isStaffOrInstitution) {
      toastStore.success('¡Sesión iniciada con Google!', 2500)
      router.push('/dashboard')
    } else {
      toastStore.success('¡Sesión iniciada con Google!', 2500)
      router.push('/feed')
    }
  } catch (error) {
    errorMessage.value = error.message || 'No se pudo iniciar sesión con Google. Intenta de nuevo.'
    toastStore.error(errorMessage.value)
  } finally {
    isLoadingGoogle.value = false
  }
}

const initGoogleGsi = () => {
  if (!GOOGLE_CLIENT_ID || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID') return false
  if (typeof window.google !== 'undefined' && window.google.accounts?.id) {
    window.google.accounts.id.initialize({
      client_id: GOOGLE_CLIENT_ID,
      callback: handleGoogleCallback,
      auto_select: false,
      cancel_on_tap_outside: true
    })
    return true
  }
  return false
}

const handleGoogleLogin = () => {
  if (!GOOGLE_CLIENT_ID || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID') {
    toastStore.warning('El inicio con Google requiere configurar VITE_GOOGLE_CLIENT_ID en el archivo .env', 5000)
    return
  }

  if (typeof window.google === 'undefined' || !window.google.accounts?.id) {
    toastStore.error('El script de Google Identity Services aún no está disponible. Intenta nuevamente en unos segundos.')
    return
  }

  initGoogleGsi()
  window.google.accounts.id.prompt()
}

onMounted(() => {
  if (!initGoogleGsi() && GOOGLE_CLIENT_ID && GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID') {
    gsiScriptEl = document.querySelector('script[src*="accounts.google.com/gsi/client"]')
    if (gsiScriptEl) {
      gsiScriptEl.addEventListener('load', initGoogleGsi, { once: true })
    }
  }
})

onUnmounted(() => {
  if (gsiScriptEl) {
    gsiScriptEl.removeEventListener('load', initGoogleGsi)
  }
})

</script>

<style scoped>
.login-page {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: 100vh;
  min-height: 100dvh;
  overflow-x: hidden;
  background: var(--bg-main);
}

.login-shell {
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: 100vh;
  min-height: 100dvh;
  flex: 1;
}

.auth-theme-btn {
  position: absolute;
  top: calc(14px + env(safe-area-inset-top, 0px));
  right: 14px;
  background: rgba(15, 23, 42, 0.52);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.35);
  color: #FFFFFF;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 50;
  transition: all var(--duration-fast);
}

.auth-theme-btn:hover {
  background: rgba(0, 0, 0, 0.5);
  transform: scale(1.06);
}

.auth-theme-btn:active {
  transform: scale(0.95);
}

/* ─── Top Hero (Móvil: diseño clásico naranja con birrete blanco) ─── */
.top-section {
  flex: 0 0 36vh;
  flex: 0 0 36dvh;
  min-height: 210px;
  width: 100%;
  background-image:
    linear-gradient(rgba(229, 90, 43, 0.82), rgba(255, 107, 53, 0.78)),
    url('/img/unsplash.png');
  background-blend-mode: multiply;
  background-size: cover;
  background-position: center;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding-bottom: 24px;
}

.login-page.is-dark .top-section {
  background-image:
    linear-gradient(rgba(204, 72, 28, 0.86), rgba(229, 90, 43, 0.82)),
    url('/img/unsplash.png');
}

.hero-brand-pill {
  position: relative;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  box-shadow: none;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  padding: 0;
}

.logo-cap {
  width: 110px;
  height: auto;
  filter: brightness(0) invert(1) drop-shadow(0 4px 12px rgba(0, 0, 0, 0.2));
}

.hero-brand-name {
  display: none;
}

.hero-desktop-copy {
  display: none;
}

/* ─── Bottom Card ─── */
.bottom-card {
  width: 100%;
  flex: 1;
  background: var(--bg-card);
  border-radius: 32px 32px 0 0;
  padding: 28px 20px calc(32px + env(safe-area-inset-bottom, 0px));
  margin-top: -32px;
  z-index: 10;
  position: relative;
  box-shadow: 0 -8px 28px rgba(15, 23, 42, 0.14);
  display: flex;
  flex-direction: column;
}

.bottom-card-inner {
  width: 100%;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.welcome-title {
  font-size: 26px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 4px;
  letter-spacing: -0.3px;
}

.welcome-sub {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0 0 28px;
}

/* ─── Form ─── */
.login-form { display: flex; flex-direction: column; gap: 0; }

.field-group { margin-bottom: 18px; }

.field-label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: var(--text-muted);
  pointer-events: none;
}

.input-wrap input {
  width: 100%;
  padding: 13px 44px 13px 44px;
  border: 1.5px solid var(--border-light);
  border-radius: var(--radius-md);
  font-family: var(--font-family);
  font-size: 16px;
  color: var(--text-body);
  background: var(--bg-card);
  outline: none;
  transition: border-color var(--duration-fast) ease,
              box-shadow var(--duration-fast) ease;
}

.input-wrap input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-ghost);
}

.input-wrap input::placeholder { color: var(--text-muted); }

.toggle-eye {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  padding: 4px;
}

/* ─── Form Row ─── */
.form-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px 12px;
  margin-bottom: 20px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--text-secondary);
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  accent-color: var(--primary);
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.forgot-link {
  font-size: 13px;
  color: var(--primary);
  font-weight: 500;
}

/* ─── Error ─── */
.error-msg {
  color: var(--error);
  font-size: 13px;
  text-align: center;
  margin: 0 0 14px;
  padding: 10px 16px;
  background: rgba(239, 68, 68, 0.06);
  border-radius: var(--radius-md);
  border: 1px solid rgba(239, 68, 68, 0.15);
}

/* ─── Buttons ─── */
.btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 24px;
  border-radius: var(--radius-full);
  font-family: var(--font-family);
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  border: none;
  transition: all var(--duration-normal) var(--ease-out);
  width: 100%;
}

.btn:active { transform: scale(0.97); }
.btn:disabled { opacity: 0.65; cursor: not-allowed; }

.btn-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  box-shadow: var(--shadow-primary);
  margin-bottom: 20px;
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Divider ─── */
.divider-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.divider-line {
  flex: 1;
  height: 1px;
  background: var(--border-light);
}

.divider-text {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
}

/* ─── Google Button ─── */
.btn-google {
  background: var(--bg-card);
  color: var(--text-body);
  border: 1.5px solid var(--border-light);
  box-shadow: var(--shadow-xs);
  margin-bottom: 28px;
}

.btn-google:hover { border-color: var(--text-muted); }

.google-icon { flex-shrink: 0; }

/* ─── Register Prompt ─── */
.register-prompt {
  text-align: center;
  font-size: 14px;
  color: var(--text-secondary);
  margin-top: auto;
}

.register-link {
  color: var(--primary);
  font-weight: 600;
  margin-left: 4px;
}

.recover-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 200;
}

.recover-modal-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: 20px;
  padding: 22px 18px;
  width: 100%;
  max-width: 420px;
  max-height: 90dvh;
  overflow-y: auto;
  box-shadow: var(--shadow-lg);
}

.recover-actions {
  display: flex;
  gap: 10px;
  margin-top: 12px;
}

@media (max-width: 400px) {
  .recover-actions {
    flex-direction: column-reverse;
  }
}

.recover-title {
  margin: 0 0 6px;
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--text-strong);
}

.recover-sub {
  margin: 0 0 18px;
  font-size: 0.85rem;
  color: var(--text-secondary);
  line-height: 1.4;
}

/* ─── Desktop & Tablet Layout ─── */
@media (min-width: 768px) {
  .login-page {
    width: 100%;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: 20px 24px;
    background-image: url('/img/login-bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  .login-page.is-dark {
    background-image: linear-gradient(rgba(8, 13, 24, 0.58), rgba(8, 13, 24, 0.64)), url('/img/login-bg.jpg');
  }

  .login-shell {
    flex-direction: row;
    flex: 0 0 auto;
    width: min(94vw, max(780px, calc(0.56 * max(100vw, 179.33dvh))));
    max-width: 1140px;
    min-height: min(calc(100dvh - 40px), max(540px, calc(0.59 * max(100dvh, 55.76vw))));
    max-height: calc(100dvh - 40px);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 28px 68px rgba(15, 23, 42, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.35);
    z-index: 10;
  }

  .login-page.is-dark .login-shell {
    border-color: rgba(148, 163, 184, 0.22);
    box-shadow: 0 28px 68px rgba(0, 0, 0, 0.6);
  }

  .top-section {
    display: flex;
    flex: 0 0 42%;
    max-width: 440px;
    min-width: 280px;
    width: auto;
    height: auto;
    min-height: 0;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 22px;
    padding: 40px 36px;
    background-blend-mode: normal;
    background-image: linear-gradient(155deg, rgba(8, 42, 92, 0.94) 0%, rgba(14, 58, 118, 0.9) 62%, rgba(234, 88, 36, 0.88) 100%);
  }

  .login-page.is-dark .top-section {
    background-image: linear-gradient(155deg, rgba(10, 25, 54, 0.96) 0%, rgba(18, 41, 82, 0.94) 65%, rgba(194, 65, 20, 0.88) 100%);
  }

  .hero-brand-pill {
    gap: 10px;
    padding: 10px 20px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.85);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16);
  }

  .login-page.is-dark .hero-brand-pill {
    background: rgba(15, 23, 42, 0.86);
    border-color: rgba(148, 163, 184, 0.3);
  }

  .logo-cap {
    width: 40px;
    height: auto;
    filter: drop-shadow(0 2px 6px rgba(255, 107, 53, 0.25));
  }

  .hero-brand-name {
    display: inline;
    font-family: var(--font-family);
    font-size: 22px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.02em;
  }

  .login-page.is-dark .hero-brand-name {
    color: #F8FAFC;
  }

  .hero-desktop-copy {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .hero-desktop-title {
    color: #FFFFFF;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
  }

  .hero-desktop-desc {
    color: rgba(241, 245, 249, 0.88);
    font-size: 13.5px;
    line-height: 1.55;
    margin: 0;
  }

  .hero-desktop-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
  }

  .hero-desktop-tags span {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #FFFFFF;
    font-size: 11.5px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 9999px;
  }

  .bottom-card {
    flex: 1;
    width: auto;
    height: auto;
    margin-top: 0;
    border-radius: 0;
    padding: 36px 40px 32px;
    background: var(--bg-card);
    box-shadow: none;
    overflow-y: auto;
    justify-content: center;
  }

  .bottom-card-inner {
    max-width: 400px;
    margin: 0 auto;
    flex: 0 1 auto;
  }

  .input-wrap input {
    font-size: 15px;
  }
}

@media (min-width: 768px) and (max-width: 899px) {
  .top-section {
    padding: 32px 24px;
  }
}
</style>

