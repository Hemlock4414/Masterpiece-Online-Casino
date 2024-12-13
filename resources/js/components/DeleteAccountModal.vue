<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

  const emit = defineEmits(['update:modelValue', 'confirm'])

  const closeModal = () => {
    if (!props.isLoading) {
        emit('update:modelValue', false)
    }
}

const confirmAction = async () => {
    if (!props.isLoading) {
        emit('confirm');
    }
};
</script>
  
<template>
    <Transition name="modal">
        <div v-if="modelValue" class="modal-overlay">
            <div class="modal">
                <div class="modal-content">
                    <h2>Konto löschen</h2>
                    <p>Sind Sie sicher, dass Sie Ihr Konto löschen möchten?</p>
                    <p>Alle Ihre gespeicherten Informationen und Ihr gesamtes Spielgeld werden gelöscht.</p>
                    <div class="btns">
                        <button 
                            @click="closeModal" 
                            class="btn-cancel"
                            :disabled="isLoading">
                            Abbrechen
                        </button>
                        <button 
                            @click="confirmAction" 
                            class="btn-del"
                            :disabled="isLoading">
                            {{ isLoading ? 'Wird gelöscht...' : 'Löschen' }}
                        </button>
                    </div>  
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal {
    min-width: 320px;
    max-width: 400px;
    padding: 2rem;
    background-color: #20252D;
    border: solid 1px #909090;
    border-radius: 20px;
    color: white;
    margin: 0 5px;
}

.modal-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

h2 {
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
}

p {
    color: #ff6b6b;
    text-align: center;
}

.btns {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

button {
    border-radius: 15px;
    border: solid 1px #000000;
    padding: 10px 15px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    width: fit-content;
}

button:hover {
    cursor: pointer;
}

.btn-del {
    background-color: #a80f33;
    color: white;
}

.btn-cancel:hover {
    background-color: #000000;
    color: #FFFFFF;
    border: solid 1px #FFFFFF;
}

.btn-del:hover {
    color: #000000;
    cursor: pointer;
    background-color: #D91544;
}

/* Neue Styles für den Loading-Zustand */
button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

button:disabled:hover {
    background-color: inherit;
    color: inherit;
    cursor: not-allowed;
}

.btn-del:disabled:hover {
    background-color: #a80f33;
    color: white;
}

/* Modal Animation */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from {
    opacity: 0;
    transform: scale(0.8) translateY(-30px);
}

.modal-enter-to {
    opacity: 1;
    transform: scale(1) translateY(0);
}

.modal-leave-from {
    opacity: 1;
    transform: scale(1) translateY(0);
}

.modal-leave-to {
    opacity: 0;
    transform: scale(0.8) translateY(30px);
}
</style>
  