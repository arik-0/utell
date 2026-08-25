<template>
  <div class="toast-container" aria-live="polite">
    <transition-group name="toast-anim" tag="div" class="toast-list">
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="toast-item"
        :class="`toast-${toast.type}`"
        @click="toastStore.removeToast(toast.id)"
      >
        <div class="toast-icon">
          <!-- Success Icon -->
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <!-- Error Icon -->
          <svg v-else-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <!-- Warning Icon -->
          <svg v-else-if="toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <!-- Info Icon -->
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </div>
        <div class="toast-content">
          <p class="toast-message">{{ toast.message }}</p>
        </div>
        <button class="toast-close" @click.stop="toastStore.removeToast(toast.id)" aria-label="Cerrar">✕</button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToastStore } from '../store/toast'

const toastStore = useToastStore()
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 18px;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 440px;
  z-index: 9999;
  pointer-events: none;
  padding: 0 16px;
}

.toast-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.toast-item {
  pointer-events: auto;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-lg);
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
}

[data-theme="dark"] .toast-item {
  background: #1E293B;
  border-color: #334155;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-success .toast-icon { color: var(--success); }
.toast-error .toast-icon   { color: var(--error); }
.toast-warning .toast-icon { color: var(--warning); }
.toast-info .toast-icon    { color: var(--teal); }

.toast-content {
  flex: 1;
}

.toast-message {
  font-family: var(--font-family);
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text-strong);
  margin: 0;
  line-height: 1.35;
}

.toast-close {
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 13px;
  cursor: pointer;
  padding: 2px 4px;
}

.toast-close:hover { color: var(--text-strong); }

/* Animation */
.toast-anim-enter-active,
.toast-anim-leave-active {
  transition: all 0.28s var(--ease-spring);
}

.toast-anim-enter-from {
  opacity: 0;
  transform: translateY(-16px) scale(0.94);
}

.toast-anim-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}
</style>
