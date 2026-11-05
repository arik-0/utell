<template>
  <div class="page-container mod-page">
    <!-- Header -->
    <header class="mod-header animate-fadein-down">
      <div class="header-left">
        <button class="back-btn" @click="router.back()" aria-label="Volver">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </button>
        <div>
          <h1 class="mod-title">Panel de Moderación y Auditoría</h1>
          <p class="mod-subtitle">Administración de contenidos, roles, estados de cuenta y trazabilidad (CU5 / CU6 / CU8 / CU9)</p>
        </div>
      </div>

      <div class="header-badge-wrap">
        <span class="badge badge-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          {{ authStore.user?.tipoPerfil || 'Staff' }}
        </span>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <div class="mod-tabs-bar animate-fadein-up">
      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'publicaciones' }"
        @click="activeTab = 'publicaciones'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Publicaciones ({{ publicaciones.length }})</span>
      </button>

      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'usuarios' }"
        @click="activeTab = 'usuarios'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Usuarios y Roles ({{ usuarios.length }})</span>
      </button>

      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'auditoria' }"
        @click="activeTab = 'auditoria'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
        <span>Log de Auditoría ({{ logs.length }})</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="state-container">
      <div class="spinner"></div>
      <p class="text-secondary text-sm mt-2">Cargando registros del sistema...</p>
    </div>

    <!-- Main Content -->
    <div v-else class="mod-content">
      <!-- ═════════════════════════════════════════════════════════════
           TAB 1: PUBLICACIONES (CU6)
           ═════════════════════════════════════════════════════════════ -->
      <section v-if="activeTab === 'publicaciones'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="searchPubs"
              placeholder="Buscar por título, autor, universidad o texto..."
              class="search-input"
            />
          </div>
          <div class="filter-pills">
            <button
              class="pill-btn"
              :class="{ active: filterPubEstado === 'todos' }"
              @click="filterPubEstado = 'todos'"
            >
              Todos ({{ publicaciones.length }})
            </button>
            <button
              class="pill-btn"
              :class="{ active: filterPubEstado === 'activo' }"
              @click="filterPubEstado = 'activo'"
            >
              Activas
            </button>
            <button
              class="pill-btn"
              :class="{ active: filterPubEstado === 'eliminado' }"
              @click="filterPubEstado = 'eliminado'"
            >
              Ocultas/Eliminadas
            </button>
          </div>
        </div>

        <div v-if="filteredPublicaciones.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="clipboard"
            title="No se encontraron publicaciones"
            subtitle="Prueba con otros términos de búsqueda o filtros de estado."
          />
        </div>

        <div v-else class="items-grid">
          <div
            v-for="pub in filteredPublicaciones"
            :key="pub.idPublicacion"
            class="mod-card"
            :class="{ 'card-deleted': pub.estado === 'eliminado', 'card-resolved': Number(pub.resuelta) > 0 }"
          >
            <div class="card-head">
              <div class="author-meta">
                <img :src="getProfilePic(pub.fotoPerfil)" alt="Avatar" class="avatar-sm" />
                <div>
                  <span class="author-name">{{ pub.nombre }} {{ pub.apellido }}</span>
                  <span class="pub-date">{{ pub.fecha }} • {{ pub.hora }}</span>
                </div>
              </div>
              <div class="status-badges">
                <span class="badge" :class="pub.tipoPublicacion === 'Experiencia' ? 'badge-primary' : 'badge-accent'">
                  {{ pub.tipoPublicacion }}
                </span>
                <span v-if="pub.estado === 'eliminado'" class="badge badge-danger">Eliminado</span>
                <span v-else-if="Number(pub.resuelta) > 0" class="badge badge-teal">Resuelta</span>
                <span v-else class="badge badge-success">Activa</span>
              </div>
            </div>

            <div class="card-body">
              <h4 v-if="pub.titulo" class="card-title">{{ pub.titulo }}</h4>
              <p class="card-text">{{ pub.texto }}</p>
              <div class="academic-tags">
                <span v-if="pub.nombreUniversidad" class="tag-chip">🏛 {{ pub.nombreUniversidad }}</span>
                <span v-if="pub.nombreCarrera" class="tag-chip">🎓 {{ pub.nombreCarrera }}</span>
              </div>
            </div>

            <div class="card-actions">
              <button
                class="btn btn-sm btn-secondary"
                @click="router.push(`/post/${pub.idPublicacion}`)"
              >
                Ver Post
              </button>

              <button
                v-if="pub.estado !== 'eliminado'"
                class="btn btn-sm btn-danger-outline"
                @click="moderarPost(pub, 'eliminar')"
              >
                Ocultar / Eliminar
              </button>

              <button
                v-if="pub.estado === 'eliminado'"
                class="btn btn-sm btn-success"
                @click="moderarPost(pub, 'reactivar')"
              >
                Reactivar
              </button>

              <button
                v-if="pub.estado !== 'eliminado' && Number(pub.resuelta) === 0"
                class="btn btn-sm btn-secondary"
                @click="moderarPost(pub, 'cerrar')"
              >
                Marcar Resuelta
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           TAB 2: USUARIOS Y ROLES (CU5 / CU8)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="activeTab === 'usuarios'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="searchUsers"
              placeholder="Buscar usuario por nombre o email..."
              class="search-input"
            />
          </div>
        </div>

        <div v-if="filteredUsuarios.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="book"
            title="No se encontraron usuarios"
            subtitle="Verifica el nombre o correo ingresado."
          />
        </div>

        <div v-else class="table-responsive">
          <table class="mod-table">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Rol de Sistema</th>
                <th>Estado de Cuenta</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in filteredUsuarios" :key="u.idUsuario">
                <td>
                  <div class="user-row-cell">
                    <img :src="getProfilePic(u.fotoPerfil)" alt="Avatar" class="avatar-xs" />
                    <div>
                      <strong>{{ u.nombre }} {{ u.apellido }}</strong>
                      <span class="text-xs text-muted d-block">ID: #{{ u.idUsuario }}</span>
                    </div>
                  </div>
                </td>
                <td class="text-sm">{{ u.email }}</td>
                <td>
                  <span class="badge badge-subtle">{{ u.tipoPerfil || 'Estudiante' }}</span>
                </td>
                <td>
                  <select
                    class="role-select"
                    :value="u.idRol"
                    @change="cambiarRol(u, $event.target.value)"
                  >
                    <option v-for="rol in roles" :key="rol.idRol" :value="rol.idRol">
                      {{ rol.nombreRol }}
                    </option>
                  </select>
                </td>
                <td>
                  <span
                    class="badge"
                    :class="u.estadoCuenta === 'activo' ? 'badge-success' : 'badge-danger'"
                  >
                    {{ u.estadoCuenta === 'activo' ? 'Activa' : 'Bloqueada' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group-sm">
                    <button
                      v-if="u.estadoCuenta === 'activo'"
                      class="btn btn-xs btn-danger-outline"
                      @click="cambiarEstadoUsuario(u, 'bloquear')"
                    >
                      Bloquear
                    </button>
                    <button
                      v-else
                      class="btn btn-xs btn-success"
                      @click="cambiarEstadoUsuario(u, 'activar')"
                    >
                      Activar
                    </button>
                    <button
                      class="btn btn-xs btn-secondary"
                      @click="router.push(`/perfil/${u.idUsuario}`)"
                    >
                      Ver
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           TAB 3: LOG DE AUDITORÍA (CU9 / RNF07)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="activeTab === 'auditoria'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="searchLogs"
              placeholder="Buscar por acción, tabla o detalle..."
              class="search-input"
            />
          </div>
          <button class="btn btn-secondary btn-sm" @click="fetchLogs">
            Refrescar Log
          </button>
        </div>

        <div v-if="filteredLogs.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="clipboard"
            title="Sin registros de auditoría"
            subtitle="Las acciones críticas del sistema se registrarán automáticamente."
          />
        </div>

        <div v-else class="table-responsive">
          <table class="mod-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Acción</th>
                <th>Usuario Actor</th>
                <th>Tabla Afectada</th>
                <th>Detalles</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in filteredLogs" :key="log.idLog">
                <td class="text-xs text-muted font-mono">#{{ log.idLog }}</td>
                <td class="text-xs whitespace-nowrap">{{ log.fechaHora }}</td>
                <td>
                  <span class="badge badge-accent font-mono text-xs">{{ log.accion }}</span>
                </td>
                <td class="text-sm">
                  <span v-if="log.nombre">{{ log.nombre }} {{ log.apellido }} (#{{ log.idUsuario }})</span>
                  <span v-else class="text-muted">Sistema (#{{ log.idUsuario || 0 }})</span>
                </td>
                <td>
                  <span class="tag-chip font-mono text-xs">{{ log.tablaAfectada || '—' }}</span>
                </td>
                <td class="text-sm log-detail-cell">{{ log.detalles || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'
import { getProfilePic } from '../utils'
import api from '../api'
import EmptyState from '../components/EmptyState.vue'

const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()

const activeTab        = ref('publicaciones')
const isLoading        = ref(true)
const publicaciones    = ref([])
const usuarios         = ref([])
const roles            = ref([])
const logs             = ref([])

const searchPubs       = ref('')
const filterPubEstado  = ref('todos')
const searchUsers      = ref('')
const searchLogs       = ref('')

const fetchPublicaciones = async () => {
  try {
    const res = await api.get('/moderacion/publicaciones')
    publicaciones.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Error fetching publicaciones para moderación:', e)
  }
}

const fetchUsuarios = async () => {
  try {
    const [resUsers, resRoles] = await Promise.all([
      api.get('/moderacion/usuarios'),
      api.get('/roles')
    ])
    usuarios.value = Array.isArray(resUsers.data) ? resUsers.data : []
    roles.value    = Array.isArray(resRoles.data) ? resRoles.data : []
  } catch (e) {
    console.error('Error fetching usuarios y roles:', e)
  }
}

const fetchLogs = async () => {
  try {
    const res = await api.get('/moderacion/auditoria')
    logs.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Error fetching logs de auditoría:', e)
  }
}

const initData = async () => {
  isLoading.value = true
  try {
    await Promise.all([
      fetchPublicaciones(),
      fetchUsuarios(),
      fetchLogs()
    ])
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  initData()
})

// ─── Filtered Computeds ───────────────────────────────────────────────────────
const filteredPublicaciones = computed(() => {
  let list = publicaciones.value
  if (filterPubEstado.value === 'activo') {
    list = list.filter(p => p.estado === 'activo')
  } else if (filterPubEstado.value === 'eliminado') {
    list = list.filter(p => p.estado === 'eliminado')
  }

  if (searchPubs.value.trim()) {
    const q = searchPubs.value.toLowerCase()
    list = list.filter(p =>
      (p.titulo && p.titulo.toLowerCase().includes(q)) ||
      (p.texto && p.texto.toLowerCase().includes(q)) ||
      (p.nombre && p.nombre.toLowerCase().includes(q)) ||
      (p.apellido && p.apellido.toLowerCase().includes(q)) ||
      (p.nombreUniversidad && p.nombreUniversidad.toLowerCase().includes(q))
    )
  }
  return list
})

const filteredUsuarios = computed(() => {
  if (!searchUsers.value.trim()) return usuarios.value
  const q = searchUsers.value.toLowerCase()
  return usuarios.value.filter(u =>
    (u.nombre && u.nombre.toLowerCase().includes(q)) ||
    (u.apellido && u.apellido.toLowerCase().includes(q)) ||
    (u.email && u.email.toLowerCase().includes(q)) ||
    (u.nombreRol && u.nombreRol.toLowerCase().includes(q))
  )
})

const filteredLogs = computed(() => {
  if (!searchLogs.value.trim()) return logs.value
  const q = searchLogs.value.toLowerCase()
  return logs.value.filter(l =>
    (l.accion && l.accion.toLowerCase().includes(q)) ||
    (l.detalles && l.detalles.toLowerCase().includes(q)) ||
    (l.tablaAfectada && l.tablaAfectada.toLowerCase().includes(q)) ||
    (l.nombre && l.nombre.toLowerCase().includes(q))
  )
})

// ─── Moderation Actions ───────────────────────────────────────────────────────
const moderarPost = async (pub, accion) => {
  let motivo = null
  if (accion === 'eliminar') {
    motivo = prompt('Motivo de moderación (opcional):', 'Infracción a las normas comunitarias')
    if (motivo === null) return // usuario canceló
  }

  try {
    await api.post('/moderacion/publicacion', {
      idPublicacion: pub.idPublicacion,
      accion,
      motivo
    })

    toastStore.success(`Publicación #${pub.idPublicacion} moderada correctamente (${accion}).`)
    await fetchPublicaciones()
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al moderar publicación.')
  }
}

const cambiarRol = async (user, nuevoIdRol) => {
  const rolId = Number(nuevoIdRol)
  if (rolId === Number(user.idRol)) return

  try {
    await api.put(`/usuarios/${user.idUsuario}/rol`, { idRol: rolId })
    user.idRol = rolId
    toastStore.success(`Rol de ${user.nombre} actualizado.`)
    await fetchLogs()
  } catch (e) {
    toastStore.error('No se pudo actualizar el rol.')
  }
}

const cambiarEstadoUsuario = async (user, accion) => {
  const confirmMsg = accion === 'bloquear'
    ? `¿Estás seguro de bloquear la cuenta de ${user.nombre} ${user.apellido}?`
    : `¿Reactivar la cuenta de ${user.nombre} ${user.apellido}?`

  if (!confirm(confirmMsg)) return

  try {
    await api.put(`/usuarios/${user.idUsuario}/estado`, { accion })
    user.estadoCuenta = accion === 'bloquear' ? 'inactivo' : 'activo'
    toastStore.success(`Cuenta de ${user.nombre} actualizada a ${user.estadoCuenta}.`)
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al cambiar estado de cuenta.')
  }
}
</script>

<style scoped>
.mod-page {
  padding-bottom: 60px;
}

.mod-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--border-light);
  margin-bottom: 20px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.back-btn {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-sm);
  width: 38px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--text-body);
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
}

.back-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.mod-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.mod-subtitle {
  font-size: 0.82rem;
  color: var(--text-secondary);
  margin: 2px 0 0;
}

.mod-tabs-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border-light);
  padding-bottom: 8px;
  overflow-x: auto;
}

.mod-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: var(--radius-sm);
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
  white-space: nowrap;
}

