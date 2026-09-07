<template>
  <div class="page-container feed-page">
    <!-- Sticky Glass Header -->
    <header class="feed-header" :class="{ 'is-scrolled': isScrolled }">
      <div class="header-top">
        <div class="logo-container">
          <img src="/img/logo.png" alt="U-Tell" class="header-logo" />
          <span class="logo-text">U-Tell</span>
        </div>
        <div class="header-actions">
          <button class="icon-btn" @click="showSearch = !showSearch" aria-label="Buscar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </button>
          <router-link to="/mensajes" class="icon-btn" aria-label="Mensajes">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          </router-link>
        </div>
      </div>

      <!-- Expandable Search Bar -->
      <transition name="slide-fade">
        <div v-if="showSearch" class="search-wrap">
          <div class="search-input-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" v-model="searchQuery" placeholder="Buscar por tema, carrera o universidad..." autofocus />
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-search">✕</button>
          </div>
        </div>
      </transition>

      <!-- Segmented Control Tabs -->
      <div class="tabs-container">
        <button class="tab-btn" :class="{ active: activeTab === 'parati' }" @click="switchTab('parati')">
          <span>Para ti</span>
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'tured' }" @click="switchTab('tured')">
          <span>Tu red</span>
        </button>
      </div>

      <!-- Quick Type Filter Chips -->
      <div class="filter-chips">
        <button 
          class="chip-btn" 
          :class="{ active: filterType === 'todos' }" 
          @click="filterType = 'todos'"
        >
          <span>Todos</span>
        </button>
        <button 
          class="chip-btn" 
          :class="{ active: filterType === 'Consulta' }" 
          @click="filterType = 'Consulta'"
        >
          <span class="type-dot type-dot-consulta"></span>
          <span>Consultas</span>
        </button>
        <button 
          class="chip-btn" 
          :class="{ active: filterType === 'Experiencia' }" 
          @click="filterType = 'Experiencia'"
        >
          <span class="type-dot type-dot-experiencia"></span>
          <span>Experiencias</span>
        </button>
      </div>
    </header>

    <!-- Feed Content -->
    <div class="feed-content">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="skeleton-container">
        <div v-for="n in 3" :key="n" class="skeleton-card">
          <div class="skeleton-header">
            <div class="skeleton-avatar"></div>
            <div class="skeleton-meta">
              <div class="skeleton-line short"></div>
              <div class="skeleton-line tiny"></div>
            </div>
          </div>
          <div class="skeleton-body">
            <div class="skeleton-line medium"></div>
            <div class="skeleton-line full"></div>
            <div class="skeleton-line three-quarters"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredPosts.length === 0" class="empty-state-wrap">
        <EmptyState
          :icon="activeTab === 'tured' ? 'users' : 'post'"
          :title="activeTab === 'tured' ? 'Tu red está vacía' : 'Sin publicaciones'"
          :subtitle="activeTab === 'tured' ? 'Conecta con otros estudiantes para ver sus consultas y experiencias en este espacio.' : 'No encontramos publicaciones para mostrar en este momento.'"
        />
      </div>

      <!-- Posts Feed List -->
      <div v-else class="posts-list">
        <article
          v-for="(post, index) in filteredPosts"
          :key="post.idPublicacion"
          class="post-card animate-fadein-up"
          :style="{ animationDelay: `${index * 0.04}s` }"
          @click="goToPost(post.idPublicacion)"
        >
          <!-- Post Author Header -->
          <div class="post-header">
            <router-link :to="`/perfil/${post.idUsuario}`" class="avatar-wrap" @click.stop>
              <img :src="getProfilePic(post.fotoPerfil)" alt="Avatar" class="avatar" />
            </router-link>
            <div class="post-meta">
              <div class="meta-primary">
                <router-link :to="`/perfil/${post.idUsuario}`" class="user-name clickable-author" @click.stop>
                  {{ post.nombre }} {{ post.apellido }}
                </router-link>
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
                  @click.stop
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

          <!-- Post Main Text -->
          <div class="post-body">
            <h3 v-if="post.titulo" class="post-title">{{ post.titulo }}</h3>
            <p class="post-text">{{ post.texto }}</p>
          </div>

          <!-- Post Footer & Engagement -->
          <div class="post-footer">
            <button class="action-btn like-btn" @click.stop="toggleLike(post)" :class="{ liked: post.hasLiked > 0 }">
              <div class="icon-wrap">
                <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" :fill="post.hasLiked > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </div>
              <span class="count">{{ post.likes || 0 }}</span>
            </button>

            <button class="action-btn comment-btn" @click.stop="goToPost(post.idPublicacion)">
              <div class="icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
              <span class="count">{{ post.cantidadRespuestas || 0 }}</span>
            </button>

            <button class="action-btn share-btn" @click.stop="sharePost(post)" aria-label="Compartir">
              <div class="icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              </div>
            </button>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'
