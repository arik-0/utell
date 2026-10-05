<template>
  <div class="page-container profile-page">
    <!-- Header -->
    <header class="profile-header-bar">
      <div class="header-left">
        <button v-if="isOtherUser" class="back-btn" @click="router.back()" aria-label="Volver">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </button>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <h1>{{ isOtherUser ? 'Perfil de Usuario' : 'Mi Perfil' }}</h1>
      </div>

      <!-- Theme Switch Icon Button in Header -->
      <button class="theme-toggle-btn" @click="handleToggleTheme" :aria-label="isDark ? 'Activar modo claro' : 'Activar modo oscuro'">
        <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
    </header>

    <!-- Loading State -->
    <div v-if="isLoading" class="state-container">
      <div class="spinner"></div>
      <p class="text-secondary text-sm mt-2">Cargando perfil...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="!profileUser" class="state-container">
      <p class="text-secondary">No se encontró el perfil de usuario solicitado.</p>
      <button class="btn btn-secondary mt-3" @click="router.push('/feed')" style="width: auto;">Volver al Feed</button>
    </div>

    <!-- Profile View Content -->
    <div v-else class="profile-content">
      <!-- User Hero Card -->
      <div class="user-hero-card animate-fadein-up">
        <div class="user-avatar-wrap" :class="{ 'avatar-editable': !isOtherUser }" @click="!isOtherUser && triggerAvatarUpload()">
          <div class="avatar-inner">
            <img v-if="profileUser.fotoPerfil" :src="getProfilePic(profileUser.fotoPerfil)" alt="Avatar" class="avatar-img" />
            <span v-else class="avatar-initial">{{ userInitial }}</span>
          </div>
          <div v-if="!isOtherUser" class="avatar-edit-badge" title="Cambiar foto de perfil">
            <svg v-if="isUploadingAvatar" class="spinner-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="30 60"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
          </div>
          <input
            v-if="!isOtherUser"
            type="file"
            ref="avatarFileInput"
            accept="image/png, image/jpeg, image/webp"
            style="display: none"
            @change="handleAvatarFileChange"
          />
        </div>
        <div class="user-details">
          <h2 class="user-name">{{ profileUser.nombre }} {{ profileUser.apellido }}</h2>
          <p class="user-email">{{ profileUser.email }}</p>
          <div class="user-badges">
            <span class="badge badge-primary">{{ profileUser.tipoPerfil || 'Estudiante' }}</span>
            <span v-if="profileUser.nombreUniversidad" class="badge badge-subtle">{{ profileUser.nombreUniversidad }}</span>
          </div>
        </div>
      </div>

      <!-- Bio / Description Section if present -->
      <div v-if="profileUser.descripcion || profileUser.trayectoria" class="bio-card animate-fadein-up">
        <p v-if="profileUser.descripcion" class="bio-text">{{ profileUser.descripcion }}</p>
        <div v-if="profileUser.trayectoria" class="trayectoria-wrap">
          <span class="trayectoria-label">Trayectoria:</span>
          <span class="trayectoria-val">{{ profileUser.trayectoria }}</span>
        </div>
        <div v-if="profileUser.nombreCarrera" class="carrera-tag-wrap">
          <span class="carrera-label">Carrera:</span>
          <span class="carrera-val">{{ profileUser.nombreCarrera }}</span>
        </div>
      </div>

      <!-- Actions Bar (For External Profiles) -->
      <div v-if="isOtherUser" class="external-actions animate-fadein-up">
        <!-- Relationship Status Actions -->
        <template v-if="relacionEstado === 'ninguno'">
          <button class="btn btn-primary btn-action" @click="enviarSolicitud" :disabled="isActionLoading">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            <span>{{ isActionLoading ? 'Enviando...' : 'Enviar Solicitud de Amistad' }}</span>
          </button>
        </template>

        <template v-else-if="relacionEstado === 'pendiente_enviada'">
          <button class="btn btn-secondary btn-action btn-disabled-look" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
            <span>Solicitud de Amistad Pendiente</span>
          </button>
        </template>

        <template v-else-if="relacionEstado === 'pendiente_recibida'">
          <div class="pending-decision-row">
            <button class="btn btn-primary btn-action flex-1" @click="responderSolicitud('aceptar')" :disabled="isActionLoading">
              Aceptar Solicitud
            </button>
            <button class="btn btn-secondary btn-action flex-1" @click="responderSolicitud('rechazar')" :disabled="isActionLoading">
              Rechazar
            </button>
          </div>
        </template>

        <template v-else-if="relacionEstado === 'aceptada'">
          <div class="connected-row">
            <button class="btn btn-primary btn-action flex-1" @click="iniciarChat">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
              <span>Enviar Mensaje Directo</span>
            </button>
            <button class="btn btn-danger-outline btn-icon-sm" @click="eliminarAmistad" title="Eliminar amistad">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </template>
      </div>

      <!-- Live Stats Metrics Row -->
      <div class="stats-row animate-fadein-up" style="animation-delay: 0.05s;">
        <div class="stat-item" :class="{ clickable: !isOtherUser }" @click="!isOtherUser && router.push('/amigos')">
          <span class="stat-num">{{ amigoCount }}</span>
          <span class="stat-label">Amigos</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item" :class="{ clickable: !isOtherUser }" @click="!isOtherUser && router.push('/publicaciones')">
          <span class="stat-num">{{ postCount }}</span>
          <span class="stat-label">Publicaciones</span>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════
           CASE A: OTHER USER'S PUBLIC FEED
           ═════════════════════════════════════════════════════════════ -->
      <div v-if="isOtherUser" class="user-posts-section">
        <h3 class="section-heading">Publicaciones de {{ profileUser.nombre }}</h3>

        <div v-if="userPosts.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="clipboard"
            title="Sin publicaciones registradas"
            subtitle="Este usuario aún no ha compartido consultas ni experiencias en la comunidad."
          />
        </div>

        <div v-else class="posts-stream">
          <article
            v-for="post in userPosts"
            :key="post.idPublicacion"
            class="post-card animate-fadein-up"
            @click="router.push(`/post/${post.idPublicacion}`)"
          >
            <div class="post-header">
              <span class="badge" :class="post.tipoPublicacion === 'Consulta' ? 'badge-accent' : 'badge-primary'">
                {{ post.tipoPublicacion }}
              </span>
              <span class="post-time">{{ timeAgo(post.fecha, post.hora) }}</span>
            </div>

            <div class="post-body">
              <h4 v-if="post.titulo" class="post-title">{{ post.titulo }}</h4>
              <p class="post-text">{{ parsePostContent(post.texto).text }}</p>
              <div v-if="parsePostContent(post.texto).image" class="post-image-attachment">
                <img :src="parsePostContent(post.texto).image" alt="Imagen adjunta" loading="lazy" />
              </div>
            </div>

            <div class="post-footer">
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

      <!-- ═════════════════════════════════════════════════════════════
           CASE B: OWN PROFILE NAVIGATION CARDS & SETTINGS
           ═════════════════════════════════════════════════════════════ -->
      <div v-else class="sections-list animate-fadein-up" style="animation-delay: 0.1s;">
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
          title="Mensajería Directa"
          subtitle="Conversaciones privadas con tus pares"
          to="/mensajes"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          </template>
        </SectionCard>

        <!-- Dark Mode Toggle Card -->
        <div class="settings-card" @click="handleToggleTheme">
          <div class="settings-left">
            <div class="settings-icon">
              <svg v-if="themeStore.isDark" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </div>
            <div class="settings-text">
              <span class="settings-title">Tema Visual</span>
              <span class="settings-sub">{{ themeStore.isDark ? 'Modo Oscuro activado' : 'Modo Claro activado' }}</span>
            </div>
          </div>
          <div class="toggle-switch" :class="{ active: themeStore.isDark }">
            <div class="toggle-circle"></div>
          </div>
        </div>

        <button class="logout-btn" @click="handleLogout">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span>Cerrar sesión</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useThemeStore } from '../store/theme'
