<script setup>
import { ref } from 'vue';
import { RouterLink, RouterView } from "vue-router";
import { storeToRefs } from "pinia";
import { useAuthStore } from "@/store/AuthStore";
import router from "@/router";

// Authentifizierungszustand abrufen
const { authUser } = storeToRefs(useAuthStore());
const authStore = useAuthStore();
const { login, logout } = useAuthStore();

const username = ref("");
const password = ref("");
const rememberMe = ref(false);
const isPasswordVisible = ref(false);
const errorMessage = ref("");

const handleLogin = async () => {
  try {
    errorMessage.value = "";
    await login({
      login: username.value,     // Hier verwenden wir ein generisches 'login'-Feld
      password: password.value,
      remember: rememberMe.value
    });
    username.value = "";
    password.value = "";
    rememberMe.value = false;
  } catch (error) {
    errorMessage.value = "Login fehlgeschlagen. Bitte überprüfen Sie Ihre Eingaben.";
    console.error("Login error:", error);
  }
};

const handleLogout = async () => {
  try {
    await logout();
    // router.push("/"); // Diese Zeile entfernen
  } catch (error) {
    console.error("Logout error:", error);
  }
};

// Handle Registrierung
const handleRegister = () => {
  router.push("/registrieren");
};

// Handle Profilseite
const navigateToProfile = () => {
  router.push("/profil");
};

</script>

<template>
    <header>
        <RouterLink to="/" class="link-div">
            <div class="headline">FORTUNA FORTUNE Online Casino</div> 
        </RouterLink>  
        
        <nav class="navi">
            <!-- <RouterLink to="/">Home</RouterLink> -->
            <!-- <RouterLink to="/dashboard" v-if="authUser">Meine Tweets</RouterLink> -->
            <!-- <RouterLink to="/einloggen" v-if="authUser == null">Einloggen</RouterLink> -->
            <!-- <RouterLink to="/registrieren" v-if="!authUser">Registrieren</RouterLink> -->
            <!-- <RouterLink to="/edit" v-if="authUser">Tweet bearbeiten</RouterLink> -->
            <!-- <RouterLink to="/post/create" v-if="authUser">Tweet erstellen</RouterLink> -->
            <!-- <RouterLink :to="{name: 'post-create'}" v-if="authUser" class="special-link">+ Tweet erstellen</RouterLink> -->
            <!-- <RouterLink :to="{name: 'post-view'}" v-if="authUser">Tweet ansehen</RouterLink> -->

            <!-- Login Container (nur anzeigen wenn nicht eingeloggt) -->
            <div v-if="!authUser" class="login-container">

              <div class="login-n-error">
                <div class="inputs-wrapper">
                  <input
                    type="text"
                    v-model="username"
                    placeholder="Benutzername/E-Mail"
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
               
                <div class="error-message" :data-visible="!!errorMessage">
                  {{ errorMessage }}
                </div>
                  
                <div class="login-options">
                  <label>
                    <input type="checkbox" v-model="rememberMe" />
                    Anmeldedaten merken
                  </label>
                  <RouterLink to="/passwort-vergessen">Passwort vergessen?</RouterLink>
                </div>
              </div>
              <!-- Buttons (Einloggen und Registrieren) -->
                <div class="buttons-wrapper">
                  <button class="login-btn" @click="handleLogin">Einloggen</button>
                  <button class="register-btn" @click="handleRegister">Registrieren</button>
                </div>
              
            </div>

            <!-- User Menu (nur anzeigen wenn eingeloggt) -->
            <div v-else class="user-menu">
              <div class="balance-display">
                Guthaben: {{ authUser?.user?.balance ?? '0' }} Credits
              </div>
              <button class="profile-btn" @click="navigateToProfile">
                Mein Profil
              </button>
              <button class="logout-btn" @click="handleLogout">
                Ausloggen
              </button>
            </div>

        </nav>
    </header>
  <RouterView />


    <footer>
      <div class="main-footer">
        <ul class="legal-pages">
          <li class="legal-pages-item">Impressum</li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/datenschutz">Datenschutz</a></li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/agb">AGB</a></li>
          <li class="legal-pages-item"><a class="legal-pages-link" href="/spielerschutz">Verantwortungsvolles Spielen</a></li>
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
  border-bottom: solid 1px #F1F1F1;
  color: #222222;
  flex-wrap: wrap;
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

/* Login Container */
.login-container {
  display: flex;
  align-items: flex-start; 
  gap: 20px;
}

.inputs-wrapper {
  display: flex;
  gap: 10px;
  justify-content: space-between;  
}

.login-n-error {
  display: flex;
  flex-direction: column; 
}

.login-input {
  padding: 8px;
  font-size: 14px;
  width: 160px; 
}

.login-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  margin-top: 0px;
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
  margin-bottom: -3px;   
}

.error-message {
  color: red;
  font-size: 12px;
  min-height: 15px;  /* Feste Höhe für die Fehlermeldung */
  margin-top: 2px;
  visibility: hidden;
}

.error-message[data-visible="true"] {
  visibility: visible; /* Sichtbar machen */
  opacity: 1; /* Voll sichtbar */
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

/* User Menu Container */
.user-menu {
  display: flex;
  flex-direction: row;
  gap: 1rem;
}

.balance-display {
  background: #f0f0f0;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-weight: bold;
}

.profile-btn, .logout-btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
  font-weight: 700;
}

.profile-btn {
  background-color: #4CAF50;
  color: white;
}

.logout-btn {
  background-color: #f44336;
  color: white;
}

/* Footer */
footer {
  margin-top: auto;
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
  .user-menu {
    gap: 0.5rem;
    margin: 0 10px;
  }
  .profile-btn, .logout-btn {
    font-size: 18px;
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