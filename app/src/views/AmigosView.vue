<template>
  <div class="page-container amigos-page">
    <!-- Header -->
    <header class="amigos-header">
      <button class="icon-btn" @click="router.back()" aria-label="Volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      </button>
      <h1>Amigos</h1>
      <div style="width:38px"></div>
    </header>

    <!-- Tabs -->
    <div class="tabs-container">
      <button class="tab-btn" :class="{ active: activeTab === 'amigos' }" @click="switchTab('amigos')">
        Amigos
        <span v-if="amigos.length > 0" class="tab-count">{{ amigos.length }}</span>
      </button>
      <button class="tab-btn" :class="{ active: activeTab === 'solicitudes' }" @click="switchTab('solicitudes')">
        Solicitudes
        <span v-if="pendientes.length > 0" class="tab-count tab-count-alert">{{ pendientes.length }}</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="state-container">
      <div class="spinner"></div>
    </div>

    <!-- Amigos Tab -->
    <div v-else-if="activeTab === 'amigos'">
      <div v-if="amigos.length === 0" class="state-container">
        <div class="empty-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <p class="empty-title">Todavia no tenes amigos</p>
        <p class="empty-sub">Conectate con otros estudiantes universitarios</p>
      </div>

      <div v-else class="list-container">
        <div
          v-for="amigo in amigos"
          :key="amigo.idAmistad"
          class="friend-card animate-fadein-up"
        >
          <div class="friend-avatar">
            <img v-if="amigo.fotoPerfil" :src="getProfilePic(amigo.fotoPerfil)" alt="Avatar" />
            <span v-else class="avatar-initial">{{ amigo.nombre?.charAt(0) }}</span>
          </div>
          <div class="friend-info">
            <span class="friend-name">{{ amigo.nombre }} {{ amigo.apellido }}</span>
            <span class="friend-tipo">{{ amigo.tipoPerfil || 'Estudiante' }}</span>
          </div>
          <button class="action-chip chip-danger" @click="eliminarAmistad(amigo.idAmistad)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Solicitudes Tab -->
    <div v-else>
      <div v-if="pendientes.length === 0" class="state-container">
        <div class="empty-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.77a16 16 0 0 0 6.29 6.29l1.61-1.61a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <p class="empty-title">Sin solicitudes pendientes</p>
        <p class="empty-sub">Las solicitudes de amistad apareceran aqui</p>
      </div>

      <div v-else class="list-container">
        <!-- Recibidas -->
        <p v-if="recibidas.length > 0" class="list-section-title">Recibidas</p>
        <div
          v-for="sol in recibidas"
          :key="sol.idAmistad"
          class="friend-card animate-fadein-up"
        >
          <div class="friend-avatar">
            <img v-if="sol.fotoPerfil" :src="getProfilePic(sol.fotoPerfil)" alt="Avatar" />
            <span v-else class="avatar-initial">{{ sol.nombre?.charAt(0) }}</span>
          </div>
          <div class="friend-info">
            <span class="friend-name">{{ sol.nombre }} {{ sol.apellido }}</span>
            <span class="friend-tipo">Quiere conectarse</span>
          </div>
          <div class="action-row">
            <button class="action-chip chip-primary" @click="responder(sol.idAmistad, 'aceptar')">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
            <button class="action-chip chip-danger" @click="responder(sol.idAmistad, 'rechazar')">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>

        <!-- Enviadas -->
        <p v-if="enviadas.length > 0" class="list-section-title">Enviadas</p>
        <div
          v-for="sol in enviadas"
          :key="sol.idAmistad"
          class="friend-card animate-fadein-up"
        >
          <div class="friend-avatar">
            <img v-if="sol.fotoPerfil" :src="getProfilePic(sol.fotoPerfil)" alt="Avatar" />
            <span v-else class="avatar-initial">{{ sol.nombre?.charAt(0) }}</span>
          </div>
          <div class="friend-info">
            <span class="friend-name">{{ sol.nombre }} {{ sol.apellido }}</span>
            <span class="friend-tipo friend-pending">Pendiente</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import axios from 'axios'

