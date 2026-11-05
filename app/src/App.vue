<template>
  <div class="app-layout" :class="{ 'auth-layout': isAuthRoute, 'fullwidth-layout': isFullWidthRoute }">
    <!-- Global Toast Notifications -->
    <ToastContainer />

    <!-- ═════════════════════════════════════════════════════════════
         DESKTOP SIDEBAR (Visible on Desktop >= 768px when authenticated)
         ═════════════════════════════════════════════════════════════ -->
    <aside class="desktop-sidebar" v-if="showDesktopSidebar">
      <div class="sidebar-top">
        <!-- Logo & Brand Header -->
        <router-link to="/feed" class="sidebar-brand">
          <img src="/img/logo.png" alt="U-Tell Logo" class="sidebar-logo" />
          <span class="sidebar-brand-name">U-Tell</span>
        </router-link>

        <!-- Navigation Links -->
        <nav class="sidebar-nav">
          <router-link to="/feed" class="sidebar-link" :class="{ active: isActive('/feed') }">
            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Feed</span>
          </router-link>

          <router-link to="/publicaciones" class="sidebar-link" :class="{ active: isActive('/publicaciones') }">
            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg>
            <span>Descubrir</span>
          </router-link>

          <router-link to="/mensajes" class="sidebar-link" :class="{ active: isActive('/mensajes') }">
            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>Mensajes</span>
          </router-link>

          <router-link to="/amigos" class="sidebar-link" :class="{ active: isActive('/amigos') }">
            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Amigos</span>
          </router-link>

          <router-link v-if="isStaffOrInstitution" to="/dashboard" class="sidebar-link" :class="{ active: isActive('/dashboard') }">
            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            <span>Tablero UAI</span>
          </router-link>

          <router-link v-if="authStore.isModeratorOrAdmin" to="/moderacion" class="sidebar-link" :class="{ active: isActive('/moderacion') }">
            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Moderación</span>
          </router-link>
        </nav>



        <!-- Quick Post Creation CTAs -->
        <div class="sidebar-cta-group">
          <router-link to="/create-consulta" class="btn btn-primary sidebar-create-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Nueva Consulta</span>
          </router-link>
          <router-link to="/create-experiencia" class="btn btn-secondary sidebar-create-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span>Nueva Experiencia</span>
          </router-link>
        </div>
      </div>

      <!-- Bottom User Profile, Theme Toggle & Logout -->
      <div class="sidebar-bottom">
        <div class="sidebar-user-card" @click="router.push('/profile')">
          <img :src="getProfilePic(authStore.user?.fotoPerfil)" alt="Avatar" class="sidebar-avatar" />
          <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ authStore.user?.nombre }} {{ authStore.user?.apellido }}</span>
            <span class="sidebar-user-role">{{ authStore.user?.tipoPerfil || 'Estudiante' }}</span>
          </div>
        </div>

        <div class="sidebar-bottom-actions">
          <button class="sidebar-icon-btn" @click="handleToggleTheme" :title="themeStore.isDark ? 'Activar modo claro' : 'Activar modo oscuro'" aria-label="Cambiar tema">
            <svg v-if="themeStore.isDark" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          </button>
          <button class="sidebar-icon-btn text-danger" @click="handleLogout" title="Cerrar sesión" aria-label="Cerrar sesión">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- ═════════════════════════════════════════════════════════════
         STAGE CONTAINER (Main Content + Right Rail centered & balanced)
         ═════════════════════════════════════════════════════════════ -->
    <div class="app-stage-container">
      <main class="app-main-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- ═════════════════════════════════════════════════════════════
           DESKTOP RIGHT RAIL (Visible on Wide Desktop >= 1024px)
           ═════════════════════════════════════════════════════════════ -->
      <aside class="desktop-right-rail" v-if="showDesktopRightRail">
        <!-- Muros Populares Widget -->
        <div class="rail-widget">
          <h3 class="rail-widget-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span>Muros Destacados</span>
          </h3>
          <div class="rail-uni-list">
            <router-link
              v-for="uni in topUniversidades"
              :key="uni.idUniversidad"
              :to="`/universidad/${uni.idUniversidad}`"
              class="rail-uni-item"
            >
              <div class="rail-uni-info">
                <span class="rail-uni-name">{{ uni.nombre }}</span>
                <span class="rail-uni-sub">{{ uni.totalExperiencias || 0 }} experiencias</span>
              </div>
              <span class="rail-score">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                {{ uni.promedioGeneral || '5.0' }}
              </span>
            </router-link>
          </div>
        </div>

        <!-- Quick Community Tips Widget -->
        <div class="rail-widget">
          <h3 class="rail-widget-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Comunidad U-Tell</span>
          </h3>
          <p class="rail-footer">
            Comparte dudas sobre materias, docentes y sedes. Califica tu facultad para orientar a nuevos ingresantes universitarios.
          </p>
        </div>

        <!-- Footer & Copyright -->
        <div class="rail-footer text-center">
          © 2026 U-Tell — Red Social Universitaria.
        </div>
      </aside>
    </div>

    <!-- ═════════════════════════════════════════════════════════════
         MOBILE ONLY CONTROLS (< 768px)
         ═════════════════════════════════════════════════════════════ -->
    <div class="mobile-only-controls">
      <!-- Full Screen Dark Overlay for FAB -->
      <div v-if="showFabMenu" class="fab-overlay" @click="toggleFab"></div>

      <!-- Floating Action Menu Items -->
      <div class="fab-menu" :class="{ 'fab-menu-active': showFabMenu }">
        <router-link to="/create-consulta" class="fab-menu-btn fab-consulta" @click="toggleFab">
          <div class="fab-icon-wrap icon-consulta">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div class="fab-text">
            <span class="fab-label">Consulta</span>
            <span class="fab-desc">Pregunta a la comunidad</span>
          </div>
        </router-link>
        <router-link to="/create-experiencia" class="fab-menu-btn fab-experiencia" @click="toggleFab">
          <div class="fab-icon-wrap icon-experiencia">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <div class="fab-text">
            <span class="fab-label">Experiencia</span>
            <span class="fab-desc">Califica tu facultad o carrera</span>
          </div>
        </router-link>
      </div>

      <!-- Modern Glassmorphism Bottom Tab Bar -->
      <nav class="bottom-tab-bar" v-if="showTabBar">
        <router-link to="/feed" class="tab-item" :class="{ active: isActive('/feed') }">
          <div class="tab-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          </div>
          <span class="tab-label">Feed</span>
        </router-link>

        <router-link to="/publicaciones" class="tab-item" :class="{ active: isActive('/publicaciones') }">
          <div class="tab-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg>
          </div>
          <span class="tab-label">Descubrir</span>
        </router-link>

        <div class="tab-item plus-btn-container">
          <button class="plus-btn" :class="{ 'plus-active': showFabMenu }" @click="toggleFab" aria-label="Crear publicación">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          </button>
        </div>

        <router-link to="/amigos" class="tab-item" :class="{ active: isActive('/amigos') }">
          <div class="tab-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <span class="tab-label">Amigos</span>
        </router-link>

        <router-link to="/profile" class="tab-item" :class="{ active: isActive('/profile') }">
          <div class="tab-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <span class="tab-label">Perfil</span>
        </router-link>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './store/auth'
