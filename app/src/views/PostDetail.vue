<template>
  <div class="post-detail-page">
    <div class="header">
      <button class="back-btn" @click="router.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      </button>
      <h2 class="title">Publicación</h2>
      <div class="spacer"></div>
    </div>

    <div class="content" v-if="post">
      <!-- Original Post -->
      <div class="original-post">
        <div class="post-header">
          <img :src="getProfilePic(post.fotoPerfil)" alt="Avatar" class="avatar" />
          <div class="post-meta">
            <div class="meta-header">
              <span class="user-name">{{ post.nombre }} {{ post.apellido }}</span>
              <span v-if="post.tipoPublicacion" :class="['type-badge', post.tipoPublicacion.toLowerCase()]">
                {{ post.tipoPublicacion === 'Consulta' ? '?' : 'XP' }}
              </span>
              <span v-if="post.nombreUniversidad" class="uni-badge">
                🎓 {{ post.nombreUniversidad }}
              </span>
            </div>
            <span class="post-time">{{ formatTime(post.fecha, post.hora) }}</span>
          </div>
          <button class="more-btn">⋮</button>
        </div>
        <div class="post-content">
          <h2 class="post-title">{{ post.titulo }}</h2>
          <p class="post-text">{{ post.texto }}</p>
          <div class="post-footer">
            <div class="action-btn" @click.stop="toggleLike(post)" :class="{ 'liked': post.hasLiked > 0 }">
              <span class="heart-icon">{{ post.hasLiked > 0 ? '❤️' : '🤍' }}</span> {{ post.likes || 0 }}
            </div>
            <div class="action-btn">
              <span>💬</span> {{ post.cantidadRespuestas || 0 }}
            </div>
          </div>
        </div>
      </div>

      <div class="responses-container">
        <h3 class="responses-title">Respuestas</h3>
        
        <div v-if="isLoadingRespuestas" class="loading">Cargando respuestas...</div>
        
        <div v-else-if="respuestas.length === 0" class="no-responses">
          Aún no hay respuestas. ¡Sé el primero en comentar!
        </div>
        
        <div v-else class="responses-list">
          <div v-for="res in respuestas" :key="res.idRespuesta" class="response-card">
            <img :src="getProfilePic(res.fotoPerfil)" alt="Avatar" class="res-avatar" />
            <div class="res-content">
              <div class="res-header">
                <span class="res-user">{{ res.nombre }} {{ res.apellido }}</span>
                <span class="res-time">{{ formatTime(res.fecha, res.hora) }}</span>
              </div>
              <p class="res-text">{{ res.texto }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="reply-bar">
      <input 
        type="text" 
        v-model="newReply" 
        placeholder="Escribe una respuesta..." 
        @keyup.enter="submitReply"
        :disabled="isSubmitting"
      />
      <button class="send-btn" @click="submitReply" :disabled="!newReply.trim() || isSubmitting">
        ➤
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const post = ref(null)
const respuestas = ref([])
const newReply = ref('')
const isLoadingPost = ref(true)
const isLoadingRespuestas = ref(true)
const isSubmitting = ref(false)

const goBack = () => {
  router.back()
}

const fetchPost = async () => {
  try {
    const userId = authStore.user?.idUsuario || 0
    const res = await axios.get(`http://localhost/utell/api/public/publicacion/${route.params.id}?idUsuario=${userId}`)
    if (res.data && typeof res.data === 'object' && !res.data.error) {
      post.value = res.data
    }
  } catch (error) {
    console.error('Error fetching post:', error)
  } finally {
    isLoadingPost.value = false
  }
}

const toggleLike = async (postItem) => {
  if (!authStore.user) return
  
  // Optimistic UI update
  const originalLiked = postItem.hasLiked
  const originalLikes = postItem.likes
  
  postItem.hasLiked = originalLiked > 0 ? 0 : 1
  postItem.likes = originalLiked > 0 ? (postItem.likes - 1) : (postItem.likes + 1)
  
  try {
    const formData = new FormData()
    formData.append('idUsuario', authStore.user.idUsuario)
    
    const res = await axios.post(`http://localhost/utell/api/public/publicacion/${postItem.idPublicacion}/like`, formData)
    
    if (res.data && res.data.totalLikes !== undefined) {
      postItem.likes = res.data.totalLikes
      postItem.hasLiked = res.data.likedByUser ? 1 : 0
    }
  } catch (error) {
    console.error('Error toggling like:', error)
    // Revert on error
    postItem.hasLiked = originalLiked
    postItem.likes = originalLikes
  }
}

const fetchRespuestas = async () => {
  try {
    const res = await axios.get(`http://localhost/utell/api/public/publicacion/${route.params.id}/respuestas`)
    if (Array.isArray(res.data)) {
      respuestas.value = res.data
    }
  } catch (error) {
    console.error('Error fetching responses:', error)
  } finally {
    isLoadingRespuestas.value = false
  }
}

const submitReply = async () => {
  if (!newReply.value.trim() || isSubmitting.value) return
  
  isSubmitting.value = true
  try {
    const formData = new FormData()
    formData.append('idPublicacion', route.params.id)
    formData.append('idUsuario', authStore.user.idUsuario)
    formData.append('texto', newReply.value.trim())

    await axios.post('http://localhost/utell/api/public/respuesta/nuevo', formData)
    
    newReply.value = ''
    // Refresh responses
    await fetchRespuestas()
  } catch (error) {
    console.error('Error submitting response:', error)
    alert("Hubo un error al guardar la respuesta.")
  } finally {
    isSubmitting.value = false
  }
}

const getProfilePic = (pic) => {
  if (!pic) return '/img/Perfil.svg'
  if (pic.startsWith('http')) return pic
  return `/img/${pic}`
}

const formatTime = (fecha, hora) => {
  if (!fecha) return 'Reciente'
  return `${fecha} ${hora || ''}`.trim()
}

onMounted(() => {
  fetchPost()
  fetchRespuestas()
})
</script>

<style scoped>
.post-detail-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: var(--bg-white);
  padding-bottom: 60px; /* Space for reply bar */
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 20px;
  background-color: var(--bg-white);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 10;
  padding-top: env(safe-area-inset-top, 20px);
}

