<script setup>

import { ref, onMounted } from 'vue';
import { authClient } from '@/store/AuthStore';

import { useRoute } from 'vue-router';

import PostButtonDelete from '../components/Button/PostButtonDelete.vue';
import PostButtonEdit from '../components/Button/PostButtonEdit.vue';
import AbortButton from '../components/Button/AbortButton.vue';

const route = useRoute()
const post_id = route.params.id

const post= ref({})

// Get Post
const getPost = async (id) => {
    try {
        const res = await authClient.get(`/api/posts/${id}`);
        post.value = res.data; // Postdaten speichern
    } catch (error) {
        console.error("Fehler beim Laden des Posts:", error);
    }
};

onMounted(()=>{
  getPost(post_id)
})

// Funktion, um das Datum in DD.MM.YYYY zu formatieren
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Monate sind 0-basiert, daher +1
  const day = String(date.getDate()).padStart(2, '0'); // Tag holen und formatieren
  return `${day}.${month}.${year}`; // Format TT.MM.JJJJ
};

</script>

<template>

    <div class="container">

        <div class="form-wrap">
            <div class="top">
              <div class="tweet-date">TWEET VOM {{ formatDate(post.created_at) }}</div>

              <AbortButton />
            </div>
            <div class="title">{{ post.title }}</div>

            <div class="text">{{ post.content }}</div>

            <div class="btns">
              <PostButtonEdit type="button" :post_id="post.id" />

              <PostButtonDelete type="button" :post_id="post_id" />
            </div>
        </div>
    </div>


</template>


<style scoped>
/* Zentriere den gesamten Inhalt auf dem Bildschirm */
.container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.form-wrap  {
  width: 768px;
  height: fit-content;
  display: flex;
  flex-direction: column;
  margin-bottom: 8px;
}

@media (max-width: 768px) {
  .form-wrap{
    max-width: 768px;
    width: 100%;
    margin-bottom: 8px;
  }
  .container {
    margin: 0 10px;
  }
}  

.top {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

.tweet-date {
  font-size: 16px;
  font-weight: 700;
  line-height: 19.36px;
  color: #888888;
  letter-spacing: 0.05em;
  margin-bottom: 33px;
}

.title {
  font-size: 32px;
  font-weight: 700;
  line-height: 38.73px;
  color: #222222;
  margin-bottom: 12px;
}

.text {
  font-size: 24px;
  font-weight: 400;
  line-height: 29.05px;
  color: #444444;
  margin-bottom: 55px;
}

.btns {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

</style>