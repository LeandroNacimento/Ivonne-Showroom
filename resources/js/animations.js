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
