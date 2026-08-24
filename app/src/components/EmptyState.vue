<template>
  <div class="empty-state animate-fadein-up">
    <div class="icon-circle">
      <slot name="icon">
        <svg v-if="icon === 'book' || !icon" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
        <svg v-else-if="icon === 'mail' || icon === 'message'" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <svg v-else-if="icon === 'clipboard' || icon === 'post'" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
        <svg v-else-if="icon === 'users'" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </slot>
    </div>
    <h2 class="title">{{ title }}</h2>
    <p v-if="subtitle" class="subtitle">{{ subtitle }}</p>
    <button v-if="buttonText" class="cta-button" @click="$emit('action')">
      {{ buttonText }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: 'book'
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  buttonText: {
    type: String,
    default: ''
  }
});

defineEmits(['action']);
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 44vh;
  padding: 32px 24px;
}

.icon-circle {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-light), rgba(255, 107, 53, 0.05));
  border: 1px solid rgba(255, 107, 53, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  box-shadow: 0 4px 12px rgba(255, 107, 53, 0.08);
}

.title {
  color: var(--text-strong);
  font-family: var(--font-family);
  font-size: 17px;
  font-weight: 700;
  margin-top: 18px;
  text-align: center;
  letter-spacing: -0.01em;
}

.subtitle {
  color: var(--text-secondary);
  font-size: 13.5px;
  max-width: 270px;
  text-align: center;
  margin-top: 6px;
  line-height: 1.45;
}

.cta-button {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  border: none;
  border-radius: var(--radius-full);
  padding: 11px 26px;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  margin-top: 22px;
  cursor: pointer;
  box-shadow: var(--shadow-primary);
  transition: all var(--duration-normal) var(--ease-out);
}

.cta-button:active {
  transform: scale(0.96);
}
</style>
