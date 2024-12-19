<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import MemoryEndModal from '../components/MemoryEndModal.vue';
import { createGame, stopGame, updateMatchedCards, startGame as startGameAPI } from '../services/MemoryService';
import { useAuthStore } from '../store/AuthStore';

const authStore = useAuthStore();
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

// Spieler-ID aus der Session erhalten
const getStoredGuestId = () => {
  return sessionStorage.getItem('memoryGuestId');
};

const resetGameState = () => {
  // Gastspieler-ID speichern, bevor wir zurücksetzen
  const guestId = getStoredGuestId();
  gameId.value = null;
  cards.value = [];
  flippedCards.value = [];
  roundCount.value = 0;
  timer.value = 0;
  showModal.value = false;
  gameStatus.value = 'waiting';

  // Wenn es einen Gastspieler gab, ID wieder in Session speichern
  if (guestId) {
  sessionStorage.setItem('memoryGuestId', guestId);
  }
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
      description: getCustomThemeDescription(theme)
    }));
  } catch (error) {
    console.error('Fehler beim Abrufen benutzerdefinierter Themen:', error);
  }
};

// Helper Funktion für Theme Beschreibungen
const getCustomThemeDescription = (theme) => {
  const descriptions = {
    'Star Wars': '...oder so ähnlich',
    'Halloween': 'Niedliche Charaktere im Chibi-Stil',
    // Hier können weitere Custom Theme Beschreibungen hinzugefügt werden
  };
  
  return descriptions[theme] || `Benutzerdefinierte Bilder aus dem Theme ${theme}`;
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

    console.log('Raw Response cards:', JSON.stringify(response.game.cards));

    // Hier das Mapping korrigieren
    cards.value = response.game.cards.map(serverCard => {
        console.log('Card before mapping:', {
            id: serverCard.card_id,
            content: serverCard.content,
            full: serverCard
        });

        // Explizit ein neues Objekt mit allen Properties erstellen
        const mappedCard = {
            card_id: serverCard.card_id,
            game_id: serverCard.game_id,
            matched_by: serverCard.matched_by,
            group_id: serverCard.group_id,
            created_at: serverCard.created_at,
            updated_at: serverCard.updated_at,
            content: serverCard.content,
            name: serverCard.name
        };

        console.log('Card after mapping:', {
            id: mappedCard.card_id,
            content: mappedCard.content,
            full: mappedCard
        });

        return mappedCard;
    });


    players.value = response.game.players || [];
    
    const guestPlayer = players.value?.find(p => p.name?.includes('Gast'));
    if (guestPlayer && !storedGuestId) {
      sessionStorage.setItem('memoryGuestId', guestPlayer.player_id);
    }

    currentPlayer.value = players.value[0] || null;
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
      // Neues Spiel erstellen
      console.log('Starte neues Spiel');
      
      const response = await createGame({
        cards_count: selectedCardCount.value,
        theme: selectedTheme.value.id,
        guest_id: sessionStorage.getItem('memoryGuestId')
      });

      console.log('Create game response:', response);
      
      if (!response.game?.game_id) {
        throw new Error('Spiel konnte nicht erstellt werden');
      }

      // Game ID setzen
      gameId.value = response.game.game_id;
    }

    console.log('Starting game with ID:', gameId.value);
    const response = await startGameAPI(gameId.value);

    if (response.game) {
      cards.value = response.game.cards;
      players.value = response.game.players;
      gameStatus.value = response.game.status;
      currentPlayer.value = players.value[0];
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

        <!-- Player Info - Always visible -->
        <div class="player-info-section">
          <h3>Spieler</h3>
          <div class="avatar-container">
            <img 
              :src="authStore.user ? authStore.profileImage : '/storage/defaults/default-avatar.png'"
              alt="Player Avatar"
              class="player-avatar"
            />
          </div>
          <p class="player-name">{{ currentPlayer?.name || 'Nicht angemeldet' }}</p>
        </div>

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
            <div class="timer-section">
              <div class="timer">
                <span>Zeit: {{ Math.floor(timer / 60) }}:{{ String(timer % 60).padStart(2, '0') }}</span>
              </div>
              <h3 class="round-count">Runde {{ roundCount }}</h3>
            </div>
          </div>

          <div class="current-player-info">
            <p v-if="currentPlayer">Am Zug: {{ currentPlayer.name }}</p>
          </div>

          <div class="game-info">
            <div class="player-list">
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
          </div>

          <MemoryGrid 
            v-if="cards.length"
            :cards="cards"
            :flippedCards="flippedCards"
            @flipCard="handleCardFlip"
          />

          <!-- Buttons unter dem Spielfeld -->
          <div class="bottom-controls">
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

.player-info-section {
  text-align: center;
  margin: 20px 0;
}

.avatar-container {
  margin: 10px auto;
}

.player-avatar {
  width: 120px;
  height: 120px;
  border-radius: 25%;
  overflow: hidden;
  border: 2px solid #909090;
  object-fit: cover;
}

.player-name {
  margin-top: 10px;
  font-weight: bold;
}

.current-player-info {
  text-align: center;
  margin: 15px 0;
  font-size: 1.2em;
  font-weight: bold;
}

.timer-section {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.round-count {
  margin-top: 5px;
  color: #666;
}

.bottom-controls {
  margin-top: 20px;
  display: flex;
  justify-content: center;
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