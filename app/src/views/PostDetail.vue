<template>
  <div class="post-detail-page">
    <!-- Header -->
    <div class="detail-header">
      <button class="back-btn" @click="router.back()" aria-label="Volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      </button>
      <h2 class="header-title">Publicacion</h2>
      <div class="spacer"></div>
    </div>

    <!-- Loading state -->
    <div v-if="isLoadingPost" class="loading-state">
      <div class="spinner"></div>
    </div>

    <!-- Content -->
    <div v-else-if="post" class="detail-content">
      <!-- Original Post -->
      <div class="original-post animate-fadein-up">
        <div class="post-header">
          <img :src="getProfilePic(post.fotoPerfil)" alt="Avatar" class="avatar" />
          <div class="post-meta">
            <div class="meta-top">
              <span class="user-name">{{ post.nombre }} {{ post.apellido }}</span>
              <span v-if="post.tipoPublicacion" class="type-badge" :class="post.tipoPublicacion === 'Consulta' ? 'badge-consulta' : 'badge-xp'">
                <span class="type-dot" :class="post.tipoPublicacion === 'Consulta' ? 'type-dot-consulta' : 'type-dot-experiencia'"></span>
                {{ post.tipoPublicacion }}
              </span>
            </div>
            <div class="meta-bottom">
              <span class="post-time">{{ timeAgo(post.fecha, post.hora) }}</span>
              <span v-if="post.nombreUniversidad" class="uni-chip">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                {{ post.nombreUniversidad }}
              </span>
            </div>
          </div>
          <button class="more-btn" aria-label="Opciones">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
          </button>
        </div>

        <div class="post-body">
          <h2 v-if="post.titulo" class="post-title">{{ post.titulo }}</h2>
          <p class="post-text">{{ post.texto }}</p>
        </div>

        <div class="post-actions">
          <button class="action-btn" @click="toggleLike(post)" :class="{ liked: post.hasLiked > 0 }">
            <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="post.hasLiked > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>{{ post.likes || 0 }}</span>
          </button>
          <button class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>{{ respuestas.length }}</span>
          </button>
        </div>
      </div>

      <!-- Responses Section -->
      <div class="responses-section">
        <h3 class="section-title">
          Respuestas
          <span class="count-chip">{{ respuestas.length }}</span>
        </h3>

        <div v-if="isLoadingRespuestas" class="loading-state compact">
          <div class="spinner"></div>
        </div>

        <div v-else-if="respuestas.length === 0" class="empty-responses">
          <p>Aun no hay respuestas. Se el primero en comentar.</p>
        </div>

        <div v-else class="responses-list">
          <div
            v-for="(res, index) in respuestas"
            :key="res.idRespuesta"
            class="response-card animate-fadein-up"
            :style="{ animationDelay: `${index * 0.05}s` }"
          >
            <img :src="getProfilePic(res.fotoPerfil)" alt="Avatar" class="res-avatar" />
            <div class="res-body">
              <div class="res-header">
                <span class="res-user">{{ res.nombre }} {{ res.apellido }}</span>
                <span class="res-time">{{ timeAgo(res.fecha, res.hora) }}</span>
              </div>
              <p class="res-text">{{ res.texto }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else class="loading-state">
      <p class="text-secondary">No se pudo cargar la publicacion.</p>
    </div>

    <!-- Reply Bar -->
    <div class="reply-bar">
      <input
        type="text"
        v-model="newReply"
        class="reply-input"
        placeholder="Escribi una respuesta..."
        @keyup.enter="submitReply"
        :disabled="isSubmitting"
      />
      <button
        class="send-btn"
        @click="submitReply"
        :disabled="!newReply.trim() || isSubmitting"
        aria-label="Enviar respuesta"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { timeAgo, getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import axios from 'axios'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()

const post              = ref(null)
const respuestas        = ref([])
const newReply          = ref('')
const isLoadingPost     = ref(true)
const isLoadingRespuestas = ref(true)
const isSubmitting      = ref(false)

const fetchPost = async () => {
  try {
    const userId = authStore.user?.idUsuario || 0
    const res    = await axios.get(`${API_BASE_URL}/publicacion/${route.params.id}?idUsuario=${userId}`)
    if (res.data && typeof res.data === 'object' && !res.data.error) {
      post.value = res.data
    }
  } catch (error) {
    console.error('Error fetching post:', error)
  } finally {
    isLoadingPost.value = false
  }
}

const fetchRespuestas = async () => {
  try {
    const res = await axios.get(`${API_BASE_URL}/publicacion/${route.params.id}/respuestas`)
    if (Array.isArray(res.data)) {
      respuestas.value = res.data
    }
  } catch (error) {
    console.error('Error fetching responses:', error)
  } finally {
    isLoadingRespuestas.value = false
  }
}

const toggleLike = async (postItem) => {
  if (!authStore.user) return
  const originalLiked = postItem.hasLiked
  const originalLikes = postItem.likes
  postItem.hasLiked   = originalLiked > 0 ? 0 : 1
  postItem.likes      = originalLiked > 0 ? (postItem.likes - 1) : (postItem.likes + 1)

  try {
    const fd = new FormData()
    fd.append('idUsuario', authStore.user.idUsuario)
    const res = await axios.post(`${API_BASE_URL}/publicacion/${postItem.idPublicacion}/like`, fd)
    if (res.data && res.data.totalLikes !== undefined) {
      postItem.likes    = res.data.totalLikes
      postItem.hasLiked = res.data.likedByUser ? 1 : 0
    }
  } catch (error) {
    postItem.hasLiked = originalLiked
    postItem.likes    = originalLikes
  }
}

const submitReply = async () => {
  if (!newReply.value.trim() || isSubmitting.value) return
  isSubmitting.value = true
  try {
    const fd = new FormData()
    fd.append('idPublicacion', route.params.id)
    fd.append('idUsuario', authStore.user.idUsuario)
    fd.append('texto', newReply.value.trim())
    await axios.post(`${API_BASE_URL}/respuesta/nuevo`, fd)
    newReply.value = ''
    await fetchRespuestas()
  } catch (error) {
    console.error('Error submitting reply:', error)
  } finally {
    isSubmitting.value = false
  }
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
  height: 100dvh;
  background-color: var(--bg-main);
  padding-bottom: 64px;
}

/* ─── Header ─── */
.detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  padding-top: max(14px, env(safe-area-inset-top));
  background-color: var(--bg-card);
  border-bottom: 1px solid var(--border-light);
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
  color: var(--text-strong);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
}

.back-btn:active { background: var(--bg-subtle); }

.header-title {
  font-size: 17px;
  font-weight: 600;
  color: var(--text-strong);
  margin: 0;
}

.spacer { width: 34px; }

/* ─── Loading / Error ─── */
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 40px 0;
}

