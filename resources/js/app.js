import "./bootstrap";
import { initAnimations } from "./animations";
import { initSpaSimulation } from "./spa-simulation";

// import Alpine from 'alpinejs'; // COMENTADO
// window.Alpine = Alpine;       // COMENTADO
// Alpine.start();               // COMENTADO

document.addEventListener("DOMContentLoaded", () => {
    initAnimations();
    initSpaSimulation();
    console.log("✨ Ivonne Showroom: SPA & Animations Initialized");
});
