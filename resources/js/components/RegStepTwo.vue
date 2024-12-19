<!-- RegStepTwo.vue -->
<script setup>
import { ref, onMounted, computed } from 'vue';
import { authClient } from '@/services/AuthService';
import CancelRegModal from './CancelRegModal.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'previous', 'submit']);

const showCancelModal = ref(false);
const validationErrors = ref({
  vorname: '',
  nachname: '',
  geburtsdatum: '',
  nationalitaet: ''
});

const hauptLaender = ref([]);
const uebrigeLaender = ref([]);

// Kalenderdaten
const selectedDay = ref('');
const selectedMonth = ref('');
const selectedYear = ref('');

// Berechne das minimale Jahr (18 Jahre zurück)
const minYear = computed(() => {
  const date = new Date();
  return date.getFullYear() - 100; // Maximales Alter 100 Jahre
});

const maxYear = computed(() => {
  const date = new Date();
  return date.getFullYear() - 18; // Minimales Alter 18 Jahre
});

// Generiere Arrays für die Dropdowns
const years = computed(() => {
  const years = [];
  for (let year = maxYear.value; year >= minYear.value; year--) {
    years.push(year);
  }
  return years;
});

const months = computed(() => {
  return Array.from({ length: 12 }, (_, i) => {
    return {
      value: i + 1,
      label: new Date(2000, i).toLocaleString('de-DE', { month: 'long' })
    };
  });
});

const days = computed(() => {
  if (!selectedYear.value || !selectedMonth.value) return Array.from({ length: 31 }, (_, i) => i + 1);
  
  const daysInMonth = new Date(selectedYear.value, selectedMonth.value, 0).getDate();
  return Array.from({ length: daysInMonth }, (_, i) => i + 1);
});

// Laden der Länderdaten
onMounted(async () => {
  try {
    const response = await authClient.get('/api/countries');
    hauptLaender.value = response.data.hauptLaender;
    uebrigeLaender.value = response.data.uebrigeLaender;
    
    // Setze Schweiz als Standard wenn noch kein Land gewählt wurde
    if (!props.modelValue.nationalitaet) {
      emit('update:modelValue', { 
        ...props.modelValue, 
        nationalitaet: 'Schweiz' 
      });
    }
  } catch (error) {
    console.error('Fehler beim Laden der Länder:', error);
  }
});

// Datum-Update Handler
const handleDateChange = () => {
  if (selectedDay.value && selectedMonth.value && selectedYear.value) {
    const formattedDate = `${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}-${String(selectedDay.value).padStart(2, '0')}`;
    emit('update:modelValue', {
      ...props.modelValue,
      geburtsdatum: formattedDate
    });
  }
};

const handleSubmit = () => {
  // Reset Fehler
  Object.keys(validationErrors.value).forEach(key => {
    validationErrors.value[key] = '';
  });

  // Validierung
  let isValid = true;

  if (!props.modelValue.vorname) {
    validationErrors.value.vorname = 'Bitte geben Sie Ihren Vornamen ein';
    isValid = false;
  }

  if (!props.modelValue.nachname) {
    validationErrors.value.nachname = 'Bitte geben Sie Ihren Nachnamen ein';
    isValid = false;
  }

  if (!props.modelValue.geburtsdatum) {
    validationErrors.value.geburtsdatum = 'Bitte geben Sie Ihr Geburtsdatum ein';
    isValid = false;
  }

  if (isValid) {
    emit('submit');
  }
};

const openCancelModal = () => {
  showCancelModal.value = true;
};

const closeCancelModal = () => {
  showCancelModal.value = false;
};

onMounted(async () => {
  try {
    const response = await authClient.get('/api/countries');
    hauptLaender.value = response.data.hauptLaender;
    uebrigeLaender.value = response.data.uebrigeLaender;
    
    // Setze Schweiz als Standard
    if (!props.modelValue.nationalitaet) {
      emit('update:modelValue', { 
        ...props.modelValue, 
        nationalitaet: 'Schweiz' 
      });
    }
  } catch (error) {
    console.error('Fehler beim Laden der Länder:', error);
  }
});
</script>

