<script setup>
import { computed } from 'vue';
import MemoryCard from './MemoryCard.vue';

const props = defineProps({
  cards: {
    type: Array,
    required: true
  },
  flippedCards: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['flipCard']);

const gridRows = computed(() => Math.ceil(props.cards.length / 4));

const isCardFlipped = (card) => {
  return props.flippedCards.some(fc => fc.card_id === card.card_id) || Boolean(card.matched_by);
};

// Style für das Grid, basierend auf der Zeilenanzahl
const gridStyle = computed(() => ({
  'grid-template-columns': 'repeat(4, 1fr)',
  'grid-template-rows': `repeat(${gridRows.value}, 1fr)`
}));
</script>

<template>
  <div class="grid-container">
    <div class="grid" :style="gridStyle">
      <MemoryCard
        v-for="card in cards"
        :key="card.card_id"
        :card="card"
        :isFlipped="isCardFlipped(card)"
        @flip="emit('flipCard', card)"
      />
    </div>
  </div>
</template>

<style scoped>
.grid-container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.grid {
  display: grid;
  gap: 15px;
  /* Standardgröße für Tablets und Desktop */
  grid-template-columns: repeat(4, minmax(120px, 1fr));
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(4, minmax(180px, 1fr));
    gap: 25px;
  }
}

/* Responsive Breakpoints */
@media (min-width: 768px) {
  .grid {
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 20px;
  }
}

/* Für kleine Bildschirme */
@media (max-width: 580px) {
  .grid {
    grid-template-columns: repeat(4, minmax(80px, 1fr));
    gap: 10px;
    padding: 10px;
  }
}
</style>