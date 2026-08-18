<template>
  <div class="register-page">
    <div class="top-section">
      <img src="/img/logo.png" alt="U-Tell Logo" class="logo-cap" />
    </div>
    
    <div class="bottom-card">
      <div class="header-row">
        <button v-if="step > 1" @click="step--" class="back-btn">← Volver</button>
        <router-link v-else to="/" class="back-btn">← Volver</router-link>
        <h2 class="step-title">Paso {{ step }} de 3</h2>
      </div>

      <form @submit.prevent="step === 3 ? handleRegister() : nextStep()" class="register-form">
        <transition name="fade" mode="out-in">
          <div v-if="step === 1" key="step1" class="step-content">
            <h3>Tus Datos Básicos</h3>
            <input type="text" v-model="form.nombre" placeholder="Nombre" required />
            <input type="text" v-model="form.apellido" placeholder="Apellido" required />
            <input type="email" v-model="form.email" placeholder="Correo Electrónico" required />
            <div class="input-group">
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Contraseña" required />
              <button type="button" class="toggle-pwd" @click="showPassword = !showPassword">
                <span v-if="showPassword">👁️</span>
                <span v-else>🙈</span>
              </button>
            </div>
            <p class="login-link">¿Ya tienes cuenta? <router-link to="/">Inicia sesión</router-link></p>
          </div>

          <div v-else-if="step === 2" key="step2" class="step-content">
            <h3>Sobre Ti</h3>
            <input type="date" v-model="form.fNac" placeholder="Fecha de Nacimiento" required />
            <input type="tel" v-model="form.celular" placeholder="Celular" />
            <select v-model="form.tipoPerfil" required>
              <option value="" disabled>Selecciona tu tipo de perfil</option>
              <option value="Estudiante">Estudiante</option>
              <option value="Docente">Docente</option>
              <option value="Universidad">Universidad</option>
              <option value="Empresa">Empresa</option>
            </select>
            <input type="text" v-model="form.descripcion" placeholder="Breve descripción" />
            <input type="text" v-model="form.trayectoria" placeholder="Trayectoria (opcional)" />
            
            <div v-if="form.tipoPerfil === 'Estudiante'">
              <select v-model="form.idUniversidad" required>
                <option value="" disabled>Selecciona tu Universidad</option>
                <option v-for="uni in universidades" :key="uni.idUniversidad" :value="uni.idUniversidad">{{ uni.nombre }}</option>
              </select>
              <select v-model="form.idCarrera" required style="margin-top: 15px;">
                <option value="" disabled>Selecciona tu Carrera</option>
                <option v-for="carrera in carreras" :key="carrera.idCarrera" :value="carrera.idCarrera">{{ carrera.nombre }}</option>
              </select>
            </div>
          </div>

          <div v-else key="step3" class="step-content">
            <h3>Tu Ubicación</h3>
            <select v-model="form.pais" @change="loadStates" required :disabled="loadingCountries">
              <option value="" disabled>{{ loadingCountries ? 'Cargando países...' : 'Selecciona un país' }}</option>
              <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
            </select>
            
            <select v-model="form.provincia" @change="loadCities" required :disabled="!form.pais || loadingStates">
              <option value="" disabled>{{ loadingStates ? 'Cargando provincias...' : 'Selecciona una provincia' }}</option>
              <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
            </select>
            
            <select v-model="form.ciudad" required :disabled="!form.provincia || loadingCities">
              <option value="" disabled>{{ loadingCities ? 'Cargando ciudades...' : 'Selecciona una ciudad' }}</option>
              <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
            </select>
          </div>
        </transition>

        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <button type="submit" class="btn btn-primary" :disabled="isLoading">
          <span v-if="isLoading">Procesando...</span>
          <span v-else>{{ step < 3 ? 'Siguiente' : 'Finalizar Registro' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { API_BASE_URL } from '../api'
import axios from 'axios'

const router = useRouter()
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
  nombre: '', apellido: '', email: '', password: '',
  fNac: '', fotoPerfil: '', celular: '', tipoPerfil: '',
  descripcion: '', trayectoria: '', pais: '', provincia: '', ciudad: '',
  idUniversidad: '', idCarrera: ''
})

onMounted(() => { 
  loadCountries()
  loadInstitutions() 
})

const loadInstitutions = async () => {
  try {
    const resUni = await axios.get(`${API_BASE_URL}/universidades`)
    if (Array.isArray(resUni.data)) universidades.value = resUni.data
    const resCar = await axios.get(`${API_BASE_URL}/carreras`)
    if (Array.isArray(resCar.data)) carreras.value = resCar.data
  } catch(e) { console.error(e) }
}

