<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import AuthService from "@/services/AuthService";

const password = ref("");
const passwordConfirmation = ref("");
const message = ref("");
const loading = ref(false);
const isError = ref(false);
const route = useRoute();
const router = useRouter();

onMounted(() => {
  const token = route.query.token;
  const email = route.query.email;
  
  if (!token || !email) {
    message.value = "Ungültiger oder abgelaufener Link.";
    isError.value = true;
  }
});

async function handleSubmit() {
  if (password.value !== passwordConfirmation.value) {
    message.value = "Die Passwörter stimmen nicht überein.";
    isError.value = true;
    return;
  }

  loading.value = true;
  message.value = "";
  isError.value = false;

  try {
    const token = route.query.token;
    const email = route.query.email;

    if (!token || !email) {
      throw new Error("Ungültiger oder abgelaufener Link.");
    }

    const payload = {
      token,
      email,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    };

    await AuthService.resetPassword(payload);
    message.value = "Ihr Passwort wurde erfolgreich zurückgesetzt.";
    isError.value = false;

    // Nach erfolgreicher Änderung zur Home-Seite weiterleiten
    setTimeout(() => {
      router.push("/");
    }, 2000);
  } catch (error) {
    console.error("Error resetting password:", error);
    message.value = error.response?.data?.message || "Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.";
    isError.value = true;
  } finally {
    loading.value = false;
  }
}
</script>

<template>
    <div class="password-new-container">
      <div class="password-new-form">
        <div class="form-header">
          <h2>Neues Passwort festlegen</h2>
        </div>
  
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="password">Passwort</label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
            />
          </div>
  
          <div class="form-group">
            <label for="password_confirmation">Passwort bestätigen</label>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              type="password"
              required
            />
            <p v-if="message" :class="{ 'error-message': isError, 'success-message': !isError }">
              {{ message }}
            </p>
          </div>
  
          <div class="form-group">
            <button
              type="submit"
              :disabled="loading"
              class="submit-button"
            >
              <span v-if="loading">Wird verarbeitet...</span>
              <span v-else>Passwort speichern</span>
            </button>
          </div>
        </form>
      </div>
    </div>
</template>
    
<style scoped>
.password-new-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
}

.password-new-form {
  max-width: 400px;
  width: 100%;
  padding: 2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-header {
  text-align: center;
  margin-bottom: 2rem;
}

.form-header h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: #333;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #4a90e2;
  box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}

.submit-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #4a90e2;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-button:hover {
  background-color: #357abd;
}

.submit-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.success-message {
  color: #28a745;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>