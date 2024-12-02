<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { XCircle, MenuIcon } from 'lucide-vue-next';

const isOpen = ref(true);
const onlinePlayers = ref([]);

const toggleSidebar = () => {
  isOpen.value = !isOpen.value;
};
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

</style>