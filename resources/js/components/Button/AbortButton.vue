<script setup>
import { defineProps } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  text: {
    type: String,
    default: 'Zurück' // Standardwert, falls kein Text übergeben wird
  }
});

const router = useRouter();

const handleClick = () => {
  router.push({ name: 'dashboard' });
};
</script>

<template>
    <button class="abort-btn" @click="handleClick">
      {{ text }}
    </button>
</template>


<style scoped>

.abort-btn {
  width: fit-content;
  height: fit-content;
  padding: 10px 15px;
  background-color: #29b73c;
  color: white;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 400;
  line-height: 18px;
  cursor: pointer;
  text-align: center;
}

.abort-btn:hover {
  background-color: #098369;
}
</style>