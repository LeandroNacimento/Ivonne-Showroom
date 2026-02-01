import "./bootstrap";
import { initAnimations } from "./animations";
import { initSpaSimulation } from "./spa-simulation";
import { initCatalog } from "./catalog";

// import Alpine from 'alpinejs'; // COMENTADO
// window.Alpine = Alpine;       // COMENTADO
// Alpine.start();               // COMENTADO

document.addEventListener("DOMContentLoaded", () => {
    initAnimations();
    initSpaSimulation();
    initCatalog();
    console.log("✨ Ivonne Showroom: SPA & Catalog Initialized");
});
