<script setup>
import { defineProps, defineEmits, ref, onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'previous', 'submit']);

const handleSubmit = () => {
  emit('submit');
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

</script>

<template>
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
      <label for="geburtsdatum">Geburtsdatum *</label>
      <input
        type="date"
        id="geburtsdatum"
        v-model="modelValue.geburtsdatum"
        required
        :max="maxDate"
        lang="de"
      >
    </div>
    <div class="form-group">
      <label for="nationalitaet">Nationalität *</label>
      <select
        id="nationalitaet"
        v-model="modelValue.nationalitaet"
        required
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
  </form>
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
</style>