<template>
  <div class="register-page">
    <div class="top-section">
      <img src="/img/logo.png" alt="U-Tell Logo" class="logo-cap" />
    </div>
    
    <div class="bottom-card">
      <div class="header-row">
        <button v-if="step > 1" @click="step--" class="back-btn" aria-label="Volver">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          <span>Volver</span>
        </button>
        <router-link v-else to="/" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          <span>Inicio</span>
        </router-link>
        
        <!-- Step Indicator Pill -->
        <div class="step-progress">
          <div class="step-dot" :class="{ active: step >= 1, current: step === 1 }"></div>
          <div class="step-line" :class="{ active: step >= 2 }"></div>
          <div class="step-dot" :class="{ active: step >= 2, current: step === 2 }"></div>
          <div class="step-line" :class="{ active: step >= 3 }"></div>
          <div class="step-dot" :class="{ active: step >= 3, current: step === 3 }"></div>
        </div>
      </div>

      <div class="step-header">
        <h2 class="step-title">
          {{ step === 1 ? 'Datos Básicos' : (step === 2 ? 'Perfil Académico' : 'Tu Ubicación') }}
        </h2>
        <p class="step-desc">
          {{ step === 1 ? 'Ingresa tus datos personales y credenciales' : (step === 2 ? 'Personaliza tu rol en la comunidad universitaria' : 'Selecciona tu ubicación geográfica') }}
        </p>
      </div>

      <form @submit.prevent="step === 3 ? handleRegister() : nextStep()" class="register-form">
        <transition name="fade" mode="out-in">
          <!-- Step 1: Basic Info -->
          <div v-if="step === 1" key="step1" class="step-content">
            <div class="input-field">
              <label class="field-label">Nombre</label>
              <input type="text" v-model="form.nombre" placeholder="Tu nombre" class="form-input" required />
            </div>

            <div class="input-field">
              <label class="field-label">Apellido</label>
              <input type="text" v-model="form.apellido" placeholder="Tu apellido" class="form-input" required />
            </div>

            <div class="input-field">
              <label class="field-label">Correo Electrónico</label>
              <input type="email" v-model="form.email" placeholder="nombre@universidad.edu" class="form-input" required />
            </div>

            <div class="input-field">
              <label class="field-label">Contraseña</label>
              <div class="password-input-wrap">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="form.password" 
                  placeholder="Crea una contraseña segura" 
                  class="form-input"
                  required 
                />
                <button type="button" class="toggle-pwd" @click="showPassword = !showPassword" aria-label="Mostrar contraseña">
                  <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>

            <p class="login-link">¿Ya tienes cuenta? <router-link to="/">Inicia sesión</router-link></p>
          </div>

          <!-- Step 2: Academic Profile -->
          <div v-else-if="step === 2" key="step2" class="step-content">
            <div class="field-row">
              <div class="input-field">
                <label class="field-label">Fecha de Nacimiento</label>
                <input type="date" v-model="form.fNac" class="form-input" required />
              </div>
              <div class="input-field">
                <label class="field-label">Celular</label>
                <input type="tel" v-model="form.celular" placeholder="+54 9 11..." class="form-input" />
              </div>
            </div>

            <div class="input-field">
              <label class="field-label">Tipo de Perfil</label>
              <select v-model="form.tipoPerfil" class="form-input select-control" required>
                <option value="" disabled>Selecciona tu rol</option>
                <option value="Estudiante">Estudiante</option>
                <option value="Docente">Docente</option>
                <option value="Universidad">Universidad</option>
                <option value="Empresa">Empresa</option>
              </select>
            </div>

            <div v-if="form.tipoPerfil === 'Estudiante'" class="academic-cascade">
              <div class="input-field">
                <label class="field-label">Universidad</label>
                <select v-model="form.idUniversidad" class="form-input select-control" required>
                  <option value="" disabled>Selecciona tu Universidad</option>
                  <option v-for="uni in universidades" :key="uni.idUniversidad" :value="uni.idUniversidad">{{ uni.nombre }}</option>
                </select>
              </div>

              <div class="input-field">
                <label class="field-label">Carrera</label>
                <select v-model="form.idCarrera" class="form-input select-control" required>
                  <option value="" disabled>Selecciona tu Carrera</option>
                  <option v-for="carrera in carreras" :key="carrera.idCarrera" :value="carrera.idCarrera">{{ carrera.nombre }}</option>
                </select>
              </div>
            </div>

            <div class="input-field">
              <label class="field-label">Breve descripción</label>
              <input type="text" v-model="form.descripcion" placeholder="Ej: Estudiante de Ingeniería en Sistemas" class="form-input" />
            </div>
          </div>

          <!-- Step 3: Location -->
          <div v-else key="step3" class="step-content">
            <div class="input-field">
              <label class="field-label">País</label>
              <select v-model="form.pais" @change="loadStates" class="form-input select-control" required :disabled="loadingCountries">
                <option value="" disabled>{{ loadingCountries ? 'Cargando países...' : 'Selecciona un país' }}</option>
                <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
              </select>
            </div>
            
            <div class="input-field">
              <label class="field-label">Provincia / Estado</label>
              <select v-model="form.provincia" @change="loadCities" class="form-input select-control" required :disabled="!form.pais || loadingStates">
                <option value="" disabled>{{ loadingStates ? 'Cargando provincias...' : 'Selecciona una provincia' }}</option>
                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
              </select>
            </div>
            
            <div class="input-field">
              <label class="field-label">Ciudad</label>
              <select v-model="form.ciudad" class="form-input select-control" required :disabled="!form.provincia || loadingCities">
                <option value="" disabled>{{ loadingCities ? 'Cargando ciudades...' : 'Selecciona una ciudad' }}</option>
                <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
              </select>
            </div>
          </div>
        </transition>

        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <button type="submit" class="btn btn-primary submit-btn" :disabled="isLoading">
          <span v-if="isLoading" class="btn-spinner"></span>
          <span v-else>{{ step < 3 ? 'Continuar' : 'Finalizar Registro' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { useToastStore } from '../store/toast'

const router = useRouter()
const toastStore = useToastStore()

const step = ref(1)
const isLoading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)

const countries = ref([])
const states = ref([])
const cities = ref([])
const loadingCountries = ref(false)
const loadingStates = ref(false)
const loadingCities = ref(false)

const universidades = ref([])
const carreras = ref([])

const form = ref({
  nombre: '',
  apellido: '',
  email: '',
  password: '',
  fNac: '',
  fotoPerfil: '',
  celular: '',
  tipoPerfil: 'Estudiante',
  descripcion: '',
  trayectoria: '',
  pais: 'Argentina',
  provincia: '',
  ciudad: '',
  idUniversidad: '',
  idCarrera: ''
})

// Fallback data for quick local selection
const defaultLocations = {
  'Argentina': {
    'Buenos Aires': ['La Plata', 'Mar del Plata', 'Tandil', 'Bahía Blanca', 'Quilmes', 'San Isidro'],
    'Ciudad Autónoma de Buenos Aires': ['CABA - Central', 'Palermo', 'Recoleta', 'Caballito', 'Belgrano'],
    'Santa Fe': ['Rosario', 'Santa Fe Capital', 'Rafaela', 'Venado Tuerto'],
    'Córdoba': ['Córdoba Capital', 'Villa María', 'Río Cuarto', 'Villa Carlos Paz'],
    'Mendoza': ['Mendoza Capital', 'San Rafael', 'Godoy Cruz'],
    'Tucumán': ['San Miguel de Tucumán', 'Yerba Buena', 'Concepción'],
    'Entre Ríos': ['Paraná', 'Concordia', 'Gualeguaychú']
  },
  'Uruguay': {
    'Montevideo': ['Montevideo Central', 'Pocitos', 'Carrasco'],
    'Canelones': ['Ciudad de la Costa', 'Las Piedras'],
    'Maldonado': ['Punta del Este', 'Maldonado']
  },
  'Chile': {
    'Región Metropolitana': ['Santiago', 'Providencia', 'Las Condes'],
    'Valparaíso': ['Valparaíso', 'Viña del Mar'],
    'Biobío': ['Concepción', 'Talcahuano']
  }
}

onMounted(() => { 
  loadInstitutions()
  loadCountries()
})

const loadInstitutions = async () => {
  try {
    const [resUni, resCar] = await Promise.allSettled([
      api.get(`/universidades`),
      api.get(`/carreras`)
    ])
    if (resUni.status === 'fulfilled' && Array.isArray(resUni.value.data)) {
      universidades.value = resUni.value.data
    }
    if (resCar.status === 'fulfilled' && Array.isArray(resCar.value.data)) {
      carreras.value = resCar.value.data
    }
  } catch (e) {
    console.error('Error loading institutions', e)
  }
}

const loadCountries = async () => {
  loadingCountries.value = true
  try {
    const res = await api.get(`/paises`)
    if (Array.isArray(res.data) && res.data.length > 0) {
      countries.value = res.data.map(p => p.nombre || p).sort()
    } else {
      countries.value = Object.keys(defaultLocations)
    }
  } catch (_) {
    countries.value = Object.keys(defaultLocations)
  } finally {
    loadingCountries.value = false
    if (!countries.value.includes(form.value.pais)) {
      form.value.pais = countries.value[0] || 'Argentina'
    }
    loadStates()
  }
}

const loadStates = async () => {
  if (!form.value.pais) return
  form.value.provincia = ''
  form.value.ciudad = ''
  states.value = []
  cities.value = []
  loadingStates.value = true

  try {
    const res = await api.get(`/provincias`)
    if (Array.isArray(res.data) && res.data.length > 0) {
      states.value = res.data.map(p => p.nombre || p).sort()
    } else if (defaultLocations[form.value.pais]) {
      states.value = Object.keys(defaultLocations[form.value.pais])
    } else {
      states.value = [form.value.pais]
    }
  } catch (_) {
    if (defaultLocations[form.value.pais]) {
      states.value = Object.keys(defaultLocations[form.value.pais])
    } else {
      states.value = ['Buenos Aires', 'Santa Fe', 'Córdoba', 'Mendoza']
    }
  } finally {
    loadingStates.value = false
    if (states.value.length > 0) {
      form.value.provincia = states.value[0]
      loadCities()
    }
  }
}

const loadCities = async () => {
  if (!form.value.pais || !form.value.provincia) return
  form.value.ciudad = ''
  cities.value = []
  loadingCities.value = true

  try {
    const res = await api.get(`/ciudades`)
    if (Array.isArray(res.data) && res.data.length > 0) {
      cities.value = res.data.map(c => c.nombre || c).sort()
    } else if (defaultLocations[form.value.pais]?.[form.value.provincia]) {
      cities.value = defaultLocations[form.value.pais][form.value.provincia]
    } else {
      cities.value = [form.value.provincia]
    }
  } catch (_) {
    if (defaultLocations[form.value.pais]?.[form.value.provincia]) {
      cities.value = defaultLocations[form.value.pais][form.value.provincia]
    } else {
      cities.value = ['Rosario', 'La Plata', 'Córdoba Capital', 'CABA']
    }
  } finally {
    loadingCities.value = false
    if (cities.value.length > 0) {
      form.value.ciudad = cities.value[0]
    }
  }
}

const validateStep = (currentStep) => {
  errorMessage.value = ''
  if (currentStep === 1) {
    if (!form.value.nombre.trim() || !form.value.apellido.trim()) {
      errorMessage.value = 'Por favor ingresa tu nombre y apellido completos.'
      return false
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!form.value.email.trim() || !emailRegex.test(form.value.email.trim())) {
      errorMessage.value = 'Por favor ingresa un correo electrónico válido.'
      return false
    }
    if (!form.value.password || form.value.password.length < 6) {
      errorMessage.value = 'La contraseña debe tener al menos 6 caracteres.'
      return false
    }
  } else if (currentStep === 2) {
    if (!form.value.fNac) {
      errorMessage.value = 'Por favor selecciona tu fecha de nacimiento.'
      return false
    }
    if (!form.value.tipoPerfil) {
      errorMessage.value = 'Por favor selecciona tu tipo de perfil.'
      return false
    }
  }
  return true
}

const nextStep = () => {
  if (validateStep(step.value)) {
    if (step.value < 3) {
      step.value++
      errorMessage.value = ''
    }
  }
}

const handleRegister = async () => {
  if (!validateStep(1) || !validateStep(2)) return
  if (!form.value.ciudad) {
    errorMessage.value = 'Por favor selecciona una ciudad.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const formData = new FormData()
    for (const key in form.value) {
      if (form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key])
      }
    }

    const response = await api.post(`/usuarios/nuevo`, formData)
    let data = response.data
    if (typeof data === 'string') {
      try { data = JSON.parse(data) } catch (e) {}
    }
    
    if (data && data.error) {
      const msg = typeof data.error === 'object' ? data.error.text : data.error
      if (msg.includes('Duplicate entry') || msg.includes('1062')) {
        throw new Error('El correo electrónico ya se encuentra registrado.')
      }
      throw new Error(msg)
    }
    
    if (typeof data === 'string' && (data.toLowerCase().includes('error'))) {
      throw new Error(data)
    }

    toastStore.success('¡Cuenta creada con éxito! Ya puedes iniciar sesión.', 4000)
    router.push('/')
  } catch (error) { 
    errorMessage.value = error.message || 'Hubo un error al crear la cuenta.'
    toastStore.error(errorMessage.value)
  } finally { 
    isLoading.value = false 
  }
}
</script>


