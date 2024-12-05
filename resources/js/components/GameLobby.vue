<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { XCircle, MenuIcon } from 'lucide-vue-next';
// npm install lucide-vue-next
import { LobbyService } from '../services/LobbyService';

const isOpen = ref(true);
const onlinePlayers = ref([]);

const isChallengeModalVisible = ref(false);
const challengeMessage = ref('');
const currentLobby = ref(null);
const currentPlayer = ref(null);

const toggleSidebar = () => {
  isOpen.value = !isOpen.value;
};

// Spieler herausfordern und Einladungen verwalten
const showChallengeModal = (lobby) => {
  isChallengeModalVisible.value = true;
  challengeMessage.value = `${lobby.challenger_name} hat dich herausgefordert!`;
  currentLobby.value = lobby;
};

const acceptChallenge = async () => {
  try {
    if (currentLobby.value) {
      await LobbyService.updateLobbyStatus(currentLobby.value.lobby_id, 'accepted');
      isChallengeModalVisible.value = false;
    }
  } catch (error) {
    console.error('Fehler beim Akzeptieren der Herausforderung:', error);
  }
};

const declineChallenge = async () => {
  try {
    if (currentLobby.value) {
      await LobbyService.updateLobbyStatus(currentLobby.value.lobby_id, 'declined');
      isChallengeModalVisible.value = false;
    }
  } catch (error) {
    console.error('Fehler beim Ablehnen der Herausforderung:', error);
  }
};

const loadPlayers = async () => {
  try {
    const response = await LobbyService.getOnlinePlayers();
    onlinePlayers.value = response;
  } catch (error) {
    console.error('Fehler beim Laden der Spieler:', error.message);
  }
};

const challengePlayer = async (player) => {
  try {
    await LobbyService.challengePlayer(player.id);
    // Hier weitere Logik für die Herausforderung
  } catch (error) {
    console.error('Fehler beim Herausfordern:', error);
  }
};

const updatePlayerList = (player) => {
  const index = onlinePlayers.value.findIndex(p => p.id === player.id);

  if (index !== -1) {
    // Spieler in der Liste aktualisieren
    onlinePlayers.value[index] = player;
  } else {
    // Neuen Spieler hinzufügen
    onlinePlayers.value.push(player);
  }
};

onMounted(() => {
    currentPlayer.value = players.value.find(player => player.isCurrent);
});

// Initialisierung
onMounted(async () => {
  console.log('Versuche Verbindung zur Lobby...');

  // Spieler initial laden
  await loadPlayers();

  // WebSocket-Listener für globale Lobby-Updates
  window.Echo.channel('lobby')
    .listen('LobbyStatusUpdated', (e) => {
      if (e.lobby.challenged_id === currentPlayer.value.id) {
        showChallengeModal(e.lobby);
      }
    })
    .listen('PlayerStatusChanged', (e) => {
      console.log('Spieler Status geändert:', e);
      updatePlayerList(e);
    });

  // WebSocket-Listener für individuelle Lobby-Updates
  window.Echo.channel(`lobby.${currentPlayer.value.id}`)
    .listen('LobbyStatusUpdated', (e) => {
      if (e.lobby.challenged_id === currentPlayer.value.id) {
        showChallengeModal(e.lobby);
      }
    });

  // Spieler-Status periodisch aktualisieren
  setInterval(() => {
    LobbyService.getOnlinePlayers().then(data => {
      onlinePlayers.value = data;
    });
  }, 5000); // Alle 5 Sekunden

  // Spieler-Status auf "verfügbar" setzen
  LobbyService.updateStatus('available')
    .then(() => console.log('Status erfolgreich aktualisiert'))
    .catch(err => console.error('Fehler:', err));
});


// Aufräumen
onUnmounted(() => {
  // WebSocket-Verbindung aufräumen
  window.Echo.leave('lobby');
});
</script>

<template>
  <div class="lobby-container" :class="{ 'closed': !isOpen }">
    <button @click="toggleSidebar" class="toggle-btn">
      <MenuIcon v-if="!isOpen" />
      <XCircle v-else />
    </button>
    
    <div class="lobby-content" v-if="isOpen">
      <h2>Spieler-Lobby</h2>
      <div class="players-list">
        <div v-for="player in onlinePlayers" 
             :key="player.id" 
             class="player-card">
          <div class="player-info">
            <span class="player-name">{{ player.name }}</span>
            <span class="player-status" :class="player.status">
              {{ player.status }}
            </span>
          </div>
          <button 
            class="challenge-btn"
            :disabled="player.status !== 'available'"
            @click="challengePlayer(player)">
            Herausfordern
          </button>
        </div>
      </div>
      <div v-if="isChallengeModalVisible" class="challenge-modal">
        <h3>{{ challengeMessage }}</h3>
        <button @click="acceptChallenge">Akzeptieren</button>
        <button @click="declineChallenge">Ablehnen</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.lobby-container {
  position: fixed;
  right: 0;
  top: 0;
  height: 100vh;
  width: 300px;
  background: white;
  box-shadow: -2px 0 5px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
  z-index: 1000;
}

.lobby-container.closed {
  transform: translateX(100%);
}

.toggle-btn {
  position: absolute;
  left: -40px;
  top: 20px;
  background: white;
  border: none;
  padding: 8px;
  border-radius: 4px 0 0 4px;
  box-shadow: -2px 0 5px rgba(0,0,0,0.1);
  cursor: pointer;
}

.lobby-content {
  padding: 20px;
  height: 100%;
  overflow-y: auto;
}

.players-list {
  margin-top: 20px;
}

.player-card {
  padding: 10px;
  border: 1px solid #eee;
  border-radius: 4px;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.player-info {
  display: flex;
  flex-direction: column;
}

.player-name {
  font-weight: bold;
}

.player-status {
  font-size: 0.8em;
  padding: 2px 6px;
  border-radius: 10px;
  margin-top: 4px;
}

.player-status.available {
  background: #4CAF50;
  color: white;
}

.player-status.in_game {
  background: #FFC107;
  color: black;
}

.challenge-btn {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
}

.challenge-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>