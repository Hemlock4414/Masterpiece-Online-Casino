<script setup>
import MemoryCard from './MemoryCard.vue';

const props = defineProps({
  cards: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['flipCard']);
</script>

<template>
  <div class="grid">
    <MemoryCard
      v-for="card in cards"
      :key="card.card_id"
      :card="card"
      @flip="emit('flipCard', card)"
    />
  </div>
</template>

<style scoped>
.grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(120px, 1fr));
  gap: 15px;
  max-width: 800px; /* Maximale Breite des Spielfelds */
  margin: 0 auto;
  padding: 20px;
}

/* Responsive Breakpoints */
@media (min-width: 768px) {
  .grid {
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 20px;
  }
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(4, minmax(180px, 1fr));
    gap: 25px;
  }
}

/* Für kleine Bildschirme */
@media (max-width: 480px) {
  .grid {
    grid-template-columns: repeat(4, minmax(80px, 1fr));
    gap: 10px;
    padding: 10px;
  }
}
</style>