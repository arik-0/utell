<template>
  <div class="create-page">
    <!-- Header -->
    <header class="create-header">
      <button @click="router.back()" class="close-btn" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <div class="header-center">
        <span class="badge badge-accent">Consulta</span>
        <h1>Nueva Consulta</h1>
      </div>
      <button @click="submitPost" class="btn-publish" :disabled="isLoading || !form.texto.trim()">
        <span v-if="isLoading" class="btn-spinner"></span>
        <span v-else>Publicar</span>
      </button>
    </header>
    
    <div class="create-content">
      <!-- Author preview card -->
      <div class="author-card">
        <div class="avatar-wrap">
          <img :src="getProfilePic(authStore.user?.fotoPerfil)" alt="Avatar" class="avatar" />
        </div>
        <div class="author-details">
          <span class="name">{{ authStore.user?.nombre }} {{ authStore.user?.apellido }}</span>
          <span class="sub-text">{{ authStore.user?.tipoPerfil || 'Estudiante' }}</span>
        </div>
      </div>

      <form class="post-form" @submit.prevent="submitPost">
        <div class="form-card">
          <div class="input-field">
            <label class="field-label">Título de la consulta</label>
            <input 
              type="text" 
              v-model="form.titulo" 
              placeholder="Ej: ¿Dónde se cursa Álgebra I los martes?" 
              class="form-input"
            />
          </div>

          <div class="field-row">
            <div class="input-field">
              <label class="field-label">Universidad (Opcional)</label>
              <div class="select-wrap">
                <input 
                  type="text" 
                  list="universidades" 
                  v-model="form.universidad" 
                  placeholder="Selecciona o escribe..." 
                  class="form-input select-input" 
                />
                <datalist id="universidades">
                  <option v-for="uni in universidades" :key="uni.idUniversidad" :value="uni.nombre"></option>
                </datalist>
              </div>
            </div>

            <div class="input-field">
              <label class="field-label">Carrera (Opcional)</label>
              <div class="select-wrap">
                <input 
                  type="text" 
                  list="carreras" 
                  v-model="form.carrera" 
                  placeholder="Selecciona o escribe..." 
                  class="form-input select-input" 
                />
                <datalist id="carreras">
                  <option v-for="car in carreras" :key="car.idCarrera" :value="car.nombre"></option>
                </datalist>
              </div>
            </div>
          </div>

          <div class="input-field">
            <label class="field-label">Detalle de tu consulta</label>
            <textarea 
              v-model="form.texto" 
              placeholder="Explica tu duda para que otros estudiantes o docentes puedan ayudarte..." 
              class="form-textarea"
              rows="6"
            ></textarea>
          </div>
        </div>

        <p v-if="errorMsg" class="error-banner">{{ errorMsg }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const isLoading = ref(false)
const errorMsg = ref('')
const universidades = ref([])
const carreras = ref([])
const form = ref({ titulo: '', universidad: '', carrera: '', texto: '' })

onMounted(async () => {
  try {
    const resUni = await axios.get(`${API_BASE_URL}/universidades`)
    if (Array.isArray(resUni.data)) universidades.value = resUni.data
    const resCar = await axios.get(`${API_BASE_URL}/carreras`)
    if (Array.isArray(resCar.data)) carreras.value = resCar.data
  } catch(e) { console.error('Error loading combos', e) }
})

const submitPost = async () => {
  if (!form.value.texto.trim()) { 
    errorMsg.value = 'El detalle de la consulta no puede estar vacío.'
    return 
  }
  isLoading.value = true
  errorMsg.value = ''
  try {
    const formData = new FormData()
    formData.append('idUsuario', authStore.user.idUsuario)
    formData.append('tipoPublicacion', 'Consulta')
    formData.append('titulo', form.value.titulo)
    formData.append('texto', form.value.texto)
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
  } catch (error) { 
    errorMsg.value = error.message || 'Hubo un error al publicar la consulta.' 
  } finally { 
    isLoading.value = false 
  }
}
</script>

<style scoped>
.create-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--bg-main);
  font-family: var(--font-body);
}

/* ─── Header ─── */
.create-header {
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-bottom: 1px solid var(--border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  position: sticky;
  top: 0;
  z-index: 20;
}

.close-btn {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 6px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:active { background: var(--bg-subtle); }

.header-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.header-center h1 {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.btn-publish {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  border: none;
  border-radius: var(--radius-full);
  padding: 7px 18px;
  font-family: var(--font-family);
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  box-shadow: var(--shadow-primary);
  transition: all var(--duration-fast);
}

.btn-publish:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Content ─── */
.create-content {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-width: 600px;
  margin: 0 auto;
  width: 100%;
}

.author-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-xs);
}

.avatar-wrap {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--bg-subtle);
  overflow: hidden;
  flex-shrink: 0;
}

.avatar { width: 100%; height: 100%; object-fit: cover; }

.author-details {
  display: flex;
  flex-direction: column;
}

.name {
  font-family: var(--font-family);
  font-weight: 700;
  font-size: 14.5px;
  color: var(--text-strong);
}

.sub-text {
  font-size: 12px;
  color: var(--text-secondary);
}

/* ─── Form Card ─── */
.form-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 18px 16px;
  box-shadow: var(--shadow-xs);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.input-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-label {
  font-family: var(--font-family);
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-secondary);
}

.form-input {
  width: 100%;
  border: 1.5px solid var(--border-light);
  background: var(--bg-subtle);
  border-radius: var(--radius-md);
  padding: 11px 14px;
  font-size: 14px;
  color: var(--text-strong);
  font-family: var(--font-body);
  outline: none;
  transition: all var(--duration-fast);
}

.form-input:focus {
  border-color: var(--primary);
  background: #FFFFFF;
  box-shadow: 0 0 0 3px var(--primary-ghost);
}

.form-input::placeholder { color: var(--text-muted); }

.field-row {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

@media (min-width: 480px) {
  .field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
}

.form-textarea {
  width: 100%;
  border: 1.5px solid var(--border-light);
  background: var(--bg-subtle);
  border-radius: var(--radius-md);
  padding: 12px 14px;
  font-size: 14.5px;
  color: var(--text-strong);
  font-family: var(--font-body);
  outline: none;
  resize: vertical;
  min-height: 140px;
  line-height: 1.55;
  transition: all var(--duration-fast);
}

.form-textarea:focus {
  border-color: var(--primary);
  background: #FFFFFF;
  box-shadow: 0 0 0 3px var(--primary-ghost);
}

.form-textarea::placeholder { color: var(--text-muted); }

.error-banner {
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid rgba(220, 38, 38, 0.2);
  border-radius: var(--radius-md);
  padding: 10px 14px;
  font-size: 13px;
  margin: 0;
}
</style>
