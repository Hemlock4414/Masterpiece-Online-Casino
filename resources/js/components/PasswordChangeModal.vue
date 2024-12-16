<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/store/AuthStore';
import { useRouter } from 'vue-router';

const router = useRouter();
const authStore = useAuthStore();
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const isSubmitting = ref(false);

const updateMessage = ref('');
const updateError = ref(false);
const messageTimeout = ref(null);

// Toggle-States für Passwortfelder
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const emit = defineEmits(['close', 'success']);

// Hilfsfunktion zum Anzeigen von Nachrichten
const showMessage = (message, isError = false) => {
  console.log('Nachricht:', message);
  if (messageTimeout.value) {
    clearTimeout(messageTimeout.value);
  }
  
  updateMessage.value = message;
  updateError.value = isError;

  // Nachricht nach 3 Sekunden ausblenden
  messageTimeout.value = setTimeout(() => {
    updateMessage.value = '';
    updateError.value = false;
  }, 3000);
};

const handleSubmit = async () => {
  if (newPassword.value !== confirmPassword.value) {
    showMessage('Die Passwörter stimmen nicht überein', true);
    return;
  }
  
  try {
    isSubmitting.value = true;
    await authStore.updatePassword({
      current_password: currentPassword.value,
      password: newPassword.value, 
      password_confirmation: confirmPassword.value
    });
    
    showMessage('Passwort wurde erfolgreich geändert. Aus Sicherheitsgründen werden Sie nun ausgeloggt. Bitte benutzen Sie anschliessend Ihr neues Passwort');
    
    setTimeout(() => {
      emit('success', 'Passwort erfolgreich geändert');
      router.push('/');
    }, 3000);
  } catch (error) {
  console.error('Fehler:', error);
  const errorMessage = 
    error.response?.data?.message || 
    error.response?.data?.error || 
    'Fehler beim Ändern des Passworts';
  
  console.log('Backend-Fehlermeldung:', errorMessage);
  showMessage(errorMessage, true);
} finally {
    isSubmitting.value = false; 
  }
};

</script>

<template>
    <div class="modal-overlay">
      <div class="modal-content">
        <h3>Passwort ändern</h3>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <div class="password-input-wrapper">
              <input 
                :type="showCurrentPassword ? 'text' : 'password'"
                v-model="currentPassword" 
                required 
                placeholder="Aktuelles Passwort"
                class="form-input"
              />
              <button 
                type="button" 
                @click="showCurrentPassword = !showCurrentPassword"
                class="password-toggle"
              >
                {{ showCurrentPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>
          
          <div class="form-group">
            <div class="password-input-wrapper">
              <input 
                :type="showNewPassword ? 'text' : 'password'"
                v-model="newPassword" 
                required 
                placeholder="Neues Passwort"
                class="form-input"
              />
              <button 
                type="button" 
                @click="showNewPassword = !showNewPassword"
                class="password-toggle"
              >
                {{ showNewPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>
          
          <div class="form-group">
            <div class="password-input-wrapper">
              <input 
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="confirmPassword" 
                required 
                placeholder="Passwort bestätigen"
                class="form-input"
              />
              <button 
                type="button" 
                @click="showConfirmPassword = !showConfirmPassword"
                class="password-toggle"
              >
                {{ showConfirmPassword ? '🙈' : '👁️' }}
              </button>
            </div>
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
        
        <div 
          v-if="updateMessage" 
          :class="['message', updateError ? 'error' : 'success']"
          role="alert"
        >
          {{ updateMessage }}
        </div>
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
  position: relative;
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

.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-toggle {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s;
  opacity: 0.6;
  font-size: 1.2rem;
}

.password-toggle:hover {
  opacity: 1;
}

/* Anpassung für das Input-Feld, damit der Toggle-Button nicht überlappt */
.form-input {
  padding-right: 40px;
}

.message {
  position: absolute;
  bottom: -50px;
  left: 0;
  right: 0;
  margin-top: 10px;
  padding: 10px 15px;
  border-radius: 4px;
  font-size: 14px;
  animation: fadeIn 0.3s ease;
  z-index: 1;
  text-align: center;
}

.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
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