import "./bootstrap";
import { initAnimations } from "./animations";
import { initSpaSimulation } from "./spa-simulation";
import { initCatalog } from "./catalog";

import Alpine from 'alpinejs';
import carousel from './carousel';

window.Alpine = Alpine;

Alpine.data('categoriesCarousel', carousel);

Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    initAnimations();
    initSpaSimulation();
    initCatalog();
    console.log("✨ Ivonne Showroom: SPA & Catalog Initialized");
});
