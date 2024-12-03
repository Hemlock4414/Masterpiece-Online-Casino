<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { XCircle, MenuIcon } from 'lucide-vue-next';
// npm install lucide-vue-next
import { LobbyService } from '../services/LobbyService';

const isOpen = ref(true);
const onlinePlayers = ref([]);

const updateLobbyState = (lobby) => {
  // Lobby-Status in der UI aktualisieren
  if (lobby.challenger_id === currentPlayer.value.id || 
      lobby.challenged_id === currentPlayer.value.id) {
    // UI-Update Logik
  }
};

const loadPlayers = async () => {
  try {
    const response = await LobbyService.getOnlinePlayers();
    players.value = response.data;
  } catch (error) {
    console.error('Fehler beim Laden der Spieler:', error);
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

const toggleSidebar = () => {
  isOpen.value = !isOpen.value;
};

onMounted(() => {
  window.Echo.channel('lobby')
    .listen('LobbyStatusUpdated', (e) => {
      updateLobbyState(e.lobby);
    });
});

onMounted(() => {
  // Initiales Laden der Spieler
  loadPlayers();

  // WebSocket-Verbindung
  window.Echo.channel('lobby')
    .listen('PlayerStatusChanged', (e) => {
      updatePlayerList(e.player);
    });
});

onMounted(() => {
  console.log('Versuche Verbindung zur Lobby...');
  
  window.Echo.channel('lobby')
    .listen('PlayerStatusChanged', (e) => {
      console.log('Spieler Status geändert:', e);
    });

  // Test-Event senden
  LobbyService.updateStatus('available')
    .then(() => console.log('Status erfolgreich aktualisiert'))
    .catch(err => console.error('Fehler:', err));
});

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