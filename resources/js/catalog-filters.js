import { navigateTo } from './spa-simulation';

export function initCatalogFilters() {
    const drawer = document.getElementById('filter-drawer');
    if (!drawer) return;

    const drawerPanel = drawer.querySelector('.drawer-panel');
    const drawerBackdrop = drawer.querySelector('.drawer-backdrop');
    const openBtns = document.querySelectorAll('.open-filters-btn');
    const closeBtns = document.querySelectorAll('.close-filters-btn');
    const body = document.body;

    // Mobile Drawer Logic
    const openDrawer = () => {
        drawer.classList.remove('invisible');
        drawer.classList.add('pointer-events-auto');
        setTimeout(() => {
            drawerPanel.classList.remove('translate-x-full');
            drawerBackdrop.classList.add('opacity-100');
            body.style.overflow = 'hidden'; // Lock scroll
        }, 10);
    };

    const closeDrawer = () => {
        drawerPanel.classList.add('translate-x-full');
        drawerBackdrop.classList.remove('opacity-100');
        body.style.overflow = ''; // Unlock scroll
        setTimeout(() => {
            drawer.classList.add('invisible');
            drawer.classList.remove('pointer-events-auto');
        }, 500);
    };

    openBtns.forEach(btn => btn.addEventListener('click', openDrawer));
    closeBtns.forEach(btn => btn.addEventListener('click', closeDrawer));
    drawerBackdrop.addEventListener('click', closeDrawer);

    // Initial logic for filtering (applied to current DOM)
    document.querySelectorAll('.catalog-filter').forEach(el => {
        // Ensure we don't attach multiple listeners if called again
        if (!el.dataset.filterInit) {
            el.dataset.filterInit = 'true';
            // Logic handled by delegation at document level below
        }
    });
}

// Global Delegation & SPA Listeners (Once)
if (typeof window !== 'undefined' && !window.catalogFiltersInitialized) {
    window.catalogFiltersInitialized = true;

    // Delegate change events for filters
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('catalog-filter')) {
            const form = document.getElementById('catalog-filters-form');
            if (form) {
                // ... applyFilters logic moved here or called from here
                applyFiltersInternal(form);
            }
        }
    });

    // Re-init specialized UI bits on SPA swap
    document.addEventListener('spa:content-loaded', () => {
        initCatalogFilters();
    });
}

function applyFiltersInternal(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }

    const baseUrl = form.getAttribute('action');
    const finalUrl = `${baseUrl}?${params.toString()}`;

    navigateTo(finalUrl).then(() => {
        const gridStart = document.getElementById('catalog-products');
        if (gridStart) {
            const headerOffset = 100;
            const elementPosition = gridStart.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    });
}
