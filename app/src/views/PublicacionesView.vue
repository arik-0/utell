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
        <h1>Descubrir</h1>
      </div>
    </header>

    <!-- Segmented Tabs -->
    <div class="tabs-container">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'muros' }" 
        @click="activeTab = 'muros'"
      >
        <span>Muros Universitarios</span>
        <span v-if="universidades.length > 0" class="tab-badge">{{ universidades.length }}</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'mis-posts' }" 
        @click="activeTab = 'mis-posts'"
      >
        <span>Mis Publicaciones</span>
        <span v-if="userPosts.length > 0" class="tab-badge">{{ userPosts.length }}</span>
      </button>
    </div>

    <!-- ═════════════════════════════════════════════════════════════
         TAB 1: MUROS UNIVERSITARIOS
         ═════════════════════════════════════════════════════════════ -->
    <div v-if="activeTab === 'muros'" class="content-area">
      <!-- Search Input -->
      <div class="search-box">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" v-model="searchUniQuery" placeholder="Buscar universidad por nombre..." />
        <button v-if="searchUniQuery" @click="searchUniQuery = ''" class="clear-btn">✕</button>
      </div>

      <div v-if="isLoadingUnis" class="loading-wrap">
        <div class="spinner"></div>
      </div>

      <div v-else-if="filteredUnis.length === 0" class="empty-wrap">
        <EmptyState
          icon="book"
          :title="searchUniQuery ? 'Sin coincidencias' : 'Aún no hay universidades registradas'"
          subtitle="No encontramos instituciones universitarias con ese nombre."
        />
      </div>

      <div v-else class="unis-grid">
        <div
          v-for="uni in filteredUnis"
          :key="uni.idUniversidad"
          class="uni-card animate-fadein-up"
          @click="router.push(`/universidad/${uni.idUniversidad}`)"
        >
          <div class="uni-card-header">
            <div class="uni-icon-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div class="uni-main-info">
              <h3 class="uni-title">{{ uni.nombre }}</h3>
              <span class="uni-subtitle">{{ uni.totalExperiencias }} experiencias · {{ uni.totalConsultas }} consultas</span>
            </div>
            <div class="uni-score-pill">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              <span>{{ uni.promedioGeneral || '5.0' }}</span>
            </div>
          </div>

          <!-- Triple Rating Indicators -->
          <div class="uni-ratings-strip">
            <div class="rating-strip-item">
              <span class="strip-label">Clima:</span>
              <strong>{{ uni.promedioClima || '5.0' }}</strong>
            </div>
            <div class="rating-strip-item">
              <span class="strip-label">Infraestructura:</span>
              <strong>{{ uni.promedioInfraestructura || '5.0' }}</strong>
            </div>
            <div class="rating-strip-item">
              <span class="strip-label">Académico:</span>
              <strong>{{ uni.promedioAcademico || '5.0' }}</strong>
            </div>
          </div>

          <div class="uni-card-footer">
            <span class="footer-cta">Explorar muro y experiencias</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════
         TAB 2: MIS PUBLICACIONES
         ═════════════════════════════════════════════════════════════ -->
    <div v-else class="content-area">
      <div v-if="isLoadingPosts" class="loading-wrap">
        <div class="spinner"></div>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { timeAgo } from '../utils'
import api from '../api'
import EmptyState from '../components/EmptyState.vue'

const router    = useRouter()
const authStore = useAuthStore()

const activeTab        = ref('muros')
const universidades    = ref([])
const userPosts        = ref([])
const isLoadingUnis    = ref(true)
const isLoadingPosts   = ref(true)
const searchUniQuery   = ref('')

const filteredUnis = computed(() => {
  if (!searchUniQuery.value.trim()) return universidades.value
  const q = searchUniQuery.value.toLowerCase()
  return universidades.value.filter(u => u.nombre.toLowerCase().includes(q))
})

const fetchUniversidades = async () => {
  isLoadingUnis.value = true
  try {
    const res = await api.get(`/universidades/con-experiencias`)
    if (Array.isArray(res.data)) {
      universidades.value = res.data
    }
  } catch (e) {
    console.error('Error fetching universities with experiences:', e)
  } finally {
    isLoadingUnis.value = false
  }
}

const fetchUserPosts = async () => {
  const userId = authStore.user?.idUsuario || 0
  isLoadingPosts.value = true
  try {
    const res = await api.get(`/parati?idUsuario=${userId}`)
    if (Array.isArray(res.data)) {
      userPosts.value = userId > 0
        ? res.data.filter(p => Number(p.idUsuario) === Number(userId))
        : res.data
    }
  } catch (e) {
    console.error('Error fetching user posts:', e)
  } finally {
    isLoadingPosts.value = false
  }
}

watch(() => authStore.user, () => {
  fetchUserPosts()
})

const handleCreate = () => {
  router.push('/create-consulta')
}

onMounted(() => {
  fetchUniversidades()
  fetchUserPosts()
})
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
  font-family: var(--font-family);
  font-size: 19px;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.02em;
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
  padding: 8px 14px;
  text-align: center;
  border-radius: var(--radius-full);
  font-family: var(--font-family);
  font-size: 13px;
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

.tab-badge {
  background: var(--bg-muted);
  color: var(--text-secondary);
  font-size: 10.5px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: var(--radius-full);
}

.tab-btn.active .tab-badge {
  background: var(--primary-ghost);
  color: var(--primary);
}

.content-area {
  padding: 0 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* ─── Search Box ─── */
.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-card);
  border-radius: var(--radius-full);
  padding: 9px 14px;
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-xs);
}

.search-box input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13.5px;
  color: var(--text-strong);
  font-family: var(--font-body);
}

.search-box input::placeholder { color: var(--text-muted); }

.clear-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 12px;
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

/* ─── University Cards Grid ─── */
.unis-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.uni-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 16px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: all var(--duration-fast);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.uni-card:hover {
  border-color: rgba(255, 107, 53, 0.3);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.uni-card:active { transform: scale(0.988); }

.uni-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.uni-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-md);
  background: var(--primary-ghost);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.uni-main-info {
  flex: 1;
  min-width: 0;
}

.uni-title {
  font-family: var(--font-family);
  font-size: 15.5px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 2px;
  line-height: 1.3;
}

.uni-subtitle {
  font-size: 12px;
  color: var(--text-secondary);
}

.uni-score-pill {
  background: var(--accent-light);
  color: #B45309;
  font-size: 13px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.uni-ratings-strip {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 6px;
  background: var(--bg-subtle);
  border-radius: var(--radius-sm);
  padding: 8px 10px;
  font-size: 11.5px;
}

.rating-strip-item {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.strip-label {
  color: var(--text-secondary);
  font-size: 10.5px;
}

.rating-strip-item strong {
  color: var(--text-strong);
  font-weight: 700;
}

.uni-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 8px;
  border-top: 1px solid var(--border-subtle);
  color: var(--primary);
  font-size: 13px;
  font-weight: 700;
}

/* ─── Posts List ─── */
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

.badge-consulta { background-color: var(--accent-ghost); color: #B45309; }
.badge-xp       { background-color: var(--primary-ghost); color: var(--primary); }

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
