<template>
  <div class="mensajes-page">
    <header class="header">
      <div class="left-side">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
          <polyline points="22,6 12,13 2,6"></polyline>
        </svg>
        <h1>Mensajes <span class="wip-badge">WIP</span></h1>
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

    <EmptyState
      icon="✉️"
      title="Tu bandeja está vacía"
      subtitle="Cuando recibas mensajes de otros estudiantes, aparecerán acá"
    />
  </div>
</template>

<script setup>
import EmptyState from '../components/EmptyState.vue';
</script>

<style scoped>
.mensajes-page {
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

.wip-badge {
  background-color: var(--accent);
  color: #92700C;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: var(--radius-full);
  margin-left: 5px;
  vertical-align: middle;
}
</style>
