<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/store/AuthStore';

const authStore = useAuthStore();

const showGenerator = ref(false);
const generatorFrame = ref(null);

const onGeneratorLoad = () => {
  window.addEventListener('message', handleGeneratorMessage);
};

const handleGeneratorMessage = (event) => {
  // Sicherheits-Check: Nur akzeptieren von bekannten Ursprüngen
  const allowedOrigins = [
    window.location.origin, 
    `${window.location.origin}/pixel-chargen`
  ];
  
  if (!allowedOrigins.includes(event.origin)) return;

  if (event.data?.type === 'character-exported') {
    saveCharacterAsProfilePicture(event.data.fileContent);
  }
};

const openGenerator = () => {
  showGenerator.value = true;
};

const closeGenerator = () => {
  showGenerator.value = false;
};

const exportCharacter = () => {
  generatorFrame.value.contentWindow.exportCharacter();
};

const saveCharacterAsProfilePicture = async (fileContent) => {
  try {
    const blob = await fetch(fileContent).then(response => response.blob());
    const formData = new FormData();
    formData.append('profile_pic', blob, 'pixel-character.png');

    await authStore.updateProfilePicture(formData);
    showMessage('Profilbild erfolgreich aktualisiert!');
  } catch (error) {
    console.error('Fehler beim Speichern:', error);
    showMessage('Speichern fehlgeschlagen', true);
  }
};

const showMessage = (message, isError = false) => {
  console.log(isError ? '❌ ' : '✅ ', message);
};
</script>

<template>
    <div class="character-generator">
      <div class="generator-controls">
        <h2>Pixel Fantasy - Charakter Generator</h2>
        <p>(experimentell, v0.05 created by Caz Wolf)</p>
        
        <div class="generator-actions">
          <button 
            @click="openGenerator" 
            class="btn-open-generator"
          >
            Charakter erstellen
          </button>
        </div>
        <p>Hinweis: Nach dem Erstellen auf "Export Bust" klicken, 
          Bild wird dann runtergeladen. Bitte manuell wieder hochladen.</p>
      </div>
  
      <!-- Generator Modal -->
      <div 
        v-if="showGenerator" 
        class="generator-modal"
        @click.self="closeGenerator"
      >
        <div class="generator-window">
          <iframe 
            src="/Pixel_Fantasy_Character_Generator/index.html"
            width="100%" 
            height="790px"
            ref="generatorFrame"
            @load="onGeneratorLoad"
          ></iframe>
          <div class="modal-actions">
            <button 
              @click="closeGenerator" 
              class="btn-close-generator"
            >
              Generator schließen
            </button>
          </div>
        </div>
      </div>
    </div>
</template>
    
<style scoped>
.character-generator {
  background-color: #f4f5f7;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.generator-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

h2 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 0px;
  color: #333;
}

.generator-actions {
  display: flex;
  gap: 15px;
  padding: 15px 0;
}

.btn-open-generator,
.btn-save-character,
.btn-close-generator {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.btn-open-generator {
  background-color: #4CAF50;
  color: #fff;
}

.btn-open-generator:hover {
  background-color: #45a049;
}

.btn-save-character {
  background-color: #2196F3;
  color: #fff;
}

.btn-save-character:hover {
  background-color: #0d8aee;
}

.btn-save-character:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-close-generator {
  background-color: #f44336;
  color: #fff;
}

.btn-close-generator:hover {
  background-color: #e53935;
}

.generator-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.generator-window {
  background: white;
  border-radius: 10px;
  width: 90%;
  max-width: 1440px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
}

.btn-export-character,
.btn-close-generator {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.btn-export-character {
  background-color: #2196F3;
  color: #fff;
}

.btn-export-character:hover {
  background-color: #0d8aee;
}

.btn-close-generator {
  background-color: #f44336;
  color: #fff;
}
</style>
