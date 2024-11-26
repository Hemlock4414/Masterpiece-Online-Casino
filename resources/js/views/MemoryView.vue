<script setup>

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