const loadCountries = async () => {
  loadingCountries.value = true
  try {
    const res = await axios.get('https://countriesnow.space/api/v0.1/countries/states')
    countries.value = res.data.data.map(c => c.name).sort()
  } catch (error) { console.error('Error loading countries', error) }
  finally { loadingCountries.value = false }
}

const loadStates = async () => {
  if (!form.value.pais) return
  form.value.provincia = ''; form.value.ciudad = ''
  states.value = []; cities.value = []
  loadingStates.value = true
  try {
    const res = await axios.post('https://countriesnow.space/api/v0.1/countries/states', { country: form.value.pais })
    states.value = res.data.data.states.map(s => s.name).sort()
    if(states.value.length === 0) states.value = [form.value.pais]
  } catch (error) { states.value = [form.value.pais] }
  finally { loadingStates.value = false }
}

const loadCities = async () => {
  if (!form.value.pais || !form.value.provincia) return
  form.value.ciudad = ''; cities.value = []
  loadingCities.value = true
  try {
    const res = await axios.post('https://countriesnow.space/api/v0.1/countries/state/cities', { country: form.value.pais, state: form.value.provincia })
    cities.value = res.data.data.sort()
    if(cities.value.length === 0) cities.value = [form.value.provincia]
  } catch (error) { cities.value = [form.value.provincia] }
  finally { loadingCities.value = false }
}

const nextStep = () => { if (step.value < 3) step.value++ }

const handleRegister = async () => {
  if(!form.value.ciudad) { errorMessage.value = 'Por favor selecciona una ciudad.'; return }
  isLoading.value = true; errorMessage.value = ''
  try {
    const formData = new FormData()
    for (const key in form.value) formData.append(key, form.value[key])
    const response = await axios.post(`${API_BASE_URL}/usuarios/nuevo`, formData)
    
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
    
    if (typeof data === 'string' && (data.includes('Error') || data.includes('error'))) {
      throw new Error(data)
    }

    alert('Cuenta creada con éxito. Ya puedes iniciar sesión.')
    router.push('/')
  } catch (error) { 
    errorMessage.value = error.message || 'Hubo un error al crear la cuenta.' 
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
  font-family: var(--font-family);
}

.top-section {
  flex: 0 0 25vh;
  background-image: linear-gradient(rgba(255,107,53,0.7), rgba(255,107,53,0.7)), url('/img/unsplash.png');
  background-blend-mode: multiply;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-cap {
  width: 100px;
  filter: brightness(0) invert(1);
}

.bottom-card {
  background: var(--bg-card);
  border-radius: 40px 40px 0 0;
  padding: 30px 25px 40px;
  margin-top: -40px;
  z-index: 10;
  position: relative;
  box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
  min-height: 75vh;
  display: flex;
  flex-direction: column;
}

.header-row {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  position: relative;
}

.back-btn {
  color: var(--primary);
  background: none;
  border: none;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  position: absolute;
  left: 0;
}

.step-title {
  color: var(--text-strong);
  font-size: 18px;
  font-weight: 700;
  margin: 0 auto;
  text-align: center;
}

.register-form {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 15px;
  flex-grow: 1;
}

.step-content h3 {
  color: var(--text-body);
  font-size: 22px;
  margin-bottom: 10px;
}

input, select {
  width: 100%;
  border-radius: var(--radius-full);
  padding: 12px 20px;
  border: 1px solid var(--border-light);
  font-size: 16px;
  color: var(--text-body);
  background: var(--bg-card);
  outline: none;
  transition: border-color var(--duration-fast) var(--ease-out);
  box-sizing: border-box;
}

input:focus, select:focus {
  border-color: var(--primary);
}

.input-group {
  position: relative;
}

.toggle-pwd {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0.6;
}

.login-link {
  text-align: center;
  margin-top: 10px;
  font-size: 14px;
  color: var(--text-secondary);
}

.login-link a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
}

.error-msg {
  color: #DC2626;
  font-size: 14px;
  text-align: center;
  margin: 15px 0 0;
}

.btn-primary {
  background: var(--primary);
  color: var(--text-inverse);
  border: none;
  border-radius: var(--radius-full);
  padding: 14px 20px;
  width: 100%;
  margin-top: 25px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: transform var(--duration-fast) var(--ease-out);
}

.btn-primary:active {
  transform: scale(0.98);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Animations */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
