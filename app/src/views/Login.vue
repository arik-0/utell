<template>
  <div class="login-page">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'

const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()

const email           = ref('')
const password        = ref('')
const showPassword    = ref(false)
const rememberMe      = ref(false)
const isLoading       = ref(false)
const isLoadingGoogle = ref(false)
const errorMessage    = ref('')

const handleForgotPassword = () => {
  toastStore.info('Para restablecer tu contraseña, contacta al soporte técnico o al administrador universitario.', 4500)
}

// ─── Classic Login ───────────────────────────────────────────────────────────
const handleLogin = async () => {
  isLoading.value    = true
  errorMessage.value = ''
  try {
    await authStore.login(email.value, password.value)
    toastStore.success(`¡Bienvenido de nuevo!`, 2500)
    router.push('/feed')
  } catch (error) {
    errorMessage.value = error.message || 'Credenciales incorrectas o error en el servidor.'
    toastStore.error(errorMessage.value)
  } finally {
    isLoading.value = false
  }
}

// ─── Google OAuth ─────────────────────────────────────────────────────────────
const GOOGLE_CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID' // Replace with actual client ID from Google Cloud Console

const handleGoogleCallback = async (response) => {
  isLoadingGoogle.value = true
  errorMessage.value    = ''
  try {
    await authStore.loginWithGoogle(response.credential)
    toastStore.success('¡Sesión iniciada con Google!', 2500)
    router.push('/feed')
  } catch (error) {
    errorMessage.value = 'No se pudo iniciar sesión con Google. Intenta de nuevo.'
    toastStore.error(errorMessage.value)
  } finally {
    isLoadingGoogle.value = false
  }
}

const handleGoogleLogin = () => {
  // Trigger the Google One Tap / popup flow
  if (typeof window.google === 'undefined') {
    errorMessage.value = 'El servicio de Google no está disponible en este momento.'
    toastStore.warning('El servicio de Google no está disponible.')
    return
  }
  window.google.accounts.id.prompt()
}

onMounted(() => {
  // Initialize Google Identity Services if GSI script is loaded
  if (typeof window.google !== 'undefined') {
    window.google.accounts.id.initialize({
      client_id: GOOGLE_CLIENT_ID,
      callback: handleGoogleCallback,
      auto_select: false,
      cancel_on_tap_outside: true
    })
  }
})

</script>

<style scoped>
.login-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  min-height: 100dvh;
  background: var(--bg-main);
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

/* ─── Desktop & Tablet Layout ─── */
@media (min-width: 768px) {
  .login-page {
    flex-direction: row;
    align-items: center;
    justify-content: center;
    background: var(--bg-subtle);
    padding: 40px 20px;
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
  }
}
</style>

