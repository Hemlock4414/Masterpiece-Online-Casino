<script setup>
import { defineProps, defineEmits, ref, onMounted } from 'vue';
import CancelRegModal from '../components/CancelRegModal.vue';


const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'previous', 'submit']);

// Variable für Modal-Steuerung
const showCancelModal = ref(false);

const openCancelModal = () => {
  showCancelModal.value = true;
};

const closeCancelModal = () => {
  showCancelModal.value = false;
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
const localDate = ref(formatDateToGerman(props.modelValue.birth_date));

// Datum-Update Handler
const handleDateChange = (event) => {
  const germanDate = event.target.value;

  // Validiere das Format
  if (germanDate && !/^\d{2}\.\d{2}\.\d{4}$/.test(germanDate)) {
    return; // Ungültiges Format, keine Aktualisierung
  }

  localDate.value = germanDate;
  
  // Update des v-model mit ISO-Format, nur updaten wenn das Datum vollständig ist
  if (germanDate.length === 10) {
    emit('update:modelValue', {
      ...props.modelValue,
      geburtsdatum: formatDateToISO(germanDate)
  });
  }
};

const hauptLaender = [
  "Schweiz",
  "Deutschland",
  "Österreich",
  "Liechtenstein"
];

const alleLaender = [
  "Afghanistan",
  "Ägypten",
  "Albanien",
  "Algerien",
  "Andorra",
  "Angola",
  "Antigua und Barbuda",
  "Äquatorialguinea",
  "Argentinien",
  "Armenien",
  "Aserbaidschan",
  "Äthiopien",
  "Australien",
  "Bahamas",
  "Bahrain",
  "Bangladesch",
  "Barbados",
  "Belgien",
  "Belize",
  "Benin",
  "Bhutan",
  "Bolivien",
  "Bosnien und Herzegowina",
  "Botswana",
  "Brasilien",
  "Brunei",
  "Bulgarien",
  "Burkina Faso",
  "Burundi",
  "Chile",
  "China",
  "Costa Rica",
  "Dänemark",
  "Dominikanische Republik",
  "Dschibuti",
  "Ecuador",
  "El Salvador",
  "Eritrea",
  "Estland",
  "Fidschi",
  "Finnland",
  "Frankreich",
  "Gabun",
  "Gambia",
  "Georgien",
  "Ghana",
  "Griechenland",
  "Grenada",
  "Guatemala",
  "Guinea",
  "Guinea-Bissau",
  "Guyana",
  "Haiti",
  "Honduras",
  "Indien",
  "Indonesien",
  "Irak",
  "Iran",
  "Irland",
  "Island",
  "Israel",
  "Italien",
  "Jamaika",
  "Japan",
  "Jemen",
  "Jordanien",
  "Kambodscha",
  "Kamerun",
  "Kanada",
  "Kap Verde",
  "Kasachstan",
  "Katar",
  "Kenia",
  "Kirgisistan",
  "Kiribati",
  "Kolumbien",
  "Komoren",
  "Kongo",
  "Kroatien",
  "Kuba",
  "Kuwait",
  "Laos",
  "Lesotho",
  "Lettland",
  "Libanon",
  "Liberia",
  "Libyen",
  "Litauen",
  "Luxemburg",
  "Madagaskar",
  "Malawi",
  "Malaysia",
  "Malediven",
  "Mali",
  "Malta",
  "Marokko",
  "Marshallinseln",
  "Mauretanien",
  "Mauritius",
  "Mazedonien",
  "Mexiko",
  "Mikronesien",
  "Moldawien",
  "Monaco",
  "Mongolei",
  "Montenegro",
  "Mosambik",
  "Myanmar",
  "Namibia",
  "Nauru",
  "Nepal",
  "Neuseeland",
  "Nicaragua",
  "Niederlande",
  "Niger",
  "Nigeria",
  "Nordkorea",
  "Norwegen",
  "Oman",
  "Pakistan",
  "Palau",
  "Panama",
  "Papua-Neuguinea",
  "Paraguay",
  "Peru",
  "Philippinen",
  "Polen",
  "Portugal",
  "Ruanda",
  "Rumänien",
  "Russland",
  "Salomonen",
  "Sambia",
  "Samoa",
  "San Marino",
  "Saudi-Arabien",
  "Schweden",
  "Senegal",
  "Serbien",
  "Seychellen",
  "Sierra Leone",
  "Simbabwe",
  "Singapur",
  "Slowakei",
  "Slowenien",
  "Somalia",
  "Spanien",
  "Sri Lanka",
  "Südafrika",
  "Sudan",
  "Südkorea",
  "Suriname",
  "Swasiland",
  "Syrien",
  "Tadschikistan",
  "Tansania",
  "Thailand",
  "Timor-Leste",
  "Togo",
  "Tonga",
  "Tschad",
  "Tschechien",
  "Tunesien",
  "Türkei",
  "Turkmenistan",
  "Tuvalu",
  "Uganda",
  "Ukraine",
  "Ungarn",
  "Uruguay",
  "Usbekistan",
  "Vanuatu",
  "Vatikanstadt",
  "Venezuela",
  "Vereinigte Arabische Emirate",
  "Vereinigte Staaten von Amerika",
  "Vietnam",
  "Zentralafrikanische Republik"
].sort();

// Filtere die Hauptländer aus der Liste der übrigen Länder
const uebrigeLaender = alleLaender.filter(land => !hauptLaender.includes(land));

// Setze die Schweiz als Standardwert
onMounted(() => {
  if (!props.modelValue.nationalitaet) {
    emit('update:modelValue', { ...props.modelValue, nationalitaet: 'Schweiz' });
  }
});

// Maximales Datum für das Geburtsdatum (heute)
const maxDate = ref(new Date().toISOString().split('T')[0]);

// Reactive Variable für AGB-Akzeptanz
const agbAccepted = ref(false);

// Modifizierte handleSubmit-Methode mit AGB-Prüfung
const handleSubmit = () => {
  if (!agbAccepted.value) {
    alert('Bitte akzeptieren Sie die AGB, um die Registrierung abzuschließen.');
    return;
  }
  emit('submit');
};

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

    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="vorname">Vorname *</label>
        <input
          type="text"
          id="vorname"
          v-model="modelValue.vorname"
          required
        >
      </div>
      <div class="form-group">
        <label for="nachname">Nachname *</label>
        <input
          type="text"
          id="nachname"
          v-model="modelValue.nachname"
          required
        >
      </div>
      <div class="form-group">
        <label for="birth_date">Geburtsdatum *</label>
        <input
          type="text"
          id="birth_date"
          :value="localDate"
          @input="handleDateChange"
          required
          placeholder="TT.MM.JJJJ"
          pattern="\d{2}.\d{2}.\d{4}"
          maxlength="10"
          class="date-input"
          title="Bitte geben Sie das Datum im Format TT.MM.JJJJ ein"
        >
      </div>
      <div class="form-group">
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

      <!-- Checkbox für AGB-Akzeptanz -->
      <div class="form-group">
        <div class="agb-checkbox">
          <input 
            type="checkbox" 
            id="agb-acceptance" 
            v-model="agbAccepted"
            required
          >
          <label for="agb-acceptance">
            Ich akzeptiere die
            <a href="/agb" target="_blank">AGB</a>&nbsp;*
          </label>
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
.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

input, select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
}

input:focus, select:focus  {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Styles für die AGB-Checkbox */
.agb-checkbox {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
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

/* Styles für die Buttons */
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

/* Styles Cancel Button */
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

.date-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
  background-color: white;
  font-family: inherit;
}

.date-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Für Browser, die type="date" unterstützen aber wir wollen das deutsche Format erzwingen */
.date-input::-webkit-calendar-picker-indicator {
  display: none;
}

.date-input::-webkit-inner-spin-button,
.date-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>