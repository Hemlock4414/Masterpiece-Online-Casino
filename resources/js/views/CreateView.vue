<script setup>

import { ref } from 'vue';
import { authClient } from '@/store/AuthStore';

import AbortButton from '../components/Button/AbortButton.vue';
import SubmitButton from '../components/Button/SubmitButton.vue';

const title = ref("");
const content = ref("");
const alertMessage = ref("")

const handleCreate = async () => {
    try{
        const rest = await authClient.post("/api/posts", {
            title: title.value,
            content: content.value
        })

        if(rest.status == 201) alertMessage.value = "Dein Tweet wurde erstellt!"

    } catch(e) {
        alert("Etwas ist schiefgelaufen!");
        console.log(e);
    }
}

</script>


<template>

    <div class="container">

        <div class="text-wrapper">
            <div>
              <h3>Tweet erstellen</h3>
              <p>Was möchtest du mitteilen?</p>
            </div> 
            <AbortButton text="Abbrechen" />
        </div>

        <div class="form-wrap">
            <!-- <div v-if="alertMessage">{{ alertMessage }}</div> -->
            <form action="" method="POST" @submit.prevent="handleCreate">
                <div class="form-group">
                    <label for ="title">Titel</label><br>
                    <input type="text" id="title" name="title" v-model="title">
                </div>
                <div class="form-group">
                    <label for ="content">Text</label><br>
                    <textarea id="content" name="content" rows="5" v-model="content"></textarea>
                </div>
                <SubmitButton>Tweet speichern</SubmitButton>
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
  display: flex;
  flex-direction: row;
  justify-content: space-between;
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

.form-group label {
  margin-bottom: 4px;
  font-weight: 400;
  font-size: 24px;
  color: #444444;
  line-height: 29.05px;
}

input[type="text"], textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #999999;
  border-radius: 4px;
  font-size: 16px;
}

</style>