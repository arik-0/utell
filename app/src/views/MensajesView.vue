<template>
  <div class="page-container mensajes-page">
    <!-- Header -->
    <header class="mensajes-header">
      <div class="header-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
          <polyline points="22,6 12,13 2,6"></polyline>
        </svg>
        <h1>Mensajes</h1>
        <span class="wip-pill">Próximamente</span>
      </div>
    </header>

    <div class="content-area">
      <EmptyState
        icon="mail"
        title="Bandeja de Mensajería"
        subtitle="El sistema de mensajería directa entre pares universitarios se habilitará en la próxima actualización."
      />
    </div>
  </div>
</template>

<script setup>
import EmptyState from '../components/EmptyState.vue';
</script>

<style scoped>
.mensajes-page {
  background-color: var(--bg-main);
  padding-bottom: 90px;
  min-height: 100vh;
}

.mensajes-header {
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

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-strong);
}

.header-left h1 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.wip-pill {
  background-color: var(--accent-ghost);
  color: #B45309;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: var(--radius-full);
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.content-area {
  padding: 16px;
}
</style>
