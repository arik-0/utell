<template>
  <div class="page-container publicaciones-page">
    <!-- Header -->
    <header class="publicaciones-header">
      <div class="header-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <h1>Mis Publicaciones</h1>
      </div>
    </header>

    <!-- Segmented Tabs -->
    <div class="tabs-container">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'publicaciones' }" 
        @click="activeTab = 'publicaciones'"
      >
        Publicadas
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'revision' }" 
        @click="activeTab = 'revision'"
      >
        En Revisión <span class="badge-mini">WIP</span>
      </button>
    </div>

    <!-- Content -->
    <div class="content-area">
      <div v-if="isLoading" class="loading-wrap">
        <div class="spinner"></div>
      </div>

      <div v-else-if="activeTab === 'revision'" class="wip-card">
        <div class="wip-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3>Módulo en desarrollo</h3>
        <p>Las publicaciones pendientes de moderación o revisión por pares aparecerán en esta sección.</p>
      </div>

      <div v-else-if="userPosts.length === 0" class="empty-wrap">
        <EmptyState
          icon="clipboard"
          title="Aún no tienes publicaciones"
          subtitle="Comparte tus consultas académicas o experiencias de cursada con la comunidad."
          buttonText="Crear publicación"
          @action="handleCreate"
        />
      </div>

      <div v-else class="posts-list">
        <article
          v-for="post in userPosts"
          :key="post.idPublicacion"
          class="post-card animate-fadein-up"
          @click="router.push(`/post/${post.idPublicacion}`)"
        >
          <div class="post-header">
            <span class="type-badge" :class="post.tipoPublicacion === 'Consulta' ? 'badge-consulta' : 'badge-xp'">
              <span class="type-dot" :class="post.tipoPublicacion === 'Consulta' ? 'type-dot-consulta' : 'type-dot-experiencia'"></span>
              {{ post.tipoPublicacion }}
            </span>
            <span class="post-time">{{ timeAgo(post.fecha, post.hora) }}</span>
          </div>

          <h3 v-if="post.titulo" class="post-title">{{ post.titulo }}</h3>
          <p class="post-text">{{ post.texto }}</p>

          <div class="post-meta-row">
            <div class="meta-item">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span>{{ post.likes || 0 }}</span>
            </div>
            <div class="meta-item">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <span>{{ post.cantidadRespuestas || 0 }}</span>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { timeAgo } from '../utils'
import { API_BASE_URL } from '../api'
import EmptyState from '../components/EmptyState.vue'
import axios from 'axios'

const router    = useRouter()
const authStore = useAuthStore()

const activeTab = ref('publicaciones')
const isLoading = ref(true)
const userPosts = ref([])

const fetchUserPosts = async () => {
  if (!authStore.user) return
  isLoading.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/parati?idUsuario=${authStore.user.idUsuario}`)
    if (Array.isArray(res.data)) {
      userPosts.value = res.data.filter(p => Number(p.idUsuario) === Number(authStore.user.idUsuario))
    }
  } catch (e) {
    console.error('Error fetching user posts:', e)
  } finally {
    isLoading.value = false
  }
}

const handleCreate = () => {
  router.push('/create-consulta')
}

onMounted(fetchUserPosts)
</script>

<style scoped>
.publicaciones-page {
  background-color: var(--bg-main);
  padding-bottom: 90px;
  min-height: 100vh;
}

.publicaciones-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px 10px;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-light);
  position: sticky;
  top: 0;
  z-index: 10;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-strong);
}

.header-left h1 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.tabs-container {
  display: flex;
  background-color: var(--bg-subtle);
  border-radius: var(--radius-full);
  padding: 3px;
  margin: 12px 16px;
}

.tab-btn {
  flex: 1;
  padding: 8px 16px;
  text-align: center;
  border-radius: var(--radius-full);
  font-family: var(--font-family);
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-secondary);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all var(--duration-fast);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.tab-btn.active {
  background-color: var(--bg-card);
  box-shadow: var(--shadow-xs);
  color: var(--text-strong);
  font-weight: 700;
}

.badge-mini {
  background-color: var(--accent-ghost);
  color: #B45309;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 5px;
  border-radius: var(--radius-full);
}

.content-area {
  padding: 0 16px;
}

.loading-wrap {
  display: flex;
  justify-content: center;
  padding: 40px 0;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.wip-card {
  text-align: center;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px dashed var(--border-light);
  padding: 36px 20px;
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.wip-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: var(--accent-ghost);
  color: #B45309;
  display: flex;
  align-items: center;
  justify-content: center;
}

.wip-card h3 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.wip-card p {
  font-size: 13px;
  color: var(--text-secondary);
  max-width: 280px;
  margin: 0;
  line-height: 1.45;
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.post-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 14px 16px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.post-card:active { transform: scale(0.988); }

.post-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
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
}

.badge-xp {
  background-color: var(--primary-ghost);
  color: var(--primary);
}

.post-time { font-size: 12px; color: var(--text-muted); }

.post-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 4px;
}

.post-text {
  font-size: 13.5px;
  color: var(--text-body);
  margin-bottom: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.45;
}

.post-meta-row {
  display: flex;
  gap: 14px;
  font-size: 12px;
  color: var(--text-secondary);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