import { timeAgo, getProfilePic } from '../utils'
import api from '../api'
import EmptyState from '../components/EmptyState.vue'

const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()

const posts       = ref([])
const isLoading   = ref(true)
const activeTab   = ref('parati')
const filterType  = ref('todos')
const showSearch  = ref(false)
const searchQuery = ref('')
const isScrolled  = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 8
}

const fetchFeed = async () => {
  isLoading.value = true
  try {
    const userId   = authStore.user?.idUsuario || 0
    const endpoint = activeTab.value === 'parati' ? '/parati' : '/tured'
    const res      = await api.get(`${endpoint}?idUsuario=${userId}`)
    posts.value    = Array.isArray(res.data) ? res.data : []
  } catch (error) {
    console.error('Error loading feed:', error)
    posts.value = []
  } finally {
    isLoading.value = false
  }
}

watch(() => authStore.user, () => {
  fetchFeed()
})

const switchTab = (tab) => {
  if (activeTab.value === tab) return
  activeTab.value = tab
  fetchFeed()
}

const filteredPosts = computed(() => {
  let list = posts.value

  // Filter by Type
  if (filterType.value !== 'todos') {
    list = list.filter(p => p.tipoPublicacion === filterType.value)
  }

  // Filter by Search Query
  if (!searchQuery.value.trim()) return list
  const q = searchQuery.value.toLowerCase()
  return list.filter(p => 
    (p.titulo && p.titulo.toLowerCase().includes(q)) ||
    (p.texto && p.texto.toLowerCase().includes(q)) ||
    (p.nombre && p.nombre.toLowerCase().includes(q)) ||
    (p.apellido && p.apellido.toLowerCase().includes(q)) ||
    (p.nombreUniversidad && p.nombreUniversidad.toLowerCase().includes(q)) ||
    (p.nombreCarrera && p.nombreCarrera.toLowerCase().includes(q))
  )
})

const goToPost = (id) => {
  if (id) router.push(`/post/${id}`)
}

