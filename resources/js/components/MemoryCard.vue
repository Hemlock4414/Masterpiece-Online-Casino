<script setup>
const props = defineProps({
  card: {
    type: Object,
    required: true
  },
  isFlipped: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['flip']);

const handleClick = () => {
  if (!props.card.matched_by) {
    emit('flip', props.card);
  }
};

// Prüft, ob es sich um ein Bild handelt
const isImagePath = (content) => {
    return content && (
        content.startsWith('/') || 
        content.startsWith('http') || 
        content.endsWith('.jpg') || 
        content.endsWith('.jpeg') || 
        content.endsWith('.png') || 
        content.endsWith('.gif')
    );
};
</script>

<template>
  <div class="card" 
       :class="{ 
         'flipped': isFlipped,
         'matched': props.card.matched_by,
         'disabled': props.card.matched_by 
       }" 
       @click="handleClick">
      <div class="card-inner">
          <div class="card-hidden">?</div>
          <div class="card-revealed">
              <div class="content-wrapper">
                  <!-- Bild anzeigen wenn der content eine URL ist -->
                  <img v-if="isImagePath(props.card.content)" 
                       :src="props.card.content" 
                       :alt="props.card.name || 'Memory Card'"
                       class="card-image">
                  <!-- Sonst den content direkt anzeigen (für Emojis) -->
                  <div v-else class="card-text-content">
                      {{ props.card.content || '❓' }}
                  </div>
              </div>
          </div>
      </div>
  </div>
</template>

<style scoped>
.card {
  aspect-ratio: 1;
  perspective: 1000px;
  cursor: pointer;
  transition: transform 0.2s ease;
  border-radius: 10px;
}

.card:hover:not(.disabled) {
  transform: scale(1.02);
}

.card:active:not(.disabled) {
  transform: scale(0.98);
}

.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  transform-style: preserve-3d;
}

.card.flipped .card-inner {
  transform: rotateY(180deg);
}

.card-hidden,
.card-revealed {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  border-radius: inherit;
}

.content-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  /* Grösse einstellen */
  padding: 3%;   
}

.card-image {
  /* Grösse einstellen */
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

  /* Größe bei Emojis */
.card-text-content {
  font-size: clamp(2rem, 8vw, 4rem);
  font-weight: bold;
}

.card-caption {
  font-size: clamp(0.7rem, 2vw, 1rem);
  margin-top: 5px;
  color: #666;
}

.card-hidden {
  background: linear-gradient(145deg, #f0f0f0, #e6e6e6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: clamp(1.5rem, 5vw, 3rem);
  color: #999;
}

.card-revealed {
  background: white;
  transform: rotateY(180deg);
}

.card.matched {
  animation: matchedPulse 0.5s ease-in-out;
}

.card.matched .card-revealed {
  background: linear-gradient(145deg, #e8f5e9, #c8e6c9);
  border: 2px solid #81c784;
}

@keyframes matchedPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

/* Responsive Anpassungen */
@media (max-width: 767px) {
  .content-wrapper {
    padding: 2%;
  }
  
  .card-caption {
    font-size: clamp(1.5rem, 6vw, 3rem);
  }
}

@media (max-width: 480px) {
  .card {
    border-radius: 6px;
  }
  
  .content-wrapper {
    padding: 0%;
  }
}
</style>