import { useToastStore } from './store/toast'
import { useThemeStore } from './store/theme'
import { getProfilePic } from './utils'
import api from './api'
import ToastContainer from './components/ToastContainer.vue'

const route      = useRoute()
const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()
const themeStore = useThemeStore()

const showFabMenu      = ref(false)
const topUniversidades = ref([])

const toggleFab = () => {
  showFabMenu.value = !showFabMenu.value
}

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const isAuthRoute = computed(() => {
  return route.path === '/' || route.path === '/register'
})

const isFullWidthRoute = computed(() => {
  return route.path === '/dashboard' || route.path === '/mensajes'
})

const showDesktopSidebar = computed(() => {
  return !isAuthRoute.value && !!authStore.user
})

const isStaffOrInstitution = computed(() => authStore.isStaffOrInstitution)

const showDesktopRightRail = computed(() => {

  return !isAuthRoute.value && !!authStore.user && (route.path === '/feed' || route.path === '/publicaciones')
})

// Hide mobile bottom tab bar on login, register, and create pages
const showTabBar = computed(() => {
  const hiddenRoutes = ['/', '/register', '/create-consulta', '/create-experiencia']
  if (route.path.startsWith('/post/')) return false
  if (route.path.startsWith('/amigos')) return true
  return !hiddenRoutes.includes(route.path)
})

