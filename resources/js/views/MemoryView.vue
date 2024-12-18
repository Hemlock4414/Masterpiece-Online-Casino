<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
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

const customThemes = ref([]);
const showModal = ref(false);
const gameResult = ref({
  points: 0,
  rounds: 0,
  time: 0,
});

const resetGameState = () => {
  gameId.value = null;
  cards.value = [];
  flippedCards.value = [];
  roundCount.value = 0;
  timer.value = 0;
  showModal.value = false;
  gameStatus.value = 'waiting';
};

// Spielkonfiguration
// Themen von der Memory Card Factory
const availableThemes = [
  { id: 'emojis', name: 'Emojis', description: 'Lustige Tieremojis' },
  { id: 'flags', name: 'Länderflaggen', description: 'Flaggen aus aller Welt' },
  { id: 'planets', name: 'Sonnensystem', description: 'Astronomische Entdeckungsreise' },
  { id: 'sports', name: 'Sportarten', description: 'Sportliche Emojis für aktive Spieler' }
];

// Custom Themes laden
const fetchCustomThemes = async () => {
  try {
    const response = await axios.get('/api/memory-games/custom-themes');
    customThemes.value = response.data.map(theme => ({
      id: theme,
      name: theme.charAt(0).toUpperCase() + theme.slice(1),
      description: `Benutzerdefiniertes Thema: ${theme}`
    }));
  } catch (error) {
    console.error('Fehler beim Abrufen benutzerdefinierter Themen:', error);
  }
};

const cardCounts = [12, 16, 20];
const selectedTheme = ref(availableThemes[0]);
const selectedCardCount = ref(16);

// Timer Management
let timerInterval = null;

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

const createNewGame = async () => {
  try {
    console.log('Erstelle neues Spiel mit', selectedCardCount.value, 'Karten und Thema', selectedTheme.value.id); 
    
    const storedGuestId = sessionStorage.getItem('memoryGuestId');
    
    const response = await createGame({
      cards_count: selectedCardCount.value,
      theme: selectedTheme.value.id,
      guest_id: storedGuestId ? parseInt(storedGuestId) : null
    });

    console.log('Raw Response cards:', JSON.stringify(response.game.cards)); // Alle Daten sehen

    gameId.value = response.game.game_id;
    gameStatus.value = response.game.status;
    cards.value = response.game.cards.map(card => ({
      ...card, 
    content: card.content,  // Explizit content übernehmen
    name: card.name || null       // Explizit name übernehmen  
    }));

    console.log('Cards after mapping:', cards.value.map(c => ({ 
      id: c.card_id, 
      content: c.content 
    })));

    // console.log('Processed cards:', JSON.stringify(cards.value)); // Verarbeitete Daten sehen

    // Debug-Log hinzufügen
    console.log('Server Response:', response);
    console.log('Response cards:', response.game.cards);

    // Prüfen ob die Antwort die erwartete Struktur hat
    if (!response.game) {
      throw new Error('Ungültiges Antwortformat vom Server');
    }

    // cards.value = response.game.cards;  // Hier sollten die Karten mit content ankommen
    players.value = response.game.players || [];  // Fallback zu leerem Array

    // Debug-Log für players
    console.log('Players:', players.value);
    
    const guestPlayer = players.value?.find(p => p.name?.includes('Gast'));
    if (guestPlayer && !storedGuestId) {
      sessionStorage.setItem('memoryGuestId', guestPlayer.player_id);
    }

    currentPlayer.value = players.value[0] || null;  // Fallback zu null
    flippedCards.value = [];
    roundCount.value = 0;
    timer.value = 0;
    showModal.value = false;

  } catch (error) {
    console.error('Fehler beim Erstellen des Spiels:', error);
  }
};

// Kombinierte Themes für Auswahl
const allThemes = computed(() => {
  return [...availableThemes, ...customThemes.value];
});

