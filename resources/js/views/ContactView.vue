<template>
  <main>  
    <div class="main-content">
      <div class="contact-container">
        <h1>Kontakt</h1>
        <div class="purple-line"></div>

        <div class="form-group">
          <label for="name">Name/Spielername *</label>
          <input v-model="contact.name" type="text" id="name" required />
        </div>

        <div class="form-group">
          <label for="email">E-Mail *</label>
          <input v-model="contact.email" type="email" id="email" required />
        </div>

        <div class="form-group">
          <label for="subject">Betreff *</label>
          <input v-model="contact.subject" type="text" id="subject" required />
        </div>

        <div class="form-group">
          <label for="message">Nachricht *</label>
          <textarea
            v-model="contact.message"
            id="message"
            rows="10"
            required
          ></textarea>
        </div>

        <div class="form-group checkbox-group">
          <input
            v-model="contact.agreeToTerms"
            type="checkbox"
            id="terms"
            required
          />
          <label for="terms">Ich habe die <a href="/datenschutz" class="privacy-link">Datenschutzerklärung</a> gelesen und akzeptiere sie. *</label>
        </div>
        <small class="hint">* Erforderlich</small>

        <div class="button-container">
          <a href="/" class="cancel-btn">Abbrechen</a>
          <button
            class="send-btn"
            @click="sendMessage"
            :disabled="!isFormValid || isSending"
          >
            {{ isSending ? "Wird gesendet..." : "Senden" }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import ContactService from "@/services/ContactService";
import AuthService from "@/services/AuthService";

const isSending = ref(false);

const contact = ref({
  name: "",
  email: "",
  message: "",
  agreeToTerms: false,
});

const fetchUserData = async () => {
  try {
    const response = await AuthService.getAuthUser();
    const user = response.data.user;
    contact.value.name = user.name;
    contact.value.email = user.email;
  } catch (error) {
    console.error("Benutzerdaten konnten nicht abgerufen werden:", error);
  }
};

const isFormValid = computed(() => {
  return (
    contact.value.name.trim() !== "" &&
    contact.value.email.trim() !== "" &&
    contact.value.message.trim() !== "" &&
    contact.value.agreeToTerms
  );
});

const sendMessage = async () => {
  if (!isFormValid.value) return;

  isSending.value = true;
  try {
    await ContactService.sendMessage(contact.value);
    alert("Nachricht wurde erfolgreich gesendet!");
    // Reset form
    contact.value = { name: "", email: "", message: "", agreeToTerms: false };
  } catch (error) {
    console.error("Error sending message:", error);
    if (error.response && error.response.data && error.response.data.message) {
      alert(`Fehler beim senden: ${error.response.data.message}`);
    } else {
      alert("Ein Fehler ist aufgetreten. Bitte nochmals versuchen.");
    }
  } finally {
    isSending.value = false;
  }
};

onMounted(() => {
  fetchUserData();
});
</script>

<style scoped>
.main-content {
  z-index: 1;
  width: 100%;
  transition: all 0.3s ease;
  padding: 100px;
  color: black;
  display: flex;
  justify-content: center;
  margin: auto;
}

.contact-container {
  width: 680px;
  max-height: 680px;
  background-color: #fff;
  border-radius: 8px;
  border: 1px solid #909090;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

h1 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 10px;
}

.purple-line {
  height: 2px;
  background-color: #ce3df3;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
}

input[type="text"],
input[type="email"],
textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #909090;
  border-radius: 14px;
  background-color: #F9F9F9;
}

textarea {
  resize: vertical;
}

.checkbox-group {
  display: flex;
  align-items: center;
}

.checkbox-group input {
  margin-right: 10px;
  margin-bottom: 4px;
}

.hint {
  display: block;
  margin-top: 0.1rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.button-container { 
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 15px;
}

.cancel-btn {
  padding: 10px 20px;
  font-weight: 600;
  font-size: 14px;
  border: 1px solid #909090;
  border-radius: 14px;
  cursor: pointer;
  background-color: #ffffff;
  color: black;
  text-decoration: none;
}

.send-btn {
  padding: 10px 20px;
  font-weight: 600;
  font-size: 14px;
  border: none;
  border-radius: 14px;
  cursor: pointer;
  background-color: #000000;
  color: white;
  align-self: flex-end;
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.privacy-link {
  color: red;
}

.privacy-link:hover {
  color: black;
}

</style>
