<script setup>

import {defineProps} from 'vue';

import { RouterLink } from 'vue-router';

// Definiere die Props, die die Komponente benötigt
const props = defineProps({
  post_id: {
    type: [String, Number], // Akzeptiere String oder Zahl für ID
    required: true
  }
});

</script>

<template>
  <!-- RouterLink wird um den Button gewickelt -->
  <RouterLink :to="{name: 'post-view', params:{id: post_id}}">
    <button class="view-btn">
        Tweet ansehen
    </button>
  </RouterLink>
</template>


<style scoped>

.view-btn {
    width: fit-content;
    height: fit-content;
    padding: 10px 15px;
    font-size: 16px;
    font-weight: 900;
    border-radius: 8px;
    background-color: #222222;
    color: #FFFFFF;
    text-align: center;
    cursor: pointer;
}

.view-btn:hover {
    background-color: #888888;
}

</style>