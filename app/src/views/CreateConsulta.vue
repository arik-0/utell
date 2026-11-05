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
                  @change="onUniversidadChange"
                  placeholder="Selecciona o escribe..." 
                  class="form-input select-input" 
                />
                <datalist id="universidades">
                  <option v-for="uni in universidades" :key="uni.idUniversidad" :value="uni.nombre"></option>
                </datalist>
              </div>
            </div>

            <div class="input-field">
              <label class="field-label">Sede Territorial (Opcional)</label>
              <div class="select-wrap">
                <input 
                  type="text" 
                  list="sedes" 
                  v-model="form.sede" 
                  @change="onSedeChange"
                  placeholder="Selecciona sede..." 
                  class="form-input select-input" 
                />
                <datalist id="sedes">
                  <option v-for="s in sedesFiltradas" :key="s.idSede" :value="s.nombre"></option>
                </datalist>
              </div>
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

          <div class="input-field">
            <div class="field-label-row">
              <label class="field-label">Detalle de tu consulta *</label>
              <span class="char-count" :class="{ 'char-limit': form.texto.length > 900 }">{{ form.texto.length }}/1000</span>
            </div>
            <textarea 
              v-model="form.texto" 
              placeholder="Explica tu duda para que otros estudiantes o docentes puedan ayudarte..." 
              class="form-textarea"
              rows="6"
              maxlength="1000"
              required
            ></textarea>
          </div>

          <!-- Attachment Section -->
          <div class="attachment-section">
            <div v-if="attachedImage" class="attachment-preview-card">
              <img :src="attachedImage.preview" alt="Adjunto" class="preview-thumb" />
              <div class="preview-info">
                <span class="preview-name">{{ attachedImage.file.name }}</span>
                <span class="preview-size">{{ (attachedImage.file.size / 1024).toFixed(0) }} KB</span>
              </div>
              <button type="button" class="remove-attachment-btn" @click="removeAttachment" title="Quitar imagen">✕</button>
            </div>

            <div v-else class="attachment-trigger-row">
              <button type="button" class="attach-btn" @click="imageFileInput?.click()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span>Adjuntar imagen</span>
              </button>
              <span class="attach-hint">JPG, PNG o WebP</span>
            </div>

            <input
              type="file"
              ref="imageFileInput"
              accept="image/*"
              style="display: none"
              @change="handleImageSelection"
            />
          </div>
        </div>

        <p v-if="errorMsg" class="error-banner">{{ errorMsg }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'
import { getProfilePic, uploadImageFile } from '../utils'
import api from '../api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()
const isLoading = ref(false)
const errorMsg = ref('')
const universidades = ref([])
const sedes = ref([])
const carreras = ref([])
const form = ref({
  titulo: '',
  universidad: typeof route.query.universidad === 'string' ? route.query.universidad : '',
  sede: '',
  carrera: '',
  texto: ''
})

const imageFileInput = ref(null)
const attachedImage = ref(null)

const sedesFiltradas = computed(() => {
  if (!form.value.universidad || !form.value.universidad.trim()) {
    return sedes.value
  }
  const uni = universidades.value.find(
    u => u.nombre.toLowerCase() === form.value.universidad.trim().toLowerCase()
  )
  if (!uni) return sedes.value
  return sedes.value.filter(s => s.idUniversidad === uni.idUniversidad)
})

const onSedeChange = () => {
  if (!form.value.sede) return
  const matchedSede = sedes.value.find(
    s => s.nombre.toLowerCase() === form.value.sede.trim().toLowerCase()
  )
  if (matchedSede && matchedSede.idUniversidad) {
    const parentUni = universidades.value.find(u => u.idUniversidad === matchedSede.idUniversidad)
    if (parentUni && (!form.value.universidad || !form.value.universidad.trim())) {
      form.value.universidad = parentUni.nombre
    }
  }
}

const onUniversidadChange = () => {
  if (!form.value.universidad) return
  const uni = universidades.value.find(
    u => u.nombre.toLowerCase() === form.value.universidad.trim().toLowerCase()
  )
  if (uni && form.value.sede) {
    const sedeObj = sedes.value.find(
      s => s.nombre.toLowerCase() === form.value.sede.trim().toLowerCase()
    )
    if (sedeObj && sedeObj.idUniversidad !== uni.idUniversidad) {
      form.value.sede = ''
    }
  }
}

const handleImageSelection = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    toastStore.error('Por favor selecciona un archivo de imagen válido.')
    return
  }

  const reader = new FileReader()
  reader.onload = (e) => {
    attachedImage.value = {
      file,
      preview: e.target.result
    }
  }
  reader.readAsDataURL(file)
}

const removeAttachment = () => {
  attachedImage.value = null
  if (imageFileInput.value) imageFileInput.value.value = ''
}

