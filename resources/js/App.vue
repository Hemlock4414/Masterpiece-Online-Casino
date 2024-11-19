<script setup>
import { RouterLink, RouterView } from "vue-router";

// Importiere die Logout-Funktion und Router
import { storeToRefs } from "pinia";
import { useAuthStore } from "@/store/AuthStore";
import router from "@/router";

import { ref } from 'vue';

const isActive = ref(false);
const { authUser } = storeToRefs(useAuthStore());
const { logout } = useAuthStore();

// Handle Logout
const handleLogout = () => {
    logout();
    router.push("/login");
};
</script>

<template>
    <header>
        <a href="http://localhost" class="link-div">
            <div class="headline">MINI-TWITTER</div>
        </a>  
        <button class="menu-btn" @click="isActive = !isActive">☰</button>
            <nav :class="['navi', { active: isActive }]">
                <!-- <RouterLink to="/">Home</RouterLink> -->
                <RouterLink to="/dashboard" v-if="authUser">Meine Tweets</RouterLink>
                <RouterLink to="/login" v-if="authUser == null">Einloggen</RouterLink>
                <RouterLink to="/register" v-if="!authUser">Registrieren</RouterLink>
                <!-- <RouterLink to="/edit" v-if="authUser">Tweet bearbeiten</RouterLink> -->
                <!-- <RouterLink to="/post/create" v-if="authUser">Tweet erstellen</RouterLink> -->
                <RouterLink :to="{name: 'post-create'}" v-if="authUser" class="special-link">+ Tweet erstellen</RouterLink>
                <!-- <RouterLink :to="{name: 'post-view'}" v-if="authUser">Tweet ansehen</RouterLink> -->

                 <!-- Logout Button -->
                <button class="logout-btn" @click="handleLogout" v-if="authUser">Logout</button>
            </nav>
    </header>
    <RouterView />

    <main></main>

    <footer>
      <div class="main-footer">
        <ul class="legal-pages">
          <li class="legal-pages-item">Impressum</li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/datenschutz">Datenschutz</a></li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/agb">AGB</a></li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/fair-gaming">Verantwortungsvolles Spielen</a></li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/kontakt">Kontakt</a></li>
          <li class="legal-pages-item">Über mich</li>
        </ul>
      </div>
    </footer>

</template>

<style scoped>

html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

header {
    display: flex;
    flex-direction: row;
    justify-content: space-around;
    align-items: center;
    background-color: #FFFFFF;
    min-height: 104px;
    border-bottom: solid 1px #F1F1F1;
    color: #222222;
    flex-wrap: wrap;
}

.headline { 
    font-size: 24px;
    font-weight: 900;
    line-height: 29.05px;
    letter-spacing: -0.02em;  /* Letter -2% */
}

.link-div {
  text-decoration: none;
  color: inherit;
}

.navi a {
  margin-left: 50px; 
  text-decoration: none;
  color: #222222;
  font-size: 18px;
  font-weight: 400;
  line-height: 21.78px;
}

.special-link {
    background-color: #1D9BF0;
    padding: 10px 15px;
    border-radius: 50px;
    width: fit-content;
    color: #FFFFFF !important;
    font-size: 16px !important;
    line-height: 19.36px !important;
}

.special-link:hover {
    background-color: #0056b3;;
}

.logout-btn {
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
    margin-left: 50px;
}

.logout-btn:hover {
    background-color: #888888;
}

@media (max-width: 690px) {
  .navi a, .logout-btn {
    margin-left: 10px;
  }
}

.menu-btn {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

@media (max-width: 600px) {
  .menu-btn {
    display: block;
  }

  .navi {
    display: none;
    flex-direction: column;
    gap: 10px;
  }

  .menu-btn.active + .navi {
    display: flex;
  }
}

.legal-pages {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    background-color: #FFFFFF;
    min-height: 48px;
    font-size: 20px;
    color: #222222;
    font-weight: 400;
    line-height: 24.2px;
    padding: 0 30px;
    list-style: none;
    flex-wrap: wrap;
}

.legal-pages-item {
  border-right: 0.8px solid #21068d;
  padding: 0 5.4em;
}

.legal-pages-link {
  text-decoration: none;
  color: inherit;
}

@media (max-width: 652px) {
    .legal-pages-item {
      border-right: none;
    }
}

@media (max-width: 595px) {
    .legal-pages-item {
      text-align: center;
      padding: 0 2.4em;
    }
}


</style>