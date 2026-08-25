<template>
  <div class="page-container uni-wall-page">
    <!-- Sticky Glass Header -->
    <header class="wall-header">
      <button class="back-btn" @click="router.back()" aria-label="Volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      </button>
      <h1 class="header-title truncate">{{ wallData?.universidad?.nombre || 'Muro Universitario' }}</h1>
      <button class="share-btn" @click="shareUniversity" aria-label="Compartir muro">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      </button>
    </header>

    <!-- Loading State -->
    <div v-if="isLoading" class="state-container">
      <div class="spinner"></div>
      <p class="text-secondary text-sm mt-2">Cargando muro institucional...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="!wallData" class="state-container">
      <p class="text-secondary">No se encontró la universidad solicitada.</p>
      <button class="btn btn-secondary mt-3" @click="fetchWall" style="width: auto;">Reintentar</button>
    </div>

    <!-- Wall Content -->
    <div v-else class="wall-content">
      <!-- Institutional Hero Card -->
      <div class="hero-card animate-fadein-up">
        <div class="hero-top">
          <div class="uni-avatar">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          </div>
          <div class="hero-info">
            <h2 class="uni-name">{{ wallData.universidad.nombre }}</h2>
            <div class="hero-tags">
              <span class="badge badge-accent">Institución Universitaria</span>
              <span v-if="wallData.universidad.email" class="uni-domain">{{ wallData.universidad.email }}</span>
            </div>
          </div>
        </div>

        <!-- Metrics Consolidated Section -->
        <div class="metrics-grid">
          <div class="metric-score-card">
            <div class="score-number">{{ metrics.promedioGeneral || '5.0' }}</div>
            <div class="score-stars">
              <span class="star-active">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </span>
              <span class="score-label">Promedio General</span>
            </div>
            <span class="reviews-count">{{ metrics.totalExperiencias || experiencias.length }} evaluaciones</span>
          </div>

          <!-- Progress Bars Breakdown -->
          <div class="metric-breakdown">
            <div class="breakdown-item">
              <div class="breakdown-header">
                <span>Clima Estudiantil</span>
                <strong>{{ metrics.promedioClima || '5.0' }}</strong>
              </div>
              <div class="meter-bar">
                <div class="meter-fill" :style="{ width: `${(Number(metrics.promedioClima || 5) / 5) * 100}%` }"></div>
              </div>
            </div>

            <div class="breakdown-item">
              <div class="breakdown-header">
                <span>Infraestructura</span>
                <strong>{{ metrics.promedioInfraestructura || '5.0' }}</strong>
              </div>
              <div class="meter-bar">
                <div class="meter-fill" :style="{ width: `${(Number(metrics.promedioInfraestructura || 5) / 5) * 100}%` }"></div>
              </div>
            </div>

            <div class="breakdown-item">
              <div class="breakdown-header">
                <span>Nivel Académico</span>
                <strong>{{ metrics.promedioAcademico || '5.0' }}</strong>
              </div>
              <div class="meter-bar">
                <div class="meter-fill" :style="{ width: `${(Number(metrics.promedioAcademico || 5) / 5) * 100}%` }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick CTAs -->
        <div class="hero-actions">
          <router-link to="/create-experiencia" class="btn btn-primary btn-cta">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span>Dejar mi Experiencia</span>
          </router-link>
          <router-link to="/create-consulta" class="btn btn-secondary btn-cta">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Hacer Consulta</span>
          </router-link>
        </div>
      </div>

      <!-- Segmented Navigation Tabs -->
      <div class="tabs-container">
        <button class="tab-btn" :class="{ active: activeTab === 'experiencias' }" @click="activeTab = 'experiencias'">
          <span>Experiencias</span>
          <span class="tab-badge">{{ experiencias.length }}</span>
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'consultas' }" @click="activeTab = 'consultas'">
          <span>Consultas</span>
          <span class="tab-badge">{{ consultas.length }}</span>
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'sedes' }" @click="activeTab = 'sedes'">
          <span>Sedes</span>
          <span class="tab-badge">{{ wallData.sedes.length }}</span>
        </button>
      </div>

      <!-- TAB 1: EXPERIENCIAS -->
      <div v-if="activeTab === 'experiencias'" class="feed-section">
        <div v-if="experiencias.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="clipboard"
            title="Aún no hay experiencias publicadas"
            subtitle="Sé el primero en compartir tu vivencia de cursada en esta institución."
            buttonText="Compartir experiencia"
            @action="router.push('/create-experiencia')"
          />
        </div>

        <div v-else class="posts-stream">
          <article
            v-for="(post, index) in experiencias"
            :key="post.idPublicacion"
            class="post-card animate-fadein-up"
            :style="{ animationDelay: `${index * 0.04}s` }"
            @click="goToPost(post.idPublicacion)"
          >
            <div class="post-header">
              <router-link :to="`/perfil/${post.idUsuario}`" class="avatar-wrap" @click.stop>
                <img :src="getProfilePic(post.fotoPerfil)" alt="Avatar" class="avatar" />
              </router-link>
              <div class="post-meta">
                <div class="meta-primary">
                  <router-link :to="`/perfil/${post.idUsuario}`" class="user-name clickable-author" @click.stop>
                    {{ post.nombre }} {{ post.apellido }}
                  </router-link>
                  <span class="badge badge-primary">Experiencia</span>
                </div>
                <div class="meta-secondary">
                  <span class="post-time">{{ timeAgo(post.fecha, post.hora) }}</span>
                  <span v-if="post.nombreCarrera" class="carrera-chip">{{ post.nombreCarrera }}</span>
                </div>
              </div>
            </div>

            <!-- Experience Score Pills -->
            <div class="xp-ratings-pills">
              <div class="xp-pill">
                <span class="pill-label">Clima:</span>
                <strong>{{ post.puntuacionClima }}/5</strong>
              </div>
              <div class="xp-pill">
                <span class="pill-label">Infraestructura:</span>
                <strong>{{ post.puntuacionInfraestructura }}/5</strong>
              </div>
              <div class="xp-pill">
                <span class="pill-label">Académico:</span>
                <strong>{{ post.puntuacionAcademica }}/5</strong>
              </div>
            </div>

            <div class="post-body">
              <h3 v-if="post.titulo" class="post-title">{{ post.titulo }}</h3>
              <p class="post-text">{{ post.texto }}</p>
            </div>

            <div class="post-footer">
              <button class="action-btn" @click.stop="toggleLike(post)" :class="{ liked: post.hasLiked > 0 }">
                <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" :fill="post.hasLiked > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span>{{ post.likes || 0 }}</span>
              </button>
              <div class="action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>{{ post.cantidadRespuestas || 0 }}</span>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- TAB 2: CONSULTAS -->
      <div v-else-if="activeTab === 'consultas'" class="feed-section">
        <div v-if="consultas.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="book"
            title="Sin consultas registradas"
            subtitle="¿Tienes dudas sobre los planes de estudio, inscripciones o cursada? Pregúntale a la comunidad."
            buttonText="Hacer consulta"
            @action="router.push('/create-consulta')"
          />
        </div>

        <div v-else class="posts-stream">
          <article
            v-for="(post, index) in consultas"
            :key="post.idPublicacion"
            class="post-card animate-fadein-up"
            :style="{ animationDelay: `${index * 0.04}s` }"
            @click="goToPost(post.idPublicacion)"
          >
            <div class="post-header">
              <router-link :to="`/perfil/${post.idUsuario}`" class="avatar-wrap" @click.stop>
                <img :src="getProfilePic(post.fotoPerfil)" alt="Avatar" class="avatar" />
              </router-link>
              <div class="post-meta">
                <div class="meta-primary">
                  <router-link :to="`/perfil/${post.idUsuario}`" class="user-name clickable-author" @click.stop>
                    {{ post.nombre }} {{ post.apellido }}
                  </router-link>
                  <span class="badge badge-accent">Consulta</span>
                </div>
                <div class="meta-secondary">
                  <span class="post-time">{{ timeAgo(post.fecha, post.hora) }}</span>
                  <span v-if="post.nombreCarrera" class="carrera-chip">{{ post.nombreCarrera }}</span>
                </div>
              </div>
            </div>

            <div class="post-body">
              <h3 v-if="post.titulo" class="post-title">{{ post.titulo }}</h3>
              <p class="post-text">{{ post.texto }}</p>
            </div>

            <div class="post-footer">
              <button class="action-btn" @click.stop="toggleLike(post)" :class="{ liked: post.hasLiked > 0 }">
                <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" :fill="post.hasLiked > 0 ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span>{{ post.likes || 0 }}</span>
              </button>
              <div class="action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>{{ post.cantidadRespuestas || 0 }}</span>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- TAB 3: SEDES -->
      <div v-else-if="activeTab === 'sedes'" class="feed-section">
        <div v-if="wallData.sedes.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="book"
            title="Sin sedes detalladas"
            subtitle="La información geográfica de sedes se actualizará próximamente."
          />
        </div>

        <div v-else class="sedes-list">
          <div
            v-for="sede in wallData.sedes"
            :key="sede.idSede"
            class="sede-card animate-fadein-up"
          >
            <div class="sede-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="sede-details">
              <h4 class="sede-name">{{ sede.nombre }}</h4>
              <p v-if="sede.direccion" class="sede-addr">{{ sede.direccion }}</p>
              <p v-if="sede.telefono" class="sede-phone">Tel: {{ sede.telefono }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { timeAgo, getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import EmptyState from '../components/EmptyState.vue'
import axios from 'axios'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()

const wallData  = ref(null)
const isLoading = ref(true)
const activeTab = ref('experiencias')

const metrics = computed(() => {
  return wallData.value?.metricas || {
    promedioClima: '5.0',
    promedioInfraestructura: '5.0',
    promedioAcademico: '5.0',
    promedioGeneral: '5.0',
    totalExperiencias: 0
  }
})

const publicaciones = computed(() => wallData.value?.publicaciones || [])

const experiencias = computed(() => {
  return publicaciones.value.filter(p => p.tipoPublicacion === 'Experiencia')
})

const consultas = computed(() => {
  return publicaciones.value.filter(p => p.tipoPublicacion === 'Consulta')
})

const fetchWall = async () => {
  isLoading.value = true
  try {
    const uniId  = route.params.id
    const userId = authStore.user?.idUsuario || 0
    const res    = await axios.get(`${API_BASE_URL}/universidades/${uniId}/muro?idUsuario=${userId}`)
    if (res.data && res.data.universidad) {
      wallData.value = res.data
    }
  } catch (e) {
    console.error('Error fetching university wall:', e)
  } finally {
    isLoading.value = false
  }
}

const goToPost = (id) => {
  if (id) router.push(`/post/${id}`)
}

const toggleLike = async (post) => {
  if (!authStore.user) return

  const originalLiked = post.hasLiked
  const originalLikes = post.likes
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
    post.hasLiked = originalLiked
    post.likes    = originalLikes
  }
}

const shareUniversity = () => {
  if (navigator.share && wallData.value) {
    navigator.share({
      title: `Muro Institucional - ${wallData.value.universidad.nombre}`,
      text: `Conoce las opiniones y calificaciones de estudiantes sobre ${wallData.value.universidad.nombre} en U-Tell.`,
      url: window.location.href
    }).catch(() => {})
  }
}

onMounted(fetchWall)
</script>

<style scoped>
.uni-wall-page {
  padding: 0;
  padding-bottom: 90px;
  background-color: var(--bg-main);
}

/* ─── Header ─── */
.wall-header {
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

.back-btn, .share-btn {
  background: none;
  border: none;
  padding: 6px;
  color: var(--text-strong);
  cursor: pointer;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
}

.back-btn:active, .share-btn:active { background: var(--bg-subtle); }

.header-title {
  font-family: var(--font-family);
  font-size: 16px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0;
  max-width: 65%;
  text-align: center;
}

/* ─── Content ─── */
.wall-content {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* ─── Hero Card ─── */
.hero-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-light);
  padding: 20px 18px;
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.hero-top {
  display: flex;
  align-items: center;
  gap: 14px;
}

.uni-avatar {
  width: 54px;
  height: 54px;
  border-radius: var(--radius-lg);
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: var(--shadow-primary);
}

.hero-info {
  flex: 1;
  min-width: 0;
}

.uni-name {
  font-family: var(--font-family);
  font-size: 18px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 4px;
  line-height: 1.3;
}

.hero-tags {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.uni-domain {
  font-size: 12px;
  color: var(--text-secondary);
}

/* ─── Metrics Grid ─── */
.metrics-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 14px;
  background: var(--bg-subtle);
  border-radius: var(--radius-lg);
  padding: 16px;
  border: 1px solid var(--border-light);
}

@media (min-width: 480px) {
  .metrics-grid {
    grid-template-columns: 120px 1fr;
    align-items: center;
  }
}

.metric-score-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  gap: 2px;
}

.score-number {
  font-family: var(--font-family);
  font-size: 36px;
  font-weight: 800;
  color: var(--text-strong);
  line-height: 1;
  letter-spacing: -0.03em;
}

.score-stars {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #F59E0B;
}

.score-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
}

