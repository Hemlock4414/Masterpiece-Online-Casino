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
    const game = await createGame(8);
    gameId.value = game.game_id;
    gameStatus.value = game.status;

    // Lade das Spiel
    const loadedGame = await getGame(gameId.value); // Initialisierung
    cards.value = loadedGame.cards;
    players.value = loadedGame.players;
  } catch (error) {
    console.error('Fehler beim Starten des Spiels:', error);
  }
};

const endGame = async () => {
  try {
    if (gameId.value) {
      const response = await stopGame(gameId.value);
      gameStatus.value = response.status || 'finished'; // Aktualisiere den Status
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

const flippedCards = ref([]);

const handleCardFlip = async (card) => {
  try {
    const updatedCard = await flipCard(gameId.value, card.card_id);

    // Lokale Karten aktualisieren
    cards.value = cards.value.map((c) =>
      c.card_id === updatedCard.card_id ? updatedCard : c
    );

    // Paarprüfung
    flippedCards.value.push(updatedCard);

    if (flippedCards.value.length === 2) {
      const [first, second] = flippedCards.value;

      if (first.group_id === second.group_id) {
        console.log('Paar gefunden!');
        // Optionale Logik: Spielerpunkte erhöhen
      } else {
        console.log('Kein Paar.');
        // Karten zurückdrehen
        setTimeout(() => {
          cards.value = cards.value.map((c) =>
            c.card_id === first.card_id || c.card_id === second.card_id
              ? { ...c, is_flipped: false }
              : c
          );
        }, 1000);
      }

      flippedCards.value = [];
    }
  } catch (error) {
    console.error('Fehler beim Kartenflip:', error);
  }
};

console.log('MemoryView loaded!');
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
