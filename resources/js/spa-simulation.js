import { initAnimations, initSmoothScroll, initScrollSpy } from './animations';

export function initSpaSimulation() {
    // Escucha global de clicks (Delegación)
    document.addEventListener('click', (e) => {
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

        // Si es la misma página (ruta exacta), ignorar (evita loop de carga)
        // Salvo que queramos un "refresh" suave, pero generalmente no.
        const targetUrl = new URL(href, window.location.origin);
        if (targetUrl.pathname === window.location.pathname && !targetUrl.search) {
            // Si es solo cambio de hash, dejarlo pasar
            if (targetUrl.hash) return;
            e.preventDefault(); // Evitar recarga si es click identico
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

async function navigateTo(url, push = true) {
    const content = document.querySelector('.spa-content');
    if (!content) {
        // Fallback crítico: si no hay estructura, navegar normal
        window.location.href = url;
        return;
    }

    // 1. Feedback visual inmediato (Salida)
    content.classList.add('spa-loading');

    try {
        // 2. Fetch del contenido
        const response = await fetch(url, {
            headers: { 'X-SPA': 'true' }
        });

        if (!response.ok) throw new Error('Network error');

        const html = await response.text();

        // 3. Parsing (Solo extraemos .spa-content)
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('.spa-content');

        if (!newContent) {
            throw new Error('.spa-content not found in response');
        }

        // 4. Actualizar URL (si es navegación nueva)
        if (push) {
            history.pushState(null, '', url);
        }

        // 5. Reemplazo del contenido (Swap)
        // Usamos un pequeño timeout para dar sensación de transición si fue muy rápido
        setTimeout(() => {
            content.innerHTML = newContent.innerHTML;

            // Scroll al top
            window.scrollTo({ top: 0, behavior: 'instant' });

            // 6. Reinicializar scripts de UI
            initAnimations();
            initSmoothScroll();
            initScrollSpy();

            // 7. Feedback visual (Entrada)
            // Forzar reflow para reiniciar animaciones CSS si las hubiera
            void content.offsetWidth;
            content.classList.remove('spa-loading');

            // Actualizar título del documento
            const newTitle = doc.querySelector('title');
            if (newTitle) document.title = newTitle.innerText;

        }, 300); // 300ms coincide con la transición CSS

    } catch (error) {
        console.warn('[SPA] Fallback to standard navigation reason:', error);
        window.location.href = url;
    }
}
