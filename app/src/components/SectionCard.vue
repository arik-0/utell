<template>
  <component
    :is="to ? 'router-link' : 'div'"
    :to="to"
    class="section-card"
  >
    <div class="icon-circle">
      <slot name="icon">{{ icon }}</slot>
    </div>
    <div class="content">
      <h3 class="title">{{ title }}</h3>
      <p v-if="subtitle" class="subtitle">{{ subtitle }}</p>
    </div>
    <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
  </component>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: '📄'
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
  padding: 16px;
  background-color: var(--bg-card, #FFFFFF);
  box-shadow: var(--shadow-sm, 0 1px 2px 0 rgba(0, 0, 0, 0.05));
  border-radius: var(--radius-lg, 16px);
  border: 1px solid var(--border-light, #E5E7EB);
  margin-bottom: 12px;
  text-decoration: none;
  cursor: pointer;
  transition: transform 150ms ease;
}

.section-card:active {
  transform: scale(0.98);
}

.icon-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background-color: var(--primary-ghost, rgba(255, 107, 53, 0.08));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.title {
  color: var(--text-strong, #003459);
  font-size: 15px;
  font-weight: 600;
  margin: 0;
}

.subtitle {
  color: var(--text-secondary, #6B7280);
  font-size: 13px;
  margin: 2px 0 0 0;
}

.chevron {
  color: var(--text-muted, #9CA3AF);
  font-size: 20px;
  font-weight: 400;
}
</style>
