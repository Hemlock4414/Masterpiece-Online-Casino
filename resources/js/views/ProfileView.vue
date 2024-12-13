<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/store/AuthStore";
import DeleteAccountModal from "../components/DeleteAccountModal.vue";
import EmailChangeModal from '../components/EmailChangeModal.vue';
import PasswordChangeModal from '../components/PasswordChangeModal.vue';
import CharacterGenerator from '../components/CharacterGenerator.vue';

const router = useRouter();
const authStore = useAuthStore();

const isSubmitting = ref(false);
const isLoading = ref(false);
const previewImage = ref(null);
const isUploading = ref(false);
const updateMessage = ref("");
const updateError = ref(false);
const messageTimeout = ref(null);
const fileInput = ref(null);
const profileImage = computed(() => authStore.profilePicUrl);

const showDeleteAccountModal = ref(false);
const isDeletingAccount = ref(false);

const showEmailModal = ref(false);
const showPasswordModal = ref(false);

// Refs für Verabschiedung
const showGoodbye = ref(false);
const goodbyeMessage = ref('');

// Konstanten für Bildvalidierung
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

const joinedDate = ref(""); // das Beitrittsdatum speichern

const authUser = ref({
  // Benutzerinformationen hier speichern
  user: {
    username: "",
    firstname: "",
    lastname: "",
    birthdate: "",
    nationality: "",
    email: "",
    password: "",
    balance: 1000,           
    created_at: null,     
    profile_pic: null,    
  },
});

const openEmailModal = () => {
  showEmailModal.value = true;
};

const openPasswordModal = () => {
  showPasswordModal.value = true;
};

const handleSuccess = (message) => {
  showMessage(message);
};

// Funktion für Bildvorschau
const createImagePreview = (file) => {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewImage.value = e.target.result;
      resolve();
    };
    reader.readAsDataURL(file);
  });
};

// Hilfsfunktion zum Validieren des Bildes
const validateImage = (file) => {
  if (!ALLOWED_TYPES.includes(file.type)) {
    return {
      isValid: false,
      message: 'Bitte nur Bilder im JPEG, PNG oder GIF Format hochladen'
    };
  }

  if (file.size > MAX_FILE_SIZE) {
    return {
      isValid: false,
      message: 'Das Bild darf maximal 5MB groß sein'
    };
  }

  return { isValid: true };
};

// Hilfsfunktion zum Anzeigen von Nachrichten
const showMessage = (message, isError = false) => {
  if (messageTimeout.value) {
    clearTimeout(messageTimeout.value);
  }
  
  updateMessage.value = message;
  updateError.value = isError;

  // Nachricht nach 3 Sekunden ausblenden
  messageTimeout.value = setTimeout(() => {
    updateMessage.value = '';
    updateError.value = false;
  }, 3000);
};

// Modal zum Löschen öffnen
const openDeleteAccountModal = () => {
  showDeleteAccountModal.value = true;
};

// Hilfsfunktion zum Öffnen des Datei-Dialogs
const triggerFileInput = () => {
  fileInput.value.click();
};

const deleteUser = async () => {
  try {
    isDeletingAccount.value = true;
    const response = await authStore.deleteAccount();
    
    showDeleteAccountModal.value = false;
    
    showGoodbye.value = true;
    goodbyeMessage.value = "Auf Wiedersehen! Wir bedauern, Sie zu verlieren.";
    
    setTimeout(() => {
      showGoodbye.value = false;
      router.push("/"); 
    }, 3000);
  } catch (error) {
    console.error("Fehler beim Löschen des Kontos:", error);
    
    // Fallback-Handling
    showDeleteAccountModal.value = false;
    showGoodbye.value = true;
    goodbyeMessage.value = "Konto wurde gelöscht. Auf Wiedersehen!";
    
    setTimeout(() => {
      showGoodbye.value = false;
      router.push("/"); 
    }, 3000);
  } finally {
    isDeletingAccount.value = false;
  }
};

