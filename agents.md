1. Identidad del Proyecto

Nombre: Ivonne Showroom
Tipo: E-commerce / Catálogo de Moda Femenina
Modelo: Venta conversacional vía WhatsApp
Ubicación: Formosa, Argentina

Este proyecto representa una marca real.
La prioridad es estética, confianza y fluidez, no complejidad técnica.

2. Arquitectura Base (NO NEGOCIABLE)

Laravel 12

Blade Templates

Tailwind CSS v4 (@theme)

Vite

Arquitectura MPA tradicional

🚫 No SPA global
🚫 No frameworks JS
🚫 No dependencias innecesarias

3. Uso de JavaScript (Principio Rector)

JavaScript:

Nunca sostiene el sitio

Solo mejora la experiencia

Puede ser eliminado sin romper nada

Si algo requiere JS para funcionar → no se implementa así.

4. Simulación de SPA (Web Pública Únicamente)
Alcance

Solo frontend público

Nunca /admin

Nunca afecta SEO

Estructura Obligatoria

<main id="spa-root">
  <div class="spa-content">
    @yield('content')
  </div>
</main>

📌 Solo se reemplaza .spa-content
📌 Nunca se mueve <main>, <body>, nav o footer

5. Animaciones Globales (UX)
Tipo

Fade-in

Translate Y (10–20px)

Stagger leve

Frecuencia

✔ Sutil y constante
✔ En todo el sitio
❌ No protagonista

Trigger

IntersectionObserver

Agregar .active

Nunca modificar estilos base

6. Desktop vs Mobile
Desktop

Animaciones completas

Fluidez visual

Transiciones suaves

Mobile

Fade simple

Sin slides

Sin lógica compleja

Prioridad: rapidez

7. Catálogo (Reglas Especiales)
Scroll

Siempre inicia arriba

Recuerda página de paginación

Paginación

✔ Sin recarga de página
✔ Animación suave
✔ Solo dentro del catálogo

📌 Esto es una mejora localizada, no SPA global.

Implementación esperada:

Fetch parcial

Reemplazo del grid

Fade/transition

URL actualizada (opcional pero recomendado)

8. Producto & Stock

Stock por talle/color es crítico

Estados claros

Nada ambiguo

Nada “forzado”

9. Carrito & Checkout

Carrito en sesión

Sin login

Checkout finaliza en WhatsApp

El mensaje debe ser:
✔ Claro
✔ Humano
✔ Fácil de leer

10. Futuro: Pagos

MercadoPago / Transferencia

No implementar sin pedido

No preparar abstracciones innecesarias

11. Panel de Administración

Independiente

Sin animaciones públicas

Sin SPA

Sin JS compartido

12. Flujo Obligatorio para Cambios

Antes de tocar código, el agente debe:

Explicar qué va a hacer

Decir qué archivo toca

Decir qué NO toca

Confirmar que el diseño no cambia

Si no puede → no implementar

13. Regla Final

Ivonne Showroom vende sensación, no tecnología.
Si algo se nota, se descarta.
Si algo fluye, se queda.