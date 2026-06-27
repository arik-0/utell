<template>
  <div class="create-page">
    <div class="header">
      <router-link to="/feed" class="close-btn text-white no-underline">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </router-link>
      <h2 class="title">Crear experiencia</h2>
      
      <div class="header-actions">
        <button class="icon-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
        </button>
        <button class="btn btn-white btn-sm" @click="submitPost" :disabled="isLoading">
          {{ isLoading ? '...' : 'Publicar' }}
        </button>
      </div>
    </div>
    
    <div class="content-body">
      <!-- Profile section -->
      <div class="profile-row">
        <div class="avatar">
          <img :src="authStore.user?.fotoPerfil || '/img/default-avatar.png'" alt="Avatar" />
        </div>
        <div class="profile-info">
          <h3 class="name">{{ authStore.user?.nombre }} {{ authStore.user?.apellido }}</h3>
          <div class="privacy-dropdown">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Amigos</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </div>
        </div>
      </div>
      
      <!-- Inputs -->
      <div class="form-container">
        <input type="text" v-model="form.titulo" class="input-line" placeholder="Titulo" />
        
        <!-- Universidad Autocomplete using HTML5 datalist -->
        <input list="uni-list" v-model="form.universidad" class="input-line" placeholder="Universidad (Opcional)" autocomplete="off">
        <datalist id="uni-list">
          <option v-for="u in universidades" :key="u.name" :value="u.name"></option>
        </datalist>
        
        <!-- Carrera Autocomplete using HTML5 datalist -->
        <input list="car-list" v-model="form.carrera" class="input-line" placeholder="Carrera (Opcional)" autocomplete="off">
        <datalist id="car-list">
          <option v-for="c in carreras" :key="c.idCarrera" :value="c.nombre"></option>
        </datalist>
        
        <textarea v-model="form.texto" class="input-line textarea-large" placeholder="Descripción" required></textarea>
      </div>
      
      <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref(false)
const errorMsg = ref('')

const universidades = ref([])
const carreras = ref([])

const form = ref({
  titulo: '',
  universidad: '',
  carrera: '',
  texto: ''
})

onMounted(async () => {
  // Llenar combos
  try {
    // Usar Hipolabs API para universidades mundiales (o filtrado por país si quieres)
    const resUni = await axios.get('http://universities.hipolabs.com/search?country=Argentina')
    if (Array.isArray(resUni.data)) {
      universidades.value = resUni.data
    }
    
    // Obtener carreras existentes en la BD
    const resCar = await axios.get('http://localhost/utell/api/public/carreras')
    if (Array.isArray(resCar.data)) {
      carreras.value = resCar.data
    }
  } catch(e) {
    console.error("Error loading combos", e)
  }
})

const submitPost = async () => {
  if (!form.value.texto) {
    errorMsg.value = "La descripción no puede estar vacía."
    return
  }
  
  isLoading.value = true
  errorMsg.value = ""
  
  try {
    const formData = new FormData()
    formData.append('idUsuario', authStore.user.idUsuario)
    formData.append('tipoPublicacion', 'Experiencia')
    formData.append('titulo', form.value.titulo)
    formData.append('texto', form.value.texto)
    // Send string names instead of IDs
    if(form.value.universidad) formData.append('universidad', form.value.universidad)
    if(form.value.carrera) formData.append('carrera', form.value.carrera)

    const res = await axios.post('http://localhost/utell/api/public/publicacion/nuevo', formData)
    if (typeof res.data === 'string' && res.data.includes('error')) {
      throw new Error(res.data)
    }
    
    router.push('/feed')
  } catch (error) {
    console.error(error)
    errorMsg.value = "Hubo un error al publicar."
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.create-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: var(--bg-white);
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: var(--primary-color);
  padding: 15px 20px;
  color: white;
  padding-top: env(safe-area-inset-top, 20px);
}

.title {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.icon-btn {
  background: none;
  border: none;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.btn-white {
  background-color: white;
  color: var(--primary-color);
  font-weight: 600;
  padding: 6px 16px;
  border-radius: 20px;
  border: none;
  cursor: pointer;
}

.content-body {
  padding: 20px;
  flex: 1;
}

.profile-row {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;
  margin-right: 15px;
  background-color: #eee;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.name {
  font-size: 16px;
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 5px;
}

.privacy-dropdown {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 3px 8px;
  font-size: 11px;
  color: #666;
  background-color: #f9f9f9;
}

.form-container {
  display: flex;
  flex-direction: column;
}

.input-line {
  border: none;
  border-bottom: 1px solid #eee;
  padding: 15px 0;
  font-size: 14px;
  color: #333;
  width: 100%;
  outline: none;
  background: transparent;
}

.input-line::placeholder {
  color: #b1b1b1;
}

select.input-line {
  color: #b1b1b1;
  appearance: none;
}

select.input-line option {
  color: #333;
}

.textarea-large {
  min-height: 150px;
  resize: none;
  border-bottom: none;
  margin-top: 10px;
}

.error-msg {
  color: red;
  font-size: 12px;
  margin-top: 15px;
}
</style>
