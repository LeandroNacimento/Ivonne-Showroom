1. Identidad del Proyecto

Nombre: Ivonne Showroom
Tipo: E-commerce / Catálogo de Moda Femenina
Modelo: Venta conversacional vía WhatsApp
Ubicación: Formosa, Argentina

Este proyecto representa una marca real.
La prioridad es estética, confianza y fluidez, no complejidad técnica.

2. Entorno de Desarrollo (OBLIGATORIO)

El proyecto se ejecuta en entorno Docker.

Todos los comandos Artisan deben ejecutarse mediante:

docker compose exec app php artisan <comando>

Ejemplos:

docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan make:migration

🚫 Nunca ejecutar php artisan directamente en host.
🚫 Nunca asumir entorno local fuera de Docker.

3. Arquitectura Base (NO NEGOCIABLE)

Laravel 12

Blade Templates

Tailwind CSS v4 (@theme)

Vite

Arquitectura MPA tradicional

🚫 No SPA global
🚫 No frameworks JS
🚫 No dependencias innecesarias

4. Base de Datos (DECISIÓN DEFINITIVA)

Motor oficial y único soportado:

MySQL 8+ (InnoDB)

Configuración obligatoria:

Engine: InnoDB

Charset: utf8mb4

Collation: utf8mb4_unicode_ci

✔ Se permite uso completo de cascadas múltiples (cascadeOnDelete)
✔ Se permite uso de características nativas de InnoDB
✔ Se optimiza exclusivamente para MySQL

🚫 No se mantiene compatibilidad con SQL Server
🚫 No se agregan workarounds por limitaciones de otros drivers
🚫 No se diseñan estructuras condicionadas por Azure SQL

El diseño debe estar:

Normalizado (3FN cuando aplique)

Indexado correctamente

Optimizado para consultas reales del frontend

5. Control de Versiones y Flujo de Trabajo (Gitflow)

Ramas:

main → producción

develop → integración

feature/\* → nuevas funcionalidades

fix/\* → correcciones

hotfix/\* → errores críticos

Reglas del Agente

❌ Nunca hace commit
❌ Nunca hace push
✅ Solo prepara cambios locales
✅ Solo se commitea cuando el usuario lo indique explícitamente

6. Convención de Commits

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

7. Uso de JavaScript (Principio Rector)

JavaScript:

Nunca sostiene el sitio

Solo mejora la experiencia

Puede eliminarse sin romper nada

📌 Si algo requiere JS para lógica de negocio → no se implementa así.
📌 Interactividad compleja → Livewire.
📌 JS solo controla:

visibilidad

animaciones

estados locales de UI

8. Arquitectura MPA con Interactividad Livewire (NO NEGOCIABLE)

🚫 No SPA global
🚫 No fetch navigation
🚫 No DOM replacement manual
🚫 No Vue / React / Inertia

✔ Livewire permitido y preferido
✔ Alpine.js solo para UX local

9. Animaciones Globales (UX)

Tipo:

Fade-in

Translate Y (10–20px)

Stagger leve

Frecuencia:

✔ Sutil
✔ Constante
❌ No protagonista

Trigger:

IntersectionObserver

Agregar clase .active

Nunca modificar estilos base

10. Desktop vs Mobile

Desktop:

Animaciones completas

Fluidez visual

Transiciones suaves

Mobile:

Fade simple

Sin slides

Sin lógica compleja

📌 Prioridad absoluta: rapidez

11. Producto & Stock

Stock por talle/color es crítico

Estados claros

Nada ambiguo

Nada forzado

La disponibilidad se calcula desde backend.
Nunca desde lógica JS.

12. Carrito & Checkout

Carrito en sesión

Sin login

Checkout finaliza en WhatsApp

El mensaje debe ser:

✔ Claro
✔ Humano
✔ Fácil de leer

13. Seguridad (Prioridad Absoluta)

Cada cambio backend debe considerar:

Validación estricta

Policies

Middleware

Guards

Prevención de:

XSS

CSRF

Acceso no autorizado

Filtrado de datos sensibles

Si no puede justificarse en términos de seguridad → no se implementa.

14. Panel de Administración

Totalmente independiente

Sin animaciones públicas

Sin SPA

Sin JS compartido con frontend

Arquitectura limpia y orientada a gestión, no a marketing.

15. Imágenes (Reglas Determinísticas)

La imagen principal nunca se determina por orden implícito.

La lógica vive en modelo o capa de dominio.

Blade solo renderiza.

URLs desacopladas del filesystem.

Todas las vistas usan la misma fuente de verdad.

Si no puede determinarse → usar placeholder controlado.

Nunca lógica condicional dispersa en vistas.

16. Flujo Obligatorio para Cambios

Antes de tocar código, el agente debe:

Explicar qué va a hacer

Decir qué archivo toca

Decir qué NO toca

Confirmar que el diseño visual no cambia

Si no puede cumplir esto → no implementar.

17. Frontend Público — Principio Rector

El frontend público es Livewire-driven.

Navegación: MPA tradicional

Livewire gestiona:

filtros

paginación

estado del carrito

Alpine.js solo UX local

JS nunca reemplaza HTML ni navega

Si algo puede resolverse con Livewire → no usar JS.

18. Regla Final

Ivonne Showroom vende sensación, no tecnología.

Si algo se nota, se descarta.
Si algo fluye, se queda.
