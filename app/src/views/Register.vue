<template>
  <div class="login-page">
    <div class="login-background">
      <img src="/img/logo.png" alt="U-Tell Logo" class="logo-cap" />
    </div>
    
    <div class="login-card-bottom" style="padding-top: 20px;">
      <div class="flex items-center mb-3">
        <button v-if="step > 1" @click="step--" class="back-btn text-primary bg-none border-none cursor-pointer">← Atrás</button>
        <router-link v-else to="/" class="back-btn text-primary no-underline">← Atrás</router-link>
        
        <h1 class="title-primary" style="margin-left: auto; margin-right: auto; margin-bottom: 0;">
          Paso {{ step }} de 3
        </h1>
        <div style="width: 50px;"></div> <!-- Spacer -->
      </div>
      
      <form @submit.prevent="step === 3 ? handleRegister() : nextStep()" class="form-container">
        
        <!-- STEP 1: Credenciales -->
        <div v-if="step === 1" class="step-content">
          <h3 class="step-title">Tus Datos Básicos</h3>
          <div class="input-group">
            <input type="text" v-model="form.nombre" class="input-control rounded-input" placeholder="Nombre" required />
          </div>
          <div class="input-group">
            <input type="text" v-model="form.apellido" class="input-control rounded-input" placeholder="Apellido" required />
          </div>
          <div class="input-group">
            <input type="email" v-model="form.email" class="input-control rounded-input" placeholder="Correo electrónico" required />
          </div>
          <div class="input-group">
            <input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="input-control rounded-input" placeholder="Contraseña" required minlength="4"/>
          </div>
        </div>

        <!-- STEP 2: Perfil -->
        <div v-if="step === 2" class="step-content">
          <h3 class="step-title">Sobre Ti</h3>
          <div class="input-group">
            <label class="input-label">Fecha de Nacimiento</label>
            <input type="date" v-model="form.fNac" class="input-control rounded-input" required />
          </div>
          <div class="input-group">
            <input type="tel" v-model="form.celular" class="input-control rounded-input" placeholder="Número de Celular" />
          </div>
          <div class="input-group">
            <select v-model="form.tipoPerfil" class="input-control rounded-input" required>
              <option value="" disabled>Selecciona tipo de perfil...</option>
              <option value="Estudiante">Estudiante</option>
              <option value="Docente">Docente</option>
              <option value="Universidad">Universidad</option>
              <option value="Empresa">Empresa</option>
            </select>
          </div>
          <div class="input-group">
            <input type="text" v-model="form.descripcion" class="input-control rounded-input" placeholder="Breve biografía (Opcional)" />
          </div>
          <div class="input-group">
            <input type="text" v-model="form.trayectoria" class="input-control rounded-input" placeholder="Trayectoria académica (Opcional)" />
          </div>
        </div>

        <!-- STEP 3: Ubicación (API Geográfica) -->
        <div v-if="step === 3" class="step-content">
          <h3 class="step-title">Tu Ubicación</h3>
          <div class="input-group">
            <select v-model="form.pais" @change="loadStates" class="input-control rounded-input" required :disabled="loadingCountries">
              <option value="">{{ loadingCountries ? 'Cargando países...' : 'Selecciona un país' }}</option>
              <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
          <div class="input-group">
            <select v-model="form.provincia" @change="loadCities" class="input-control rounded-input" required :disabled="loadingStates || !form.pais">
              <option value="">{{ loadingStates ? 'Cargando estados/provincias...' : 'Selecciona provincia/estado' }}</option>
              <option v-for="s in states" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>
          <div class="input-group">
            <select v-model="form.ciudad" class="input-control rounded-input" required :disabled="loadingCities || !form.provincia">
              <option value="">{{ loadingCities ? 'Cargando ciudades...' : 'Selecciona tu ciudad' }}</option>
              <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
            </select>
          </div>
        </div>
        
        <div class="btn-container mt-3">
          <p v-if="errorMessage" class="text-error text-center" style="color: red; font-size: 12px; margin-bottom: 10px;">{{ errorMessage }}</p>
          
          <button v-if="step < 3" type="submit" class="btn btn-primary rounded-btn">
            Siguiente
          </button>
          
          <button v-if="step === 3" type="submit" class="btn btn-primary rounded-btn" :disabled="isLoading">
            {{ isLoading ? 'Creando...' : 'Finalizar Registro' }}
          </button>
        </div>
      </form>
      
      <div v-if="step === 1" class="register-prompt text-center mt-3 text-sm text-gray">
        ¿Ya tienes una cuenta? <router-link to="/" class="text-primary font-medium no-underline">Iniciar sesión</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const step = ref(1)
