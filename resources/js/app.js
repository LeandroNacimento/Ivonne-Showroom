import "./bootstrap";
import { initAnimations } from "./animations";
import Sortable from 'sortablejs';

// Make Sortable available globally for inline scripts
window.Sortable = Sortable;

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
    initHeroSortable();
});

function initHeroSortable() {
    const list = document.getElementById('slides-sortable');
    if (!list) return;

    const reorderUrl = list.dataset.reorderUrl;
    let previousOrder = [...list.querySelectorAll('[data-slide-id]')].map(el => el.dataset.slideId);

    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd() {
            const newOrder = [...list.querySelectorAll('[data-slide-id]')].map(el => el.dataset.slideId);

            fetch(reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: newOrder }),
            })
            .then(r => {
                if (!r.ok) throw new Error();
                previousOrder = newOrder;
            })
            .catch(() => {
                // Revertir al orden anterior en el DOM
                const items = [...list.querySelectorAll('[data-slide-id]')];
                const byId  = Object.fromEntries(items.map(el => [el.dataset.slideId, el]));
                previousOrder.forEach(id => { if (byId[id]) list.appendChild(byId[id]); });

                alert('No se pudo guardar el nuevo orden. Verificá tu conexión e intentá de nuevo.');
            });
        },
    });
}
