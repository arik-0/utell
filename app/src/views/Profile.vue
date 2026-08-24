<template>
  <div class="page-container profile-page">
    <!-- Header -->
    <header class="profile-header-bar">
      <div class="header-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <h1>Mi Perfil</h1>
      </div>
    </header>

    <!-- User Info Card -->
    <div class="user-hero-card animate-fadein-up">
      <div class="user-avatar-wrap">
        <img v-if="authStore.user?.fotoPerfil" :src="getProfilePic(authStore.user.fotoPerfil)" alt="Avatar" class="avatar-img" />
        <span v-else class="avatar-initial">{{ userInitial }}</span>
      </div>
      <div class="user-details">
        <h2 class="user-name">{{ userName }}</h2>
        <p class="user-email">{{ userEmail }}</p>
        <div class="user-badges">
          <span class="badge badge-primary">{{ authStore.user?.tipoPerfil || 'Estudiante' }}</span>
        </div>
      </div>
    </div>

    <!-- Live Stats Metrics Row -->
    <div class="stats-row animate-fadein-up" style="animation-delay: 0.05s;">
      <router-link to="/amigos" class="stat-item">
        <span class="stat-num">{{ amigoCount }}</span>
        <span class="stat-label">Amigos</span>
      </router-link>
      <div class="stat-divider"></div>
      <router-link to="/publicaciones" class="stat-item">
        <span class="stat-num">{{ postCount }}</span>
        <span class="stat-label">Publicaciones</span>
      </router-link>
    </div>

    <!-- Section Navigation List -->
    <div class="sections-list animate-fadein-up" style="animation-delay: 0.1s;">
      <SectionCard
        title="Mis Amigos y Solicitudes"
        subtitle="Gestiona tus conexiones universitarias"
        to="/amigos"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </template>
      </SectionCard>

      <SectionCard
        title="Mis Publicaciones"
        subtitle="Consultas y relatos que compartiste"
        to="/publicaciones"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </template>
      </SectionCard>

      <SectionCard
        title="Validación Académica"
        subtitle="Certifica tu condición de alumno o docente"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </template>
      </SectionCard>

      <button class="logout-btn" @click="handleLogout">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Cerrar sesión</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import SectionCard from '../components/SectionCard.vue'
import axios from 'axios'

const router    = useRouter()
const authStore = useAuthStore()

const amigoCount = ref(0)
const postCount  = ref(0)

const userName    = computed(() => authStore.user ? `${authStore.user.nombre} ${authStore.user.apellido}` : 'Usuario')
const userEmail   = computed(() => authStore.user?.email || '')
const userInitial = computed(() => authStore.user?.nombre?.charAt(0)?.toUpperCase() || 'U')

const handleLogout = () => {
  authStore.logout()
  router.push('/')
}

const loadProfileStats = async () => {
  if (!authStore.user?.idUsuario) return
  try {
    const resAmigos = await axios.get(`${API_BASE_URL}/usuarios/${authStore.user.idUsuario}/amigos`)
    if (Array.isArray(resAmigos.data)) amigoCount.value = resAmigos.data.length
    
    const resFeed = await axios.get(`${API_BASE_URL}/parati?idUsuario=${authStore.user.idUsuario}`)
    if (Array.isArray(resFeed.data)) {
      postCount.value = resFeed.data.filter(p => Number(p.idUsuario) === Number(authStore.user.idUsuario)).length
    }
  } catch (_) {}
}

onMounted(loadProfileStats)
</script>

<style scoped>
.profile-page {
  background-color: var(--bg-main);
  padding-bottom: 90px;
}

.profile-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0 16px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-strong);
}

.header-left h1 { font-size: 20px; font-weight: 800; margin: 0; }

/* ─── Hero Card ─── */
.user-hero-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm);
  margin-bottom: 12px;
}

.user-avatar-wrap {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  padding: 2px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  background: var(--bg-subtle);
}

.avatar-initial {
  color: white;
  font-family: var(--font-family);
  font-size: 22px;
  font-weight: 800;
}

.user-details { flex: 1; min-width: 0; }

.user-name {
  font-family: var(--font-family);
  font-size: 17.5px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0;
  letter-spacing: -0.01em;
}

.user-email {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 2px 0 6px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-badges {
  display: flex;
  gap: 6px;
}

/* ─── Stats ─── */
.stats-row {
  display: flex;
  align-items: center;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 14px 0;
  margin-bottom: 14px;
  box-shadow: var(--shadow-xs);
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  text-decoration: none;
  cursor: pointer;
  transition: opacity var(--duration-fast);
}

.stat-item:active { opacity: 0.7; }

.stat-num {
  font-family: var(--font-family);
  font-size: 20px;
  font-weight: 800;
  color: var(--text-strong);
  letter-spacing: -0.02em;
}

.stat-label {
  font-size: 11.5px;
  color: var(--text-secondary);
  font-weight: 600;
}

.stat-divider {
  width: 1px;
  height: 28px;
  background: var(--border-light);
}

/* ─── Sections ─── */
.sections-list {
  display: flex;
  flex-direction: column;
}

/* ─── Logout ─── */
.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: transparent;
  color: #DC2626;
  border: 1.5px solid rgba(220, 38, 38, 0.3);
  border-radius: var(--radius-full);
  padding: 12px 20px;
  margin-top: 16px;
  font-family: var(--font-family);
  font-size: 14.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all var(--duration-fast) ease;
  width: 100%;
}

.logout-btn:hover {
  background: rgba(220, 38, 38, 0.06);
  border-color: #DC2626;
}

.logout-btn:active {
  transform: scale(0.98);
}
</style>
