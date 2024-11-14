<script setup>
import { ref, onMounted } from 'vue'

import { storeToRefs } from "pinia";
import { useAuthStore, authClient } from "@/store/AuthStore";

import TweetCard from '../components/TweetCard.vue';
import PostViewButton from '../components/Button/PostViewButton.vue';

// Pinia Store (authUser und logout aus dem Store)
const { authUser } = storeToRefs(useAuthStore());

// Funktion, um das Datum in TT.MM.JJJJ zu formatieren
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Monate sind 0-basiert, daher +1
  const day = String(date.getDate()).padStart(2, '0'); // Tag holen und formatieren
  return `${day}.${month}.${year}`; // Format TT.MM.JJJJ
};

// Get Posts
const posts = ref([])

const getPosts = async () => {
    const response = await authClient.get("/api/posts")

/*     const response = await fetch("/api/posts") // mit Fetch als Alternative
    const data = res.data */

    // Datum für jeden Post formatieren
    posts.value = response.data.map(post => ({
        ...post,
        created_at: formatDate(post.created_at) // Datum formatieren
    }));

    console.log(posts.value);
}
onMounted(() => {
    getPosts()
})

</script>

<template>

    <div class="container">
        <div class="text-wrapper"> 
            <div>
                <p>FEED VON</p>
                <h3>{{ authUser.name }}</h3> 
            </div>    
        </div>

        <TweetCard
            v-for="post in posts" :key="post.id"
                :date="post.created_at"
                :title="post.title"
                :text="post.content"
        >        
            <PostViewButton type="button" :post_id="post.id" />
        </TweetCard>

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

.text-wrapper {
  width: 768px; /* Gleiche Breite wie der form-wrapper */
  text-align: left;
  display: flex;
  flex-direction: row;
  margin-bottom: 37px;
}

@media (max-width: 768px) {
  .text-wrapper {
    width: 100%; /* Breite auf 100% des Bildschirms setzen */
    max-width: 768px;
    margin: 0 10px;
    margin-bottom: 37px;
  }
  .container {
    margin: 0 10px;
  }
}

@media (max-width: 480px) {
  .text-wrapper {
    width: 100%;
    font-size: 14vw;
    margin-left: auto;
    margin-right: auto;
    justify-content: center;
  }
}  

h3 {
  font-size: 32px;
  font-weight: 700;
  line-height: 38.73px;
  margin-bottom: 4px;
  color: #222222;
}

p {
  font-size: 16px;
  font-weight: 700;
  line-height: 19.36px;
  color: #888888;
}

</style>