// Profilbild löschen
const deleteProfilePicture = async () => {
    try {
        isUploading.value = true;
        await authStore.deleteProfilePicture();
        showMessage('Profilbild wurde erfolgreich zurückgesetzt');
        previewImage.value = null;
    } catch (error) {
        console.error('Fehler beim Zurücksetzen des Profilbildes:', error);
        showMessage('Fehler beim Zurücksetzen des Profilbildes', true);
    } finally {
        isUploading.value = false;
    }
};

// Hauptfunktion zum Hochladen des Bildes
const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Bild validieren
  const { isValid, message } = validateImage(file);
  if (!isValid) {
    showMessage(message, true);
    return;
  }

  try {
    isUploading.value = true;

    // Vorschau erstellen
    await createImagePreview(file);

    // FormData erstellen und Bild hochladen
    const formData = new FormData();
    formData.append('profile_pic', file);

    await authStore.updateProfilePicture(formData);

    // Erfolgsmeldung anzeigen
    showMessage('Profilbild wurde erfolgreich aktualisiert!');

    // Benutzerdaten neu laden um aktualisiertes Bild zu erhalten
    await authStore.getAuthUser();
  } catch (error) {
    console.error('Fehler beim Hochladen des Bildes:', error);
    showMessage('Fehler beim Hochladen des Bildes. Bitte versuchen Sie es erneut.', true);
    
    // Vorschau zurücksetzen bei Fehler
    previewImage.value = null;
  } finally {
    isUploading.value = false;
    // Datei-Input zurücksetzen
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  }
};

// Benutzerdaten beim Laden der Seite abrufen
onMounted(async () => {
  try {
    await authStore.getAuthUser();

    const response = await authStore.getAuthUser();
    console.log('API Response Daten:', response.data.user);
    
    // Benutzerdaten strukturieren
    authUser.value = {
      user: {
        username: authStore.user?.user?.username || "",
        firstname: authStore.user?.user?.firstname || "",
        lastname: authStore.user?.user?.lastname || "",
        email: authStore.user?.user?.email || "",
        birthdate: authStore.user?.user?.birth_date || "",
        nationality: authStore.user?.user?.nationality || "",
        balance: authStore.user?.user?.balance || 0,
        created_at: authStore.user?.user?.created_at,
        profile_pic: authStore.user?.user?.profile_pic || null
      },
    };

    // Beitrittsdatum formatieren
    if (authUser.value.user?.created_at) {
      const options = {
        day: "numeric",
        month: "long",
        year: "numeric",
      };
      joinedDate.value = new Date(
        authUser.value.user.created_at
      ).toLocaleDateString("de-DE", options);
    }
  } catch (error) {
    console.error("Fehler beim Laden des Users:", error);
    showMessage('Fehler beim Laden der Benutzerdaten', true);
  }
});

</script>

