<template>
  <div class="app-wrapper">
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>

    <!-- Full Screen Dark Overlay for FAB -->
    <div v-if="showFabMenu" class="fab-overlay" @click="toggleFab"></div>

    <!-- Floating Action Menu Items -->
    <div class="fab-menu" :class="{ 'fab-menu-active': showFabMenu }">
      <router-link to="/create-consulta" class="fab-menu-btn" @click="toggleFab">
        <span class="fab-label">Consulta</span>
        <svg class="fab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </router-link>
      <router-link to="/create-experiencia" class="fab-menu-btn" @click="toggleFab">
        <span class="fab-label">Experiencia</span>
        <svg class="fab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </router-link>
    </div>

    <!-- Bottom Tab Bar -->
    <nav class="bottom-tab-bar" v-if="showTabBar">
      <router-link to="/feed" class="tab-item" :class="{ active: isActive('/feed') }">
        <div class="tab-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="tab-label">Feed</span>
      </router-link>

      <router-link to="/publicaciones" class="tab-item" :class="{ active: isActive('/publicaciones') }">
        <div class="tab-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg>
        </div>
        <span class="tab-label">Publicaciones</span>
      </router-link>

      <div class="tab-item plus-btn-container">
        <button class="plus-btn" :class="{ 'plus-active': showFabMenu }" @click="toggleFab">
          <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
      </div>

      <router-link to="/amigos" class="tab-item" :class="{ active: isActive('/amigos') }">
        <div class="tab-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <span class="tab-label">Amigos</span>
      </router-link>

      <router-link to="/profile" class="tab-item" :class="{ active: isActive('/profile') }">
        <div class="tab-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <span class="tab-label">Perfil</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const showFabMenu = ref(false)

const toggleFab = () => {
  showFabMenu.value = !showFabMenu.value
}

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

// Hide tab bar on login, register, create, and detail pages
const showTabBar = computed(() => {
  const hiddenRoutes = ['/', '/register', '/create-consulta', '/create-experiencia']
  if (route.path.startsWith('/post/')) return false
  if (route.path.startsWith('/amigos')) return true  // always show on amigos
  return !hiddenRoutes.includes(route.path)
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
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* ─── Bottom Tab Bar ─── */
.bottom-tab-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 72px;
  background-color: var(--bg-card);
  border-top: 1px solid var(--border-light);
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom);
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
  gap: 3px;
  transition: color var(--duration-fast) ease;
  position: relative;
}

.tab-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 28px;
  border-radius: var(--radius-full);
  transition: all var(--duration-normal) var(--ease-out);
}

.tab-item.active .tab-icon-wrap {
  background-color: var(--primary-ghost);
  width: 56px;
}

.tab-item.active {
  color: var(--primary);
}

.tab-label {
  font-size: 10px;
  font-weight: 500;
  transition: color var(--duration-fast) ease;
}

.tab-item.active .tab-label {
  color: var(--primary);
  font-weight: 600;
}

/* ─── FAB Plus Button ─── */
.plus-btn-container {
  position: relative;
  top: -18px;
}

.plus-btn {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-primary);
  cursor: pointer;
  transition: all var(--duration-normal) var(--ease-spring);
}

.plus-btn:active {
  transform: scale(0.92);
}

.plus-active {
  transform: rotate(45deg);
  background: var(--bg-card) !important;
  box-shadow: var(--shadow-md) !important;
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
  background-color: rgba(0, 52, 89, 0.5);
  z-index: 900;
  animation: overlayIn 0.25s var(--ease-out);
  backdrop-filter: blur(2px);
}

@keyframes overlayIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* ─── FAB Menu ─── */
.fab-menu {
  position: fixed;
  bottom: 90px;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  gap: 16px;
  z-index: 950;
  pointer-events: none;
}

.fab-menu-active {
  pointer-events: auto;
}

.fab-menu-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: var(--radius-full);
  background: var(--bg-card);
  color: var(--text-strong);
  font-weight: 600;
  font-size: 14px;
  box-shadow: var(--shadow-lg);
  cursor: pointer;
  text-decoration: none;
  opacity: 0;
  transform: translateY(20px) scale(0.8);
  transition: all 0.35s var(--ease-spring);
}

.fab-menu-active .fab-menu-btn {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.fab-menu-active .fab-menu-btn:nth-child(1) { transition-delay: 0.05s; }
.fab-menu-active .fab-menu-btn:nth-child(2) { transition-delay: 0.1s; }

.fab-menu-btn:active {
  transform: scale(0.95) !important;
}

.fab-icon {
  font-size: 18px;
}

.fab-label {
  font-size: 13px;
}
</style>
