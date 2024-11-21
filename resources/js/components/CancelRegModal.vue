<template>
    <div 
      v-if="isVisible" 
      class="modal-overlay"
      @click.self="closeModal"
    >
      <div class="modal-content">
        <div class="modal-body">
            <h2>Moment!</h2><br />
            <br />
            <p>Möchten Sie Ihre Registrierung wirklich abbrechen?</p>
            <p>Verpassen Sie nicht die vielen Vorteile die eine Anmeldung beinhalten,</p>
            <p>wie zum Beispiel den</p>
            <h2>Willkommensbonus!</h2><br /> 
            <br /> 
        </div>
        <div class="modal-actions">
          <button 
            @click="closeModal" 
            class="btn-continue"
          >
            FORTFAHREN
          </button>
          <a 
            href="/" 
            class="btn-cancel"
          >
            Nicht jetzt
          </a>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { defineProps, defineEmits } from 'vue';
  
  const props = defineProps({
    isVisible: {
      type: Boolean,
      default: false
    }
  });
  
  const emit = defineEmits(['close']);
  
  const closeModal = () => {
    emit('close');
  };
  </script>
  
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
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .modal-body p {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
  }
  
  .modal-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .btn-continue {
    background: #3b82f6;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 700;
    font-size: larger;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .btn-continue:hover {
    background: #2563eb;
  }
  
  .btn-cancel {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
  }
  
  .btn-cancel:hover {
    text-decoration: underline;
  }
  </style>