<template>
  <main>
    <div class="container">
      <div class="profile-container">
        <div class="profile-content">
          <h1>Profil</h1>
          <div class="purple-line"></div>

          <section>
            
            <h2>Profilbild</h2>
            <div class="profile-info-section">
              <div class="profile-main">
                <div class="profileImage">
                  <div class="image-preview">
                    <img
                    :src="previewImage || authStore.profilePicUrl || '/storage/defaults/default-avatar.png'"
                    class="profile-picture"
                    alt="Profilbild"
                    />
                    <div v-if="isUploading" class="upload-overlay">
                      <span class="loading-spinner"></span>
                      <span>Wird hochgeladen...</span>
                    </div>
                  </div>

                  <div class="image-upload"> 
                    <input
                      type="file"
                      ref="fileInput"
                      style="display: none"
                      accept="image/*"
                      @change="handleFileUpload"
                      :disabled="isUploading"
                    />
                    <div class="button-group">
                      <button 
                        @click="triggerFileInput" 
                        :disabled="isUploading"
                        class="upload-button"
                      >
                        {{ isUploading ? 'Wird hochgeladen...' : 'Profilbild ändern' }}
                      </button>
                      <button 
                        @click="deleteProfilePicture" 
                        :disabled="isUploading"
                        class="delete-button"
                      >
                        Profilbild zurücksetzen
                      </button>
                    </div>
                    <div 
                      v-if="updateMessage" 
                      :class="['message', updateError ? 'error' : 'success']"
                      role="alert"
                    >
                      {{ updateMessage }}
                    </div>
                  </div>

                </div>

                <div class="balance-section">
                  <h2 class="balance-title">Aktuelles Guthaben:</h2>

                  <div class="balance">
                    <span>{{ authUser.user.balance }}</span>
                  </div>
                </div>
              </div>
            </div>

            <CharacterGenerator />
          </section>

          <section>
            <h2>Kontoinformationen</h2>

            <div class="account-info">
              <div class="account-detail">
                <label>Angemeldet seit:</label>                
                <input type="text" :value="joinedDate" readonly />
                <span class="placeholder"></span> <!-- Leerer Platzhalter für den Button -->
              </div>

              <div class="account-detail">
                <label>Spielername:</label>
                <input type="text" :value="authUser.user.username" readonly />
                <span class="placeholder"></span>
              </div>

              <div class="account-detail">
                <label>Vorname:</label>
                <input type="text" :value="authUser.user.firstname" readonly />
                <span class="placeholder"></span>
              </div>

              <div class="account-detail">
                <label>Nachname:</label>
                <input type="text" :value="authUser.user.lastname" readonly />
                <span class="placeholder"></span>
              </div>

              <div class="account-detail">
                <label>Geburtsdatum:</label>
                <input type="text" :value="authUser.user.birthdate" readonly />
                <span class="placeholder"></span>
              </div>

              <div class="account-detail">
                <label>Nationalität:</label>
                <input type="text" :value="authUser.user.nationality" readonly />
                <span class="placeholder"></span>
              </div>

              <div class="account-detail">
                <label>E-Mail:</label>
                <input type="text" :value="authStore.user?.user?.email"  readonly />
                <button @click="openEmailModal" class="btn-change">Ändern</button>              
              </div>

              <div class="account-detail">
                <label>Passwort:</label>
                <input type="password" value="**********" readonly />
                <button @click="openPasswordModal" class="btn-change">Ändern</button>
              </div>
            </div>
          </section>

            <div class="notice">
              <p><b>Hinweis:</b> Wenn andere Angaben geändert werden sollen, ist eine E-Mail an uns nötig.</p>
            </div>
          
          <section class="account-delete">
            <h2>Konto löschen</h2>

            <p>Das löschen des Kontos führt zu:</p>
            <ol>
              <li>
                Löscht das Profil dauerhaft.
              </li>
              <li>
                Alle gespeicherten Informationen und das Spielgeld werden gelöscht.
              </li>
              <li>Erlaubt, dass Ihr Benutzername/Spielername für jeden verfügbar wird.</li>
            </ol>

            <button
              @click="openDeleteAccountModal"
              class="btn-danger"
              :disabled="isDeletingAccount"
            >
              {{ isDeletingAccount ? "Wird gelöscht..." : "Löschen" }}
            </button>
          </section>
        </div>
      </div>
    </div>
    <!-- Verabschiedungs-Popup -->
    <div v-if="showGoodbye" class="goodbye-message">
      {{ goodbyeMessage }}
    </div>

    <DeleteAccountModal
      v-model="showDeleteAccountModal"
      :isLoading="isDeletingAccount"
      @confirm="deleteUser"
    />

    <EmailChangeModal 
      v-if="showEmailModal"
      @close="showEmailModal = false"
      @success="handleSuccess"
    />
    
    <PasswordChangeModal 
      v-if="showPasswordModal"
      @close="showPasswordModal = false"
      @success="handleSuccess"
    />
  </main>
</template>

<style scoped>
.container {
  width: 100%;
  transition: all 0.3s ease;
  padding: 20px;
  display: flex;
  justify-content: center;
  padding-top: 43px;
}

.profile-container {
  max-width: 680px;
  height: min-content;
  border-radius: 8px;
  border: 1px solid #909090;
  display: flex;
  flex-direction: column;
  padding: 15px;
  margin: 0 10px;
}