<template>
  <div>
    <div class="cancel-button-container">
      <button 
        type="button" 
        class="btn-cancel"
        @click="openCancelModal"
      >
        ✕
      </button>
    </div>

    <form @submit.prevent="handleSubmit" novalidate>
      <!-- Vorname -->
      <div class="input-container">
        <label for="vorname">Vorname *</label>
        <div class="input-wrapper">
          <input
            type="text"
            id="vorname"
            v-model="modelValue.vorname"
            :class="{ 'input-error': validationErrors.vorname }"
          >
        </div>
        <div class="error-container">
          <span v-if="validationErrors.vorname" class="error-message">
            {{ validationErrors.vorname }}
          </span>
          <span v-else class="error-placeholder">&nbsp;</span>
        </div>
      </div>

      <!-- Nachname -->
      <div class="input-container">
        <label for="nachname">Nachname *</label>
        <div class="input-wrapper">
          <input
            type="text"
            id="nachname"
            v-model="modelValue.nachname"
            :class="{ 'input-error': validationErrors.nachname }"
          >
        </div>
        <div class="error-container">
          <span v-if="validationErrors.nachname" class="error-message">
            {{ validationErrors.nachname }}
          </span>
          <span v-else class="error-placeholder">&nbsp;</span>
        </div>
      </div>

      <!-- Geburtsdatum -->
      <div class="input-container">
        <label>Geburtsdatum *</label>
        <div class="date-inputs-wrapper">
          <div class="date-select-container">
            <select 
              v-model="selectedDay"
              @change="handleDateChange"
              class="date-select"
            >
              <option value="" disabled selected>Tag</option>
              <option v-for="day in days" :key="day" :value="day">
                {{ day }}
              </option>
            </select>

            <select 
              v-model="selectedMonth"
              @change="handleDateChange"
              class="date-select"
            >
              <option value="" disabled selected>Monat</option>
              <option v-for="month in months" 
                      :key="month.value" 
                      :value="month.value">
                {{ month.label }}
              </option>
            </select>

            <select 
              v-model="selectedYear"
              @change="handleDateChange"
              class="date-select"
            >
              <option value="" disabled selected>Jahr</option>
              <option v-for="year in years" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>
          <div class="calendar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </div>
        </div>
        <div class="error-container">
          <span v-if="validationErrors.geburtsdatum" class="error-message">
            {{ validationErrors.geburtsdatum }}
          </span>
          <span v-else class="error-placeholder">&nbsp;</span>
        </div>
      </div>

      <!-- Nationalität -->
      <div class="input-container">
        <label for="nationalitaet">Nationalität</label>
        <select
          id="nationalitaet"
          v-model="modelValue.nationalitaet"
          class="select-input"
        >
          <option value="" disabled>Bitte wählen Sie Ihr Land</option>
          <option 
            v-for="land in hauptLaender" 
            :key="land" 
            :value="land"
          >
            {{ land }}
          </option>
          <option disabled>──────────</option>
          <option 
            v-for="land in uebrigeLaender" 
            :key="land" 
            :value="land"
          >
            {{ land }}
          </option>
        </select>
      </div>

      <div class="button-group">
        <button type="button" class="btn-secondary" @click="$emit('previous')">
          Zurück
        </button>
        <button type="submit" class="btn-primary">Abschliessen</button>
      </div>
      <small class="hint">* erforderlich</small>
    </form>

    <CancelRegModal 
      :is-visible="showCancelModal"
      @close="closeCancelModal"
    />
  </div>
</template>

<style scoped>
.input-container {
  display: flex;
  flex-direction: column;
  min-height: 90px;
}

.input-wrapper {
  position: relative;
  width: 100%;
}

.date-inputs-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
}

.date-select-container {
  display: flex;
  gap: 10px;
  flex: 1;
}

.date-select {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
  flex: 1;
}

.calendar-icon {
  color: #6b7280;
  margin-right: 10px;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

input, select, .select-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
}

input:focus, select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.error-container {
  min-height: 24px;
  margin-top: 0.25rem;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
}

.error-placeholder {
  display: block;
  visibility: hidden;
  height: 1.25rem;
}

.input-error {
  border-color: #ef4444;
}

.button-group {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.cancel-button-container {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.btn-cancel {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.btn-cancel:hover {
  color: #374151;
}

.hint {
  display: block;
  margin-top: 1.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}
</style>