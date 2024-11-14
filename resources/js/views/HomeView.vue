<script setup>
import { ref, onMounted } from 'vue'

import { storeToRefs } from "pinia";
import { useAuthStore, authClient } from "@/store/AuthStore";
import router from "@/router";

import TweetCard from '../components/TweetCard.vue';


// Pinia Store (authUser und logout aus dem Store)
const { authUser } = storeToRefs(useAuthStore());
const { logout } = useAuthStore();

// Handle Logout
const handleLogout = () => {
    logout();
    router.push("/login");
};

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
        <div> 
    
        <TweetCard
            v-for="post in posts" :key="post.id"
                :date="post.created_at"
                :title="post.title"
                :text="post.content"  
        />

        </div>
    </div>
</template>

<style scoped>
.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  
</style>