import { getProfilePic, timeAgo, uploadImageFile, parsePostContent } from '../utils'
import api from '../api'
import { useToastStore } from '../store/toast'
import SectionCard from '../components/SectionCard.vue'
import EmptyState from '../components/EmptyState.vue'

const route      = useRoute()
const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()
const themeStore = useThemeStore()

const profileUser      = ref(null)
const amigoCount       = ref(0)
const postCount        = ref(0)
const userPosts        = ref([])
const isLoading        = ref(true)
const isActionLoading  = ref(false)
const relacionEstado   = ref('ninguno')
const idAmistadRel     = ref(null)
const avatarFileInput  = ref(null)
const isUploadingAvatar = ref(false)

const triggerAvatarUpload = () => {
  if (isUploadingAvatar.value) return
  avatarFileInput.value?.click()
}

const handleAvatarFileChange = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  isUploadingAvatar.value = true
  toastStore.info('Optimizando y subiendo imagen...', 2000)

  try {
    const uploadedUrl = await uploadImageFile(file, {
      maxWidth: 400,
      maxHeight: 400,
      quality: 0.85,
      idUsuario: authStore.user?.idUsuario,
      tipo: 'avatar'
    })

    // Try updating profile in backend
    try {
      await api.put(`/editarPerfil/${authStore.user?.idUsuario}`, {
        fotoPerfil: uploadedUrl
      })
    } catch (_) {
      // Backend /upload may have already handled this
    }

    if (authStore.user) {
      authStore.user.fotoPerfil = uploadedUrl
      try {
        localStorage.setItem('utell_user', JSON.stringify(authStore.user))
      } catch (_) {}
    }
    if (profileUser.value) {
      profileUser.value.fotoPerfil = uploadedUrl
    }

    toastStore.success('Foto de perfil actualizada correctamente.')
  } catch (error) {
    console.error('Error al subir avatar:', error)
    toastStore.error('No se pudo actualizar la foto de perfil.')
  } finally {
    isUploadingAvatar.value = false
    if (event.target) event.target.value = ''
  }
}

