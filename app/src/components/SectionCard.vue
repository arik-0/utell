<template>
  <component
    :is="to ? 'router-link' : 'div'"
    :to="to"
    class="section-card"
  >
    <div class="icon-circle">
      <slot name="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </slot>
    </div>
    <div class="content">
      <h3 class="title">{{ title }}</h3>
      <p v-if="subtitle" class="subtitle">{{ subtitle }}</p>
    </div>
    <div class="chevron-wrap">
      <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </div>
  </component>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  to: {
    type: String,
    default: ''
  }
});
</script>

<style scoped>
.section-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background-color: var(--bg-card);
  box-shadow: var(--shadow-sm);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  margin-bottom: 10px;
  text-decoration: none;
  cursor: pointer;
  transition: transform var(--duration-fast) ease,
              box-shadow var(--duration-fast) ease,
              border-color var(--duration-fast) ease;
}

.section-card:hover {
  border-color: rgba(255, 107, 53, 0.3);
  box-shadow: var(--shadow-md);
}

.section-card:active {
  transform: scale(0.985);
  box-shadow: var(--shadow-xs);
}

.icon-circle {
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  background-color: var(--primary-ghost);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid rgba(255, 107, 53, 0.12);
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.title {
  color: var(--text-strong);
  font-family: var(--font-family);
  font-size: 14.5px;
  font-weight: 600;
  margin: 0;
  letter-spacing: -0.01em;
}

.subtitle {
  color: var(--text-secondary);
  font-size: 12.5px;
  margin: 2px 0 0 0;
  line-height: 1.4;
}

.chevron-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: transform var(--duration-fast) ease, color var(--duration-fast) ease;
}

.section-card:hover .chevron-wrap {
  color: var(--primary);
  transform: translateX(2px);
}
</style>
