<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { XCircle, MenuIcon } from 'lucide-vue-next';
import { LobbyService } from '../services/LobbyService';
import { useAuthStore } from "@/store/AuthStore";

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const isOpen = ref(true);
const onlinePlayers = ref([]);
const isChallengeModalVisible = ref(false);
const challengeMessage = ref('');
const currentLobby = ref(null);

// Toggle Sidebar
const toggleSidebar = () => {
  isOpen.value = !isOpen.value;
};

// Spieler zur Liste hinzufügen/aktualisieren
const updatePlayerList = (player) => {
  const index = onlinePlayers.value.findIndex(p => p.id === player.id);
  
  if (index !== -1) {
    onlinePlayers.value[index] = player;
  } else {
    onlinePlayers.value.push(player);
  }
};

// Spieler-Herausforderung
const showChallengeModal = (lobby) => {
  if (!lobby || lobby.challenged_id !== user.value?.id) return;
  
  isChallengeModalVisible.value = true;
  challengeMessage.value = `${lobby.challenger_name} fordert Sie zu einer Partie Memory heraus!`;
  currentLobby.value = lobby;
};

const acceptChallenge = async () => {
  try {
    await LobbyService.updateLobbyStatus(currentLobby.value.lobby_id, 'accepted');
    isChallengeModalVisible.value = false;
  } catch (error) {
    console.error('Fehler beim Akzeptieren der Herausforderung:', error);
  }
};

const declineChallenge = async () => {
  try {
    await LobbyService.updateLobbyStatus(currentLobby.value.lobby_id, 'declined');
    isChallengeModalVisible.value = false;
  } catch (error) {
    console.error('Fehler beim Ablehnen der Herausforderung:', error);
  }
};

const challengePlayer = async (player) => {
  if (!user.value?.id && player.id === sessionStorage.getItem('memoryGuestId')) return;
  
  try {
    await LobbyService.challengePlayer(player.id);
  } catch (error) {
    console.error('Fehler beim Herausfordern:', error);
  }
};

onMounted(() => {
  const playerId = LobbyService.getCurrentPlayerId();
  console.log('WebSocket-Verbindung wird aufgebaut für Spieler:', playerId);
  
  window.Echo.channel('lobby')
    .listen('PlayerStatusChanged', (e) => {
      console.log('Status-Update empfangen:', e);
      if (e.player) {
        updatePlayerList({
          id: e.player.id,
          name: e.player.name,
          status: e.player.status,
          isRegistered: e.player.isRegistered
        });
      }
    })
    .listen('LobbyStatusUpdated', (e) => {
      console.log('Lobby-Update empfangen:', e);
      if (e.lobby) {
        const lobby = {
          lobby_id: e.lobby.lobby_id,
          challenger_id: e.lobby.challenger_id,
          challenger_name: e.lobby.challenger_name,
          challenger_registered: e.lobby.challenger_registered,
          challenged_id: e.lobby.challenged_id,
          challenged_name: e.lobby.challenged_name,
          challenged_registered: e.lobby.challenged_registered,
          status: e.lobby.status
        };
        showChallengeModal(lobby);
      }
    });
});

// Cleanup beim Unmount
onUnmounted(() => {
  window.Echo.leave('lobby');
  LobbyService.updatePlayerStatus('offline');
});

</script>

<template>
  <!-- Button außerhalb des Containers -->
  <button @click="toggleSidebar" class="toggle-btn" :class="{ 'closed': !isOpen }">
    <MenuIcon v-if="!isOpen" />
    <XCircle v-else />
  </button>
    
  <!-- Separater Container -->
  <div class="lobby-container" :class="{ 'closed': !isOpen }">
    <div class="lobby-content" v-if="isOpen">
      <h2>Spieler-Lobby</h2>
      
      <div class="players-list">
        <div v-for="player in onlinePlayers" 
             :key="player.id" 
             class="player-card"
             :class="{ 'self': player.id === (user?.id || sessionStorage.getItem('memoryGuestId')) }">
             <div class="player-info">
              <span class="player-name">
                {{ player.name }} 
                {{ player.id === (user?.id || sessionStorage.getItem('memoryGuestId')) ? '(Sie)' : '' }}
                {{ player.isRegistered ? '👤' : '👻' }}
              </span>
              <span class="player-status" :class="player.status">
                {{ player.status || 'verfügbar' }}
              </span>
            </div>
            <button 
              v-if="player.id !== (user?.id || sessionStorage.getItem('memoryGuestId')) && 
                    onlinePlayers.length < 2 && 
                    player.status !== 'offline'"
              class="challenge-btn"
              :disabled="player.status === 'in_game'"
              @click="challengePlayer(player)">
              Herausfordern
            </button>
        </div>
      </div>

      <!-- Challenge Modal -->
      <div v-if="isChallengeModalVisible" class="challenge-modal">
        <div class="modal-content">
          <h3>{{ challengeMessage }}</h3>
          <div class="modal-buttons">
            <button class="accept-btn" @click="acceptChallenge">Akzeptieren</button>
            <button class="decline-btn" @click="declineChallenge">Ablehnen</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:root {
  --header-height: 315px;
  --footer-height: 170px;
}

.lobby-container {
  position: fixed;
  right: 0;
  top: var(--header-height);
  bottom: var(--footer-height);
  width: 300px;
  background: white;
  box-shadow: -2px 0 5px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
  z-index: 1000;
  overflow-y: auto;
  background: rgba(255, 0, 0, 0.1);
}

.lobby-container.closed {
  transform: translateX(100%);
}

.toggle-btn {
  position: fixed;
  right: 300px; /* Breite des Containers */
  top: var(--header-height);
  padding: 8px;
  background: white;
  border: none;
  border-radius: 4px 0 0 4px;
  box-shadow: -2px 0 5px rgba(0,0,0,0.1);
  cursor: pointer;
  z-index: 1001;
  transition: right 0.3s ease;
}

.toggle-btn.closed {
  right: 0;
}

.lobby-content {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
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

.player-card.registered {
  border-left: 3px solid #4CAF50;
}

.player-name .icon {
  margin-left: 5px;
  font-size: 0.8em;
}

.player-card.self {
  background-color: #f5f5f5;
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

.challenge-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  z-index: 1100;
}

.modal-buttons {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.accept-btn, .challenge-btn {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.decline-btn {
  background: #f44336;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .lobby-container {
    width: 100%;
    max-width: 300px;
  }
}
</style>