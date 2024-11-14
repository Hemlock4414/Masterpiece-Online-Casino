<script setup>

import router from "@/router";

// refs verwenden wir um die Daten zu binden um sie reaktiv zu machen
import { ref } from "vue";

// wir importieren den AuthStore damit wir die Authentifizierung durchführen können

import { useAuthStore } from "@/store/AuthStore";

// wir holen uns die login und getAuthUser Funktionen aus dem Store
const { login, getAuthUser } = useAuthStore(); 
// für das v-model binding, siehe unten im template und wir verwenden anschliessen im handleLogin die email.value
const email = ref("");
const password = ref("");

const handleLogin = async () => {
    // wir geben die email und password Werte an die login Funktion in unserem Store weiter
    await login({ email: email.value, password: password.value });

    // nach dem Login holen wir uns den authentifizierten User um ihn im Store zu speichern
    const res = await getAuthUser();

    // wenn der Status 200 ist, leiten wir den Benutzer auf die Dashboard-Seite weiter
    if (res.status === 200) router.push("/dashboard");
};
</script>
<template>
    <div class="container">

        <div class="text-wrapper">
            <h3>Live and Trending</h3>
            <p>Welcome back!</p>
        </div>    

        <div class="form-wrap">
            <form @submit.prevent="handleLogin">
                <div class="form-group login">Login</div>
                <div class="form-group">
                    <label for ="email">E-Mail *</label><br>
                    <input type="text" id="email" name="email" v-model="email">
                </div>
                <div class="form-group">
                    <label for ="password">Passwort *</label><br>
                    <input type="password" id="password" name="password" v-model="password">
                </div>
                <div class="registered">
                    <span>Noch keinen Account? 
                        <a href="http://localhost/register" class="log_in"> 
                            Registrieren
                        </a>
                    </span>
                    <button type="submit">Anmelden</button>
                </div>
            </form>
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

.text-wrapper {
  max-width: 768px; /* Gleiche Breite wie der form-wrapper */
  width: 100%;
  text-align: left;
}

h3 {
    font-size: 32px;
    font-weight: 700;
    line-height: 38.73px;
    margin-bottom: 4px;
    color: #222222;
}

p {
    font-size: 24px;
    font-weight: 400;
    line-height: 29.05px;
    margin-bottom: 31px;
    color: #666666;
}

/* Der Rahmen um das Formular */
.form-wrap {
  background-color: #FFFFFF;
  max-width: 768px;
  width: 100%;
  max-height: 444px;
  border-radius: 4px;
  border: solid 1px #F1F1F1;
  padding: 40px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Leichter Schatten */
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

@media (max-width: 768px) {
  .container {
    margin: 0 10px;
  }
}

.form-group {
  margin-bottom: 20px; /* Gleichmäßiger Abstand zwischen den Eingabefeldern */
}

.login {
    font-size: 32px;
    font-weight: 700;
    line-height: 38.73px;
    color: #222222;
    margin-bottom: 30px;
}

.form-group label {
  margin-bottom: 4px;
  font-weight: 400;
  font-size: 24px;
  color: #444444;
  line-height: 29.05px;
}

input[type="text"], input[type="password"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #999999;
  border-radius: 4px;
  font-size: 16px;
}

.registered {
    font-size: 14px;
    font-weight: 400;
    line-height: 18px;
    color: #444444;
    display: flex;
    flex-direction: row;
    margin-top: 15px; 
    justify-content: space-between;
}

.log_in {
    color: #1D9BF0;
    text-decoration: none !important;
}


button {
  width: 100px;
  padding: 10px 15px;
  background-color: #1D9BF0;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 400;
  line-height: 18px;
  cursor: pointer;
  text-align: center;
}

button:hover {
  background-color: #0056b3;
}
</style>