onMounted(async () => {
  try {
    const [resUni, resSed, resCar] = await Promise.allSettled([
      api.get(`/universidades`),
      api.get(`/sedes`),
      api.get(`/carreras`)
    ])
    if (resUni.status === 'fulfilled' && Array.isArray(resUni.value.data)) {
      universidades.value = resUni.value.data
    }
    if (resSed.status === 'fulfilled' && Array.isArray(resSed.value.data)) {
      sedes.value = resSed.value.data
    }
    if (resCar.status === 'fulfilled' && Array.isArray(resCar.value.data)) {
      carreras.value = resCar.value.data
    }
  } catch(e) {
    console.error('Error loading combos', e)
  }
})

const submitPost = async () => {
  if (!form.value.texto.trim()) { 
    errorMsg.value = 'El detalle de la consulta no puede estar vacío.'
    toastStore.warning(errorMsg.value)
    return 
  }
  isLoading.value = true
  errorMsg.value = ''
  try {
    let postText = form.value.texto.trim()

    if (attachedImage.value) {
      toastStore.info('Optimizando imagen...', 1500)
      try {
        const imgUrl = await uploadImageFile(attachedImage.value.file, {
          maxWidth: 1200,
          maxHeight: 1200,
          quality: 0.85
        })
        postText += `\n\n[img:${imgUrl}]`
      } catch (imgErr) {
        console.warn('Error subiendo imagen adjunta:', imgErr)
      }
    }

    const formData = new FormData()
    formData.append('idUsuario', authStore.user?.idUsuario || 0)
    formData.append('tipoPublicacion', 'Consulta')
    formData.append('titulo', form.value.titulo.trim())
    formData.append('texto', postText)
    
    if (form.value.universidad && form.value.universidad.trim()) {
      formData.append('universidad', form.value.universidad.trim())
      const uniObj = universidades.value.find(u => u.nombre === form.value.universidad.trim())
      if (uniObj && uniObj.idUniversidad) {
        formData.append('idUniversidad', uniObj.idUniversidad)
      }
    }
    if (form.value.sede && form.value.sede.trim()) {
      formData.append('sede', form.value.sede.trim())
      const sedeObj = sedes.value.find(s => s.nombre.toLowerCase() === form.value.sede.trim().toLowerCase())
      if (sedeObj && sedeObj.idSede) {
        formData.append('idSede', sedeObj.idSede)
      }
    }
    if (form.value.carrera && form.value.carrera.trim()) {
      formData.append('carrera', form.value.carrera.trim())
      const carObj = carreras.value.find(c => c.nombre === form.value.carrera.trim())
      if (carObj && carObj.idCarrera) {
        formData.append('idCarrera', carObj.idCarrera)
      }
    }

    const res = await api.post(`/publicacion/nuevo`, formData)
    
    if (res.data?.error) {
      const msg = typeof res.data.error === 'object' ? res.data.error.text : res.data.error
      throw new Error(msg || 'Error al guardar la consulta.')
    }
    if (typeof res.data === 'string' && res.data.toLowerCase().includes('error')) {
      throw new Error(res.data)
    }
    
    toastStore.success('¡Consulta publicada exitosamente!')
    router.push('/feed')
  } catch (error) { 
    const serverMsg = error.response?.data?.error?.text 
      || error.response?.data?.mensaje 
      || (typeof error.response?.data?.error === 'string' ? error.response.data.error : null)
      || error.message 
      || 'Hubo un error al publicar la consulta.'
    errorMsg.value = serverMsg
    toastStore.error(serverMsg)
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
  background: var(--bg-card);
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
  background: var(--bg-card);
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

/* ─── Attachment Section ─── */
.attachment-section {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid var(--border-light);
}

.attachment-trigger-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.attach-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-subtle);
  border: 1.5px dashed var(--border-light);
  border-radius: var(--radius-full);
  padding: 8px 16px;
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-fast) ease;
}

.attach-btn:hover {
  background: var(--primary-ghost);
  border-color: var(--primary);
  color: var(--primary);
}

.attach-hint {
  font-size: 11.5px;
  color: var(--text-muted);
}

.attachment-preview-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: var(--radius-md);
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
}

.preview-thumb {
  width: 52px;
  height: 52px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  border: 1px solid var(--border-light);
}

.preview-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.preview-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.preview-size {
  font-size: 11px;
  color: var(--text-muted);
}

.remove-attachment-btn {
  background: none;
  border: none;
  font-size: 16px;
  color: var(--text-muted);
  cursor: pointer;
  padding: 6px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--duration-fast);
}

.remove-attachment-btn:hover {
  background: var(--error-bg);
  color: var(--error);
}
</style>