const isOtherUser = computed(() => {
  if (!route.params.id) return false
  return Number(route.params.id) !== Number(authStore.user?.idUsuario)
})

const userInitial = computed(() => {
  return profileUser.value?.nombre?.charAt(0) || 'U'
})

const handleToggleTheme = () => {
  const next = themeStore.toggle()
  toastStore.info(next === 'dark' ? 'Modo Oscuro activado' : 'Modo Claro activado', 2000)
}

// ─── Data Loaders ─────────────────────────────────────────────────────────────
const fetchProfileData = async () => {
  isLoading.value = true
  const targetId = route.params.id ? Number(route.params.id) : authStore.user?.idUsuario

  if (!targetId) {
    isLoading.value = false
    return
  }

  try {
    // 1. Fetch User Data
    const res = await api.get(`/usuarios/${targetId}`)
    const data = Array.isArray(res.data) ? res.data[0] : res.data

    if (data && data.idUsuario) {
      profileUser.value = data
      amigoCount.value  = data.totalAmigos || 0
      postCount.value   = data.totalPublicaciones || 0
    } else {
      profileUser.value = isOtherUser.value ? null : authStore.user
    }

    // 2. Fetch Relationship status if external profile
    if (isOtherUser.value && authStore.user) {
      try {
        const relRes = await api.get(`/usuarios/${authStore.user.idUsuario}/relacion/${targetId}`)
        if (relRes.data) {
          relacionEstado.value = relRes.data.estado || 'ninguno'
          idAmistadRel.value   = relRes.data.idAmistad || null
        }
      } catch (_) {
        relacionEstado.value = 'ninguno'
      }

      // 3. Fetch user's publications
      try {
        const postsRes = await api.get(`/parati?idUsuario=${targetId}`)
        if (Array.isArray(postsRes.data)) {
          userPosts.value = postsRes.data.filter(p => Number(p.idUsuario) === targetId)
        }
      } catch (_) {
        userPosts.value = []
      }
    } else if (!isOtherUser.value && authStore.user) {
      // Fetch own live stats
      const [friendsRes, postsRes] = await Promise.allSettled([
        api.get(`/usuarios/${authStore.user.idUsuario}/amigos`),
        api.get(`/parati?idUsuario=${authStore.user.idUsuario}`)
      ])
      if (friendsRes.status === 'fulfilled' && Array.isArray(friendsRes.value.data)) {
        amigoCount.value = friendsRes.value.data.length
      }
      if (postsRes.status === 'fulfilled' && Array.isArray(postsRes.value.data)) {
        postCount.value = postsRes.value.data.filter(p => Number(p.idUsuario) === Number(authStore.user.idUsuario)).length
      }
    }
  } catch (error) {
    console.error('Error fetching profile data:', error)
    if (!isOtherUser.value && authStore.user) {
      profileUser.value = authStore.user
    }
  } finally {
    isLoading.value = false
  }
}

