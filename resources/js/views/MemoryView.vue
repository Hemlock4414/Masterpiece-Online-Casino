<script setup>
import { ref, onMounted } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import { createGame, stopGame, updateMatchedCards, startGame as startGameAPI } from '../services/MemoryService';

const gameId = ref(null);
const gameStatus = ref(null);
const players = ref([]);
const cards = ref([]);
const flippedCards = ref([]);
const currentPlayer = ref(null);
const isProcessingMove = ref(false);

const createNewGame = async () => {
  try {
    console.log('Erstelle neues Spiel...'); // Debug
    const game = await createGame(8);
    console.log('Spiel erfolgreich erstellt:', game); // Debug
    gameId.value = game.game_id;
    gameStatus.value = game.status;
    cards.value = game.cards;
    players.value = game.players;
    currentPlayer.value = players.value[0];
    flippedCards.value = [];
  } catch (error) {
    console.error('Fehler beim Erstellen des Spiels:', error);
  }
};

const handleGameStart = async () => {
  try {
    if (!gameId.value) return;

    const response = await startGameAPI(gameId.value);
    
    if (response.game) {
      cards.value = response.game.cards;
      players.value = response.game.players;
      gameStatus.value = response.game.status;
      currentPlayer.value = players.value[0];
    }
  } catch (error) {
    console.error('Fehler beim Starten des Spiels:', error);
  }
};

const endGame = async () => {
  try {
    if (gameId.value) {
      const response = await stopGame(gameId.value);
      gameStatus.value = response.status || 'finished';
    }
  } catch (error) {
    console.error('Fehler beim Beenden des Spiels:', error);
  }
};

const switchPlayer = () => {
  const currentIndex = players.value.findIndex(p => p.player_id === currentPlayer.value.player_id);
  currentPlayer.value = players.value[(currentIndex + 1) % players.value.length];
};

const handleCardFlip = async (card) => {
  if (isProcessingMove.value || gameStatus.value !== 'in_progress') {
    return;
  }

  try {
    const currentCard = cards.value.find(c => c.card_id === card.card_id);
    
    if (currentCard.is_matched) {
      return;
    }

    if (flippedCards.value.find(c => c.card_id === currentCard.card_id)) {
      return;
    }

    flippedCards.value.push(currentCard);
    console.log('Karte aufgedeckt:', currentCard.group_id);

    if (flippedCards.value.length === 2) {
      const [first, second] = flippedCards.value;
      
      console.log('Prüfe Match:', first.group_id, second.group_id);

      await new Promise(resolve => setTimeout(resolve, 1000));

      if (first.group_id === second.group_id) {
        console.log('Match gefunden!');
        // Aktualisiere matched_by im Backend
        await updateMatchedCards(
          gameId.value,
          [first.card_id, second.card_id],
          currentPlayer.value.player_id
        );

        // Update Frontend-Status
        cards.value = cards.value.map(c => {
          if (c.card_id === first.card_id || c.card_id === second.card_id) {
            return { 
              ...c, 
              is_matched: true,
              matched_by: currentPlayer.value.player_id
            };
          }
          return c;
        });

        // Aktualisiere den Score des Spielers
        if (currentPlayer.value) {
          currentPlayer.value.pivot.player_score = (currentPlayer.value.pivot.player_score || 0) + 1;
        }

        if (cards.value.every(c => c.is_matched)) {
          console.log('Spiel beendet!');
          await endGame();
        }
      } else {
        console.log('Kein Match - drehe Karten zurück');
        await new Promise(resolve => setTimeout(resolve, 1000));
        cards.value = cards.value.map(c => {
          if (c.card_id === first.card_id || c.card_id === second.card_id) {
            return { ...c, isFlipped: false };
          }
          return c;
        });
        switchPlayer();
      }

      flippedCards.value = [];
    }

  } catch (error) {
    console.error('Fehler beim Kartenflip:', error);
  }
};

onMounted(async () => {
  await createNewGame();
});
</script>

<template>
  <div class="memory-game">
    <h1>Memory</h1>

    <div class="game-status">
      <p>Spielstatus: {{ gameStatus }}</p>
      
      <div class="player-info" v-if="currentPlayer">
        <p>Am Zug: {{ currentPlayer.name }}</p>
      </div>

      <div class="game-controls">
        <button 
          v-if="gameStatus === 'waiting'" 
          @click="handleGameStart"
          class="btn-primary"
        >
          Spiel Starten
        </button>

        <button 
          v-if="gameStatus === 'in_progress'" 
          @click="endGame"
          class="btn-secondary"
        >
          Spiel Beenden
        </button>

        <button 
          v-if="gameStatus === 'finished'" 
          @click="createNewGame"
          class="btn-primary"
        >
          Neues Spiel
        </button>
      </div>
    </div>

    <div v-if="players.length" class="player-list">
      <h2>Spieler</h2>
      <ul>
        <li 
          v-for="player in players" 
          :key="player.player_id"
          :class="{ 'active-player': currentPlayer?.player_id === player.player_id }"
        >
          {{ player.name }}: {{ player.pivot?.player_score ?? 0 }} Punkte
        </li>
      </ul>
    </div>

    <MemoryGrid 
      v-if="gameStatus === 'in_progress' && cards.length" 
      :cards="cards"
      :flippedCards="flippedCards"
      @flipCard="handleCardFlip" 
    />

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

.player-info {
  margin: 10px 0;
  font-weight: bold;
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

.player-list ul {
  list-style: none;
  padding: 0;
}

.active-player {
  color: #4CAF50;
  font-weight: bold;
}

h1, h2 {
  text-align: center;
  font-family: Arial, sans-serif;
}

@media (max-width: 605px) {
  .memory-game {
    padding: 10px;
  }
}

@media (max-width: 400px) {
  .memory-game {
    padding: 0px;
  }
}
</style>