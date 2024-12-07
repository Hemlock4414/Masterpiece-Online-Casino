<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/store/AuthStore";
import DeleteModal from "../components/DeleteModal.vue";

const router = useRouter();
const authStore = useAuthStore();

const isSubmitting = ref(false);
const updateMessage = ref("");
const updateError = ref(false);
const fileInput = ref(null);

const showDeleteModal = ref(false);
const isDeletingAccount = ref(false);

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
  },
});

const isLoading = ref(false);
const { profileImage } = useAuthStore();

/* const profileImage = ref(null);
const defaultProfilePicture = "../user-profile-icon.jpg"; */

const triggerFileInput = () => {
  fileInput.value.click();
};

// Benutzerdaten beim Laden der Seite abrufen
onMounted(async () => {
  try {
    await authStore.fetchUser();
    // Ensure we're creating a proper structure
    authUser.value = {
      user: {
        name: authStore.user?.user?.name || "",
        email: authStore.user?.user?.email || "",
        created_at: authStore.user?.user?.created_at,
      },
    };
    // Format join date
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
  }
});

// Modal zum Löschen öffnen
const openDeleteModal = () => {
  showDeleteModal.value = true;
};

// Funktion zum Löschen des Benutzers
const deleteUser = async () => {
  try {
    isDeletingAccount.value = true;

    await authStore.deleteAccount();
    showDeleteModal.value = false;

    // Redirect zur Home-Seite nach erfolgreichem Löschen
    router.push("/");
  } catch (error) {
    console.error("Error deleting account:", error);
    alert(
      error.response?.data?.message ||
        "Konto konnte nicht gelöscht werden. Bitte versuchen Sie es später noch einmal."
    );
  } finally {
    isDeletingAccount.value = false;
  }
};

// Funktion zum Hochladen des Bildes vervollständigen
const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const formData = new FormData();
      formData.append("profile_pic", file);

      await authStore.uploadProfilePicture(formData);
      // Optional: Erfolgsmeldung anzeigen
      updateMessage.value = "Profile picture updated successfully!";
      updateError.value = false;

      // Optional: Profilbild neu laden
      await authStore.fetchUser();
    } catch (error) {
      console.error("Fehler beim Hochladen des Bildes:", error);
      updateMessage.value = "Error uploading image";
      updateError.value = true;
    }
  }
};

// Optional: Profilbild beim Laden der Seite abrufen
/* onMounted(async () => {
  try {
    await authStore.fetchUser();
    if (authStore.user && authStore.user.user.profile_pic_url) {
      profileImage.value = authStore.user.user.profile_pic_url;
    } else {
      console.log("Kein Profilbild gefunden.");
    }
  } catch (error) {
    console.error("Fehler beim Laden des Profilbildes:", error);
  }
}); */
</script>

<template>

  <div class="container">
    <div class="profile-container">
      <div class="profile-content">
        <h1>Profil</h1>
        <div class="purple-line"></div>

        <section>
          <div class="profile-pic">
            <h2>Profilbild</h2>

            <div class="profileImage">
              <div class="image-preview">
                <img
                  :src="profileImage"
                  alt="Profile Picture"
                  class="profile-picture"
                />
              </div>

              <div class="image-upload" @click="triggerFileInput">
                <input
                  type="file"
                  ref="fileInput"
                  style="display: none"
                  @change="handleFileUpload"
                />
                <i class="fas fa-camera fa-2x"></i>
                <span>Profilbild ändern</span>
              </div>
            </div>
          </div>

          <div class="balance">
            <h2>Aktuelles Guthaben</h2>

            <h2>{{ balance }}</h2>

          </div>
        </section>

        <section>
          <h2>Kontoinformationen</h2>

          <div>
            <div>
              <p>Registriert seit:</p>
                
              <p class="info-display">{{ joinedDate }}</p>
            </div>

            <div>
              <p>Spielername</p>

              <p class="info-display">{{ authUser.user.username }}</p>
            </div>

            <div>
              <p>Vorname</p>

              <p class="info-display">{{ authUser.user.firstname }}</p>
            </div>

            <div>
              <p>Nachname</p>

              <p class="info-display">{{ authUser.user.lastname }}</p>
            </div>

            <div>
              <p>Geburtsdatum</p>

              <p class="info-display">{{ authUser.user.birthdate }}</p>
            </div>

            <div>
              <p>Natonalität</p>

              <p class="info-display">{{ authUser.user.nationality }}</p>
            </div>

            <div>
              <p>E-Mail</p>

              <p class="info-display">{{ authUser.user.email }}</p>

              <button @click="openPasswordModal" class="btn-set">
                Ändern
              </button>
            </div>

            <div>
              <p>Passwort</p>

              <p class="info-display">{{ authUser.user.password }}</p>

              <button @click="openPasswordModal" class="btn-set">
                Ändern
              </button>
            </div>
          </div>
        </section>

        <section class="acc-del">
          <h2>Konto löschen</h2>

          <p>Das löschen des Kontos führt zu:</p>
          <br />
          <ol>
            <li>
              Permanently delete your profile, along with your authentication
              associations.
            </li>
            <li>
              Permanently delete all your content, including your posts,
              bookmarks, comments, etc.
            </li>
            <li>Allow your username to become available to anyone.</li>
          </ol>

          <button
            @click="openDeleteModal"
            class="btn-danger"
            :disabled="isDeletingAccount"
          >
            {{ isDeletingAccount ? "Wird gelöscht..." : "Löschen" }}
          </button>
        </section>
      </div>
    </div>
  </div>

  <DeleteModal
    v-model="showDeleteModal"
    :isLoading="isDeletingAccount"
    @confirm="deleteUser"
  />
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

h2 {
  font-size: 20px;
  font-weight: 500;
  margin-bottom: 15px;
}

p {
  font-size: 14px;
}

.profileImage {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 1rem;
  margin-top: 30px;
}

.image-preview {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid #909090;
}

.profile-picture {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

section {
  margin: 40px 0;
}

.image-upload {
  width: 250px;
  height: 100px;
  border-radius: 25px;
  border-style: solid;
  border-color: #909090;
  display: flex;
  flex-direction: column;
  justify-content: space-evenly;
  align-items: center;
  cursor: pointer;
  margin-top: 20px;
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

.acc-del {
  border: red 3px solid;
  padding: 30px;
  border-radius: 8px;
}

@media (max-width: 500px) {
  .profileImage {
    display: flex;
    flex-direction: column;
  }
}

@media (max-width: 500px) {
  .profile-container {
    margin: 0 5px;
  }
}
</style>
