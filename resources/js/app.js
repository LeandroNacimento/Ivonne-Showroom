import "./bootstrap";
import { initAnimations } from "./animations";
import { initSpaSimulation } from "./spa-simulation";
import { initCatalogFilters } from "./catalog-filters";

import Alpine from 'alpinejs';
import carousel from './carousel';

window.Alpine = Alpine;

Alpine.data('categoriesCarousel', carousel);

Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    initAnimations();
    initSpaSimulation();
    initCatalogFilters();
    console.log("✨ Ivonne Showroom: Boutique Showroom Initialized");
});
