<script setup>
import { ref, onMounted } from 'vue';
import MemoryGrid from '../components/MemoryGrid.vue';
import { createGame, getGame, stopGame, flipCard, startGame as startGameAPI } from '../services/MemoryService';

const gameId = ref(null);
const gameStatus = ref(null);
const players = ref([]);
const cards = ref([]);
const flippedCards = ref([]);
const currentPlayer = ref(null);
const isProcessingMove = ref(false);

const createNewGame = async () => {
  try {
    // Standardkonfiguration: 8 Paare
    const game = await createGame(8);
    gameId.value = game.game_id;
    gameStatus.value = game.status;
    cards.value = game.cards;
    players.value = game.players;
    currentPlayer.value = players.value[0];
    console.log('New game created:', game);
  } catch (error) {
    console.error('Fehler beim Erstellen des Spiels:', error);
  }
};

const handleGameStart = async () => {
  try {
    if (!gameId.value) return;

    console.log('Starting game with ID:', gameId.value);
    const response = await startGameAPI(gameId.value);
    
    if (response.game) {
      cards.value = response.game.cards;
      players.value = response.game.players;
      gameStatus.value = response.game.status;
      currentPlayer.value = players.value[0];
    }
  } catch (error) {
    console.error('Fehler beim Starten des Spiels:', error);
    alert(error.response?.data?.error || 'Fehler beim Starten des Spiels');
  }
};

const endGame = async () => {
  try {
    if (gameId.value) {
      const response = await stopGame(gameId.value);
      gameStatus.value = response.status || 'finished';
      
      // Optional: Zeige stattdessen eine Statusmeldung im UI
      if (players.value.length > 0) {
        const winner = players.value.reduce((prev, curr) => 
          (prev.pivot?.player_score || 0) > (curr.pivot?.player_score || 0) ? prev : curr
        );
        // Hier könnte man eine UI-Komponente für das Spielende anzeigen
      }
    }
  } catch (error) {
    console.error('Fehler beim Beenden des Spiels:', error);
  }
};

const switchPlayer = () => {
  const currentIndex = players.value.findIndex(p => p.player_id === currentPlayer.value.player_id);
  currentPlayer.value = players.value[(currentIndex + 1) % players.value.length];
};

const updateGameState = async () => {
  try {
    const gameData = await getGame(gameId.value);
    cards.value = gameData.cards;
    players.value = gameData.players;
  } catch (error) {
    console.error('Fehler beim Aktualisieren des Spielstatus:', error);
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

    // Wenn die Karte bereits aufgedeckt ist
    if (currentCard.is_flipped) {
      // Drehe sie direkt im Frontend zurück
      currentCard.is_flipped = false;
      return;
    }

    // Prüfe ob bereits zwei ungepaarte Karten aufgedeckt sind
    const flippedUnmatched = cards.value.filter(c => 
      c.is_flipped && !c.is_matched
    );

    if (flippedUnmatched.length >= 2) {
      // Drehe alle ungepaarten Karten zurück
      await resetUnmatchedCards(gameId.value);
      cards.value = cards.value.map(c => {
        if (!c.is_matched) {
          return { ...c, is_flipped: false };
        }
        return c;
      });
      flippedCards.value = [];
      return;
    }

    isProcessingMove.value = true;

    // Optimistisches UI-Update
    currentCard.is_flipped = true;

    try {
      const response = await flipCard(
        gameId.value, 
        card.card_id,
        currentPlayer.value.player_id
      );

      // Rest der Logik für Matching usw.
      if (flippedCards.value.length < 2) {
        flippedCards.value.push(currentCard);
      }

      if (flippedCards.value.length === 2) {
        await checkMatch();
      }

    } catch (error) {
      // Bei Fehler den optimistischen Update rückgängig machen
      currentCard.is_flipped = false;
      console.error('Fehler beim Kartenflip:', error);
    }

  } finally {
    isProcessingMove.value = false;
  }
};