.mod-tab-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.mod-tab-btn.active {
  background: var(--primary-ghost);
  color: var(--primary);
}

.pane-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 240px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-input {
  width: 100%;
  padding: 10px 14px 10px 38px;
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  color: var(--text-strong);
  font-size: 0.88rem;
  outline: none;
  transition: border-color var(--duration-fast);
}

.search-input:focus {
  border-color: var(--primary);
}

.filter-pills {
  display: flex;
  gap: 6px;
}

.pill-btn {
  padding: 6px 12px;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: var(--radius-full);
  border: 1px solid var(--border-light);
  background: var(--bg-card);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.pill-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: #fff;
}

.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.mod-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 12px;
  transition: box-shadow var(--duration-fast);
}

.mod-card:hover {
  box-shadow: var(--shadow-sm);
}

.card-deleted {
  border-left: 4px solid var(--error);
  opacity: 0.75;
}

.card-resolved {
  border-left: 4px solid var(--teal);
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.author-meta {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar-sm {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-full);
  object-fit: cover;
}

.avatar-xs {
  width: 28px;
  height: 28px;
  border-radius: var(--radius-full);
  object-fit: cover;
}

.author-name {
  display: block;
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--text-strong);
}

.pub-date {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  justify-content: flex-end;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 6px;
}

