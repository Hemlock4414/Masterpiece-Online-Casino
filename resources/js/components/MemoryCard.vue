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
  if (!props.card.is_matched) {
    emit('flip', props.card);
  }
};

// Ermittle die Bild-URL für die Karte
const cardBackgroundImage = computed(() => {
  // Wenn ein spezifisches Bild definiert ist, nutze dieses
  if (props.card.card_image) {
    // Stelle sicher, dass der Pfad korrekt ist
    return props.card.card_image.startsWith('/') 
      ? props.card.card_image 
      : `/${props.card.card_image}`;
  }
  // Fallback auf Standardverhalten
  return null;
});
</script>

<template>
  <div 
    class="card" 
    :class="{ 
      'flipped': isFlipped || card.is_matched,
      'matched': card.is_matched,
      'disabled': card.is_matched 
    }" 
    @click="handleClick"
  >
    <div class="card-inner">
      <div class="card-hidden">
        <div class="card-content">?</div>
      </div>
      <div class="card-revealed">
        <!-- Zeige Bild oder Textinhalt -->
        <div v-if="card.card_image" class="card-image">
          <img :src="card.card_image" alt="Kartenbild">
        </div>
        <div v-else-if="card.card_content" class="card-text-content">
          {{ card.card_content }}
        </div>
        <div v-else class="card-content">
          {{ card.group_id }}
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
  transition: transform 0.1s;
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
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  border-radius: 12px;
  background: linear-gradient(145deg, #ffffff, #f0f0f0);
  border: 2px solid #e0e0e0;
}

.card-content {
  transform: scale(1);
  transition: transform 0.3s ease;
}

.card-hidden {
  background: linear-gradient(145deg, #f0f0f0, #e6e6e6);
}

.card-revealed {
  background: linear-gradient(145deg, #ffffff, #f5f5f5);
  transform: rotateY(180deg);
}

.card.matched .card-back {
  background: linear-gradient(145deg, #e8f5e9, #c8e6c9);
  border-color: #81c784;
}

.card.matched .card-content {
  transform: scale(1.1);
  color: #2e7d32;
}

@keyframes matchedPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.card.matched {
  animation: matchedPulse 0.5s ease-in-out;
}

/* Für Geräte die hover unterstützen */
@media (hover: hover) {
  .card:hover:not(.disabled) {
    transform: scale(1.02);
  }
}
</style>