<script setup>
import { ref, onMounted } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import { createGame, getGame, stopGame } from '../services/MemoryService';

const gameId = ref(null);
const gameStatus = ref(null);
const players = ref([]);
const cards = ref([]);

const startNewGame = async () => {
  try {
    const game = await createGame(8); // Standard: 8 Paare
    gameId.value = game.game_id;
    gameStatus.value = game.status;

    // Karten und Spieler laden
    const loadedGame = await getGame(gameId.value);
    cards.value = loadedGame.cards;
    players.value = loadedGame.players;
  } catch (error) {
    console.error('Fehler beim Starten des Spiels:', error);
  }
};

const endGame = async () => {
  try {
    if (gameId.value) {
      await stopGame(gameId.value);
      gameStatus.value = 'finished';
      console.log('Spiel wurde beendet.');
    }
  } catch (error) {
    console.error('Fehler beim Beenden des Spiels:', error);
  }
};

// Neues Spiel starten, wenn die Komponente geladen wird
onMounted(() => {
  startNewGame();
});
</script>

<template>
  <div>
    <h1>Memory Game</h1>
    <p v-if="gameStatus">Spielstatus: {{ gameStatus }}</p>

    <button v-if="gameStatus !== 'finished'" @click="endGame">Spiel Beenden</button>
    <button v-if="gameStatus === 'finished'" @click="startNewGame">Neues Spiel</button>

    <!-- Memory Grid für Kartenanzeige -->
    <MemoryGrid v-if="cards.length" :cards="cards" />

    <!-- Spielerinformationen anzeigen -->
    <div v-if="players.length">
      <h2>Spieler</h2>
      <ul>
        <li v-for="player in players" :key="player.id">
          Spieler {{ player.id }}: {{ player.score }} Punkte
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
button {
  margin: 10px;
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}

h1, h2 {
  font-family: Arial, sans-serif;
}
</style>