.card-text {
  font-size: 0.85rem;
  color: var(--text-body);
  line-height: 1.4;
  margin: 0 0 10px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.academic-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.tag-chip {
  font-size: 0.72rem;
  background: var(--bg-subtle);
  color: var(--text-secondary);
  padding: 3px 8px;
  border-radius: var(--radius-xs);
  font-weight: 500;
}

.card-actions {
  display: flex;
  gap: 8px;
  border-top: 1px solid var(--border-light);
  padding-top: 10px;
  flex-wrap: wrap;
}

.table-responsive {
  overflow-x: auto;
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
}

.mod-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.mod-table th {
  padding: 12px 14px;
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--text-secondary);
  background: var(--bg-subtle);
  border-bottom: 1px solid var(--border-light);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.mod-table td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-light);
  color: var(--text-body);
  vertical-align: middle;
}

.user-row-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.role-select {
  padding: 4px 8px;
  border-radius: var(--radius-xs);
  border: 1px solid var(--border-light);
  background: var(--bg-card);
  color: var(--text-strong);
  font-size: 0.8rem;
  font-weight: 600;
  outline: none;
}

.role-select:focus {
  border-color: var(--primary);
}

.btn-group-sm {
  display: flex;
  gap: 6px;
}

.btn-xs {
  padding: 4px 8px;
  font-size: 0.75rem;
  border-radius: var(--radius-xs);
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
}

.badge-danger {
  background: var(--error-bg);
  color: var(--error);
}

.badge-teal {
  background: var(--teal-light);
  color: var(--teal);
}

.log-detail-cell {
  max-width: 320px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
