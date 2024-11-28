<script setup>
import { ref, onMounted } from 'vue';
import { createGame, getCards, flipCard } from '../services/MemoryService';
import MemoryCard from './MemoryCard.vue';

const cards = ref([]);
const gameId = ref(null);

onMounted(async () => {
  // Neues Spiel erstellen
  const game = await createGame(8); // Standard: 8 Paare
  gameId.value = game.game_id;

  // Karten für das Spiel laden
  cards.value = await getCards(gameId.value);
});

const handleCardFlip = async (card) => {
  try {
    const updatedCard = await flipCard(gameId.value, card.id);

    // Lokale Karten aktualisieren
    cards.value = cards.value.map((c) =>
      c.id === updatedCard.id ? updatedCard : c
    );
  } catch (error) {
    console.error('Fehler beim Flip:', error);
  }
};
</script>

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

<style>
.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
</style>