.loading-state.compact { padding: 24px 0; }

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Content ─── */
.detail-content { flex: 1; overflow-y: auto; }

.original-post {
  padding: 20px 16px;
  background-color: var(--bg-card);
  border-bottom: 1px solid var(--border-light);
}

.post-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 14px;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background-color: var(--bg-subtle);
  object-fit: cover;
  flex-shrink: 0;
}

.post-meta { flex: 1; min-width: 0; }

.meta-top {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-name {
  font-weight: 600;
  font-size: 15px;
  color: var(--text-strong);
}

.type-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.badge-consulta { background-color: var(--accent-ghost); color: #92700C; }
.badge-xp       { background-color: var(--primary-ghost); color: var(--primary-dark); }

.meta-bottom {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 3px;
}

.post-time { font-size: 12px; color: var(--text-muted); }

.uni-chip {
  font-size: 11px;
  font-weight: 500;
  padding: 2px 7px;
  border-radius: var(--radius-full);
  background-color: var(--accent-ghost);
  color: #92700C;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.more-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
}

.more-btn:active { background: var(--bg-subtle); }

.post-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 8px;
  line-height: 1.3;
}

.post-text {
  font-size: 15px;
  line-height: 1.6;
  color: var(--text-body);
  margin-bottom: 16px;
}

.post-actions {
  display: flex;
  gap: 20px;
  padding-top: 12px;
  border-top: 1px solid var(--border-light);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  background: none;
  border: none;
  font-size: 14px;
  color: var(--text-secondary);
  padding: 4px 0;
  transition: color var(--duration-fast) ease,
              transform 0.15s var(--ease-spring);
}

.action-btn:active { transform: scale(0.88); }

.action-btn.liked { color: #E53E3E; }

.heart-icon { transition: transform 0.25s var(--ease-spring); }
.action-btn.liked .heart-icon { transform: scale(1.2); }

/* ─── Responses ─── */
.responses-section { padding: 16px; }

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.count-chip {
  background-color: var(--primary-ghost);
  color: var(--primary-dark);
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: var(--radius-full);
}

.empty-responses {
  text-align: center;
  color: var(--text-secondary);
  font-size: 14px;
  padding: 24px 16px;
}

.responses-list { display: flex; flex-direction: column; gap: 14px; }

.response-card {
  display: flex;
  gap: 10px;
}

.res-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: var(--bg-subtle);
  object-fit: cover;
  flex-shrink: 0;
}

.res-body {
  flex: 1;
  background-color: var(--bg-subtle);
  padding: 10px 14px;
  border-radius: 4px var(--radius-md) var(--radius-md) var(--radius-md);
}

.res-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.res-user {
  font-weight: 600;
  font-size: 13px;
  color: var(--text-strong);
}

.res-time { font-size: 11px; color: var(--text-muted); }

.res-text {
  font-size: 14px;
  color: var(--text-body);
  line-height: 1.45;
  margin: 0;
}

/* ─── Reply Bar ─── */
.reply-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: var(--bg-card);
  border-top: 1px solid var(--border-light);
  padding: 10px 16px;
  padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 -2px 10px rgba(0,0,0,0.04);
}

.reply-input {
  flex: 1;
  border: 1.5px solid var(--border-light);
  border-radius: var(--radius-full);
  padding: 10px 16px;
  font-size: 14px;
  font-family: var(--font-family);
  outline: none;
  background-color: var(--bg-subtle);
  color: var(--text-body);
  transition: border-color var(--duration-fast) ease,
              background var(--duration-fast) ease;
}

.reply-input:focus {
  border-color: var(--primary);
  background-color: var(--bg-card);
}

.reply-input::placeholder { color: var(--text-muted); }

.send-btn {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity var(--duration-fast) ease,
              transform var(--duration-fast) ease;
}

.send-btn:active { transform: scale(0.9); }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
