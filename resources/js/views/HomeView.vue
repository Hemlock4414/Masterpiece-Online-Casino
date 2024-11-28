<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from "vue-router";

import { storeToRefs } from "pinia";
import { useAuthStore, authClient } from "@/store/AuthStore";
import router from "@/router";

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

</script>

<template>
    <main>

        <div class="container">
        
            <div class="upper-container">
                <div class="hero-container">
                    <img src="/public/img/Hero-Header-2.jpg" alt="">
             
                </div>
            </div>    

            <div class="row">
                <!-- Alle 6 Kacheln in einer Row -->
                <a href="/blackjack/play" style="text-decoration: none;">
                    <div class="card-container">
                        <div class="card">
                            <img src="/public/img/casino-placeholder.jpg" alt="Bild 1" class="card-image">
                        </div>
                        <button class="button">Play Blackjack</button>
                    </div>
                </a>
                
                <RouterLink to="/memory/play" style="text-decoration: none;">
                    <div class="card-container">
                        <div class="card">
                            <img src="/public/img/casino-placeholder.jpg" alt="Bild 2" class="card-image">
                        </div>
                        <button class="button">Play Memory</button>
                    </div>
                </RouterLink>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 3" class="card-image">
                    </div>
                    <button class="button">Play Poker</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 4" class="card-image">
                    </div>
                    <button class="button">Play Baccarat</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 5" class="card-image">
                    </div>
                    <button class="button">Play Sabacc</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 6" class="card-image">
                    </div>
                    <button class="button">Play Roulette</button>
                </div>
            </div>
            
        </div>
    </main> 
</template>

<style scoped>
.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.upper-container {
    width: 100%;
    background-color: black;  
    display: flex; 
    justify-content: center;
}
  
.hero-container {
    max-width: 1280px; 
}

.hero-container img {
    width: 100%;  
    height: auto;  
    display: block;
}

.row {
    max-width: 1280px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 50px;
    margin-bottom: 20px;
    margin-top: 30px;
}

.card-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 0 10px;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.button {
    width: 100%;
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: large;
    font-weight: 900;
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