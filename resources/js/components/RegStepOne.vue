<script setup>
import { defineProps, defineEmits, ref } from 'vue';
import { authClient } from '@/services/AuthService';
import CancelRegModal from '../components/CancelRegModal.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'next']);

const showCancelModal = ref(false);
const validationErrors = ref({
  spielername: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Status für Live-Validierung
const validationStatus = ref({
  spielername: { isValid: false, isChecking: false },
  email: { isValid: false, isChecking: false }
});

// Debounced Username-Check
const checkUsername = debounce(async (username) => {
  if (!username) return;
  
  validationStatus.value.spielername.isChecking = true;
  validationErrors.value.spielername = '';
  
  try {
    const response = await authClient.post('/api/check-username', { username });
    validationStatus.value.spielername.isValid = response.data.available;
    if (!response.data.available) {
      validationErrors.value.spielername = 'Dieser Spielername ist bereits vergeben';
    }
  } catch (error) {
    validationStatus.value.spielername.isValid = false;
  } finally {
    validationStatus.value.spielername.isChecking = false;
  }
}, 500);

// Debounced Email-Check
const checkEmail = debounce(async (email) => {
  if (!email || !email.includes('@')) return;
  
  validationStatus.value.email.isChecking = true;
  validationErrors.value.email = '';
  
  try {
    const response = await authClient.post('/api/check-email', { email });
    validationStatus.value.email.isValid = response.data.available;
    if (!response.data.available) {
      validationErrors.value.email = 'Diese E-Mail-Adresse ist bereits registriert';
    }
  } catch (error) {
    validationStatus.value.email.isValid = false;
  } finally {
    validationStatus.value.email.isChecking = false;
  }
}, 500);

const handleInput = (field) => {
  // Bei Eingabe Fehlermeldung entfernen
  validationErrors.value[field] = '';
  
  // Wenn Feld leer ist, sofort Fehlermeldung setzen
  if (!props.modelValue[field]) {
    validationErrors.value[field] = getEmptyFieldMessage(field);
    return;
  }
  
  // Validierungen für Email und Username
  if (field === 'spielername') {
    checkUsername(props.modelValue.spielername);
  } else if (field === 'email') {
    checkEmail(props.modelValue.email);
  }
};

// Hilfsfunktion für leere Felder-Meldungen
const getEmptyFieldMessage = (field) => {
  const messages = {
    spielername: 'Bitte geben Sie einen Spielernamen ein',
    email: 'Bitte geben Sie eine E-Mail-Adresse ein',
    password: 'Bitte geben Sie ein Passwort ein',
    password_confirmation: 'Bitte bestätigen Sie Ihr Passwort'
  };
  return messages[field];
};

const validateForm = () => {
  let isValid = true;
  
  // Basis-Validierung
  if (!props.modelValue.spielername) {
    validationErrors.value.spielername = 'Bitte geben Sie einen Spielernamen ein';
    isValid = false;
  }
  
  if (!props.modelValue.email) {
    validationErrors.value.email = 'Bitte geben Sie eine E-Mail-Adresse ein';
    isValid = false;
  } else if (!props.modelValue.email.includes('@')) {
    validationErrors.value.email = 'Bitte geben Sie eine gültige E-Mail-Adresse ein';
    isValid = false;
  }
  
  if (!props.modelValue.password) {
    validationErrors.value.password = 'Bitte geben Sie ein Passwort ein';
    isValid = false;
  } else if (props.modelValue.password.length < 8) {
    validationErrors.value.password = 'Das Passwort muss mindestens 8 Zeichen lang sein';
    isValid = false;
  }
  
  if (!props.modelValue.password_confirmation) {
    validationErrors.value.password_confirmation = 'Bitte bestätigen Sie Ihr Passwort';
    isValid = false;
  } else if (props.modelValue.password !== props.modelValue.password_confirmation) {
    validationErrors.value.password_confirmation = 'Die Passwörter stimmen nicht überein';
    isValid = false;
  }
  
  return isValid;
};

const handleNext = async () => {
  // Reset aller Fehlermeldungen
  Object.keys(validationErrors.value).forEach(key => {
    validationErrors.value[key] = '';
  });
  
  // Client-seitige Validierung
  if (!validateForm()) return;
  
  try {
    // Server-seitige Unique-Checks
    const [usernameCheck, emailCheck] = await Promise.all([
      authClient.post('/api/check-username', { username: props.modelValue.spielername }),
      authClient.post('/api/check-email', { email: props.modelValue.email })
    ]);
    
    if (!usernameCheck.data.available) {
      validationErrors.value.spielername = 'Dieser Spielername ist bereits vergeben';
      return;
    }
    
    if (!emailCheck.data.available) {
      validationErrors.value.email = 'Diese E-Mail-Adresse ist bereits registriert';
      return;
    }
    
    // Wenn alle Validierungen erfolgreich sind
    emit('next');
  } catch (error) {
    console.error('Validierungsfehler:', error);
  }
};

const openCancelModal = () => {
  showCancelModal.value = true;
};

const closeCancelModal = () => {
  showCancelModal.value = false;
};
</script>

<template>
  <div>
    <div class="cancel-button-container">
      <button 
        type="button" 
        class="btn-cancel"
        @click="openCancelModal"
      >
        ✕
      </button>
    </div>

    <form @submit.prevent="handleNext" novalidate>
      <div class="form-group">
        <label for="spielername">Spielername *</label>
        <div class="input-container">
          <div class="input-wrapper">
            <input
              type="text"
              id="spielername"
              v-model="modelValue.spielername"
              @input="handleInput('spielername')"
              @blur="handleInput('spielername')"
              :class="{
                'input-error': validationErrors.spielername,
                'input-success': validationStatus.spielername.isValid
              }"
            >
            <div v-if="validationStatus.spielername.isChecking" class="input-icon">
              <span class="loading-spinner"></span>
            </div>
            <div v-else-if="validationStatus.spielername.isValid" class="input-icon success">
              <svg xmlns="http://www.w3.org/2000/svg" class="check-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="error-container">
            <span v-if="validationErrors.spielername" class="error-message">
              {{ validationErrors.spielername }}
            </span>
            <span v-else class="error-placeholder">&nbsp;</span>
          </div>
        </div>
      </div>

      <!-- Gleiches Pattern für Email -->
      <div class="form-group">
        <label for="email">E-Mail *</label>
        <div class="input-container">
          <div class="input-wrapper">
            <input
              type="email"
              id="email"
              v-model="modelValue.email"
              @input="handleInput('email')"
              @blur="handleInput('email')"
              :class="{
                'input-error': validationErrors.email,
                'input-success': validationStatus.email.isValid
              }"
            >
            <div v-if="validationStatus.email.isChecking" class="input-icon">
              <span class="loading-spinner"></span>
            </div>
            <div v-else-if="validationStatus.email.isValid" class="input-icon success">
              <svg xmlns="http://www.w3.org/2000/svg" class="check-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="error-container">
            <span v-if="validationErrors.email" class="error-message">
              {{ validationErrors.email }}
            </span>
            <span v-else class="error-placeholder">&nbsp;</span>
          </div>
        </div>
      </div>

      <!-- Password Felder ähnlich, aber ohne Success-Icon -->
      <div class="form-group">
        <label for="password">Passwort *</label>
        <div class="input-container">
          <div class="input-wrapper">
            <input
              type="password"
              id="password"
              v-model="modelValue.password"
              @input="handleInput('password')"
              @blur="handleInput('password')"
              :class="{ 'input-error': validationErrors.password }"
            >
          </div>
          <div class="error-container">
            <span v-if="validationErrors.password" class="error-message">
              {{ validationErrors.password }}
            </span>
            <span v-else class="error-placeholder">&nbsp;</span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="password_confirmation">Passwort bestätigen *</label>
        <div class="input-container">
          <div class="input-wrapper">
            <input
              type="password"
              id="password_confirmation"
              v-model="modelValue.password_confirmation"
              @input="handleInput('password_confirmation')"
              @blur="handleInput('password_confirmation')"
              :class="{ 'input-error': validationErrors.password_confirmation }"
            >
          </div>
          <div class="error-container">
            <span v-if="validationErrors.password_confirmation" class="error-message">
              {{ validationErrors.password_confirmation }}
            </span>
            <span v-else class="error-placeholder">&nbsp;</span>
          </div>
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn-primary">Weiter</button>
      </div>
      <small class="hint">* erforderlich</small>
    </form>

    <CancelRegModal 
      :is-visible="showCancelModal"
      @close="closeCancelModal"
    />
  </div>
</template>

<style scoped>
label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.input-container {
  display: flex;
  flex-direction: column;
  min-height: 80px; /* Feste Höhe um Verschiebungen zu vermeiden */
}

.input-wrapper {
  position: relative;
  width: 100%;
}

input {
  width: 100%;
  padding: 0.65rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
}

input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.input-icon.success {
  color: #10b981;
}

.check-icon {
  width: 24px; /* Größeres Icon */
  height: 24px;
  color: #10b981;
}

.loading-spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.error-container {
  min-height: 20px; /* Feste Höhe für Fehlermeldungen */
  margin-top: 0.25rem;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
}

.error-placeholder {
  display: block;
  visibility: hidden;
  height: 1.25rem;
}

.input-error {
  border-color: #ef4444;
}

.input-success {
  border-color: #10b981;
}

.button-group {
  display: flex;
  justify-content: flex-end;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.cancel-button-container {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.btn-cancel {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.btn-cancel:hover {
  color: #374151;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>