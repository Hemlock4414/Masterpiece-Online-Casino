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
    <RouterLink :to="{name: 'post-edit', params:{id: post_id}}">
      <button class="edit-btn">
          Tweet bearbeiten
      </button>
    </RouterLink>
</template>

<style scoped>
.edit-btn {
    background-color: #222222;
    padding: 10px 15px;
    border-radius: 8px;
    color: #FFFFFF;
    font-size: 14px;
    font-weight: 400;
    line-height: 18px;
    width: fit-content;
    cursor: pointer;
    text-align: center;
}
  
.edit-btn:hover {
    background-color: #888888;
}
</style>