const handleToggleTheme = () => {
  const newTheme = themeStore.toggle()
  toastStore.info(newTheme === 'dark' ? 'Modo Oscuro activado' : 'Modo Claro activado', 2000)
}

const handleLogout = () => {
  authStore.logout()
  toastStore.info('Sesión cerrada.')
  router.push('/')
}

const fetchTopUniversidades = async () => {
  try {
    const res = await api.get('/universidades/con-experiencias')
    if (Array.isArray(res.data)) {
      topUniversidades.value = res.data.slice(0, 4)
    }
  } catch (_) {
    topUniversidades.value = [
      { idUniversidad: 1, nombre: 'Universidad Nacional de Rosario', totalExperiencias: 14, promedioGeneral: '4.8' },
      { idUniversidad: 2, nombre: 'Universidad de Buenos Aires', totalExperiencias: 28, promedioGeneral: '4.9' },
      { idUniversidad: 3, nombre: 'Universidad Tecnológica Nacional', totalExperiencias: 19, promedioGeneral: '4.7' }
    ]
  }
}

onMounted(() => {
  fetchTopUniversidades()
})
</script>


<style scoped>
.app-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  min-height: 100dvh;
}

/* ─── Transitions ─── */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* ─── Bottom Tab Bar ─── */
.bottom-tab-bar {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 680px;
  height: 68px;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-top: 1px solid rgba(226, 232, 240, 0.8);
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom, 0px);
  box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.04);
}

@media (min-width: 1200px) {
  .bottom-tab-bar {
    max-width: 720px;
  }
}

.tab-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  text-decoration: none;
  flex: 1;
  height: 100%;
  gap: 2px;
  transition: color var(--duration-fast) ease;
  position: relative;
}

.tab-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 28px;
  border-radius: var(--radius-full);
  transition: all var(--duration-normal) var(--ease-out);
}

.tab-item.active .tab-icon-wrap {
  background-color: var(--primary-ghost);
  color: var(--primary);
  width: 52px;
}

.tab-item.active {
  color: var(--primary);
}

.tab-label {
  font-family: var(--font-family);
  font-size: 10.5px;
  font-weight: 500;
  transition: color var(--duration-fast) ease;
  letter-spacing: -0.01em;
}

.tab-item.active .tab-label {
  color: var(--primary);
  font-weight: 700;
}

/* ─── FAB Plus Button ─── */
.plus-btn-container {
  position: relative;
  top: -16px;
}

.plus-btn {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  border: 3px solid #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-primary);
  cursor: pointer;
  transition: all var(--duration-normal) var(--ease-spring);
}

.plus-btn:hover {
  transform: scale(1.05);
}

.plus-btn:active {
  transform: scale(0.92);
}

.plus-active {
  transform: rotate(45deg);
  background: var(--bg-card) !important;
  box-shadow: var(--shadow-md) !important;
  border-color: var(--border-light) !important;
}

.plus-active svg {
  stroke: var(--primary) !important;
}

/* ─── FAB Overlay ─── */
.fab-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(15, 23, 42, 0.45);
  z-index: 900;
  animation: overlayIn 0.22s var(--ease-out);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

@keyframes overlayIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* ─── FAB Menu Modal ─── */
.fab-menu {
  position: fixed;
  bottom: 86px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 32px);
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 950;
  pointer-events: none;
}

.fab-menu-active {
  pointer-events: auto;
}

.fab-menu-btn {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-radius: var(--radius-lg);
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  color: var(--text-strong);
  box-shadow: var(--shadow-lg);
  cursor: pointer;
  text-decoration: none;
  opacity: 0;
  transform: translateY(16px) scale(0.95);
  transition: all 0.28s var(--ease-spring);
}

.fab-menu-active .fab-menu-btn {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.fab-menu-active .fab-menu-btn:nth-child(1) { transition-delay: 0.04s; }
.fab-menu-active .fab-menu-btn:nth-child(2) { transition-delay: 0.08s; }

.fab-menu-btn:active {
  transform: scale(0.97) !important;
}

.fab-icon-wrap {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-consulta {
  background-color: var(--accent-ghost);
  color: #B45309;
}

.icon-experiencia {
  background-color: var(--primary-ghost);
  color: var(--primary);
}

.fab-text {
  display: flex;
  flex-direction: column;
}

.fab-label {
  font-family: var(--font-family);
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-strong);
}

.fab-desc {
  font-size: 12px;
  color: var(--text-secondary);
  margin-top: 1px;
}
</style>
