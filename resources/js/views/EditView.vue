<script setup>

import { ref, onMounted } from 'vue';
import { authClient } from '@/store/AuthStore';

import { useRoute } from 'vue-router';  // useRouter

import router from "@/router"
// const router = useRouter()

import PostButtonDelete from '../components/Button/PostButtonDelete.vue';
import SubmitButton from '../components/Button/SubmitButton.vue';
import AbortButton from '@/components/Button/AbortButton.vue';

const route = useRoute()
const post_id = route.params.id

const title = ref("");
const content = ref("");

const post= ref([])

const alertMessage = ref("")

const handleSubmit = () => {
  console.log("Form submitted");
};

// Get Post
const getPosts = async (id) => {
    const res = await authClient.get(`/api/posts/${id}`)
    post.value = res.data
    title.value = post.value.title
    content.value = post.value.content
}

onMounted(()=>{
  getPosts(post_id)
})

const handleUpdate = async (id) => {
    try{
        const rest = await authClient.put(`/api/posts/${id}`, {
            title: title.value,
            content: content.value
        })

        if(rest.status == 200) {
          //alertMessage.value = "Dein Tweet wurde bearbeitet!"
        router.push("/dashboard")  
        // Zweite Variante:
        // router.push({name: "dashboard"})
        }

    } catch(e) {
        alert("Etwas ist schiefgelaufen!");
        console.log(e);
    }
}
</script>

<template>
    <div class="container">

        <div class="text-wrapper">

              <h3>Tweet bearbeiten</h3> 
              <AbortButton text="Abbrechen" />
        </div>

        <div class="form-wrap">
            <div v-if="alertMessage">{{ alertMessage }}</div>
            <form action="" method="POST" @submit.prevent="$event=>handleUpdate(post_id)">
                <div class="form-group">
                    <label for ="title">Titel</label><br>
                    <input type="text" id="title" name="title" v-model="title">
                </div>
                <div class="form-group">
                    <label for ="content">Text</label><br>
                    <textarea id="content" name="content" rows="5" v-model="content"></textarea>
                </div>
                <div class="btns">
                  <SubmitButton>Tweet updaten</SubmitButton>
                  <PostButtonDelete type="button" :post_id="post_id" />
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
  margin-bottom: 60px;
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

@media (max-width: 768px) {
  .container {
    margin: 0 10px;
  }
}  

.btns {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}
</style>