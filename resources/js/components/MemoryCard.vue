<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  card: Object,
});

const emit = defineEmits(['flip']);

const flipCard = () => {
  if (!card.flipped) {
    emit('flip', card);
  }
};
</script>

<template>
  <div class="card" :class="{ flipped: card.flipped }" @click="flipCard">
    <div v-if="card.flipped">{{ card.group_id }}</div>
    <div v-else>?</div>
  </div>
</template>

<style>
.card {
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: lightgray;
  border: 1px solid #ddd;
  cursor: pointer;
  user-select: none;
}

.card.flipped {
  background-color: white;
}
</style>
