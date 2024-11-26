<template>
    <div class="grid">
      <MemoryCard
        v-for="card in cards"
        :key="card.id"
        :card="card"
        @flip="handleCardFlip"
      />
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { getCards } from '../services/api';
  import MemoryCard from './MemoryCard.vue';
  
  const cards = ref([]);
  
  onMounted(async () => {
    cards.value = await getCards();
  });
  
  const handleCardFlip = (card) => {
    console.log('Card flipped:', card);
    // Logik für Memory (Paarprüfung) kommt später
  };
  </script>
  
  <style>
  .grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }
  </style>
  