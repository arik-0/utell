<template>
  <div class="publicaciones-page">
    <header class="header">
      <div class="left-side">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <h1>Publicaciones</h1>
      </div>
      <div class="right-side">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
          <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
        </svg>
      </div>
    </header>

    <div class="tabs-container">
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'publicaciones' }" 
        @click="activeTab = 'publicaciones'"
      >
        Publicaciones
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeTab === 'revision' }" 
        @click="activeTab = 'revision'; alert('Sección de revisión en desarrollo (WIP)')"
      >
        Revisión <span class="wip-badge">WIP</span>
      </button>
    </div>

    <EmptyState
      icon="📋"
      title="Aún no tenés publicaciones"
      subtitle="Compartí tu experiencia universitaria con la comunidad"
      buttonText="Crear publicación"
      @action="handleCreate"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import EmptyState from '../components/EmptyState.vue';

const router = useRouter();
const activeTab = ref('publicaciones');

const handleCreate = () => {
  router.push('/create-consulta');
};
</script>

<style scoped>
.publicaciones-page {
  background-color: var(--bg-main, #F8FAFC);
  padding-bottom: 90px;
  min-height: 100vh;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
}

.left-side {
  display: flex;
  align-items: center;
  gap: 8px;
}

.left-side h1 {
  color: var(--text-strong, #003459);
  font-size: 22px;
  font-weight: 700;
  margin: 0;
}

.right-side {
  display: flex;
  align-items: center;
  gap: 16px;
}

.icon {
  color: var(--text-secondary, #6B7280);
}

.left-side .icon {
  color: var(--text-strong, #003459);
}

.tabs-container {
  display: flex;
  background-color: var(--bg-subtle, #F1F5F9);
  border-radius: 9999px;
  padding: 4px;
  margin: 0 16px 16px 16px;
}

.tab-button {
  flex: 1;
  padding: 8px;
  text-align: center;
  border-radius: 9999px;
  font-size: 14px;
  font-weight: 500;
  color: var(--text-secondary, #6B7280);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-button.active {
  background-color: var(--bg-card, #FFFFFF);
  box-shadow: var(--shadow-xs, 0 1px 2px 0 rgba(0,0,0,0.05));
  color: var(--text-strong, #003459);
  font-weight: 600;
}

.wip-badge {
  background-color: var(--accent);
  color: #92700C;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 4px;
  border-radius: var(--radius-full);
  margin-left: 2px;
  vertical-align: super;
}
</style>