// ─── Friendship Actions ───────────────────────────────────────────────────────
const enviarSolicitud = async () => {
  if (!authStore.user || !profileUser.value) return
  isActionLoading.value = true
  try {
    const fd = new FormData()
    fd.append('idSolicitante', authStore.user.idUsuario)
    fd.append('idReceptor', profileUser.value.idUsuario)
    await api.post(`/amistad/solicitar`, fd)
    relacionEstado.value = 'pendiente_enviada'
    toastStore.success('Solicitud de amistad enviada con éxito.')
  } catch (e) {
    console.error('Error sending friend request:', e)
    toastStore.error('No se pudo enviar la solicitud.')
  } finally {
    isActionLoading.value = false
  }
}

const responderSolicitud = async (accion) => {
  if (!idAmistadRel.value) return
  isActionLoading.value = true
  try {
    const fd = new FormData()
    fd.append('accion', accion)
    await api.post(`/amistad/${idAmistadRel.value}/responder`, fd)
    relacionEstado.value = (accion === 'aceptar') ? 'aceptada' : 'ninguno'
    if (accion === 'aceptar') {
      amigoCount.value++
      toastStore.success('¡Ahora son amigos conectados!')
    } else {
      toastStore.info('Solicitud rechazada.')
    }
  } catch (e) {
    console.error('Error responding to request:', e)
    toastStore.error('Error al responder la solicitud.')
  } finally {
    isActionLoading.value = false
  }
}

const eliminarAmistad = async () => {
  if (!idAmistadRel.value) return
  if (!confirm(`¿Deseas eliminar a ${profileUser.value?.nombre} de tus amigos?`)) return
  try {
    await api.delete(`/amistad/${idAmistadRel.value}`)
    relacionEstado.value = 'ninguno'
    amigoCount.value = Math.max(0, amigoCount.value - 1)
    toastStore.info('Amistad eliminada.')
  } catch (e) {
    console.error('Error removing friend:', e)
    toastStore.error('Error al eliminar la amistad.')
  }
}

const iniciarChat = () => {
  if (profileUser.value?.idUsuario) {
    router.push(`/mensajes?contacto=${profileUser.value.idUsuario}`)
  }
}

const handleLogout = () => {
  authStore.logout()
  router.push('/')
}

watch(() => route.params.id, () => {
  fetchProfileData()
})

onMounted(() => {
  fetchProfileData()
})
</script>

<style scoped>
.profile-page {
  padding: 0;
  padding-bottom: 90px;
  background-color: var(--bg-main);
  min-height: 100vh;
}

.profile-header-bar {
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

@media (min-width: 768px) {
  .profile-header-bar {
    top: 16px;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-xs);
    margin-bottom: 14px;
  }

  .profile-content {
    padding: 0;
  }
}

@media (min-width: 1280px) {
  .profile-header-bar {
    top: 20px;
    margin-bottom: 16px;
  }
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

.back-btn {
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  color: var(--text-strong);
  display: flex;
  align-items: center;
}

.theme-toggle-btn {
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-full);
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-strong);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.theme-toggle-btn:active { transform: scale(0.92); }

.profile-content {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* ─── Hero Card ─── */
.user-hero-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm);
}

.user-avatar-wrap {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  padding: 2px;
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
  box-shadow: var(--shadow-primary);
}

.avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-card);
}

.avatar-editable {
  cursor: pointer;
}

