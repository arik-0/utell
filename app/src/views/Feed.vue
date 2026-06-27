<template>
  <div class="page-container feed-page">
    <header class="header">
      <div class="tabs">
        <button class="tab active">Para ti</button>
        <button class="tab">Siguiendo</button>
      </div>
    </header>

    <div class="feed-content">
      <div v-if="isLoading" class="loading-state text-center p-4 text-gray">
        Cargando publicaciones...
      </div>
      <div v-else-if="posts.length === 0" class="empty-state text-center p-4 text-gray">
        No hay publicaciones para mostrar.
      </div>
      <div v-else>
        <!-- Loop through fetched posts -->
        <div v-for="(post, index) in posts" :key="index" class="post-card card" @click="goToPost(post.idPublicacion)">
          <div class="post-header">
            <img :src="getProfilePic(post.fotoPerfil)" alt="User avatar" class="avatar" />
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
            <button class="more-btn" @click.stop="() => {}">⋮</button>
          </div>
          <div class="post-body">
            <h3 v-if="post.titulo" class="post-title">{{ post.titulo }}</h3>
            <p>{{ post.texto }}</p>
          </div>
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
const posts = ref([])
const isLoading = ref(true)

const goToPost = (id) => {
  if (id) {
    router.push(`/post/${id}`)
  }
}

const fetchFeed = async () => {
  try {
    isLoading.value = true
    const userId = authStore.user?.idUsuario || 0
    const res = await axios.get(`http://localhost/utell/api/public/parati?idUsuario=${userId}`)
    if (Array.isArray(res.data)) {
      posts.value = res.data
    }
  } catch (error) {
    console.error('Error fetching feed:', error)
  } finally {
    isLoading.value = false
  }
}

const toggleLike = async (post) => {
  if (!authStore.user) return
  
  // Optimistic UI update
  const originalLiked = post.hasLiked
  const originalLikes = post.likes
  
  post.hasLiked = originalLiked > 0 ? 0 : 1
  post.likes = originalLiked > 0 ? (post.likes - 1) : (post.likes + 1)
  
  try {
    const formData = new FormData()
    formData.append('idUsuario', authStore.user.idUsuario)
    
    const res = await axios.post(`http://localhost/utell/api/public/publicacion/${post.idPublicacion}/like`, formData)
    
    if (res.data && res.data.totalLikes !== undefined) {
      post.likes = res.data.totalLikes
      post.hasLiked = res.data.likedByUser ? 1 : 0
    }
  } catch (error) {
    console.error('Error toggling like:', error)
    // Revert on error
    post.hasLiked = originalLiked
    post.likes = originalLikes
  }
}

// Helper to handle relative image paths
const getProfilePic = (pic) => {
  if (!pic) return '/img/Perfil.svg'
  if (pic.startsWith('http')) return pic
  return `/img/${pic}`
}

// Very basic formatter for time
const formatTime = (fecha, hora) => {
  if (!fecha) return 'Reciente'
  return `${fecha} ${hora || ''}`.trim()
}

onMounted(() => {
  fetchFeed()
})
</script>

<style scoped>
.feed-page {
  padding-bottom: 70px;
}

.header {
  padding: var(--spacing-sm) var(--spacing-md);
  border-bottom: 1px solid var(--border-color);
  background-color: var(--bg-white);
  position: sticky;
  top: 0;
  z-index: 10;
}

.tabs {
  display: flex;
  justify-content: space-around;
}

.tab {
  background: none;
  border: none;
  padding: var(--spacing-sm);
  color: var(--text-gray);
  font-weight: 500;
  position: relative;
}

.tab.active {
  color: var(--text-dark);
  font-weight: 600;
}

.tab.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background-color: var(--primary-color);
  border-radius: 3px 3px 0 0;
}

.feed-content {
  padding: var(--spacing-md);
}

.post-card {
  margin-bottom: var(--spacing-md);
  padding: var(--spacing-md);
  border-radius: var(--border-radius-lg);
  background-color: var(--bg-white);
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.post-card:active {
  transform: scale(0.98);
  box-shadow: 0 1px 5px rgba(0,0,0,0.05);
}

.post-header {
  display: flex;
  align-items: center;
  margin-bottom: var(--spacing-sm);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: var(--spacing-sm);
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
  font-size: 14px;
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
  font-size: 16px;
  font-weight: 600;
  color: var(--text-dark);
  margin-bottom: 5px;
  margin-top: 0;
}

.post-body p {
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: var(--spacing-md);
}

.post-footer {
  display: flex;
  gap: var(--spacing-md);
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-sm);
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
</style>
