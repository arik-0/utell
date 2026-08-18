<template>
  <div class="page-container feed-page">
    <!-- Header -->
    <header class="feed-header">
      <div class="header-top">
        <img src="/img/logo.png" alt="U-Tell" class="header-logo" />
        <div class="header-actions">
          <button class="icon-btn" aria-label="Buscar">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </button>
          <button class="icon-btn" aria-label="Filtros">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs-container">
        <button class="tab-btn" :class="{ active: activeTab === 'parati' }" @click="switchTab('parati')">
          Para ti
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'tured' }" @click="switchTab('tured')">
          Tu red
        </button>
      </div>
    </header>

    <!-- Feed Content -->
    <div class="feed-content">
      <!-- Loading -->
      <div v-if="isLoading" class="state-container">
        <div class="spinner"></div>
        <p class="text-secondary text-sm mt-1">Cargando publicaciones...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="posts.length === 0" class="state-container">
        <div class="empty-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <p class="empty-title">
          {{ activeTab === 'tured' ? 'Tu red esta vacia' : 'Sin publicaciones' }}
        </p>
        <p class="empty-sub">
          {{ activeTab === 'tured' ? 'Seguí a otros estudiantes o completa tu perfil universitario' : 'Volvé más tarde o creá una nueva publicación' }}
        </p>
      </div>

      <!-- Posts List -->
      <div v-else class="posts-list">
        <div
          v-for="(post, index) in posts"
          :key="post.idPublicacion"
          class="post-card animate-fadein-up"
          :style="{ animationDelay: `${index * 0.04}s` }"
          @click="goToPost(post.idPublicacion)"
        >
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
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                  {{ post.nombreUniversidad }}
                </span>
              </div>
            </div>
            <button class="more-btn" @click.stop aria-label="Opciones">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
            </button>
          </div>

          <div class="post-body">
            <h3 v-if="post.titulo" class="post-title">{{ post.titulo }}</h3>
            <p class="post-text">{{ post.texto }}</p>
          </div>

          <div class="post-footer">
            <button class="action-btn" @click.stop="toggleLike(post)" :class="{ liked: post.hasLiked > 0 }">
              <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" :fill="post.hasLiked > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span>{{ post.likes || 0 }}</span>
            </button>
            <button class="action-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <span>{{ post.cantidadRespuestas || 0 }}</span>
            </button>
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
import { timeAgo, getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import axios from 'axios'

const router    = useRouter()
const authStore = useAuthStore()

const posts     = ref([])
const isLoading = ref(true)
const activeTab = ref('parati')

const goToPost = (id) => {
  if (id) router.push(`/post/${id}`)
}

const switchTab = (tab) => {
  if (activeTab.value === tab) return
  activeTab.value = tab
  fetchFeed()
}

const fetchFeed = async () => {
  try {
    isLoading.value = true
    posts.value = []
    const userId   = authStore.user?.idUsuario || 0
    const endpoint = activeTab.value === 'tured' ? '/tured' : '/parati'
    const res      = await axios.get(`${API_BASE_URL}${endpoint}?idUsuario=${userId}`)
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

  const originalLiked = post.hasLiked
  const originalLikes = post.likes

  // Optimistic update
  post.hasLiked = originalLiked > 0 ? 0 : 1
  post.likes    = originalLiked > 0 ? (post.likes - 1) : (post.likes + 1)

  try {
    const fd = new FormData()
    fd.append('idUsuario', authStore.user.idUsuario)
    const res = await axios.post(`${API_BASE_URL}/publicacion/${post.idPublicacion}/like`, fd)
    if (res.data && res.data.totalLikes !== undefined) {
      post.likes    = res.data.totalLikes
      post.hasLiked = res.data.likedByUser ? 1 : 0
    }
  } catch (error) {
    console.error('Error toggling like:', error)
    post.hasLiked = originalLiked
    post.likes    = originalLikes
  }
}

onMounted(fetchFeed)
</script>

<style scoped>
.feed-page {
  padding: 0;
  padding-bottom: 80px;
  background-color: var(--bg-main);
}

/* ─── Header ─── */
.feed-header {
  background-color: var(--bg-card);
  position: sticky;
  top: 0;
  z-index: 10;
  border-bottom: 1px solid var(--border-light);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px 8px;
}

.header-logo { height: 28px; width: auto; }

.header-actions { display: flex; gap: 4px; }

/* ─── Tabs ─── */
.tabs-container {
  display: flex;
  margin: 0 16px 10px;
  background-color: var(--bg-subtle);
  border-radius: var(--radius-full);
  padding: 3px;
}

.tab-btn {
  flex: 1;
  padding: 8px 16px;
  background: none;
  border: none;
  border-radius: var(--radius-full);
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-normal) var(--ease-out);
}

.tab-btn.active {
  background-color: var(--bg-card);
  color: var(--text-strong);
  font-weight: 600;
  box-shadow: var(--shadow-xs);
}

/* ─── Content ─── */
.feed-content { padding: 12px 16px; }

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 50vh;
  gap: 12px;
}

.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid var(--border-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background-color: var(--primary-ghost);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
}

.empty-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-strong);
}

.empty-sub {
  font-size: 13px;
  color: var(--text-secondary);
  text-align: center;
  max-width: 260px;
  line-height: 1.5;
}

/* ─── Post Cards ─── */
.posts-list { display: flex; flex-direction: column; gap: 12px; }

.post-card {
  background-color: var(--bg-card);
  border-radius: var(--radius-lg);
  padding: 16px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  cursor: pointer;
  transition: transform var(--duration-fast) ease,
              box-shadow var(--duration-fast) ease;
}

.post-card:active {
  transform: scale(0.985);
  box-shadow: var(--shadow-xs);
}

.post-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 12px;
}

.avatar {
  width: 40px;
  height: 40px;
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
  font-size: 14px;
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
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
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
  flex-shrink: 0;
}

.more-btn:active { background: var(--bg-subtle); }

/* ─── Post Body ─── */
.post-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 5px;
  line-height: 1.3;
}

.post-text {
  font-size: 14px;
  line-height: 1.55;
  color: var(--text-body);
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ─── Post Footer ─── */
.post-footer {
  display: flex;
  gap: 16px;
  padding-top: 10px;
  border-top: 1px solid var(--border-light);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  background: none;
  border: none;
  font-size: 13px;
  color: var(--text-secondary);
  padding: 4px 0;
  transition: color var(--duration-fast) ease,
              transform 0.15s var(--ease-spring);
}

.action-btn:active { transform: scale(0.88); }

.action-btn.liked { color: #E53E3E; }

.heart-icon {
  transition: transform 0.25s var(--ease-spring);
}

.action-btn.liked .heart-icon { transform: scale(1.2); }
</style>