.avatar-editable:hover .avatar-edit-badge {
  transform: scale(1.1);
  background-color: var(--primary-hover);
}

.avatar-edit-badge {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--bg-card);
  box-shadow: var(--shadow-sm);
  transition: all var(--duration-fast) ease;
}

.spinner-svg {
  width: 13px;
  height: 13px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-initial {
  color: white;
  font-family: var(--font-family);
  font-size: 26px;
  font-weight: 800;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.user-name {
  font-size: 18px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0;
}

.user-email {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0 0 6px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-badges {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.badge-subtle {
  background: var(--bg-subtle);
  color: var(--text-secondary);
  font-size: 11.5px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: var(--radius-full);
}

/* ─── Bio Card ─── */
.bio-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 14px 16px;
  box-shadow: var(--shadow-xs);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.bio-text {
  font-size: 13.5px;
  line-height: 1.5;
  color: var(--text-body);
  margin: 0;
}

.trayectoria-wrap, .carrera-tag-wrap {
  display: flex;
  gap: 6px;
  font-size: 12.5px;
}

.trayectoria-label, .carrera-label {
  font-weight: 700;
  color: var(--text-strong);
}

.trayectoria-val, .carrera-val {
  color: var(--text-secondary);
}

/* ─── External Actions ─── */
.external-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.btn-action {
  width: 100%;
  padding: 12px 18px;
  font-size: 14px;
}

.btn-disabled-look {
  opacity: 0.75;
  cursor: default;
}

.pending-decision-row, .connected-row {
  display: flex;
  gap: 10px;
}

.flex-1 { flex: 1; }

.btn-icon-sm {
  width: 44px;
  border-radius: var(--radius-md);
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-danger-outline {
  background: var(--bg-subtle);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: var(--error);
  cursor: pointer;
}

.btn-danger-outline:active { background: rgba(239, 68, 68, 0.1); }

/* ─── Live Stats ─── */
.stats-row {
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding: 16px 20px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-xs);
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  text-decoration: none;
}

.stat-item.clickable { cursor: pointer; }
.stat-item.clickable:hover .stat-num { color: var(--primary); }

.stat-num {
  font-family: var(--font-family);
  font-size: 22px;
  font-weight: 800;
  color: var(--text-strong);
  transition: color var(--duration-fast);
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
}

.stat-divider {
  width: 1px;
  height: 32px;
  background-color: var(--border-light);
}

/* ─── External User Posts Section ─── */
.user-posts-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.section-heading {
  font-size: 15px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 4px 0 2px;
}

.posts-stream {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.post-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 14px 16px;
  box-shadow: var(--shadow-xs);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.post-card:active { transform: scale(0.988); }

.post-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.post-time { font-size: 11.5px; color: var(--text-muted); }

.post-title {
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 4px;
}

.post-text {
  font-size: 13px;
  color: var(--text-body);
  line-height: 1.45;
  margin: 0 0 10px;
}

.post-footer {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: var(--text-secondary);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ─── Settings Card (Dark Mode) ─── */
.sections-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.settings-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-xs);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.settings-card:hover {
  border-color: rgba(255, 107, 53, 0.3);
}

.settings-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.settings-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  background: var(--bg-subtle);
  color: var(--text-strong);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.settings-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.settings-title {
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
}

.settings-sub {
  font-size: 12px;
  color: var(--text-secondary);
}

.toggle-switch {
  width: 44px;
  height: 24px;
  border-radius: var(--radius-full);
  background: var(--bg-muted);
  padding: 2px;
  transition: all var(--duration-normal);
  display: flex;
  align-items: center;
}

.toggle-switch.active {
  background: var(--primary);
}

.toggle-circle {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #FFFFFF;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  transition: all var(--duration-normal) var(--ease-out);
  transform: translateX(0);
}

.toggle-switch.active .toggle-circle {
  transform: translateX(20px);
}

/* ─── Logout ─── */
.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 13px;
  background-color: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  color: var(--error);
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 8px;
  box-shadow: var(--shadow-xs);
  transition: all var(--duration-fast);
}

.logout-btn:active { transform: scale(0.98); background-color: var(--error-bg); }

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
