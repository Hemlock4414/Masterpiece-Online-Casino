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
    <main>

        <div class="container">
        
            <div class="hero-container">
                <img src="/public/img/Hero-Header-2.jpg" alt="">
            </div>

            <div class="row">
                <!-- Alle 6 Kacheln in einer Row -->
                <div class="card-container">
                    <div class="card">
                        <img src="" alt="Bild 1" class="card-image">
                        <div class="card-content">
                            <h3>Kachel 1</h3>

                        </div>
                    </div>
                    <button class="button">Play Blackjack</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="" alt="Bild 2" class="card-image">
                        <div class="card-content">
                            <h3>Kachel 2</h3>

                        </div>
                    </div>
                    <button class="button">Play Memory</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="" alt="Bild 3" class="card-image">
                        <div class="card-content">
                            <h3>Kachel 3</h3>

                        </div>
                    </div>
                    <button class="button">Button 3</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="" alt="Bild 4" class="card-image">
                        <div class="card-content">
                            <h3>Kachel 4</h3>

                        </div>
                    </div>
                    <button class="button">Button 4</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="" alt="Bild 5" class="card-image">
                        <div class="card-content">
                            <h3>Kachel 5</h3>

                        </div>
                    </div>
                    <button class="button">Button 5</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="" alt="Bild 6" class="card-image">
                        <div class="card-content">
                            <h3>Kachel 6</h3>

                        </div>
                    </div>
                    <button class="button">Button 6</button>
                </div>
            </div>


        </div>
    </main> 
</template>

<style scoped>
.container {
    /* max-width: 1440px; */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
  
.hero-container {
    max-width: 1440px;
    height: auto;    
}

.hero-container img {
    width: 100%;  
    height: auto;  
}

.row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 100px;
    margin-bottom: 20px;
    margin-top: 30px;
}

.card-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.card {
    background: #f5f5f5;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    height: 100%;
}

.card-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-content {
    padding: 20px;
}

.button {
    width: 100%;
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.button:hover {
    background: #0056b3;
}

/* Responsive Anpassungen */
@media (max-width: 968px) {
    .row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>