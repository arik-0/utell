<template>
  <div class="post-detail-page">
    <!-- Sticky Glass Header -->
    <header class="detail-header">
      <button class="back-btn" @click="router.back()" aria-label="Volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      </button>
      <h2 class="header-title">Publicación</h2>
      <div class="header-right">
        <button class="icon-btn" @click="shareCurrentPost" aria-label="Compartir">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </button>
      </div>
    </header>

    <!-- Loading state -->
    <div v-if="isLoadingPost" class="loading-state">
      <div class="spinner"></div>
      <p class="text-secondary text-sm mt-2">Cargando publicación...</p>
    </div>

    <!-- Content -->
    <div v-else-if="post" class="detail-content">
      <!-- Original Post Card -->
      <article class="original-post animate-fadein-up">
        <div class="post-header">
          <div class="avatar-wrap">
            <img :src="getProfilePic(post.fotoPerfil)" alt="Avatar" class="avatar" />
          </div>
          <div class="post-meta">
            <div class="meta-primary">
              <span class="user-name">{{ post.nombre }} {{ post.apellido }}</span>
              <span v-if="post.tipoPublicacion" class="type-badge" :class="post.tipoPublicacion === 'Consulta' ? 'badge-consulta' : 'badge-xp'">
                <span class="type-dot" :class="post.tipoPublicacion === 'Consulta' ? 'type-dot-consulta' : 'type-dot-experiencia'"></span>
                {{ post.tipoPublicacion }}
              </span>
            </div>
            <div class="meta-secondary">
              <span class="post-time">{{ timeAgo(post.fecha, post.hora) }}</span>
              <router-link 
                v-if="post.nombreUniversidad && post.idUniversidad" 
                :to="`/universidad/${post.idUniversidad}`" 
                class="uni-tag"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                <span>{{ post.nombreUniversidad }}</span>
              </router-link>
              <span v-else-if="post.nombreUniversidad" class="uni-tag">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                <span>{{ post.nombreUniversidad }}</span>
              </span>
            </div>
          </div>
        </div>

        <div class="post-body">
          <h2 v-if="post.titulo" class="post-title">{{ post.titulo }}</h2>
          <p class="post-text">{{ post.texto }}</p>
        </div>

        <div class="post-actions">
          <button class="action-btn like-btn" @click="toggleLike(post)" :class="{ liked: post.hasLiked > 0 }">
            <div class="icon-wrap">
              <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" :fill="post.hasLiked > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <span>{{ post.likes || 0 }} Me gusta</span>
          </button>
          <div class="action-btn comment-count">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>{{ respuestas.length }} respuestas</span>
          </div>
        </div>
      </article>

      <!-- Responses Section -->
      <section class="responses-section">
        <div class="section-header">
          <h3 class="section-title">
            <span>Comentarios</span>
            <span class="count-badge">{{ respuestas.length }}</span>
          </h3>
        </div>

        <div v-if="isLoadingRespuestas" class="loading-state compact">
          <div class="spinner"></div>
        </div>

        <div v-else-if="respuestas.length === 0" class="empty-responses-card">
          <div class="empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <p class="empty-title">Aún no hay respuestas</p>
          <p class="empty-sub">Sé el primero en compartir tu punto de vista con la comunidad.</p>
        </div>

        <div v-else class="responses-list">
          <div
            v-for="(res, index) in respuestas"
            :key="res.idRespuesta"
            class="response-card animate-fadein-up"
            :style="{ animationDelay: `${index * 0.04}s` }"
          >
            <div class="res-avatar-wrap">
              <img :src="getProfilePic(res.fotoPerfil)" alt="Avatar" class="res-avatar" />
            </div>
            <div class="res-bubble">
              <div class="res-header">
                <span class="res-user">{{ res.nombre }} {{ res.apellido }}</span>
                <span class="res-time">{{ timeAgo(res.fecha, res.hora) }}</span>
              </div>
              <p class="res-text">{{ res.texto }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Error state -->
    <div v-else class="loading-state">
      <p class="text-secondary">No se pudo cargar la publicación.</p>
      <button class="btn btn-secondary mt-3" @click="fetchPost" style="width: auto;">Reintentar</button>
    </div>

    <!-- Modern Fixed Reply Bar -->
    <div class="reply-bar" v-if="post">
      <div class="reply-input-wrap">
        <input
          type="text"
          v-model="newReply"
          class="reply-input"
          placeholder="Escribe una respuesta..."
          @keyup.enter="submitReply"
          :disabled="isSubmitting"
        />
        <button
          class="send-btn"
          @click="submitReply"
          :disabled="!newReply.trim() || isSubmitting"
          aria-label="Enviar respuesta"
        >
          <svg v-if="!isSubmitting" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          <div v-else class="btn-spinner"></div>
        </button>
      </div>
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

const post                = ref(null)
const respuestas          = ref([])
const newReply            = ref('')
const isLoadingPost       = ref(true)
const isLoadingRespuestas = ref(true)
const isSubmitting        = ref(false)

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

const shareCurrentPost = () => {
  if (navigator.share && post.value) {
    navigator.share({
      title: post.value.titulo || 'Publicación en U-Tell',
      text: post.value.texto,
      url: window.location.href
    }).catch(() => {})
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
  padding-bottom: 74px;
}

/* ─── Header ─── */
.detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  padding-top: max(10px, env(safe-area-inset-top));
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  position: sticky;
  top: 0;
  z-index: 20;
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
  transition: background var(--duration-fast);
}

.back-btn:active { background: var(--bg-subtle); }

.header-title {
  font-family: var(--font-family);
  font-size: 16.5px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
}

/* ─── Loading / Error ─── */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 40px 0;
}

