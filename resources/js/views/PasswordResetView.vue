// PasswordResetView.vue
<script setup>
import { ref } from "vue";
import AuthService from "@/services/AuthService";
import { useRouter } from "vue-router";

const email = ref("");
const message = ref("");
const loading = ref(false);
const isError = ref(false);
const router = useRouter();

async function submitForgotPassword() {
  loading.value = true;
  message.value = "";
  isError.value = false;
  
  try {
    const payload = { email: email.value };
    await AuthService.forgotPassword(payload);
    message.value = "Wir haben Ihnen einen Link zum Zurücksetzen Ihres Passworts zugesandt.";
    email.value = "";
    isError.value = false;
  } catch (error) {
    console.error("Error sending reset password email:", error);
    message.value = error.response?.data?.message || "Es ist ein Fehler aufgetreten. Bitte versuche es erneut.";
    isError.value = true;
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <main>
    <div class="password-reset-container">
      <div class="password-reset-form">
        <div class="form-header">
          <h2>Passwort zurücksetzen</h2>
        </div>

        <form @submit.prevent="submitForgotPassword">
          <div class="form-group">
            <label for="email">E-Mail</label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              placeholder="ihre@email.de"
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
              <span v-if="loading">Wird gesendet...</span>
              <span v-else>Senden</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</template>
  
  
  <style scoped>
  .password-reset-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f5f5;
  }
  
  .password-reset-form {
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
    margin-bottom: 1rem;
  }
  
  .form-header p {
    color: #666;
    font-size: 0.9rem;
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