const router    = useRouter()
const authStore = useAuthStore()

const activeTab  = ref('amigos')
const isLoading  = ref(true)
const amigos     = ref([])
const pendientes = ref([])

const recibidas = computed(() => pendientes.value.filter(s => s.tipo === 'recibida'))
const enviadas  = computed(() => pendientes.value.filter(s => s.tipo === 'enviada'))

const switchTab = (tab) => {
  if (activeTab.value !== tab) {
    activeTab.value = tab
    tab === 'amigos' ? loadAmigos() : loadSolicitudes()
  }
}

const loadAmigos = async () => {
  isLoading.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/usuarios/${authStore.user.idUsuario}/amigos`)
    amigos.value = Array.isArray(res.data) ? res.data : []
  } catch (_) {
    amigos.value = []
  } finally {
    isLoading.value = false
  }
}

const loadSolicitudes = async () => {
  isLoading.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/usuarios/${authStore.user.idUsuario}/solicitudes`)
    pendientes.value = Array.isArray(res.data) ? res.data : []
  } catch (_) {
    pendientes.value = []
  } finally {
    isLoading.value = false
  }
}

const responder = async (idAmistad, accion) => {
  try {
    const fd = new FormData()
    fd.append('accion', accion)
    await axios.post(`${API_BASE_URL}/amistad/${idAmistad}/responder`, fd)
    await loadSolicitudes()
    if (accion === 'aceptar') await loadAmigos()
  } catch (e) {
    console.error('Error respondiendo solicitud:', e)
  }
}

const eliminarAmistad = async (idAmistad) => {
  try {
    await axios.delete(`${API_BASE_URL}/amistad/${idAmistad}`)
    amigos.value = amigos.value.filter(a => a.idAmistad !== idAmistad)
  } catch (e) {
    console.error('Error eliminando amistad:', e)
  }
}

onMounted(loadAmigos)
</script>

<style scoped>
.amigos-page {
  background: var(--bg-main);
  padding-bottom: 90px;
  padding-top: 0;
}

/* ─── Header ─── */
.amigos-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px 10px;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-light);
  position: sticky;
  top: 0;
  z-index: 10;
}

.amigos-header h1 {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

/* ─── Tabs ─── */
.tabs-container {
  display: flex;
  margin: 12px 16px;
  background: var(--bg-subtle);
  border-radius: var(--radius-full);
  padding: 3px;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
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
  background: var(--bg-card);
  color: var(--text-strong);
  font-weight: 600;
  box-shadow: var(--shadow-xs);
}

.tab-count {
  background: var(--bg-subtle);
  color: var(--text-secondary);
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: var(--radius-full);
  line-height: 1.6;
}

.tab-count-alert {
  background: var(--primary);
  color: white;
}

/* ─── States ─── */
.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 40vh;
  gap: 12px;
  padding: 24px;
}

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--primary-ghost);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
}

.empty-title { font-size: 16px; font-weight: 600; color: var(--text-strong); margin: 0; }
.empty-sub   { font-size: 13px; color: var(--text-secondary); text-align: center; margin: 0; }

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── List ─── */
.list-container { padding: 0 16px; }

.list-section-title {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin: 14px 0 8px;
}

.friend-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  margin-bottom: 10px;
  box-shadow: var(--shadow-xs);
}

.friend-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}

.friend-avatar img { width: 100%; height: 100%; object-fit: cover; }

.avatar-initial { color: white; font-size: 18px; font-weight: 700; }

.friend-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.friend-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-strong);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.friend-tipo { font-size: 12px; color: var(--text-secondary); }
.friend-pending { color: var(--primary); font-weight: 500; }

.action-row { display: flex; gap: 6px; }

.action-chip {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: transform var(--duration-fast) ease;
}

.action-chip:active { transform: scale(0.88); }

.chip-primary { background: var(--primary-ghost); color: var(--primary); }
.chip-danger  { background: rgba(220,38,38,0.08); color: #DC2626; }
</style>
