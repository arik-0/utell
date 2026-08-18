<template>
  <div class="empty-state">
    <div class="icon-circle">
      {{ icon }}
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
    default: '🎓'
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
  min-height: 50vh;
  padding: 24px;
  animation: fadeIn 0.4s ease-out forwards;
}

.icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background-color: var(--primary-ghost, rgba(255, 107, 53, 0.08));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
}

.title {
  color: var(--text-strong, #003459);
  font-size: 18px;
  font-weight: 600;
  margin-top: 20px;
  text-align: center;
}

.subtitle {
  color: var(--text-secondary, #6B7280);
  font-size: 14px;
  max-width: 280px;
  text-align: center;
  margin-top: 8px;
}

.cta-button {
  background-color: var(--primary, #FF6B35);
  color: white;
  border: none;
  border-radius: 9999px;
  padding: 12px 32px;
  font-size: 14px;
  font-weight: 600;
  margin-top: 24px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.cta-button:active {
  opacity: 0.8;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
