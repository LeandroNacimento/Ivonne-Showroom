import { initAnimations, initSmoothScroll, initScrollSpy } from './animations';

export function initSpaSimulation() {
    // Protección: Si no estamos en el frontend público (no hay .spa-content), no hacer nada.
    if (!document.querySelector('.spa-content')) {
        return;
    }

    // Escucha global de clicks (Delegación)
    document.addEventListener('click', (e) => {
        // ... resto del código ...
        // Encontrar el anchor más cercano
        const link = e.target.closest('a');

        // Validaciones básicas para IGNORAR el click
        if (!link) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return; // Nuevas pestañas
        if (link.getAttribute('target') === '_blank') return; // Target blank

        const href = link.getAttribute('href');
        if (!href) return;

        // Ignorar anchors puros (#) o rutas externas
        if (href.startsWith('#')) return;
        if (!href.startsWith(window.location.origin) && !href.startsWith('/')) return;

        // Si es la misma página exacta (misma ruta y mismos parámetros), ignorar
        const currentUrl = new URL(window.location.href);
        if (targetUrl.pathname === currentUrl.pathname && targetUrl.search === currentUrl.search) {
            // Si hay un hash distinto, dejar que el navegador lo maneje o dejar pasar si es navegación a ancla
            if (targetUrl.hash !== currentUrl.hash) return;
            
            e.preventDefault(); // Evitar recarga si es click idéntico
            return;
        }

        // --- INICIO SIMULACIÓN SPA ---
        e.preventDefault();
        navigateTo(href);
    });

    // Manejo de Back/Forward del navegador
    window.addEventListener('popstate', () => {
        navigateTo(window.location.href, false);
    });
}

export function navigateTo(url, push = true) {
    return new Promise((resolve, reject) => {
        const content = document.querySelector('.spa-content');
        if (!content) {
            window.location.href = url;
            return resolve();
        }

        content.classList.add('spa-loading');

        fetch(url, { headers: { 'X-SPA': 'true' } })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('.spa-content');

                if (!newContent) throw new Error('.spa-content not found');

                if (push) history.pushState(null, '', url);

                setTimeout(() => {
                    content.innerHTML = newContent.innerHTML;

                    const targetUrl = new URL(url, window.location.origin);
                    if (targetUrl.hash) {
                        const targetElement = document.getElementById(targetUrl.hash.substring(1));
                        if (targetElement) {
                            targetElement.scrollIntoView({ behavior: 'smooth' });
                        } else {
                            window.scrollTo({ top: 0, behavior: 'instant' });
                        }
                    } else {
                        window.scrollTo({ top: 0, behavior: 'instant' });
                    }

                    initAnimations();
                    initSmoothScroll();
                    initScrollSpy();

                    void content.offsetWidth;
                    content.classList.remove('spa-loading');

                    const newTitle = doc.querySelector('title');
                    if (newTitle) document.title = newTitle.innerText;

                    // Trigger event for other components to re-init
                    document.dispatchEvent(new CustomEvent('spa:content-loaded', { 
                        detail: { url, doc } 
                    }));

                    resolve();
                }, 300);
            })
            .catch(error => {
                console.warn('[SPA] Fallback reason:', error);
                window.location.href = url;
                resolve();
            });
    });
}