const isLoading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)

// Geolocation Data
const countries = ref([])
const states = ref([])
const cities = ref([])

const loadingCountries = ref(false)
const loadingStates = ref(false)
const loadingCities = ref(false)

const form = ref({
  nombre: '',
  apellido: '',
  email: '',
  password: '',
  fNac: '',
  fotoPerfil: '',
  celular: '',
  tipoPerfil: '',
  descripcion: '',
  trayectoria: '',
  pais: '',
  provincia: '',
  ciudad: ''
})

onMounted(() => {
  loadCountries()
})

const loadCountries = async () => {
  loadingCountries.value = true
  try {
    const res = await axios.get('https://countriesnow.space/api/v0.1/countries/states')
    countries.value = res.data.data.map(c => c.name).sort()
  } catch (error) {
    console.error("Error loading countries", error)
  } finally {
    loadingCountries.value = false
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
    const res = await axios.post('https://countriesnow.space/api/v0.1/countries/states', { country: form.value.pais })
    states.value = res.data.data.states.map(s => s.name).sort()
    if(states.value.length === 0) {
      states.value = [form.value.pais] // Fallback if no states
    }
  } catch (error) {
    console.error("Error loading states", error)
    states.value = [form.value.pais]
  } finally {
    loadingStates.value = false
  }
}

const loadCities = async () => {
  if (!form.value.pais || !form.value.provincia) return
  form.value.ciudad = ''
  cities.value = []
  
  loadingCities.value = true
  try {
    // Some states have " Province" or " State" appended which the cities API doesn't like, but we pass it as is.
    const res = await axios.post('https://countriesnow.space/api/v0.1/countries/state/cities', { 
      country: form.value.pais,
      state: form.value.provincia
    })
    cities.value = res.data.data.sort()
    if(cities.value.length === 0) {
      cities.value = [form.value.provincia] // Fallback if no cities
    }
  } catch (error) {
    console.error("Error loading cities", error)
    cities.value = [form.value.provincia] // Fallback
  } finally {
    loadingCities.value = false
  }
}

const nextStep = () => {
  if (step.value < 3) step.value++
}

const handleRegister = async () => {
  if(!form.value.ciudad) {
    errorMessage.value = "Por favor selecciona una ciudad."
    return
  }
  
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const formData = new FormData()
    for (const key in form.value) {
      formData.append(key, form.value[key])
    }
    
    // We send 'pais', 'provincia', 'ciudad' to PHP. PHP will resolve idCiudad automatically!
    const response = await axios.post('http://localhost/utell/api/public/usuarios/nuevo', formData)
    
    if (typeof response.data === 'string' && response.data.includes('error')) {
      throw new Error(response.data)
    }
    
    alert("Cuenta creada con éxito. Ya puedes iniciar sesión.")
    router.push('/')
  } catch (error) {
    console.error('Registration failed', error)
    errorMessage.value = 'Hubo un error al crear la cuenta. Revisa los datos y vuelve a intentarlo.'
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
  overflow-y: auto;
}

.login-background {
  flex: 0 0 25vh; /* slightly smaller for wizard */
  background-image: url("/img/unsplash.png"), linear-gradient(rgba(255, 107, 29, 0.6), rgba(255, 107, 29, 0.8));
  background-blend-mode: multiply;
  background-size: cover;
  background-position: center;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.logo-cap {
  width: 80px;
  height: auto;
  z-index: 2;
  filter: brightness(0) invert(1);
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
  min-height: 75vh;
}

.step-title {
  text-align: center;
  color: var(--text-dark);
  margin-bottom: 20px;
  font-weight: 600;
  font-size: 18px;
}

.step-content {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateX(10px); }
  to { opacity: 1; transform: translateX(0); }
}

.form-container {
  display: flex;
  flex-direction: column;
}

.input-label {
  font-size: 12px;
  color: var(--text-gray);
  margin-left: 15px;
  margin-bottom: 4px;
  display: block;
}

.rounded-input {
  border-radius: 20px;
  padding: 12px 20px;
  border: 1px solid #d1d1d1;
  color: #555;
  background-color: #fff;
}

.rounded-input::placeholder {
  color: #b1b1b1;
}

.text-sm {
  font-size: 11px;
}

.bg-none { background: none; }
.border-none { border: none; }
.cursor-pointer { cursor: pointer; }

.no-underline {
  text-decoration: none;
}

.back-btn {
  font-weight: 600;
  font-size: 14px;
}

.btn-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.rounded-btn {
  border-radius: 20px;
  width: 60%;
  padding: 12px 0;
  font-size: 14px;
  font-weight: 500;
  box-shadow: none;
}
</style>
