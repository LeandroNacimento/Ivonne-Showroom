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

📌 Si algo requiere JavaScript para sostener lógica de negocio, no se implementa así.
📌 La interactividad compleja debe resolverse con Livewire.
📌 JavaScript solo controla:

- visibilidad
- animaciones
- estados locales de UI

6. Arquitectura MPA con interactividad Livewire (NO NEGOCIABLE)

🚫 No SPA global
🚫 No fetch navigation
🚫 No DOM replacement manual
🚫 No frameworks JS (Vue / React / Inertia)

✔ Livewire permitido y preferido para interactividad
✔ Alpine.js solo para UX local

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

9. Producto & Stock

Stock por talle/color es crítico

Estados claros

Nada ambiguo

Nada forzado

10. Carrito & Checkout

Carrito en sesión

Sin login

Checkout finaliza en WhatsApp

El mensaje debe ser:
✔ Claro
✔ Humano
✔ Fácil de leer

11. Base de Datos (Reglas Críticas)

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

12. Seguridad (Prioridad Absoluta)

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

13. Panel de Administración

Totalmente independiente

Sin animaciones públicas

Sin SPA

Sin JS compartido con frontend

14. Flujo Obligatorio para Cambios

Antes de tocar código, el agente debe:

Explicar qué va a hacer

Decir qué archivo toca

Decir qué NO toca

Confirmar que el diseño no cambia

Si no puede cumplir esto → no implementar.

15. Imagenes

Las imágenes de productos deben manejarse con reglas explícitas y determinísticas.

- La imagen principal de un producto nunca se define por coincidencia ni por orden implícito.
- La lógica para obtener la imagen principal debe vivir fuera de las vistas (modelo o capa de dominio).
- Blade no decide qué imagen mostrar, solo renderiza el resultado.
- La construcción de URLs de imágenes debe estar desacoplada del filesystem.
- Todas las secciones públicas (home, catálogo, detalle) deben usar la misma fuente de verdad para la imagen principal.

Si una imagen no puede determinarse con claridad, se debe usar un placeholder controlado, nunca lógica condicional dispersa en vistas.

16. Regla Final

Ivonne Showroom vende sensación, no tecnología.

Si algo se nota, se descarta.

Si algo fluye, se queda.

17. Frontend Público — Principio Rector

El frontend público es Livewire-driven.

- La navegación es MPA tradicional.
- Livewire gestiona:
    - filtros
    - paginación
    - estado del carrito
- Alpine.js gestiona únicamente UX local.
- JavaScript nunca reemplaza HTML ni navega.

📌 Si una funcionalidad puede resolverse con Livewire, no se implementa en JavaScript.
