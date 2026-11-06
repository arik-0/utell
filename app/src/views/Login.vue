<template>
  <div class="login-page">
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
    <div class="top-section">
      <img src="/img/logo.png" alt="U-Tell Logo" class="logo-cap" />
    </div>

    <div class="bottom-card">
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

          <div style="display:flex; gap:10px; margin-top:12px;">
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
  min-height: 100vh;
  min-height: 100dvh;
  background: var(--bg-main);
}

.auth-theme-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.25);
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

/* ─── Top Hero ─── */
.top-section {
  flex: 0 0 38vh;
  background-image:
    linear-gradient(rgba(229, 90, 43, 0.82), rgba(255, 107, 53, 0.78)),
    url('/img/unsplash.png');
  background-blend-mode: multiply;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-cap {
  width: 110px;
  filter: brightness(0) invert(1);
  drop-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* ─── Bottom Card ─── */
.bottom-card {
  flex: 1;
  background: var(--bg-card);
  border-radius: 32px 32px 0 0;
  padding: 32px 24px 40px;
  margin-top: -32px;
  z-index: 10;
  position: relative;
  box-shadow: 0 -6px 20px rgba(0,0,0,0.08);
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
  font-size: 15px;
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
  padding: 26px 24px;
  width: 100%;
  max-width: 420px;
  box-shadow: var(--shadow-lg);
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
    flex-direction: row;
    align-items: center;
    justify-content: center;
    background: transparent;
    padding: 40px 20px;
    width: 100%;
  }

  .top-section {
    flex: 0 0 380px;
    height: 560px;
    border-radius: 28px 0 0 28px;
    box-shadow: var(--shadow-lg);
  }

  .logo-cap {
    width: 140px;
  }

  .bottom-card {
    flex: 0 0 460px;
    height: 560px;
    margin-top: 0;
    border-radius: 0 28px 28px 0;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-light);
    border-left: none;
    overflow-y: auto;
    padding: 40px 36px;
    background: var(--bg-card);
  }
}
</style>

