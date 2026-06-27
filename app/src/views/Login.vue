<template>
  <div class="login-page">
    <div class="login-background">
      <!-- We will use a placeholder or the provided background image here -->
      <img src="/img/logo.png" alt="U-Tell Logo" class="logo-cap" />
    </div>
    
    <div class="login-card-bottom">
      <h1 class="title-primary title-login">Bienvenido</h1>
      
      <form @submit.prevent="handleLogin" class="form-container">
        <div class="input-group">
          <input 
            type="text" 
            v-model="username" 
            class="input-control rounded-input" 
            placeholder="Ingrese su usuario" 
            required
          />
        </div>
        
        <div class="input-group">
          <input 
            :type="showPassword ? 'text' : 'password'" 
            v-model="password" 
            class="input-control rounded-input" 
            placeholder="Ingrese su contraseña" 
            required
          />
        </div>
        
        <div class="flex justify-between items-center mb-3 text-sm">
          <label class="flex items-center" style="gap: 8px;">
            <input type="checkbox" v-model="rememberMe" class="custom-checkbox" />
            <span class="text-gray">Guardar contraseña</span>
          </label>
          <a href="#" class="text-primary no-underline">¿Olvidaste tu contraseña?</a>
        </div>
        
        <div class="btn-container">
          <p v-if="errorMessage" class="text-error" style="color: red; font-size: 12px; margin-bottom: 10px; text-align: center;">{{ errorMessage }}</p>
          <button type="submit" class="btn btn-primary rounded-btn" :disabled="isLoading">
            {{ isLoading ? 'Cargando...' : 'Iniciar sesión' }}
          </button>
        </div>
      </form>
      
      <div class="register-prompt text-center mt-2 text-sm text-gray">
        ¿No tienes una cuenta? <router-link to="/register" class="text-primary font-medium no-underline">Crear cuenta</router-link>
      </div>
      
      <div class="btn-container mt-3">
        <button class="btn btn-google-outline">
          <img src="/img/Vector.svg" alt="Google" width="18" style="margin-right: 8px;" />
          Continuar con Google
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    // Attempt login via API using the store
    await authStore.login(username.value, password.value)
    console.log('Login successful!')
    router.push('/feed')
  } catch (error) {
    console.error('Login failed', error)
    errorMessage.value = 'Credenciales incorrectas o error en el servidor.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.login-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: var(--bg-white);
  overflow: hidden;
}

.login-background {
  flex: 1;
  background-image: url("/img/unsplash.png"), linear-gradient(rgba(255, 107, 29, 0.6), rgba(255, 107, 29, 0.8));
  background-blend-mode: multiply;
  background-size: cover;
  background-position: center;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  min-height: 40vh;
}

.logo-cap {
  width: 120px;
  height: auto;
  z-index: 2;
  filter: brightness(0) invert(1); /* Makes the logo pure white */
}

.login-card-bottom {
  background-color: var(--bg-white);
  border-radius: 40px 40px 0 0;
  padding: 30px 25px 40px 25px;
  margin-top: -40px;
  z-index: 10;
  position: relative;
  box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
}

.title-login {
  margin-bottom: 25px;
}

.form-container {
  display: flex;
  flex-direction: column;
}

.rounded-input {
  border-radius: 20px;
  padding: 12px 20px;
  border: 1px solid #d1d1d1;
  color: #888;
}

.rounded-input::placeholder {
  color: #b1b1b1;
}

.text-sm {
  font-size: 11px;
}

.no-underline {
  text-decoration: none;
}

.custom-checkbox {
  accent-color: var(--primary-color);
  width: 14px;
  height: 14px;
}

.btn-container {
  display: flex;
  justify-content: center;
}

.rounded-btn {
  border-radius: 20px;
  width: 60%;
  padding: 12px 0;
  font-size: 14px;
  font-weight: 500;
  box-shadow: none;
}

.btn-google-outline {
  background-color: transparent;
  color: var(--primary-color);
  border: 1px solid var(--primary-color);
  border-radius: 20px;
  width: 70%;
  padding: 10px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 500;
}
</style>
