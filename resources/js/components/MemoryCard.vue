<script setup>
import { computed } from 'vue'

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

const cardContent = computed(() => {
  return props.card.content || '';
});

// Prüft, ob es sich um ein Bild handelt (für Flags/Planeten)
const isImage = computed(() => {
  return props.card.content && 
         (props.card.content.startsWith('http') || 
          props.card.content.startsWith('/'));
});

// Für Bildunterschriften bei Flags/Planeten
const cardName = computed(() => {
  return props.card.name || '';
});
</script>

<template>
  <div 
    class="card" 
    :class="{ 
      'flipped': isFlipped || card.matched_by,
      'matched': card.matched_by,
      'disabled': card.matched_by 
    }" 
    @click="handleClick"
  >
    <div class="card-inner">
      <div class="card-hidden">
        <div class="card-content">?</div>
      </div>
      <div class="card-revealed">
        <div class="content-wrapper">
          <!-- Bild-Anzeige -->
          <img v-if="isImage" 
               :src="cardContent" 
               :alt="cardName" 
               class="card-image"
          />
          <!-- Text/Emoji-Anzeige -->
          <div v-else class="card-text-content">
            {{ cardContent }}
          </div>
          <!-- Bildunterschrift für Flags/Planeten -->
          <div v-if="cardName" class="card-caption">
            {{ cardName }}
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
}

.content-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 10%;
}

.card-image {
  max-width: 90%;
  max-height: 75%;
  object-fit: contain;
}

.card-text-content {
  font-size: clamp(1rem, 4vw, 2rem);
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
    padding: 5%;
  }
  
  .card-caption {
    font-size: clamp(0.6rem, 1.5vw, 0.8rem);
  }
}

@media (max-width: 480px) {
  .card {
    border-radius: 6px;
  }
  
  .content-wrapper {
    padding: 3%;
  }
}
</style>