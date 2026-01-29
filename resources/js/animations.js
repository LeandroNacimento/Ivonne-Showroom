export function initAnimations() {
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -50px 0px', // Activa un poco antes de que entre del todo, pero con margen inferior
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Pequeño delay artificial para asegurar que el renderizado esté listo
                setTimeout(() => {
                    entry.target.classList.add('active');
                }, 100);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const elements = document.querySelectorAll('.reveal, .reveal-fade, .reveal-stagger-container');

    elements.forEach(el => {
        // Si es un contenedor de stagger (cascada), observamos el contenedor
        if (el.classList.contains('reveal-stagger-container')) {
            observer.observe(el);
        } else {
            observer.observe(el);
        }
    });

    // Lógica especial para contenedores con hijos en cascada (stagger)
    // Cuando el contenedor se hace visible, animamos sus hijos secuencialmente
    document.querySelectorAll('.reveal-stagger-container').forEach(container => {
        const containerObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const children = entry.target.querySelectorAll('.reveal-child');
                    children.forEach((child, index) => {
                        setTimeout(() => {
                            child.classList.add('active');
                        }, index * 100); // 100ms de diferencia entre cada hijo
                    });
                    containerObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);
        containerObserver.observe(container);
    });
}

// Función para scroll suave al hacer click en links internos (Navegación Soft Arrival)
export function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"], a[href^="/#"], a[href^="' + window.location.pathname + '#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            // Obtener el href del elemento clickeado
            const href = this.getAttribute('href');
            let hash = href;

            // Si viene con ruta completa (ej. /#categorias), validar si es para esta página
            if (href.includes('#')) {
                // Si el link empieza con / y estamos en /, o si empieza con la ruta actual
                if (href.startsWith('/') && href.length > 1) {
                    // Caso /#section
                    const path = href.split('#')[0];
                    if (path !== window.location.pathname && path !== '/') {
                        return; // Es para otra página, dejar navegar normal
                    }
                    hash = '#' + href.split('#')[1];
                }
            } else {
                // Si no tiene hash (ej. sólo "/"), verificar si es link a top
                if (href === '/' && window.location.pathname === '/') {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: "smooth" });
                    return;
                }
                // Si es solo "/" y no estamos en home, dejamos navegar.
                if (!hash.startsWith('#')) return;
            }

            // Solo interceptamos si el target existe en esta página
            try {
                const targetElement = document.querySelector(hash);
                if (targetElement) {
                    e.preventDefault();

                    // Calculamos la posición considerando el header fijo
                    const headerOffset = 80; // Altura aproximada del header
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.scrollY - headerOffset;

                    // Si estamos muy cerca (<50px), no hacemos nada (evita micro-scrolls molestos)
                    if (Math.abs(window.scrollY - offsetPosition) < 50) return;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });
                }
            } catch (err) {
                // Si el selector es inválido
            }
        });
    });
}

// Función para Scroll Spy (Detectar sección activa)
// REFACTORIZADA: Usa .spy-section y data-spy-target para robustez
export function initScrollSpy() {
    // 1. Selector más robusto: solo secciones explícitamente marcadas
    const spySections = document.querySelectorAll('.spy-section');
    const navLinks = document.querySelectorAll('.nav-link[data-spy-target]');

    // Si no hay secciones espias, solo chequeamos URL y salimos (para páginas estáticas)
    if (spySections.length === 0) {
        checkActiveUrl(navLinks);
        return;
    }

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.55 // Un poco más de la mitad para evitar falsos positivos
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                const targetSelector = '#' + id;

                // Buscar link que apunte a este target
                // Buscamos exacto el data-spy-target que coincida con #ID
                const activeLink = document.querySelector(`.nav-link[data-spy-target="${targetSelector}"]`);

                if (activeLink) {
                    navLinks.forEach(link => link.classList.remove('active'));
                    activeLink.classList.add('active');
                } else {
                    // Si la sección visible no tiene link
                    // Verificamos si debemos apagar "Inicio" (si estuviera activo) para no confundir
                    navLinks.forEach(link => {
                        if (link.getAttribute('data-spy-target') === '#inicio') {
                            link.classList.remove('active');
                        }
                    });
                }
            }
        });
    }, observerOptions);

    spySections.forEach(section => {
        observer.observe(section);
    });

    // Verificación inicial de URL para páginas estáticas
    checkActiveUrl(navLinks);
}

// Función auxiliar para chequear URL (extraída para claridad)
function checkActiveUrl(navLinks) {
    const currentPath = window.location.pathname;

    navLinks.forEach(link => {
        const target = link.getAttribute('data-spy-target');

        // Si el target parece una ruta (empieza con / y no tiene #)
        if (target && target.startsWith('/') && !target.includes('#')) {
            // Comparación relajada: si currentPath empieza con target (para subsecciones) o es exacto
            // Ej: /catalogo/producto-1 vs /catalogo
            if (currentPath === target || (currentPath.startsWith(target) && target !== '/')) {
                link.classList.add('active');
            }
            // Caso especial Inicio (/)
            if (target === '/' && currentPath === '/') {
                // Dejamos que ScrollSpy maneje #inicio si existe, si no, activamos aquí
                if (!document.querySelector('#inicio')) {
                    link.classList.add('active');
                }
            }
        }
    });
}
