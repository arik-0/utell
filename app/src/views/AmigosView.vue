<template>
  <div class="page-container amigos-page">
    <!-- Header -->
    <header class="amigos-header">
      <button class="icon-btn" @click="router.back()" aria-label="Volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      </button>
      <h1>Conexiones</h1>
      <div style="width: 36px"></div>
    </header>

    <!-- Segmented Tabs -->
    <div class="tabs-container">
      <button class="tab-btn" :class="{ active: activeTab === 'amigos' }" @click="switchTab('amigos')">
        <span>Mis Amigos</span>
        <span v-if="amigos.length > 0" class="tab-count">{{ amigos.length }}</span>
      </button>
      <button class="tab-btn" :class="{ active: activeTab === 'solicitudes' }" @click="switchTab('solicitudes')">
        <span>Solicitudes</span>
        <span v-if="pendientes.length > 0" class="tab-count tab-count-alert">{{ pendientes.length }}</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="state-container">
      <div class="spinner"></div>
    </div>

    <!-- Amigos Tab -->
    <div v-else-if="activeTab === 'amigos'">
      <div v-if="amigos.length === 0" class="empty-state-wrap">
        <EmptyState
          icon="users"
          title="Aún no tienes amigos agregados"
          subtitle="Conéctate con otros estudiantes universitarios para seguir sus consultas y experiencias."
        />
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
          <div class="action-row">
            <button class="action-chip chip-msg" @click="router.push(`/mensajes?contacto=${amigo.idUsuario}`)" aria-label="Enviar mensaje">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            </button>
            <button class="action-chip chip-danger" @click="eliminarAmistad(amigo.idAmistad)" aria-label="Eliminar amigo">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Solicitudes Tab -->
    <div v-else>
      <div v-if="pendientes.length === 0" class="empty-state-wrap">
        <EmptyState
          icon="mail"
          title="Sin solicitudes pendientes"
          subtitle="Las nuevas solicitudes de amistad recibidas o enviadas aparecerán en esta sección."
        />
      </div>

      <div v-else class="list-container">
        <!-- Recibidas -->
        <p v-if="recibidas.length > 0" class="list-section-title">Recibidas ({{ recibidas.length }})</p>
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
            <span class="friend-tipo">Solicitud de conexión</span>
          </div>
          <div class="action-row">
            <button class="action-chip chip-primary" @click="responder(sol.idAmistad, 'aceptar')" aria-label="Aceptar">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
            <button class="action-chip chip-danger" @click="responder(sol.idAmistad, 'rechazar')" aria-label="Rechazar">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>

        <!-- Enviadas -->
        <p v-if="enviadas.length > 0" class="list-section-title">Enviadas ({{ enviadas.length }})</p>
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
            <span class="friend-tipo friend-pending">Pendiente de confirmación</span>
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
import EmptyState from '../components/EmptyState.vue'
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
  font-family: var(--font-family);
  font-size: 18px;
  font-weight: 800;
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
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-normal) var(--ease-out);
}

.tab-btn.active {
  background: var(--bg-card);
  color: var(--text-strong);
  font-weight: 700;
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
  font-family: var(--font-family);
  font-size: 11.5px;
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
  transition: all var(--duration-fast);
}

.friend-card:hover {
  border-color: rgba(255, 107, 53, 0.2);
  box-shadow: var(--shadow-sm);
}

.friend-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  padding: 1.5px;
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}

.friend-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

.avatar-initial { color: white; font-family: var(--font-family); font-size: 18px; font-weight: 700; }

.friend-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.friend-name {
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.friend-tipo { font-size: 12px; color: var(--text-secondary); }
.friend-pending { color: var(--primary); font-weight: 600; }

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
.chip-msg     { background: var(--bg-subtle); color: var(--text-strong); }
.chip-msg:hover { background: var(--primary-ghost); color: var(--primary); }
.chip-danger  { background: rgba(220,38,38,0.08); color: #DC2626; }
</style>
