<script setup>

import MemoryCard from '../components/MemoryCard.vue';
import MemoryGrid from '../components/MemoryGrid.vue';

import { getCards } from '../services/MemoryService';

// Karten abrufen
onMounted(async () => {
  cards.value = await getCards();
});

</script>

<template>

    
</template>

<style scope>

</style>