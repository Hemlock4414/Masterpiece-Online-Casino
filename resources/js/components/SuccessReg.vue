<script setup>
import { defineProps, onMounted } from 'vue';

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true
  },
  message: {
    type: String,
    default: 'Registrierung erfolgreich!'
  }
});

defineEmits(['hide']);
</script>

<template>
    <Transition name="fade">
      <div v-if="isVisible" class="popup-overlay">
        <div class="success-popup">
          <div class="success-icon">✓</div>
          <div class="success-message">{{ message }}</div>
        </div>
      </div>
    </Transition>
</template>

<style scoped>
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.success-popup {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.success-icon {
  color: #10b981;
  font-size: 3rem;
  margin-bottom: 1rem;
}

.success-message {
  color: #374151;
  font-size: 1.25rem;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>