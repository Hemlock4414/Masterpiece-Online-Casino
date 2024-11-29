<script setup>
import { ref, onMounted } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import { createGame, getGame, stopGame, flipCard, startGame as startGameAPI } from '../services/MemoryService';

const gameId = ref(null);
const gameStatus = ref(null);
const players = ref([]);
const cards = ref([]);
const flippedCards = ref([]);

const createNewGame = async () => {
  try {
    // Nur erstellen wenn noch kein Spiel existiert
    if (!gameId.value) {
      const game = await createGame(8);
      gameId.value = game.game_id;
      gameStatus.value = game.status;
      console.log('New game created:', game);
    }
  } catch (error) {
    console.error('Fehler beim Erstellen des Spiels:', error);
  }
};

const handleGameStart = async () => {
  try {
    if (!gameId.value) {
      console.error('Keine Game ID vorhanden');
      return;
    }

    console.log('Starting game with ID:', gameId.value);
    const response = await startGameAPI(gameId.value);
    console.log('Start game response:', response);
    
    if (response.game) {
      cards.value = response.game.cards;
      players.value = response.game.players;
      gameStatus.value = response.game.status;
    }
  } catch (error) {
    console.error('Fehler beim Starten des Spiels:', error);
    console.error('Server Antwort:', error.response?.data);
    alert(error.response?.data?.error || 'Fehler beim Starten des Spiels');
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
    if (gameStatus.value !== 'in_progress') return;
    
    // Prüfe lokalen Status der Karte
    const currentCard = cards.value.find(c => c.card_id === card.card_id);
    if (!currentCard || currentCard.is_flipped || currentCard.is_matched) {
      return;
    }
    
    // Prüfe ob bereits zwei Karten aufgedeckt sind
    const currentlyFlipped = cards.value.filter(c => c.is_flipped && !c.is_matched);
    if (currentlyFlipped.length >= 2) {
      return;
    }

    console.log('Attempting to flip card:', { 
      gameId: gameId.value, 
      cardId: card.card_id,
      card: currentCard 
    });

    try {
      const updatedCard = await flipCard(gameId.value, card.card_id);
      
      // Aktualisiere die Karte im lokalen State
      cards.value = cards.value.map((c) =>
        c.card_id === updatedCard.card.card_id ? updatedCard.card : c
      );

      // Füge die Karte zu flippedCards hinzu
      flippedCards.value.push(updatedCard.card);

      // Prüfe auf Paar wenn zwei Karten aufgedeckt sind
      if (flippedCards.value.length === 2) {
        const [first, second] = flippedCards.value;

        if (first.group_id === second.group_id) {
          // Paar gefunden
          cards.value = cards.value.map((c) =>
            c.card_id === first.card_id || c.card_id === second.card_id
              ? { ...c, is_matched: true }
              : c
          );
        } else {
          // Kein Paar - Karten zurückdrehen
          setTimeout(() => {
            cards.value = cards.value.map((c) =>
              c.card_id === first.card_id || c.card_id === second.card_id
                ? { ...c, is_flipped: false }
                : c
            );
          }, 1000);
        }

        // Zurücksetzen für nächsten Versuch
        setTimeout(() => {
          flippedCards.value = [];
        }, 1000);
      }
    } catch (error) {
      console.error('Fehler beim Kartenflip:', error);
      // Bei einem Fehler Karte im lokalen State zurücksetzen
      cards.value = cards.value.map((c) =>
        c.card_id === card.card_id ? { ...c, is_flipped: false } : c
      );
    }
  } catch (error) {
    console.error('Fehler in handleCardFlip:', error);
  }
};

onMounted(async () => {
  await createNewGame();
});
</script>

<template>
  <div class="memory-game">
    <h1>Memory Game</h1>
    
    <div class="game-status">
      <p>Spielstatus: {{ gameStatus }}</p>
      
      <!-- Buttons basierend auf Spielstatus -->
      <div class="game-controls">
        <!-- Zeige Start-Button nur im waiting Status -->
        <button 
          v-if="gameStatus === 'waiting'" 
          @click="handleGameStart"
          class="btn-primary"
        >
          Spiel Starten
        </button>

        <!-- Zeige Ende-Button nur während des Spiels -->
        <button 
          v-if="gameStatus === 'in_progress'" 
          @click="endGame"
          class="btn-secondary"
        >
          Spiel Beenden
        </button>

        <!-- Zeige Neustart-Button nur wenn Spiel beendet -->
        <button 
          v-if="gameStatus === 'finished'" 
          @click="createNewGame"
          class="btn-primary"
        >
          Neues Spiel
        </button>
      </div>
    </div>

    <!-- Zeige Spielfeld nur wenn Spiel läuft -->
    <MemoryGrid 
      v-if="gameStatus === 'in_progress' && cards.length" 
      :cards="cards" 
      @flipCard="handleCardFlip" 
    />

    <!-- Spielerliste -->
    <div v-if="players && players.length" class="player-list">
        <h2>Spieler</h2>
        <ul>
            <li v-for="player in players" :key="player.player_id">
                {{ player.name }}: {{ player.pivot?.player_score ?? 0 }} Punkte
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

.game-status {
  margin: 20px 0;
  text-align: center;
}

.game-controls {
  margin: 20px 0;
  display: flex;
  justify-content: center;
  gap: 10px;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-secondary {
  background-color: #f44336;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  opacity: 0.9;
}

.player-list {
  margin-top: 20px;
}

h1, h2 {
  text-align: center;
  font-family: Arial, sans-serif;
}
</style>