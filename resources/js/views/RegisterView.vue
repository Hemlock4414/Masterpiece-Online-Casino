<script setup>
import { ref } from "vue";
import { useAuthStore } from "@/store/AuthStore";
import router from "@/router";

import RegStepOne from '../components/RegStepOne.vue';
import RegStepTwo from '../components/RegStepTwo.vue';

const { register, getAuthUser } = useAuthStore();

const currentStep = ref(1);
const formData = ref({
  // Schritt 1
  spielername: '',
  email: '',
  password: '',
  password_confirmation: '',
  // Schritt 2
  vorname: '',
  nachname: '',
  geburtsdatum: '',
  nationalitaet: ''
});

const handleRegister = async () => {
  try {
    // Log der Daten zur Überprüfung
    console.log('Registrierungsdaten:', {
      playername: formData.value.spielername,
      firstname: formData.value.vorname,
      lastname: formData.value.nachname,
      email: formData.value.email,
      birth_date: formData.value.geburtsdatum,
      nationality: formData.value.nationalitaet,
      password: formData.value.password,
      password_confirmation: formData.value.password_confirmation
    });

    const respReg = await register({
      playername: formData.value.spielername,
      firstname: formData.value.vorname,
      lastname: formData.value.nachname,
      email: formData.value.email,
      birth_date: formData.value.geburtsdatum,
      nationality: formData.value.nationalitaet,
      password: formData.value.password,
      password_confirmation: formData.value.password_confirmation
    });

    if (respReg.status === 201) {
      await getAuthUser();
      router.push("/");
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      const errorMessages = Object.values(error.response.data.errors)
        .flat()
        .join('\n');
      alert(errorMessages);
    } else {
      alert("Bei der Registrierung ist ein Fehler aufgetreten");
    }
  }
};

const nextStep = () => {
  currentStep.value++;
};

const previousStep = () => {
  currentStep.value--;
};
</script>
 
<template>
  <main>
    <div class="container">
      <div class="registration-wrapper">
        <div class="text-wrapper">
          <h3>Herzlich Willkommen auf NAME</h3>
          <p>Beginnen Sie jetzt Ihre Registrierung in nur wenigen Schritten</p>
        </div>
  
        <div class="steps-indicator">
          <div class="step" :class="{ active: currentStep === 1 }">1</div>
          <div class="step-line"></div>
          <div class="step" :class="{ active: currentStep === 2 }">2</div>
        </div>
  
        <div class="form-wrap">
          <RegStepOne
            v-if="currentStep === 1"
            v-model="formData"
            @next="nextStep"
          />
          <RegStepTwo
            v-if="currentStep === 2"
            v-model="formData"
            @previous="previousStep"
            @submit="handleRegister"
          />
        </div>
      </div>
    </div>
  </main>  
</template>

<style scoped>
.container {
  max-width: 480px;
  margin: 2rem auto;
  padding: 0 1rem;
}

.registration-wrapper {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  position: relative;
}

.text-wrapper {
  text-align: center;
  margin-bottom: 2rem;
}

.text-wrapper h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.steps-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2rem;
}

.step {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.step.active {
  background: #3b82f6;
  color: white;
}

.step-line {
  height: 2px;
  width: 4rem;
  background: #e5e7eb;
  margin: 0 0.5rem;
}
</style>