.reviews-count {
  font-size: 11px;
  color: var(--text-muted);
}

.metric-breakdown {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.breakdown-item {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.breakdown-header {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: var(--text-secondary);
}

.breakdown-header strong {
  color: var(--text-strong);
  font-weight: 700;
}

.meter-bar {
  height: 6px;
  background: var(--bg-muted);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.meter-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent), var(--primary));
  border-radius: var(--radius-full);
  transition: width 0.6s var(--ease-out);
}

/* ─── Hero CTAs ─── */
.hero-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.btn-cta {
  font-size: 13px;
  padding: 10px 14px;
}

/* ─── Segmented Tabs ─── */
.tabs-container {
  display: flex;
  background-color: var(--bg-subtle);
  border-radius: var(--radius-full);
  padding: 3px;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  background: none;
  border: none;
  border-radius: var(--radius-full);
  font-family: var(--font-family);
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.tab-btn.active {
  background-color: var(--bg-card);
  color: var(--text-strong);
  font-weight: 700;
  box-shadow: var(--shadow-xs);
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

/* ─── Feed & Cards ─── */
.posts-stream {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.post-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 16px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.post-card:active { transform: scale(0.988); }

.post-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 10px;
}

.avatar-wrap {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  padding: 1.5px;
  background: linear-gradient(135deg, var(--primary-light), var(--primary-ghost));
  flex-shrink: 0;
}

.avatar { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

.post-meta { flex: 1; min-width: 0; }

.meta-primary {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-name {
  font-family: var(--font-family);
  font-weight: 700;
  font-size: 14px;
  color: var(--text-strong);
}

.meta-secondary {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 2px;
}

.post-time { font-size: 11.5px; color: var(--text-muted); }

.carrera-chip {
  font-size: 11px;
  font-weight: 500;
  padding: 1px 7px;
  border-radius: var(--radius-full);
  background: var(--bg-subtle);
  color: var(--text-secondary);
}

/* ─── Experience Specific Rating Pills ─── */
.xp-ratings-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.xp-pill {
  font-size: 11.5px;
  padding: 3px 8px;
  border-radius: var(--radius-sm);
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.xp-pill .pill-label { color: var(--text-secondary); }
.xp-pill strong { color: var(--primary); }

.post-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.post-text {
  font-size: 13.5px;
  line-height: 1.5;
  color: var(--text-body);
  margin-bottom: 12px;
}

.post-footer {
  display: flex;
  gap: 12px;
  padding-top: 10px;
  border-top: 1px solid var(--border-subtle);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  background: var(--bg-subtle);
  border: none;
  border-radius: var(--radius-full);
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
}

.action-btn.liked { background: #FEF2F2; color: #EF4444; }

/* ─── Sedes List ─── */
.sedes-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sede-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
}

.sede-icon {
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  background: var(--primary-ghost);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sede-details {
  display: flex;
  flex-direction: column;
}

.sede-name {
  font-family: var(--font-family);
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 2px;
}

.sede-addr, .sede-phone {
  font-size: 12.5px;
  color: var(--text-secondary);
  margin: 0;
}

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 50vh;
  padding: 24px;
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
</style>
