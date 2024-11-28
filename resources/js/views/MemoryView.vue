<script setup>
import { ref, onMounted } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import { createGame, getGame, stopGame, flipCard } from '../services/MemoryService';

const gameId = ref(null);
const gameStatus = ref(null);
const players = ref([]);
const cards = ref([]);
const flippedCards = ref([]);

const startNewGame = async () => {
  try {
    const game = await createGame(8);
    gameId.value = game.game_id;
    gameStatus.value = game.status;

    const loadedGame = await getGame(game.game_id);
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
      gameStatus.value = response.status || 'finished';
      console.log('Spiel wurde beendet.');
    }
  } catch (error) {
    console.error('Fehler beim Beenden des Spiels:', error);
  }
};

const handleCardFlip = async (card) => {
  try {
    // Prüfen ob die Karte bereits aufgedeckt ist
    if (card.is_flipped) return;
    
    // Prüfen ob bereits zwei Karten aufgedeckt sind
    if (flippedCards.value.length >= 2) return;

    const updatedCard = await flipCard(gameId.value, card.card_id);

    // Lokale Karten aktualisieren
    cards.value = cards.value.map((c) =>
      c.card_id === updatedCard.card_id ? updatedCard : c
    );

    flippedCards.value.push(updatedCard);

    if (flippedCards.value.length === 2) {
      const [first, second] = flippedCards.value;

      if (first.group_id === second.group_id) {
        console.log('Paar gefunden!');
        // Hier könnte man die Punkte aktualisieren
      } else {
        console.log('Kein Paar.');
        setTimeout(() => {
          cards.value = cards.value.map((c) =>
            c.card_id === first.card_id || c.card_id === second.card_id
              ? { ...c, is_flipped: false }
              : c
          );
        }, 1000);
      }

      setTimeout(() => {
        flippedCards.value = [];
      }, 1000);
    }
  } catch (error) {
    console.error('Fehler beim Kartenflip:', error);
  }
};

onMounted(() => {
  startNewGame();
});
</script>

<template>
  <div class="memory-game">
    <h1>Memory Game</h1>
    <div class="game-controls">
      <p v-if="gameStatus">Spielstatus: {{ gameStatus }}</p>
      <button v-if="gameStatus !== 'finished'" @click="endGame">Spiel Beenden</button>
      <button v-if="gameStatus === 'finished'" @click="startNewGame">Neues Spiel</button>
    </div>

    <MemoryGrid 
      v-if="cards.length" 
      :cards="cards" 
      @flipCard="handleCardFlip" 
    />

    <div v-if="players.length" class="player-list">
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
.memory-game {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.game-controls {
  margin: 20px 0;
}

button {
  margin: 10px;
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}

.player-list {
  margin-top: 20px;
}

h1, h2 {
  font-family: Arial, sans-serif;
}
</style>