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

const props = defineProps({
  cards: Array,
});

const emit = defineEmits(['flipCard']);
</script>

<template>
  <div class="grid">
    <MemoryCard
      v-for="card in cards"
      :key="card.card_id""
      :card="card"
      @flip="emit('flipCard', card)"
    />
  </div>

  <MemoryGrid v-if="cards.length" :cards="cards" @flipCard="handleCardFlip" />

</template>

<style>
.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
</style>
