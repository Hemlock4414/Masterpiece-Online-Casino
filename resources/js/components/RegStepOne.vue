<script setup>
import { defineProps, defineEmits, ref } from 'vue';
import CancelRegModal from '../components/CancelRegModal.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'next']);

// Variable für Modal-Steuerung
const showCancelModal = ref(false);

const handleNext = () => {
  emit('next');
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
    <form @submit.prevent="handleNext">
      <div class="form-group">
        <label for="spielername">Spielername *</label>
        <input
          type="text"
          id="spielername"
          v-model="modelValue.spielername"
          required
        >
      </div>
      <div class="form-group">
        <label for="email">E-Mail *</label>
        <input
          type="email"
          id="email"
          v-model="modelValue.email"
          required
        >
      </div>
      <div class="form-group">
        <label for="password">Passwort *</label>
        <input
          type="password"
          id="password"
          v-model="modelValue.password"
          required
        >
      </div>
      <div class="form-group">
        <label for="password_confirmation">Passwort bestätigen *</label>
        <input
          type="password"
          id="password_confirmation"
          v-model="modelValue.password_confirmation"
          required
        >
      </div>
      <div class="button-group">
        <button type="submit" class="btn-primary">Weiter</button>
      </div>
      <small class="hint">* Erforderlich</small>
    </form>

    <CancelRegModal 
      :is-visible="showCancelModal"
      @close="closeCancelModal"
    />
  </div>  

</template>

<style scoped>
.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
}

input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.button-group {
  display: flex;
  justify-content: flex-end;
  margin-top: 2rem;
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
  margin-top: 1.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

/* Styles Cancel Button */
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
</style>