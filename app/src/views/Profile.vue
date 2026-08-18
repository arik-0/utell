<template>
  <div class="page-container profile-page">
    <!-- Header -->
    <header class="profile-header-bar">
      <div class="header-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <h1>Perfil</h1>
      </div>
      <div class="header-right">
        <button class="icon-btn" aria-label="Notificaciones">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </button>
        <button class="icon-btn" aria-label="Ajustes">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </button>
      </div>
    </header>

    <!-- User Info -->
    <div class="user-info-section">
      <div class="user-avatar">
        <img v-if="authStore.user?.fotoPerfil" :src="getProfilePic(authStore.user.fotoPerfil)" alt="Avatar" class="avatar-img" />
        <span v-else class="avatar-initial">{{ userInitial }}</span>
      </div>
      <div class="user-details">
        <h2 class="user-name">{{ userName }}</h2>
        <p class="user-email">{{ userEmail }}</p>
        <p v-if="authStore.user?.tipoPerfil" class="user-tipo">{{ authStore.user.tipoPerfil }}</p>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
      <router-link to="/amigos" class="stat-item">
        <span class="stat-num">{{ amigoCount }}</span>
        <span class="stat-label">Amigos</span>
      </router-link>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-num">–</span>
        <span class="stat-label">Publicaciones</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-num">–</span>
        <span class="stat-label">Respuestas</span>
      </div>
    </div>

    <!-- Section Cards -->
    <div class="sections-list">
      <SectionCard
        title="Informacion Personal"
        subtitle="Datos personales y de contacto"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </template>
      </SectionCard>

      <SectionCard
        title="Validacion Academica"
        subtitle="Verifica tu identidad como estudiante o docente"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </template>
      </SectionCard>

      <SectionCard
        title="Mis Amigos"
        subtitle="Gestioná tus conexiones universitarias"
        to="/amigos"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </template>
      </SectionCard>

      <SectionCard
        title="Mis Experiencias"
        subtitle="Publicaciones que compartiste con la comunidad"
        to="/publicaciones"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </template>
      </SectionCard>

      <SectionCard
        title="Mis Consultas"
        subtitle="Preguntas que realizaste a la comunidad"
        to="/publicaciones"
      >
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </template>
      </SectionCard>

      <button class="logout-btn" @click="handleLogout">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Cerrar sesion
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

const userName    = computed(() => authStore.user ? `${authStore.user.nombre} ${authStore.user.apellido}` : 'Usuario')
const userEmail   = computed(() => authStore.user?.email || '')
const userInitial = computed(() => authStore.user?.nombre?.charAt(0)?.toUpperCase() || 'U')

const handleLogout = () => {
  authStore.logout()
  router.push('/')
}

const loadFriendCount = async () => {
  if (!authStore.user?.idUsuario) return
  try {
    const res = await axios.get(`${API_BASE_URL}/usuarios/${authStore.user.idUsuario}/amigos`)
    if (Array.isArray(res.data)) amigoCount.value = res.data.length
  } catch (_) {}
}

onMounted(loadFriendCount)
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
  padding-bottom: var(--space-4);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-strong);
}

.header-left h1 { font-size: 22px; font-weight: 700; margin: 0; }

.header-right { display: flex; gap: 4px; }

/* ─── User Info ─── */
.user-info-section {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: var(--space-5) 0 var(--space-4);
}

.user-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.avatar-img { width: 100%; height: 100%; object-fit: cover; }

.avatar-initial { color: white; font-size: 24px; font-weight: 700; }

.user-details { flex: 1; min-width: 0; }

.user-name {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.user-email {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 2px 0 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-tipo {
  font-size: 12px;
  color: var(--primary);
  font-weight: 600;
  margin: 4px 0 0;
  background: var(--primary-ghost);
  display: inline-block;
  padding: 2px 10px;
  border-radius: var(--radius-full);
}

/* ─── Stats ─── */
.stats-row {
  display: flex;
  align-items: center;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 14px 0;
  margin-bottom: var(--space-5);
  box-shadow: var(--shadow-xs);
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  text-decoration: none;
  cursor: default;
}

a.stat-item { cursor: pointer; }
a.stat-item:active { opacity: 0.7; }

.stat-num {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-strong);
}

.stat-label {
  font-size: 11px;
  color: var(--text-secondary);
  font-weight: 500;
}

.stat-divider {
  width: 1px;
  height: 32px;
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
  gap: 10px;
  background: transparent;
  color: #DC2626;
  border: 1.5px solid #DC2626;
  border-radius: var(--radius-full);
  padding: 13px 20px;
  margin-top: 24px;
  font-family: var(--font-family);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background var(--duration-fast) ease;
  width: 100%;
}

.logout-btn:active { background: rgba(220, 38, 38, 0.08); }
</style>
