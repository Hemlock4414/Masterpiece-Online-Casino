<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import MemoryEndModal from '../components/MemoryEndModal.vue';
import { createGame, stopGame, updateMatchedCards, startGame as startGameAPI } from '../services/MemoryService';

const gameId = ref(null);
const gameStatus = ref(null);
const players = ref([]);
const cards = ref([]);
const flippedCards = ref([]);
const currentPlayer = ref(null);
const isProcessingMove = ref(false);
const roundCount = ref(0);
const timer = ref(0); 

const showModal = ref(false);
const gameResult = ref({
  points: 0,
  rounds: 0,
  time: 0,
});

let timerInterval = null;

const createNewGame = async () => {
  try {
    console.log('Erstelle neues Spiel...'); // Debug
    const game = await createGame(8);
    console.log('Spiel erfolgreich erstellt:', game); // Debug
    gameId.value = game.game_id;
    gameStatus.value = 'waiting'; // Setze den Status auf "waiting", bis "Spiel Starten" geklickt wird
    cards.value = game.cards;
    players.value = game.players;
    currentPlayer.value = players.value[0];
    flippedCards.value = [];
    roundCount.value = 0;
    timer.value = 0;

    // Modal schließen
    showModal.value = false;
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

      // Timer starten
      startTimer();
    }
  } catch (error) {
    console.error('Fehler beim Starten des Spiels:', error);
  }
};

const startTimer = () => {
  if (timerInterval) return; // Timer läuft bereits
  timer.value = 0; // Timer zurücksetzen
  timerInterval = setInterval(() => {
    timer.value += 1;
  }, 1000);
};

const stopTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }
};

const endGame = async () => {
  try {
    if (gameId.value) {
      const response = await stopGame(gameId.value);
      gameStatus.value = response.status || 'finished';

      stopTimer();

      if (gameStatus.value === 'finished') {
        showModal.value = true;
        gameResult.value = {
          points: players.value.reduce((acc, player) => acc + (player.pivot?.player_score ?? 0), 0),
          rounds: roundCount.value,
          time: timer.value,
        };
      }
    }
  } catch (error) {
    console.error('Fehler beim Beenden des Spiels:', error);
  }
};

const abortGame = async () => {
  try {
    if (!gameId.value) return;
    
    // Zuerst den Timer stoppen
    stopTimer();
    
    // Status aktualisieren
    gameStatus.value = 'aborted';
    
    // Optional: API-Aufruf um Backend zu informieren
    await stopGame(gameId.value);
    
    // Spielzustand zurücksetzen
    gameId.value = null;
    cards.value = [];
    players.value = [];
    currentPlayer.value = null;
    resetGameState();
    
    console.log('Spiel wurde erfolgreich abgebrochen');
  } catch (error) {
    console.error('Fehler beim Abbrechen des Spiels:', error);
  }
};

// Basis-Funktion zum Zurücksetzen des Spielstatus
const resetGameState = () => {
  flippedCards.value = [];
  roundCount.value = 0;
  timer.value = 0;
  showModal.value = false;
};

const switchPlayer = () => {
  const currentIndex = players.value.findIndex(p => p.player_id === currentPlayer.value.player_id);
  currentPlayer.value = players.value[(currentIndex + 1) % players.value.length];
};