const handleGameStart = async () => {
  try {
    if (!gameId.value) {
      // Neues Spiel erstellen mit gewählter Konfiguration
      await createNewGame();

      if (!gameId.value) {
        throw new Error('Spiel konnte nicht erstellt werden');
      }
    }

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

// Spielende
const endGame = async () => {
  try {
    if (gameId.value) {
      const response = await stopGame(gameId.value);
      gameStatus.value = response.status || 'finished';

      stopTimer();

      if (gameStatus.value === 'finished') {
        showModal.value = true;
        gameResult.value = {
          points: players.value.reduce((acc, player) => 
            acc + (player.pivot?.player_score ?? 0), 0),
          rounds: roundCount.value,
          time: timer.value,
        };
      }
    }
  } catch (error) {
    console.error('Fehler beim Beenden des Spiels:', error);
  }
};

// Spiel neu starten
const abortGame = async () => {
  try {
    if (!gameId.value) return;
    
    stopTimer();
    await stopGame(gameId.value, 'aborted');
    
    // Spielzustand zurücksetzen aber Gast-ID behalten
    const currentGuestId = players.value.find(p => 
      p.name.includes('Gast'))?.player_id;
    
    resetGameState();
    
  } catch (error) {
    console.error('Fehler beim Abbrechen des Spiels:', error);
  }
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

// Card Management
 const handleCardFlip = async (card) => {
  if (
    isProcessingMove.value || 
    gameStatus.value !== 'in_progress' ||
    card.matched_by ||  // Geändert von is_matched
    flippedCards.value.find(c => c.card_id === card.card_id)
  ) {
    return;
  }

  try {
    const currentCard = cards.value.find(c => c.card_id === card.card_id);
    flippedCards.value.push(currentCard);
    
    if (flippedCards.value.length === 2) {
      isProcessingMove.value = true;
      roundCount.value++;
      
      const [first, second] = flippedCards.value;
      
      await new Promise(resolve => setTimeout(resolve, 700));

      if (first.group_id === second.group_id) {
        // Match gefunden
        await updateMatchedCards(
          gameId.value,
          [first.card_id, second.card_id],
          currentPlayer.value.player_id
        );

        // Update cards state
        cards.value = cards.value.map(c => 
          [first.card_id, second.card_id].includes(c.card_id)
            ? { ...c, matched_by: currentPlayer.value.player_id }
            : c
        );

        // Punktzahl erhöhen
        if (currentPlayer.value) {
          currentPlayer.value.pivot.player_score = 
            (currentPlayer.value.pivot.player_score || 0) + 1;
        }

        // Prüfen ob Spiel beendet
        if (cards.value.every(c => c.matched_by)) {
          await endGame();
        }
      } else {
        // Kein Match - Karten wieder umdrehen
        await new Promise(resolve => setTimeout(resolve, 500));
        switchPlayer();
      }

      flippedCards.value = [];
      isProcessingMove.value = false;
    }

  } catch (error) {
    console.error('Fehler beim Kartenflip:', error);
    isProcessingMove.value = false;
  }
};
onMounted(async () => {
  await fetchCustomThemes();
});

// Timer stoppen, wenn die Komponente zerstört wird
onUnmounted(() => {
  stopTimer();
});
</script>

<template>
  <main>
    <div class="memory-container">
      <div class="memory-game">
        <!-- Spielkonfiguration -->
        <div v-if="!gameId || gameStatus === 'waiting'" class="game-configuration">
          <h2>Memory Konfiguration</h2>

          <!-- Themenauswahl -->
          <div class="theme-selection">
            <h3>Thema wählen</h3>
            <div class="theme-grid">
              <div 
                v-for="theme in allThemes" 
                :key="theme.id"
                class="theme-card"
                :class="{ 'selected': selectedTheme.id === theme.id }"
                @click="selectedTheme = theme"
              >
                <h4>{{ theme.name }}</h4>
                <p>{{ theme.description }}</p>
              </div>
            </div>
          </div>
          
          <!-- Kartenanzahl -->
          <div class="card-count-selection">
            <h3>Anzahl der Karten</h3>
            <div class="card-count-buttons">
              <button 
                v-for="count in cardCounts" 
                :key="count"
                :class="{ 'selected': selectedCardCount === count }"
                @click="selectedCardCount = count"
              >
                {{ count }} Karten
              </button>
            </div>
          </div>

          <div class="game-controls">
            <button 
              @click="handleGameStart"
              class="btn-primary"
            >
              Spiel starten
            </button>
          </div>
        </div>

        <!-- Spielbereich -->
        <div v-else>
          <div class="header">
            <h1>Memory: {{ selectedTheme.name }}</h1>
            <div class="timer">
              <span>Zeit: {{ Math.floor(timer / 60) }}:{{ String(timer % 60).padStart(2, '0') }}</span>
            </div>
          </div>

          <div class="game-info">
            <div class="player-list">
              <h3>Spieler</h3>
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

            <div class="round-info">
              <h3>Runde {{ roundCount }}</h3>
              <p v-if="currentPlayer">Am Zug: {{ currentPlayer.name }}</p>
            </div>
          </div>

          <div class="game-controls">
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
            v-if="cards.length"
            :cards="cards"
            :flippedCards="flippedCards"
            @flipCard="handleCardFlip"
          />
        </div>

        <MemoryEndModal
          v-if="showModal"
          :points="gameResult.points"
          :rounds="gameResult.rounds"
          :time="gameResult.time"
          @newGame="createNewGame"
        />
      </div>
    </div>
  </main>
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

.game-configuration {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  text-align: center;
}

.theme-selection {
  margin-top: 20px;
  margin-bottom: 10px;
}

.theme-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.theme-card {
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.theme-card.selected {
  border-color: #4CAF50;
  background-color: #e8f5e9;
}

.card-count-buttons {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
}

.card-count-buttons button {
  padding: 10px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 5px;
  background-color: white;
  cursor: pointer;
}

.card-count-buttons button.selected {
  background-color: #4CAF50;
  color: white;
  border-color: #4CAF50;
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