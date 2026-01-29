import './bootstrap';
import { initAnimations, initSmoothScroll, initScrollSpy } from './animations';

// import Alpine from 'alpinejs'; // COMENTADO
// window.Alpine = Alpine;       // COMENTADO
// Alpine.start();               // COMENTADO

document.addEventListener('DOMContentLoaded', () => {
    initAnimations();
    initSmoothScroll();
    initScrollSpy();
});