const updatePlayerScore = async (playerId, score) => {
  try {
    await updatePlayerScore(gameId.value, playerId, score);
    // Aktualisiere den lokalen State erst nach erfolgreicher API-Antwort
    currentPlayer.value = {
      ...currentPlayer.value,
      pivot: {
        ...currentPlayer.value.pivot,
        player_score: score
      }
    };
  } catch (error) {
    handleError(error, 'Fehler beim Aktualisieren der Punkte');
  }
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
      // Erhöhe den Rundenzähler
      roundCount.value += 1;

      const [first, second] = flippedCards.value;

      console.log('Prüfe Match:', first.group_id, second.group_id);

      await new Promise(resolve => setTimeout(resolve, 700));

      if (first.group_id === second.group_id) {
        console.log('Match gefunden!');
        await updateMatchedCards(
          gameId.value,
          [first.card_id, second.card_id],
          currentPlayer.value.player_id
        );

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

        if (currentPlayer.value) {
          currentPlayer.value.pivot.player_score = (currentPlayer.value.pivot.player_score || 0) + 1;
        }

        if (cards.value.every(c => c.is_matched)) {
          console.log('Spiel beendet!');
          await endGame();
        }
      } else {
        console.log('Kein Match - drehe Karten zurück');
        await new Promise(resolve => setTimeout(resolve, 500));
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

// Timer stoppen, wenn die Komponente zerstört wird
onUnmounted(() => {
  stopTimer();
});
</script>

<template>

  <div class="memory-container">

    <div class="memory-game">

      <div class="header">
        <h1>Memory</h1>
        <div class="timer">
          <h2>Timer</h2>
          <p>{{ Math.floor(timer / 60) }}:{{ String(timer % 60).padStart(2, '0') }}</p>
        </div>
      </div>

      <div class="game-status">
        <p>Spielstatus: {{ gameStatus }}</p>
      </div>

      <div class="game-layout">
        <div class="player-list">
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

        <div class="player-info" v-if="currentPlayer">
          <p>Am Zug: {{ currentPlayer.name }}</p>
        </div>

        <div class="round-info">
          <h2>Runden</h2>
          <p>{{ roundCount }}</p>
        </div>
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
          @click="abortGame"
          class="btn-secondary"
        >
          Spiel abbrechen
        </button>

        <button 
          v-if="gameStatus === 'finished'" 
          @click="createNewGame"
          class="btn-primary"
        >
          Neues Spiel
        </button>
      </div>

      <MemoryGrid 
        v-if="gameStatus === 'in_progress' && cards.length" 
        :cards="cards"
        :flippedCards="flippedCards"
        @flipCard="handleCardFlip" 
      />

      <MemoryEndModal
      v-if="showModal"
      :title="'Spiel beendet!'"
      :points="gameResult.points"
      :rounds="gameResult.rounds"
      :time="gameResult.time"
      :on-new-game="createNewGame"
      :on-go-to-home="() => $router.push('/')"
      />

    </div>
  </div>

</template>

<style scoped>
.memory-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.memory-game {
  max-width: 100%;
}

.header {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  text-align: center;
  position: relative;
}

.header h1 {
  flex-grow: 1; /* Erweitert den Platz des h1 für die Zentrierung */
}

.timer {
  position: absolute;
  right: 0; /* Positioniert den Timer ganz rechts */
  top: 50%; /* Vertikal zentriert den Timer */
  transform: translateY(-50%); /* Korrigiert die vertikale Position */
  text-align: right;
}

.timer h2 {
  font-size: 1.2em;
}

.timer p {
  font-size: 1.5em;
  font-weight: bold;
}

.game-layout {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  text-align: center;
  margin: 20px 0;
}

.game-status {
  margin: 20px 0;
  text-align: center;
}

.player-list, .player-info, .round-info {
  width: 30%;
}

.player-list ul {
  list-style: none;
  padding: 0;
}

.active-player {
  color: #4CAF50;
  font-weight: bold;
}

.round-info {
  text-align: center;
}

h1, h2 {
  text-align: center;
  font-family: Arial, sans-serif;
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

@media (max-width: 605px) {
  .memory-container {
    padding: 10px;
    margin-top: 20px;
  }
  .header {
    flex-direction: column;
    align-items: center;
    margin-top: 20px;
  }
  .timer {
    margin-top: 30px;
    position: static; /* Entfernt absolute Positionierung */
    transform: none; /* Entfernt die vertikale Korrektur */
    margin-top: 20px; /* Abstand zwischen h1 und Timer */
    text-align: center;
  }
  .game-layout {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .player-list, .player-info, .round-info {
    width: 100%;
    margin-bottom: 20px;
  }
}

@media (max-width: 400px) {
  .memory-container {
    padding: 0px;
  }
}
</style>