<style scoped>
.register-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--bg-main);
  font-family: var(--font-body);
}

.top-section {
  flex: 0 0 22vh;
  background-image: linear-gradient(rgba(229, 90, 43, 0.85), rgba(255, 107, 53, 0.8)), url('/img/unsplash.png');
  background-blend-mode: multiply;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-cap {
  width: 90px;
  filter: brightness(0) invert(1);
}

.bottom-card {
  background: var(--bg-card);
  border-radius: 32px 32px 0 0;
  padding: 24px 20px 40px;
  margin-top: -28px;
  z-index: 10;
  position: relative;
  box-shadow: 0 -6px 20px rgba(15, 23, 42, 0.08);
  min-height: 78vh;
  display: flex;
  flex-direction: column;
  max-width: 520px;
  width: 100%;
  margin-left: auto;
  margin-right: auto;
}

.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.back-btn {
  color: var(--text-secondary);
  background: none;
  border: none;
  font-family: var(--font-family);
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 0;
}

.back-btn:hover { color: var(--primary); }

.step-progress {
  display: flex;
  align-items: center;
  gap: 4px;
}

.step-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border-light);
  transition: all var(--duration-normal);
}

.step-dot.active { background: var(--primary); }
.step-dot.current { width: 18px; border-radius: var(--radius-full); }

