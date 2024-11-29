<script setup>
const props = defineProps({
  card: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['flip']);

const handleClick = () => {
  // Nur gematchte Karten blockieren
  if (!props.card.is_matched) {
    emit('flip', props.card);
  }
};
</script>

<template>
  <div 
    class="card" 
    :class="{ 
      flipped: card.is_flipped,
      matched: card.is_matched,
      disabled: card.is_matched // Nur gematchte Karten deaktivieren
    }" 
    @click="handleClick"
  >
    <div class="card-inner">
      <div class="card-front">?</div>
      <div class="card-back">{{ card.group_id }}</div>
    </div>
  </div>
</template>

<style scoped>
.card {
  aspect-ratio: 1;
  perspective: 1000px;
  cursor: pointer;
}

.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.3s;
  transform-style: preserve-3d;
}

.card.flipped .card-inner {
  transform: rotateY(180deg);
}

.card-front,
.card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  background-color: #fff;
  border: 2px solid #ddd;
  border-radius: 8px;
}

.card-front {
  background-color: #f0f0f0;
}

.card-back {
  background-color: white;
  transform: rotateY(180deg);
}

.card.disabled {
  cursor: default;  /* Ändere den Cursor für deaktivierte Karten */
}
</style>