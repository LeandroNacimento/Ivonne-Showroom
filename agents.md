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

3. Control de Versiones y Flujo de Trabajo (Git)
   Gitflow (Obligatorio)

Las ramas se manejan bajo el modelo Gitflow:

main: producción

develop: integración

feature/\*: nuevas funcionalidades

fix/\*: correcciones

hotfix/\*: errores críticos en producción

Reglas del Agente

❌ El agente NUNCA hace commit

❌ El agente NUNCA hace push

✅ Solo prepara cambios locales

✅ Solo se commitea cuando el usuario lo indique explícitamente

4. Convención de Commits

Todos los commits deben cumplir:

Idioma: Español

Formato: Conventional Commits

Ejemplos válidos:

feat: agregar validación de stock por talle

fix: corregir cálculo de total en carrito

refactor: optimizar consulta de productos

chore: ajustar configuración de vite

🚫 Commits genéricos
🚫 Commits en inglés
🚫 Commits automáticos

5. Uso de JavaScript (Principio Rector)

JavaScript:

Nunca sostiene el sitio

Solo mejora la experiencia

Puede eliminarse sin romper nada

📌 Si algo requiere JS para funcionar, no se implementa así.

6. Simulación de SPA (Web Pública Únicamente)
   Alcance

La UI debe construirse de forma reutilizable, consistente y mantenible.

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

7. Animaciones Globales (UX)
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

Agregar clase .active

Nunca modificar estilos base

8. Desktop vs Mobile
   Desktop

Animaciones completas

Fluidez visual

Transiciones suaves

Mobile

Fade simple

Sin slides

Sin lógica compleja

📌 Prioridad absoluta: rapidez

9. Catálogo (Reglas Especiales)
   Scroll

Siempre inicia arriba

Recuerda página de paginación

Paginación

✔ Sin recarga de página
✔ Animación suave
✔ Solo dentro del catálogo

📌 Mejora localizada, no SPA global

Implementación esperada:

Fetch parcial

Reemplazo del grid

Fade / transition

URL actualizada (opcional pero recomendado)

10. Producto & Stock

Stock por talle/color es crítico

Estados claros

Nada ambiguo

Nada forzado

11. Carrito & Checkout

Carrito en sesión

Sin login

Checkout finaliza en WhatsApp

El mensaje debe ser:
✔ Claro
✔ Humano
✔ Fácil de leer

12. Base de Datos (Reglas Críticas)

Todo cambio en BD debe ser:

Eficiente

Consistente

Transaccional cuando aplique

Evitar:

queries innecesarias

duplicación de datos

estructuras rígidas

📌 El diseño debe ser compatible con:

MySQL

SQL Server (Azure Hosting)

🚫 Nada que dependa de comportamientos exclusivos de MySQL
🚫 Nada que rompa reglas de SQL Server

13. Seguridad (Prioridad Absoluta)

Cada cambio en backend debe considerar:

Protección de datos críticos

Validación estricta de inputs

Uso correcto de:

policies

middleware

guards

Prevención de:

XSS

CSRF

acceso no autorizado

filtrado de información sensible

📌 Si una implementación no puede justificarse en términos de seguridad → no se implementa.

14. Panel de Administración

Totalmente independiente

Sin animaciones públicas

Sin SPA

Sin JS compartido con frontend

15. Flujo Obligatorio para Cambios

Antes de tocar código, el agente debe:

Explicar qué va a hacer

Decir qué archivo toca

Decir qué NO toca

Confirmar que el diseño no cambia

Si no puede cumplir esto → no implementar.

16. Regla Final

Ivonne Showroom vende sensación, no tecnología.

Si algo se nota, se descarta.

Si algo fluye, se queda.
