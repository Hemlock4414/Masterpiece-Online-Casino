<!-- RegStepTwo.vue -->
<script setup>
import { ref, onMounted } from 'vue';
import CancelRegModal from './CancelRegModal.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'previous', 'submit']);

const showCancelModal = ref(false);
const agbAccepted = ref(false);
const validationErrors = ref({
  vorname: '',
  nachname: '',
  geburtsdatum: '',
  agb: ''
});

const hauptLaender = [
    "Schweiz",
    "Deutschland",
    "Österreich",
    "Liechtenstein"
];

// Berechne das maximale Datum (18 Jahre zurück)
const calculateMaxDate = () => {
  const today = new Date();
  const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
  return maxDate.toISOString().split('T')[0];
};

// Konvertierung des Datums in deutsches Format für die Anzeige
const formatDateToGerman = (isoDate) => {
  if (!isoDate) return '';
  const [year, month, day] = isoDate.split('-');
  return `${day}.${month}.${year}`;
};

// Konvertierung des deutschen Datums zurück in ISO-Format für v-model
const formatDateToISO = (germanDate) => {
  if (!germanDate) return '';
  const [day, month, year] = germanDate.split('.');
  return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
};

// Lokales Date-Handling
const localDate = ref(formatDateToGerman(props.modelValue.geburtsdatum));

// Hilfsfunktion zur Überprüfung eines gültigen Datums
const isValidDate = (day, month, year) => {
  // Erstelle ein Date-Objekt und prüfe ob die Werte übereinstimmen
  const date = new Date(year, month - 1, day);
  return date 
    && date.getDate() === Number(day)
    && date.getMonth() === Number(month) - 1
    && date.getFullYear() === Number(year);
};

// Datum-Update Handler
const handleDateChange = (event) => {
  const germanDate = event.target.value;
  localDate.value = germanDate;

  // Wenn das Datum nicht vollständig ist, noch keine Validierung
  if (germanDate.length !== 10) {
    return;
  }

  // Validiere das Format
  if (!/^\d{2}\.\d{2}\.\d{4}$/.test(germanDate)) {
    validationErrors.value.geburtsdatum = 'Bitte geben Sie das Datum im Format TT.MM.JJJJ ein';
    return;
  }

  // Zerlege das Datum
  const [day, month, year] = germanDate.split('.').map(Number);

  // Prüfe ob es ein gültiges Datum ist
  if (!isValidDate(day, month, year)) {
    validationErrors.value.geburtsdatum = 'Bitte geben Sie ein gültiges Datum ein';
    return;
  }

  const isoDate = formatDateToISO(germanDate);
  const inputDate = new Date(isoDate);
  const maxDate = new Date(calculateMaxDate());

  // Prüfe ob das Datum in der Zukunft liegt
  if (inputDate > new Date()) {
    validationErrors.value.geburtsdatum = 'Das Datum darf nicht in der Zukunft liegen';
    return;
  }
  
  // Prüfe das Mindestalter von 18 Jahren
  if (inputDate > maxDate) {
    validationErrors.value.geburtsdatum = 'Sie müssen mindestens 18 Jahre alt sein';
    return;
  }

  // Wenn alle Validierungen bestanden wurden
  validationErrors.value.geburtsdatum = '';
  emit('update:modelValue', {
    ...props.modelValue,
    geburtsdatum: isoDate
  });
};

// Validierungsregeln für Namen
const nameRegex = /^[a-zA-ZäöüÄÖÜß\- ]+$/;

// Input-Handler für Namen
const handleNameInput = (field) => {
  validationErrors.value[field] = '';
  const value = props.modelValue[field];

  if (!value) {
    validationErrors.value[field] = field === 'vorname' 
      ? 'Bitte geben Sie Ihren Vornamen ein'
      : 'Bitte geben Sie Ihren Nachnamen ein';
    return;
  }

  if (!nameRegex.test(value)) {
    validationErrors.value[field] = 'Bitte verwenden Sie nur Buchstaben, Bindestriche und Leerzeichen';
    return;
  }

  // Mindestlänge prüfen
  if (value.length < 2) {
    validationErrors.value[field] = field === 'vorname' 
      ? 'Der Vorname muss mindestens 2 Zeichen lang sein'
      : 'Der Nachname muss mindestens 2 Zeichen lang sein';
    return;
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

  // AGB Validierung hierhin
  if (!agbAccepted.value) {
    validationErrors.value.agb = 'Bitte akzeptieren Sie die AGB';
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

onMounted(() => {
    if (!props.modelValue.nationalitaet) {
        emit('update:modelValue', { 
            ...props.modelValue, 
            nationalitaet: 'Schweiz' 
        });
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
            @input="handleNameInput('vorname')"
            @blur="handleNameInput('vorname')"
            :class="{ 'input-error': validationErrors.vorname }"
            placeholder="Ihr Vorname"
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
            @input="handleNameInput('nachname')"
            @blur="handleNameInput('nachname')"
            :class="{ 'input-error': validationErrors.nachname }"
            placeholder="Ihr Nachname"
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
        <label for="geburtsdatum">Geburtsdatum *</label>
        <div class="input-wrapper">
          <input
            type="text"
            id="geburtsdatum"
            :value="localDate"
            @input="handleDateChange"
            required
            placeholder="TT.MM.JJJJ"
            pattern="\d{2}.\d{2}.\d{4}"
            maxlength="10"
            :class="{ 'input-error': validationErrors.geburtsdatum }"
            title="Bitte geben Sie das Datum im Format TT.MM.JJJJ ein"
          >
          <div class="calendar-icon">
            <img 
              src="/public/img/icons8-18-plus-50.png" 
              alt="18+" 
              width="30" 
              height="30"
            />
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
        </select>
      </div>

      <!-- Checkbox -->
      <div class="input-container">
        <div class="agb-checkbox">
          <input 
            type="checkbox" 
            id="agb-acceptance" 
            v-model="agbAccepted"
            :class="{ 'input-error': validationErrors.agb }"
          >
          <label for="agb-acceptance">
            Ich akzeptiere die
            <a href="/agb" target="_blank">AGB</a>&nbsp;*
          </label>
        </div>
        <div class="error-container">
          <span v-if="validationErrors.agb" class="error-message">
            {{ validationErrors.agb }}
          </span>
          <span v-else class="error-placeholder">&nbsp;</span>
        </div>
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
}

.calendar-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
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

.agb-checkbox {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
}

.agb-checkbox input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.agb-checkbox label {
  display: inline;
  font-weight: 400;
  margin: 0;
}

.agb-checkbox a {
  color: #3b82f6;
  text-decoration: none;
}

.agb-checkbox a:hover {
  text-decoration: underline;
}

.hint {
  display: block;
  margin-top: 1.69rem;
  font-size: 0.875rem;
  color: #6b7280;
}
</style>