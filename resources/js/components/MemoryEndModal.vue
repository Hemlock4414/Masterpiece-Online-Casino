<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  points: {
    type: Number,
    default: null,
  },
  rounds: {
    type: Number,
    default: null,
  },
  time: {
    type: Number,
    default: null,
  },
  onNewGame: {
    type: Function,
    required: true,
  },
  onGoToHome: {
    type: Function,
    required: true,
  },
});
</script>

<template>
    <div class="modal">
      <div class="modal-content">
        <h2>{{ title }}</h2>
        <p v-if="points !== null">Gesamtpunkte: {{ points }}</p>
        <p v-if="rounds !== null">Runden: {{ rounds }}</p>
        <p v-if="time !== null">
          Verstrichene Zeit: {{ Math.floor(time / 60) }}:{{ String(time % 60).padStart(2, '0') }}
        </p>
        <div class="modal-actions">
          <button class="btn-primary" @click="onNewGame">Neues Spiel</button>
          <button class="btn-secondary" @click="onGoToHome">Hauptseite</button>
        </div>
      </div>
    </div>
  </template>
    
  <style scoped>
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  
  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    text-align: center;
  }
  
  .modal-content h2 {
    margin-bottom: 15px;
  }
  
  .modal-content p {
    margin: 10px 0;
  }
  
  .modal-actions {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
  }
  
  .btn-primary {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .btn-secondary {
    background-color: #f44336;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  
  button:hover {
    opacity: 0.9;
  }
  </style>
  