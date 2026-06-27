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
      <router-link to="/create-consulta" class="fab-menu-btn fab-consulta" @click="toggleFab">?</router-link>
      <router-link to="/create-experiencia" class="fab-menu-btn fab-experiencia" @click="toggleFab">XP</router-link>
    </div>

    <!-- Tab Bar (Only show if not on Login/Register) -->
    <nav class="bottom-tab-bar" v-if="showTabBar">
      <router-link to="/feed" class="tab-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      </router-link>
      
      <router-link to="/search" class="tab-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </router-link>
      
      <div class="tab-item plus-btn-container">
        <button class="plus-btn" :class="{'plus-active': showFabMenu}" @click="toggleFab">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
      </div>
      
      <router-link to="/dm" class="tab-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
      </router-link>
      
      <router-link to="/profile" class="tab-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
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

// Hide tab bar on login and register pages
const showTabBar = computed(() => {
  const hiddenRoutes = ['/', '/register', '/create-consulta', '/create-experiencia']
  if (route.path.startsWith('/post/')) return false
  return !hiddenRoutes.includes(route.path)
})
</script>

<style scoped>
.app-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.nav-icon {
  width: 24px;
  height: 24px;
  opacity: 0.5; /* inactive state */
  transition: opacity 0.2s ease, color 0.2s ease;
}

.tab-item.active .nav-icon {
  opacity: 1;
  color: var(--primary-color);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.bottom-tab-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 70px;
  background-color: var(--bg-white);
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
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
  color: var(--text-light);
  text-decoration: none;
  flex: 1;
  height: 100%;
}

.tab-item.active {
  color: var(--primary-color);
}

.icon {
  font-size: 24px;
}

.plus-btn-container {
  position: relative;
  top: -20px;
}

.plus-btn {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background-color: var(--primary-color);
  color: white;
  border: none;
  font-size: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(255, 107, 29, 0.4);
  cursor: pointer;
}
.plus-active {
  transform: rotate(45deg);
  background-color: white !important;
}

.plus-active svg {
  stroke: var(--primary-color) !important;
}

.fab-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 900;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.fab-menu {
  position: fixed;
  bottom: 85px; /* Above the bottom tab bar */
  left: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  gap: 25px;
  z-index: 950;
  pointer-events: none; /* Ignore clicks when closed */
}

.fab-menu-active {
  pointer-events: auto;
}

.fab-menu-btn {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background-color: var(--primary-color);
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: 700;
  font-size: 16px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  text-decoration: none;
  
  /* Animation states */
  opacity: 0;
  transform: translateY(20px) scale(0.5);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fab-menu-active .fab-menu-btn {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.fab-consulta {
  transition-delay: 0.05s;
}

.fab-experiencia {
  transition-delay: 0.1s;
}
</style>
