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
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin: 20px 0;
}
</style>