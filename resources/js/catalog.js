import { navigateTo } from './spa-simulation';

export function initCatalog() {
    // Interceptar cambios en los filtros del catálogo
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('catalog-filter')) {
            const form = e.target.closest('form');
            if (!form) return;

            // Construir la URL con los parámetros del formulario
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            const baseUrl = form.getAttribute('action') || window.location.pathname;
            const newUrl = `${baseUrl}?${params.toString()}`;

            // Navegar usando la lógica SPA
            navigateTo(newUrl);
        }
    });
}
