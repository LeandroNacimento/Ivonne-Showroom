import "./bootstrap";
import { initAnimations } from "./animations";

import carousel from './carousel';
import homeHeroCarousel from './home-hero';
import orderForm from './admin/order-form';
import productGallery from './product-gallery';

// Register Alpine components before Livewire's Alpine.start() runs.
// Livewire v4 ships its own Alpine — do NOT import alpinejs again.
document.addEventListener('alpine:init', () => {
    Alpine.data('categoriesCarousel', carousel);
    Alpine.data('homeHeroCarousel', homeHeroCarousel);
    Alpine.data('orderForm', orderForm);
    Alpine.data('productGallery', productGallery);
});

document.addEventListener("DOMContentLoaded", () => {
    initAnimations();
});