.profile-content {
  width: 100%;
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

.profile-info-section {
  display: flex;
  justify-content: space-around;
  align-items: center;
  margin-bottom: 20px;
}

.profile-main {
  display: flex;
  flex-direction: row;
  gap: 40px;
}

.profileImage {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 1rem;
  margin-top: 30px;
  position: relative;
}

.image-preview {
  width: 180px;
  height: 180px;
  border-radius: 25%;
  overflow: hidden;
  border: 2px solid #909090;
}

.profile-picture {
  width: 100%;
  height: 100%;
  object-fit: cover;
  /* transform: scale(1.2); */
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  gap: 10px;
}

.loading-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #ce3df3;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.image-upload {
  width: 200px;
  height: 90px;
  border-radius: 25px;
  border: 1px solid #909090;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative; 
}

.image-upload:hover:not(.disabled) {
  background-color: #f5f5f5;
  border-color: #ce3df3;
}

.image-upload.disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.upload-button,
.delete-button {
  padding: 8px 16px;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  border: 1px solid;
}

.upload-button {
  background-color: #eee;
  color: #333;
  border-color: #909090;
}

.upload-button:hover:not(:disabled) {
  background-color: #f5f5f5;
  border-color: #ce3df3;
}

.upload-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.delete-button {
  background-color: #f8d7da;
  color: #721c24;
  border-color: #f5c6cb;
}

.delete-button:hover:not(:disabled) {
  background-color: #721c24;
  color: white;
}

.delete-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 10px;
  padding: 10px 15px;
  border-radius: 4px;
  font-size: 14px;
  animation: fadeIn 0.3s ease;
  z-index: 1;
}

.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

section {
  margin: 40px 0;
}

h2 {
  text-align: left;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 15px;
}

.account-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
  max-width: 800px;
  margin: 0 auto;
}

.account-detail {
  display: flex;
  align-items: center; /* Vertikale Ausrichtung der Inhalte */
  justify-content: space-between;
  gap: 15px;
  background-color: #fff;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ddd;
}

.account-detail label {
  flex: 1;  /* Label nimmt 1 Teil des Platzes */
  font-weight: 600;
  margin-right: 10px;
  text-align: left
}

input {
  flex: 2;  /* Eingabefeld nimmt 2 Teile des Platzes */
  padding: 5px;
  border: 1px solid #ccc;
  border-radius: 3px;
  background-color: #eee;
  text-align: left;
  font-size: 14px;
}

input[readonly] {
  background-color: #f9f9f9;
  color: #555;
}

.placeholder {
  flex: 1; /* Platz reservieren */
  visibility: hidden; /* Unsichtbar machen */
}

.btn-change {
  flex: 1;  /* Button nimmt 1 Teil des Platzes */
  padding: 5px 10px;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  text-align: center;
}

.btn-change:hover {
  background-color: #555;
}

.balance-section {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.balance-title {
  text-align: center;
}

.balance {
  align-self: center;
  font-size: 36px;
  font-weight: 800;
  color: green;
}

ol {
  padding: 20px;
}

button[type="submit"],
.btn-danger
 {
  border-radius: 15px;
  border: solid 1px #000000;
  padding: 10px 15px;
  font-size: 14px;
  font-weight: 700;
  text-align: center;
  width: fit-content;
}

.btn-danger {
  margin-top: 15px;
  background-color: #a80f33;
  color: #ffffff;
}

.btn-danger:hover {
  color: #000000;
  cursor: pointer;
  background-color: #d91544;
}

.info-display {
  padding: 5px;
  background-color: #f0f0f0;
  border: 1px solid #ddd;
  border-radius: 4px;
  min-height: 20px;
}

.account-delete {
  border: red 3px solid;
  padding: 30px;
  border-radius: 8px;
}

.goodbye-message {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #4a5568;
  color: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  z-index: 1100;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@media (max-width: 650px) {
  .profileImage {
    display: flex;
    flex-direction: column;
  }

}

@media (max-width: 500px) {
  .profile-container {
    margin: 0 5px;
  }
  .profile-main {
    gap: 0px;
  }
  .account-detail {
    display: flex;
    flex-direction: column;
  }
  .btn-change {
    padding: 5px 30px;
  }
  input {
    text-align: center;
  }
}

@media (max-width: 420px) {
  .profile-info-section {
    gap: 30px;
  }
  .profile-container {
    padding: 5px;
  }
  .container {
  padding: 0px;
  }
  .account-delete {
    padding: 10px;
  }
  ol {
    padding-right: 0;
  }
}
</style>