.step-line {
  width: 12px;
  height: 2px;
  background: var(--border-light);
}

.step-line.active { background: var(--primary); }

.step-header {
  margin-bottom: 20px;
}

.step-title {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 4px;
}

.step-desc {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0;
}

.register-form {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
}

.input-field {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.field-label {
  font-family: var(--font-family);
  font-size: 11.5px;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input {
  width: 100%;
  border-radius: var(--radius-md);
  padding: 12px 16px;
  border: 1.5px solid var(--border-light);
  font-size: 14.5px;
  color: var(--text-strong);
  background: var(--bg-subtle);
  outline: none;
  font-family: var(--font-body);
  transition: all var(--duration-fast);
}

.form-input:focus {
  border-color: var(--primary);
  background: #FFFFFF;
  box-shadow: 0 0 0 3px var(--primary-ghost);
}

.form-input::placeholder { color: var(--text-muted); }

.select-control {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 36px;
}

.password-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.toggle-pwd {
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

.toggle-pwd:hover { color: var(--text-strong); }

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.academic-cascade {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 12px;
  background: var(--bg-subtle);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
}

.login-link {
  text-align: center;
  margin-top: 10px;
  font-size: 13.5px;
  color: var(--text-secondary);
}

.login-link a {
  color: var(--primary);
  font-weight: 700;
}

.error-msg {
  color: #DC2626;
  font-size: 13px;
  text-align: center;
  background: #FEF2F2;
  border: 1px solid rgba(220, 38, 38, 0.2);
  border-radius: var(--radius-md);
  padding: 10px 14px;
  margin: 14px 0 0;
}

.submit-btn {
  margin-top: 22px;
}

.btn-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.22s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* ─── Desktop & Tablet Layout ─── */
@media (min-width: 768px) {
  .register-page {
    flex-direction: row;
    align-items: center;
    justify-content: center;
    background: transparent;
    padding: 40px 20px;
    width: 100%;
  }

  .top-section {
    flex: 0 0 360px;
    height: 620px;
    border-radius: 28px 0 0 28px;
    box-shadow: var(--shadow-lg);
  }

  .logo-cap {
    width: 130px;
  }

  .bottom-card {
    flex: 0 0 500px;
    height: 620px;
    margin: 0;
    border-radius: 0 28px 28px 0;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-light);
    border-left: none;
    overflow-y: auto;
    padding: 36px 32px;
    background: var(--bg-card);
  }
}
</style>

