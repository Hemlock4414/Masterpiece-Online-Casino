<script setup>
import { RouterLink } from "vue-router";
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import Flickity from 'flickity';
// npm install flickity
import 'flickity/css/flickity.css';

// Bilder-Array definieren
const images = ref([
  { src: "/img/blackjack-1.jpg", alt: "Blackjack", link: null }, // Kein Link
  { src: "/img/memory-1.png", alt: "Memory", link: "/memory/play" },
  { src: "/img/poker-1.jpg", alt: "Poker", link: null },
  { src: "/img/roulette-1.jpg", alt: "Roulette", link: null },
  { src: "/img/slots-1.jpg", alt: "Slot Machines", link: null },
  { src: "/img/blackjack-2.jpg", alt: "Blackjack", link: null },
  { src: "/img/poker-2.jpg", alt: "Poker", link: null },
  { src: "/img/roulette-2.jpg", alt: "Roulette", link: null },
  { src: "/img/slots-2.jpg", alt: "Slot Machines", link: null },
]);

let flkty;

onMounted(() => {
  // Warte bis das DOM vollständig geladen ist
  nextTick(() => {
    flkty = new Flickity('.slider', {
    cellAlign: "left",   // "left" für einzelne Bilder
    contain: false,         // false um Lücken zu vermeiden
    groupCells: false,      // false für einzelne Bilder
    autoPlay: 2500,        // Slider wechselt alle 2 Sekunden
    wrapAround: true,      // Endlos-Schleife
    selectedAttraction: 0.0015,  // Beschleunigung der Bildbewegung
    friction: 0.8,             // Verlangsamung des Übergangs
    direction: "ltr",        // Horizontal von links nach rechts
    // Neue Optionen für smootheres Verhalten
    adaptiveHeight: false,
    percentPosition: true,
    freeScroll: false,
    draggable: true,
    watchCSS: false,
    pageDots: false,         // keine Dots
    prevNextButtons: false,   // keine Buttons
    });
    // Autoplay nach dem Draggen wieder starten
    flkty.on('dragEnd', () => {
    flkty.playPlayer();
    });
  });
});

// Optional: Cleanup bei Component Unmount
onUnmounted(() => {
  if (flkty) {
    flkty.destroy();
  }
});

</script>

<template>
    <main>

        <div class="container">
        
            <div class="upper-container">
                <div class="hero-container">
                    <img src="/public/img/hero-header.jpg" alt="">
             
                </div>
            </div> 
            
            <!-- Flickity Slider -->
            <div class="slider">
                <div
                v-for="(img, index) in images"
                :key="index"
                class="carousel-cell"
                >
                <div class="image-container">
                    <RouterLink 
                    v-if="img.link" 
                    :to="img.link"
                    >
                    <img 
                        :src="img.src" 
                        :alt="img.alt" 
                        class="game-image"
                    />
                    <span class="tooltip">{{ img.alt }}</span>
                    </RouterLink>
                    <div v-else>
                    <img 
                        :src="img.src" 
                        :alt="img.alt"
                        class="game-image"
                    />
                    <span class="tooltip">{{ img.alt }}</span>
                    </div>
                </div>
                </div>
            </div>

            <div class="row">
                <!-- Alle 6 Kacheln in einer Row -->
                <RouterLink to="/blackjack/play" style="text-decoration: none;">
                    <div class="card-container">
                        <div class="card">
                            <img src="/public/img/casino-placeholder.jpg" alt="Bild 1" class="card-image">
                        </div>
                        <button class="button">Play Blackjack</button>
                    </div>
                </RouterLink>
                
                <RouterLink to="/memory/play" style="text-decoration: none;">
                    <div class="card-container">
                        <div class="card">
                            <img src="/public/img/casino-placeholder.jpg" alt="Bild 2" class="card-image">
                        </div>
                        <button class="button">Play Memory</button>
                    </div>
                </RouterLink>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 3" class="card-image">
                    </div>
                    <button class="button">Play Poker</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 4" class="card-image">
                    </div>
                    <button class="button">Play Baccarat</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 5" class="card-image">
                    </div>
                    <button class="button">Play Sabacc</button>
                </div>
                
                <div class="card-container">
                    <div class="card">
                        <img src="/public/img/casino-placeholder.jpg" alt="Bild 6" class="card-image">
                    </div>
                    <button class="button">Play Roulette</button>
                </div>
            </div>
            
        </div>
    </main> 
</template>

<style scoped>
.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.upper-container {
    width: 100%;
    background-color: black;  
    display: flex; 
    justify-content: center;
}
  
.hero-container {
    max-width: 1280px; 
}

.hero-container img {
    width: 100%;  
    height: auto;  
    display: block;
}

/* Neue Flickity-spezifische Styles */
.carousel-cell {
  width: 300px;
  height: 200px;
  margin-right: 10px;
}

.carousel-cell img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Wichtig für die Slider-Dimension */
.slider {
  width: 100%;
  /* max-width: 1440px; */
  margin: 10px auto;
  /* Optional: Zusätzliche Styles für besseres Verhalten */
  position: relative;
  overflow: hidden;
}

.game-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
}

.image-container {
  position: relative;
  width: 300px;
  height: 200px;
}

.image-container:hover .game-image {
  filter: brightness(0.7);  /* Verdunkelt das Bild beim Hover */
  transform: scale(1.1);
}

.tooltip {
  position: absolute;
  top: 50%;  /* Mittig über dem Bild */
  left: 50%;
  transform: translate(-50%, -50%);  /* Zentriert den Tooltip */
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 14px;
  opacity: 0;
  transition: opacity 0.3s;
  pointer-events: none;
  z-index: 5;  /* Höherer z-index */
  white-space: nowrap;
}

/* Anzeige des Tooltips beim Hover */
.image-container:hover .tooltip {
  opacity: 1;
}

.row {
    max-width: 1280px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 50px;
    margin-bottom: 20px;
    margin-top: 30px;
}

.card-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 0 10px;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.button {
    width: 100%;
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: large;
    font-weight: 900;
}

.button:hover {
    background: #0056b3;
}

/* Responsive Anpassungen */
@media (max-width: 968px) {
    .row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>