/* const handleCardFlip = async (card) => {
  
  if (
    isProcessingMove.value || 
    gameStatus.value !== 'in_progress' ||
    card.is_flipped ||  // Prüfe vor API-Aufruf
    card.is_matched
  ) {
    return;
  }

  if (isProcessingMove.value || gameStatus.value !== 'in_progress') {
    return;
  }

  try {
    const currentCard = cards.value.find(c => c.card_id === card.card_id);
    
    // Prüfe ob die Karte bereits gematcht ist
    if (currentCard.is_matched) {
      return;
    }

    // Prüfe ob bereits zwei ungepaarte Karten aufgedeckt sind
    const flippedUnmatched = cards.value.filter(c => 
      c.is_flipped && !c.is_matched
    );

    if (flippedUnmatched.length >= 2) {
      // Drehe alle ungepaarten Karten zurück
      await resetUnmatchedCards(gameId.value);
      cards.value = cards.value.map(c => {
        if (!c.is_matched) {
          return { ...c, is_flipped: false };
        }
        return c;
      });
      flippedCards.value = [];
      // Wichtig: Hier return, damit der aktuelle Klick nach dem Zurücksetzen
      // als neue Aktion behandelt wird
      return;
    }

    // Prüfe ob die aktuelle Karte bereits aufgedeckt ist
    if (currentCard.is_flipped) {
      return;
    }

    isProcessingMove.value = true;

    const response = await flipCard(
      gameId.value, 
      card.card_id,
      currentPlayer.value.player_id
    );

    // Aktualisiere die Karte im State
    cards.value = cards.value.map(c => 
      c.card_id === response.card.card_id ? response.card : c
    );

    // Füge die neue Karte zu den aufgedeckten Karten hinzu
    flippedCards.value.push(response.card);

    // Wenn zwei Karten aufgedeckt sind, prüfe auf ein Paar
    if (flippedCards.value.length === 2) {
      const [first, second] = flippedCards.value;
      
      // Warte kurz, damit der Spieler die Karten sehen kann
      await new Promise(resolve => setTimeout(resolve, 1000));

      if (first.group_id === second.group_id) {
        // Paar gefunden - markiere Karten als gematcht
        cards.value = cards.value.map(c => {
          if (c.card_id === first.card_id || c.card_id === second.card_id) {
            return { ...c, is_matched: true };
          }
          return c;
        });

        // Prüfe auf Spielende
        if (cards.value.every(c => c.is_matched)) {
          await endGame();
        }
      } else {
        // Kein Paar - drehe Karten zurück
        await new Promise(resolve => setTimeout(resolve, 500));
        await resetUnmatchedCards(gameId.value);
        cards.value = cards.value.map(c => {
          if (c.card_id === first.card_id || c.card_id === second.card_id) {
            return { ...c, is_flipped: false };
          }
          return c;
        });
        switchPlayer();
      }
      
      // Leere das Array der aufgedeckten Karten
      flippedCards.value = [];
    }

  } catch (error) {
    if (error.response?.data?.error === 'Karte ist bereits aufgedeckt') {
      console.log('Karte bereits aufgedeckt');
    } else {
      console.error('Fehler beim Kartenflip:', error);
    }
  } finally {
    isProcessingMove.value = false;
  }
}; */

const resetUnmatchedCards = async () => {
  try {
    const flippedUnmatched = cards.value.filter(c => c.is_flipped && !c.is_matched);
    if (flippedUnmatched.length > 0) {
      await Promise.all(flippedUnmatched.map(card => {
        card.is_flipped = false;
      }));
      flippedCards.value = [];
    }
  } catch (error) {
    console.error('Fehler beim Zurücksetzen der Karten:', error);
  }
};

const checkMatch = async () => {
  const [first, second] = flippedCards.value;
  
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  if (first.group_id === second.group_id) {
    // Paar gefunden
    await updateGameState();
    if (cards.value.every(c => c.is_matched)) {
      await endGame();
    }
  } else {
    await resetUnmatchedCards();
    switchPlayer();
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
      <p>Status: {{ gameStatus }}</p>
      
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

    <MemoryGrid 
      v-if="gameStatus === 'in_progress' && cards.length" 
      :cards="cards" 
      @flipCard="handleCardFlip" 
    />

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