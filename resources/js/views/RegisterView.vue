<script setup>

import { ref } from "vue";
import { useAuthStore } from "@/store/AuthStore";
import router from "@/router";

const { register, getAuthUser } = useAuthStore(); 

const name = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");

const handleRegister = async () => {
    const respReg = await register(
        {
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value
        }
    )
    if (respReg.status !== 201) {
        return alert("something went wrong")
    }  

    const resUser = await getAuthUser();

    if (resUser.status === 200) router.push("/dashboard");
}
</script>

<template>

    <div class="container">

        <div class="text-wrapper">
            <h3>Live and Trending</h3>
            <p>Join now!</p>
        </div>    

        <div class="form-wrap">
            <form action="" method="POST" @submit.prevent="handleRegister">
                <div class="form-group register">Registrieren</div>
                <div class="form-group">
                    <label for ="name">Name *</label><br>
                    <input type="text" id="name" name="name" required v-model="name">
                </div>
                <div class="form-group">
                    <label for ="email">E-Mail *</label><br>
                    <input type="text" id="email" name="email" required v-model="email">
                </div>
                <div class="form-group">
                    <label for ="password">Passwort *</label><br>
                    <input type="password" id="password" name="password" required v-model="password">
                </div>
                <div class="form-group">
                    <label for ="password_confirmation">Passwort bestätigen *</label><br>
                    <input type="password" id="password_confirmation" name="password_confirmation" required v-model="password_confirmation">
                </div>
                <div class="registered">
                    <span>Schon registriert? 
                        <a href="http://localhost/login" class="log_in"> 
                            Melde dich an
                        </a>
                    </span>
                    <button type="submit">Registrieren</button>
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
  min-height: 444px;
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

.register {
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

input[type="text"], input[type="password"], textarea {
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
  width: 110px;
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