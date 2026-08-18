<template>
  <div class="create-page">
    <header class="create-header">
      <router-link to="/feed" class="close-btn">✕</router-link>
      <h1>Crear Experiencia</h1>
      <button @click="submitPost" class="btn-publish" :disabled="isLoading">
        <span class="icon">📎</span> Publicar
      </button>
    </header>
    
    <div class="create-content">
      <div class="user-info">
        <img :src="authStore.user?.fotoPerfil || '/img/default-avatar.png'" alt="Avatar" class="avatar" />
        <div class="user-details">
          <span class="name">{{ authStore.user?.nombre }} {{ authStore.user?.apellido }}</span>
          <select class="privacy-dropdown">
            <option>Público</option>
            <option>Solo amigos</option>
          </select>
        </div>
      </div>

      <form class="post-form">
        <input type="text" v-model="form.titulo" placeholder="Título de la experiencia" class="underline-input" />
        
        <input type="text" list="universidades" v-model="form.universidad" placeholder="Universidad (Opcional)" class="underline-input" />
        <datalist id="universidades">
          <option v-for="uni in universidades" :key="uni.idUniversidad" :value="uni.nombre"></option>
        </datalist>

        <input type="text" list="carreras" v-model="form.carrera" placeholder="Carrera (Opcional)" class="underline-input" />
        <datalist id="carreras">
          <option v-for="car in carreras" :key="car.idCarrera" :value="car.nombre"></option>
        </datalist>

        <div class="ratings-container">
          <div class="rating-group">
            <label>Clima Estudiantil</label>
            <div class="stars">
              <span v-for="n in 5" :key="'c'+n" @click="form.puntuacionClima = n" :class="{ active: n <= form.puntuacionClima }">★</span>
            </div>
          </div>
          <div class="rating-group">
            <label>Infraestructura</label>
            <div class="stars">
              <span v-for="n in 5" :key="'i'+n" @click="form.puntuacionInfraestructura = n" :class="{ active: n <= form.puntuacionInfraestructura }">★</span>
            </div>
          </div>
          <div class="rating-group">
            <label>Nivel Académico</label>
            <div class="stars">
              <span v-for="n in 5" :key="'a'+n" @click="form.puntuacionAcademica = n" :class="{ active: n <= form.puntuacionAcademica }">★</span>
            </div>
          </div>
        </div>

        <textarea v-model="form.texto" placeholder="Describe tu experiencia aquí..." class="post-textarea"></textarea>
        
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { API_BASE_URL } from '../api'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const isLoading = ref(false)
const errorMsg = ref('')
const universidades = ref([])
const carreras = ref([])
const form = ref({ titulo: '', universidad: '', carrera: '', texto: '', puntuacionClima: 5, puntuacionInfraestructura: 5, puntuacionAcademica: 5 })

onMounted(async () => {
  try {
    const resUni = await axios.get(`${API_BASE_URL}/universidades`)
    if (Array.isArray(resUni.data)) universidades.value = resUni.data
    const resCar = await axios.get(`${API_BASE_URL}/carreras`)
    if (Array.isArray(resCar.data)) carreras.value = resCar.data
  } catch(e) { console.error('Error loading combos', e) }
})

const submitPost = async () => {
  if (!form.value.texto) { errorMsg.value = 'La descripción no puede estar vacía.'; return }
  isLoading.value = true; errorMsg.value = ''
  try {
    const formData = new FormData()
    formData.append('idUsuario', authStore.user.idUsuario)
    formData.append('tipoPublicacion', 'Experiencia')
    formData.append('titulo', form.value.titulo)
    formData.append('texto', form.value.texto)
    formData.append('puntuacionClima', form.value.puntuacionClima)
    formData.append('puntuacionInfraestructura', form.value.puntuacionInfraestructura)
    formData.append('puntuacionAcademica', form.value.puntuacionAcademica)

    if (form.value.universidad) {
      const uniObj = universidades.value.find(u => u.nombre === form.value.universidad)
      if (uniObj && uniObj.idUniversidad) formData.append('idUniversidad', uniObj.idUniversidad)
    }
    if (form.value.carrera) {
      const carObj = carreras.value.find(c => c.nombre === form.value.carrera)
      if (carObj && carObj.idCarrera) formData.append('idCarrera', carObj.idCarrera)
    }
    const res = await axios.post(`${API_BASE_URL}/publicacion/nuevo`, formData)
    if (typeof res.data === 'string' && res.data.includes('error')) throw new Error(res.data)
    router.push('/feed')
  } catch (error) { errorMsg.value = 'Hubo un error al publicar.' }
  finally { isLoading.value = false }
}
</script>

<style scoped>
.create-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--bg-main);
  font-family: var(--font-family);
}

.create-header {
  background: var(--primary);
  color: var(--text-inverse);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 20px;
}

.close-btn {
  color: var(--text-inverse);
  text-decoration: none;
  font-size: 20px;
  font-weight: bold;
}

h1 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.btn-publish {
  background: var(--bg-card);
  color: var(--primary);
  border: none;
  border-radius: var(--radius-full);
  padding: 8px 16px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: opacity var(--duration-fast);
}

.btn-publish:disabled {
  opacity: 0.7;
}

.create-content {
  padding: 20px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border-light);
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.name {
  font-weight: 600;
  color: var(--text-strong);
}

.privacy-dropdown {
  background: transparent;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-sm);
  font-size: 12px;
  padding: 2px 5px;
  color: var(--text-secondary);
}

.post-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  flex-grow: 1;
}

.underline-input {
  border: none;
  border-bottom: 1px solid var(--border-light);
  background: transparent;
  padding: 10px 0;
  font-size: 16px;
  color: var(--text-body);
  outline: none;
  transition: border-color var(--duration-fast);
}

.underline-input:focus {
  border-bottom-color: var(--primary);
}

.post-textarea {
  border: none;
  background: transparent;
  padding: 10px 0;
  font-size: 16px;
  color: var(--text-body);
  outline: none;
  resize: none;
  flex-grow: 1;
  font-family: inherit;
  min-height: 150px;
}

.ratings-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 10px 0;
  padding: 15px;
  background-color: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
}

.rating-group {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.rating-group label {
  font-size: 14px;
  color: var(--text-strong);
  font-weight: 500;
}

.stars {
  display: flex;
  gap: 4px;
  cursor: pointer;
}

.stars span {
  font-size: 24px;
  color: #E5E7EB;
  transition: color var(--duration-fast);
}

.stars span.active {
  color: var(--accent); /* Yellow */
}

.error-msg {
  color: #DC2626;
  font-size: 14px;
  margin: 0;
}
</style>
