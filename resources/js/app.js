import "./bootstrap";
import { initAnimations } from "./animations";

import carousel from './carousel';

// Register Alpine components before Livewire's Alpine.start() runs.
// Livewire v4 ships its own Alpine — do NOT import alpinejs again.
document.addEventListener('alpine:init', () => {
    Alpine.data('categoriesCarousel', carousel);
});

document.addEventListener("DOMContentLoaded", () => {
    initAnimations();
});