.back-btn {
  background: none;
  border: none;
  padding: 5px;
  cursor: pointer;
  color: var(--text-dark);
}

.title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: var(--text-dark);
}

.spacer {
  width: 34px; /* match back button width for centering */
}

.content {
  flex: 1;
  overflow-y: auto;
}

.original-post {
  padding: 20px;
}

.post-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  margin-right: 12px;
  background-color: #eee;
  object-fit: cover;
}

.avatar-sm {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  margin-right: 10px;
  background-color: #eee;
  object-fit: cover;
}

.post-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.meta-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.type-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  color: white;
}

.type-badge.consulta {
  background-color: #ff9800; /* Naranja para consulta */
}

.type-badge.experiencia {
  background-color: #9c27b0; /* Violeta/morado para experiencia */
}

.uni-badge {
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  background-color: #e0f7fa;
  color: #00838f;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
}

.user-name {
  font-weight: 600;
  font-size: 15px;
  color: var(--text-dark);
}

.post-time {
  font-size: 12px;
  color: var(--text-gray);
}

.more-btn {
  background: none;
  border: none;
  color: var(--text-gray);
  font-size: 20px;
}

.post-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-dark);
  margin-bottom: 8px;
  margin-top: 0;
}

.post-body p {
  font-size: 15px;
  line-height: 1.5;
  color: var(--text-dark);
  margin-bottom: 15px;
}

.post-footer {
  display: flex;
  gap: 20px;
  color: var(--text-gray);
  font-size: 14px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  transition: transform 0.1s;
}

.action-btn:active {
  transform: scale(0.9);
}

.heart-icon {
  transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: inline-block;
}

.action-btn.liked .heart-icon {
  transform: scale(1.2);
}

.divider {
  height: 8px;
  background-color: #f5f5f5;
  border-top: 1px solid var(--border-color);
  border-bottom: 1px solid var(--border-color);
}

.responses-list {
  padding: 15px 20px;
}

.response-item {
  display: flex;
  margin-bottom: 20px;
}

.response-content {
  flex: 1;
  background-color: #f9f9f9;
  padding: 12px;
  border-radius: 0 12px 12px 12px;
}

.response-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.response-text {
  font-size: 14px;
  color: #333;
  margin: 0;
  line-height: 1.4;
}

.loading-state, .empty-state {
  text-align: center;
  color: var(--text-gray);
  padding: 20px;
}

.full-page {
  margin-top: 50px;
}

/* Sticky Reply Bar */
.reply-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: var(--bg-white);
  border-top: 1px solid var(--border-color);
  padding: 10px 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
  padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
}

.reply-input {
  flex: 1;
  border: 1px solid var(--border-color);
  border-radius: 20px;
  padding: 10px 15px;
  font-size: 14px;
  outline: none;
  background-color: #f5f5f5;
}

.reply-input:focus {
  border-color: var(--primary-color);
  background-color: var(--bg-white);
}

.send-btn {
  background-color: var(--primary-color);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: opacity 0.2s;
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