.loading-state.compact { padding: 20px 0; }

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.btn-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Content ─── */
.detail-content {
  flex: 1;
  overflow-y: auto;
}

.original-post {
  padding: 20px 16px;
  background-color: var(--bg-card);
  border-bottom: 1px solid var(--border-light);
}

.post-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 14px;
}

.avatar-wrap {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  padding: 1.5px;
  background: linear-gradient(135deg, var(--primary-light), var(--primary-ghost));
  flex-shrink: 0;
}

.avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.post-meta { flex: 1; min-width: 0; }

.meta-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-name {
  font-family: var(--font-family);
  font-weight: 700;
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
  gap: 4px;
}

.badge-consulta {
  background-color: var(--accent-ghost);
  color: #B45309;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.badge-xp {
  background-color: var(--primary-ghost);
  color: var(--primary);
  border: 1px solid rgba(255, 107, 53, 0.2);
}

.meta-secondary {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 2px;
}

.post-time { font-size: 12px; color: var(--text-muted); }

.uni-tag {
  font-size: 11px;
  font-weight: 500;
  padding: 1px 7px;
  border-radius: var(--radius-full);
  background-color: var(--bg-subtle);
  color: var(--text-secondary);
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.post-title {
  font-size: 18px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 10px;
  line-height: 1.35;
  letter-spacing: -0.015em;
}

.post-text {
  font-size: 15px;
  line-height: 1.6;
  color: var(--text-body);
  margin-bottom: 18px;
}

.post-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 14px;
  border-top: 1px solid var(--border-subtle);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  background: var(--bg-subtle);
  border: none;
  border-radius: var(--radius-full);
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
  transition: all var(--duration-fast) ease;
}

.action-btn:hover { background: var(--bg-muted); color: var(--text-strong); }
.action-btn:active { transform: scale(0.92); }

.action-btn.liked {
  background-color: #FEF2F2;
  color: #EF4444;
}

.heart-icon { transition: transform 0.22s var(--ease-spring); }
.action-btn.liked .heart-icon { transform: scale(1.2); }

.comment-count {
  background: transparent;
  cursor: default;
}

/* ─── Responses Section ─── */
.responses-section {
  padding: 18px 16px;
}

.section-header {
  margin-bottom: 14px;
}

.section-title {
  font-family: var(--font-family);
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  display: flex;
  align-items: center;
  gap: 8px;
}

.count-badge {
  background-color: var(--primary-ghost);
  color: var(--primary);
  font-size: 11px;
  font-weight: 700;
  padding: 1px 8px;
  border-radius: var(--radius-full);
}

.empty-responses-card {
  text-align: center;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px dashed var(--border-light);
  padding: 28px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.empty-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: var(--bg-subtle);
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}

.empty-title {
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.empty-sub {
  font-size: 13px;
  color: var(--text-secondary);
  max-width: 240px;
  margin: 0;
  line-height: 1.4;
}

.responses-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.response-card {
  display: flex;
  gap: 10px;
}

.res-avatar-wrap {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  flex-shrink: 0;
}

.res-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--border-light);
}

.res-bubble {
  flex: 1;
  background-color: var(--bg-card);
  border: 1px solid var(--border-light);
  padding: 10px 14px;
  border-radius: 4px var(--radius-md) var(--radius-md) var(--radius-md);
  box-shadow: var(--shadow-xs);
}

.res-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.res-user {
  font-family: var(--font-family);
  font-weight: 700;
  font-size: 13px;
  color: var(--text-strong);
}

.res-time { font-size: 11px; color: var(--text-muted); }

.res-text {
  font-size: 13.5px;
  color: var(--text-body);
  line-height: 1.5;
  margin: 0;
}

/* ─── Reply Bar ─── */
.reply-bar {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 680px;
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-top: 1px solid rgba(226, 232, 240, 0.8);
  padding: 10px 16px;
  padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
  z-index: 30;
  box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.04);
}

@media (min-width: 1200px) {
  .reply-bar {
    max-width: 720px;
  }
}

.reply-input-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.reply-input {
  flex: 1;
  border: 1.5px solid var(--border-light);
  border-radius: var(--radius-full);
  padding: 10px 16px;
  font-size: 14px;
  font-family: var(--font-body);
  outline: none;
  background-color: var(--bg-subtle);
  color: var(--text-strong);
  transition: all var(--duration-fast) ease;
}

.reply-input:focus {
  border-color: var(--primary);
  background-color: #FFFFFF;
  box-shadow: 0 0 0 3px var(--primary-ghost);
}

.reply-input::placeholder { color: var(--text-muted); }

.send-btn {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  border: none;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  box-shadow: var(--shadow-primary);
  transition: all var(--duration-fast) ease;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
}

.send-btn:active:not(:disabled) { transform: scale(0.92); }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; box-shadow: none; }
</style>
