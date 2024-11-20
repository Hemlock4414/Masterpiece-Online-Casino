<script setup>
import { ref } from 'vue';
import { RouterLink, RouterView } from "vue-router";

// Importiere die Logout-Funktion und Router
import { storeToRefs } from "pinia";
import { useAuthStore } from "@/store/AuthStore";
import router from "@/router";

const { authUser } = storeToRefs(useAuthStore());
const { logout } = useAuthStore();

const username = ref("");
const password = ref("");
const rememberMe = ref(false);
const isPasswordVisible = ref(false); // Zustand für das Passwort-Toggle

const handleLogin = () => {
  // Beispiel: API-Login-Logik
  console.log("Login:", { username: username.value, password: password.value, rememberMe: rememberMe.value });
  // Fügen Sie hier Ihre API-Logik hinzu
};

// Handle Logout
const handleLogout = () => {
    logout();
    router.push("/login");
};

// Handle Registrierung
const handleRegister = () => {
  router.push("/registrieren");
};

// Handle PAsswort vergessen
const handleForgotPassword = () => {
  router.push("/passwort-vergessen");
};

</script>

<template>
    <header>
        <a href="/" class="link-div">
            <div class="headline">MINI-TWITTER</div>
        </a>  
        
        <nav class="navi">
            <!-- <RouterLink to="/">Home</RouterLink> -->
            <!-- <RouterLink to="/dashboard" v-if="authUser">Meine Tweets</RouterLink> -->
            <!-- <RouterLink to="/einloggen" v-if="authUser == null">Einloggen</RouterLink> -->
            <!-- <RouterLink to="/registrieren" v-if="!authUser">Registrieren</RouterLink> -->
            <!-- <RouterLink to="/edit" v-if="authUser">Tweet bearbeiten</RouterLink> -->
            <!-- <RouterLink to="/post/create" v-if="authUser">Tweet erstellen</RouterLink> -->
            <!-- <RouterLink :to="{name: 'post-create'}" v-if="authUser" class="special-link">+ Tweet erstellen</RouterLink> -->
            <!-- <RouterLink :to="{name: 'post-view'}" v-if="authUser">Tweet ansehen</RouterLink> -->

              <!-- Logout Button -->
            <!-- <button class="logout-btn" @click="handleLogout" v-if="authUser">Logout</button> -->

            <div class="login-container">
              <!-- Eingabefelder nebeneinander -->
              <div>
                <div class="inputs-wrapper">
                  <input
                    type="text"
                    v-model="username"
                    placeholder="Benutzername"
                    class="login-input"
                  />
                  <div class="password-wrapper">
                    <input
                      :type="isPasswordVisible ? 'text' : 'password'"
                      v-model="password"
                      placeholder="Passwort"
                      class="login-input"
                    />
                    <button
                      type="button"
                      class="toggle-password-btn"
                      @click="isPasswordVisible = !isPasswordVisible"
                      aria-label="Toggle Passwort Sichtbarkeit"
                    >
                      {{ isPasswordVisible ? '🙈' : '👁️' }}
                    </button>
                  </div>
                </div>  

                <div>                    
                  <div class="login-options">
                    <label>
                      <input type="checkbox" v-model="rememberMe" />
                      Benutzernamen merken
                    </label>
                    <a href="#" @click.prevent="handleForgotPassword">
                      Passwort vergessen?
                    </a>
                  </div>
                </div>  
              </div>

              <!-- Buttons (Einloggen und Registrieren) -->
                <div class="buttons-wrapper">
                  <button class="login-btn" @click="handleLogin">Einloggen</button>
                  <button class="register-btn" @click="handleRegister">Registrieren</button>
                </div>
              
            </div>
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

header {
  display: flex;
  flex-direction: row;
  justify-content: space-around;
  align-items: center;
  padding: 10px 20px;
  background-color: #FFFFFF;
  min-height: 104px;
  border-bottom: solid 1px #F1F1F1;
  color: #222222;
  flex-wrap: wrap;
  height: auto;
  align-items: stretch;
}

.headline { 
  font-size: 24px;
  font-weight: 900;
  line-height: 29.05px;
  letter-spacing: -0.02em;  /* Letter -2% */
  padding: 10px 0;
}

.link-div {
  text-decoration: none;
  color: inherit;
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

/* Login Container */
.login-container {
  display: flex;
  align-items: flex-start; /* Bündige Ausrichtung oben */
  gap: 20px;
}

/* Eingabefelder nebeneinander */
.inputs-wrapper {
  display: flex;
  gap: 10px; /* Abstand zwischen den Feldern */
  justify-content: space-between;
}

.login-input {
  padding: 8px;
  font-size: 14px;
  width: 160px; 
}

.login-options {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  font-size: 12px;
  margin-top: 20px;
}

.login-options label {
  display: flex; 
  align-items: center; 
  font-size: 14px; 
  gap: 5px; 
}

.login-options input[type="checkbox"] {
  width: 16px; 
  height: 16px;
  margin: 0;
}

.login-options a {
  font-size: 14px;
  line-height: 1.2;   
  color: #0056b3;   
  text-decoration: none;
  margin-left: 10px;    
}

.password-wrapper {
  position: relative;
  width: 160px;
}

.password-wrapper input {
  width: 100%;
  padding-right: 35px;
}

.toggle-password-btn {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  padding: 5px;
  color: #555;
}

.toggle-password-btn:hover {
  color: #000;
}

/* Buttons */
.buttons-wrapper {
  display: flex;
  flex-direction: column; 
  justify-content: space-between; 
  gap: 10px; 
}

.login-btn, .register-btn {
  padding: 8px 16px;
  border: none;
  font-size: 14px;
  cursor: pointer;
  width: 120px;
}

.login-btn {
  background-color: #0056b3;
  color: white;
}

.register-btn {
  background-color: #28a745;
  color: white;
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

@media (max-width: 500px) {
  header {
    flex-direction: column;
    align-items: center;
    padding: 0;
    padding-bottom: 20px;
  }
  .headline {
    margin-bottom: 10px;
  }
  .navi {
    max-width: 400px;
  }
  .login-container {
    flex-direction: column; 
    align-items: center;
    gap: 15px; 
  }
  .inputs-wrapper {
    flex-direction: column; 
    width: 100%;
  }
  .password-wrapper {
    width: 100%;
  }
  .login-input {
    width: 100%; 
  }
  .buttons-wrapper {
    width: 100%;
  }
  .login-btn, .register-btn {
    width: 100%;
  }
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