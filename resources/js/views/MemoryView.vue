<script setup>

import MemoryCard from '../components/MemoryCard.vue';
import MemoryGrid from '../components/MemoryGrid.vue';

import { getCards } from '../services/MemoryService';

// Karten abrufen
onMounted(async () => {
  cards.value = await getCards();
});

// Neues Spiel erstellen
axios.post('/api/games/create', { pairs: 10 })
    .then(response => {
        console.log('Spiel erstellt:', response.data);
    })
    .catch(error => {
        console.error('Fehler beim Erstellen des Spiels:', error);
    });

// Spiel abrufen
axios.get('/api/games/1')
    .then(response => {
        console.log('Spiel geladen:', response.data);
    })
    .catch(error => {
        console.error('Fehler beim Laden des Spiels:', error);
    });

// Spiel beenden
axios.post('/api/games/1/stop')
    .then(response => {
        console.log('Spiel beendet:', response.data);
    })
    .catch(error => {
        console.error('Fehler beim Beenden des Spiels:', error);
    });


</script>

<template>

    
</template>

<style scope>

</style>