const toggleLike = async (post) => {
  if (!authStore.user) return

  const originalLiked = post.hasLiked
  const originalLikes = post.likes

  // Optimistic update
  post.hasLiked = originalLiked > 0 ? 0 : 1
  post.likes    = originalLiked > 0 ? Math.max(0, post.likes - 1) : (post.likes + 1)

  try {
    const fd = new FormData()
    fd.append('idUsuario', authStore.user.idUsuario)
    const res = await api.post(`/publicacion/${post.idPublicacion}/like`, fd)
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

const sharePost = async (post) => {
  const shareUrl = window.location.origin + `/post/${post.idPublicacion}`
  if (navigator.share) {
    try {
      await navigator.share({
        title: post.titulo || 'Publicación en U-Tell',
        text: post.texto,
        url: shareUrl
      })
      return
    } catch (_) {}
  }

  // Fallback: Copy to clipboard
  try {
    await navigator.clipboard.writeText(shareUrl)
    toastStore.success('Enlace de la publicación copiado al portapapeles.', 2500)
  } catch (_) {
    toastStore.info(`Enlace: ${shareUrl}`, 4000)
  }
}

onMounted(() => {
  fetchFeed()
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

</script>

<style scoped>
.feed-page {
  padding: 0;
  padding-bottom: 84px;
  background-color: var(--bg-main);
}

/* ─── Header ─── */
.feed-header {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  padding-top: env(safe-area-inset-top, 0px);
  transition: all var(--duration-fast) var(--ease-out);
}

.feed-header.is-scrolled {
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
  border-bottom-color: rgba(226, 232, 240, 1);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px 8px;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.header-logo {
  height: 26px;
  width: auto;
}

.logo-text {
  font-family: var(--font-family);
  font-size: 19px;
  font-weight: 800;
  color: var(--text-strong);
  letter-spacing: -0.03em;
}

.header-actions {
  display: flex;
  gap: 4px;
}

/* ─── Search Bar ─── */
.search-wrap {
  padding: 0 16px 10px;
}

.search-input-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-full);
  padding: 8px 14px;
  color: var(--text-secondary);
}

.search-input-box input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13.5px;
  color: var(--text-strong);
  font-family: var(--font-body);
}

.search-input-box input::placeholder {
  color: var(--text-muted);
}

.clear-search {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 12px;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s var(--ease-out);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ─── Tabs Segmented Control ─── */
.tabs-container {
  display: flex;
  margin: 0 16px 10px;
  background-color: var(--bg-subtle);
  border-radius: var(--radius-full);
  padding: 3px;
}

.tab-btn {
  flex: 1;
  padding: 7px 16px;
  background: none;
  border: none;
  border-radius: var(--radius-full);
  font-family: var(--font-family);
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-normal) var(--ease-out);
}

.tab-btn.active {
  background-color: var(--bg-card);
  color: var(--text-strong);
  font-weight: 700;
  box-shadow: var(--shadow-xs);
}

/* ─── Content ─── */
.feed-content {
  padding: 12px 16px;
}

/* ─── Skeleton Loading ─── */
.skeleton-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  padding: 16px;
  border: 1px solid var(--border-light);
}

.skeleton-header {
  display: flex;
  gap: 10px;
  margin-bottom: 14px;
}

.skeleton-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--bg-subtle);
  animation: pulse 1.5s infinite;
}

.skeleton-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
  justify-content: center;
}

.skeleton-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-line {
  height: 12px;
  background: var(--bg-subtle);
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}

.skeleton-line.tiny { width: 30%; }
.skeleton-line.short { width: 50%; }
.skeleton-line.medium { width: 70%; }
.skeleton-line.full { width: 100%; }
.skeleton-line.three-quarters { width: 85%; }

@keyframes pulse {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}

/* ─── Post Cards ─── */
.posts-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.post-card {
  background-color: var(--bg-card);
  border-radius: var(--radius-lg);
  padding: 16px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  cursor: pointer;
  transition: transform var(--duration-fast) ease,
              box-shadow var(--duration-fast) ease,
              border-color var(--duration-fast) ease;
}

.post-card:hover {
  border-color: rgba(255, 107, 53, 0.25);
  box-shadow: var(--shadow-md);
}

.post-card:active {
  transform: scale(0.988);
  box-shadow: var(--shadow-xs);
}

.post-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 12px;
}

.avatar-wrap {
  width: 40px;
  height: 40px;
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

.post-meta {
  flex: 1;
  min-width: 0;
}

.meta-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-name {
  font-family: var(--font-family);
  font-weight: 700;
  font-size: 14.5px;
  color: var(--text-strong);
  letter-spacing: -0.01em;
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

.post-time {
  font-size: 12px;
  color: var(--text-muted);
}

.uni-tag {
  font-size: 11px;
  font-weight: 500;
  padding: 1px 7px;
  border-radius: var(--radius-full);
  background-color: var(--bg-subtle);
  color: var(--text-secondary);
  max-width: 170px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

/* ─── Post Body ─── */
.post-title {
  font-size: 15.5px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 5px;
  line-height: 1.35;
  letter-spacing: -0.01em;
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

/* ─── Post Footer Actions ─── */
.post-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 10px;
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
  padding: 6px 12px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-secondary);
  transition: all var(--duration-fast) ease;
}

.action-btn:hover {
  background: var(--bg-muted);
  color: var(--text-strong);
}

.action-btn:active {
  transform: scale(0.92);
}

.action-btn.liked {
  background-color: #FEF2F2;
  color: #EF4444;
}

.heart-icon {
  transition: transform 0.22s var(--ease-spring);
}

.action-btn.liked .heart-icon {
  transform: scale(1.18);
}

.share-btn {
  margin-left: auto;
  padding: 6px 9px;
}
</style>
