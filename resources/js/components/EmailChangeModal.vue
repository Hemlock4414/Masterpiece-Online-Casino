<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/store/AuthStore';

const authStore = useAuthStore();
const newEmail = ref('');
const isSubmitting = ref(false);
const emit = defineEmits(['close', 'success']);

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    await authStore.updateEmail({ email: newEmail.value });
    emit('success', 'E-Mailadresse wurde erfolgreich geändert');
    emit('close');
  } catch (error) {
    console.error('Fehler beim Ändern der E-Mail:', error);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
    <div class="modal-overlay">
      <div class="modal-content">
        <h3>E-Mail ändern</h3>
        <p>Bitte neue E-Mailadresse eingeben</p>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <input 
              type="email" 
              v-model="newEmail" 
              required 
              placeholder="Neue E-Mail"
              class="form-input"
            />
          </div>
          
          <div class="button-group">
            <button 
              type="button" 
              @click="$emit('close')" 
              class="btn-secondary"
            >
              Abbrechen
            </button>
            <button 
              type="submit" 
              class="btn-primary" 
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Wird gespeichert...' : 'Speichern' }}
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  animation: modal-appear 0.3s ease-out;
}

@keyframes modal-appear {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  color: #333;
}

p {
  margin-bottom: 1.5rem;
  color: #666;
}

.form-group {
  margin-bottom: 1rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.button-group {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-secondary,
.btn-primary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  flex: 1;
}

.btn-secondary {
  background-color: #f3f4f6;
  color: #4b5563;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
}

.btn-primary {
  background-color: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background-color: #4338ca;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .modal-content {
    margin: 1rem;
    padding: 1.5rem;
  }

  .button-group {
    flex-direction: column;
  }
}
</style>