<template>
    <div class="card" :class="{ flipped: card.flipped }" @click="flipCard">
      <div v-if="card.flipped">{{ card.value }}</div>
      <div v-else>?</div>
    </div>
  </template>
  
  <script setup>
  import { defineProps } from 'vue';
  
  const props = defineProps({
    card: Object,
  });
  
  const flipCard = () => {
    card.flipped = !card.flipped; // Einfacher Flip
    emit('flip', card);
  };
  </script>
  
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
  