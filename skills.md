# Skills — Ivonne Showroom

Este documento define las habilidades técnicas que el agente puede ejecutar.
Toda skill debe respetar las reglas establecidas en `agents.md`.

Las skills:

- NO toman decisiones de producto
- NO alteran diseño visual sin autorización
- NO ejecutan acciones de Git

🧠 Skills de Análisis (no tocan código)
🔍 analyze_feature

Descripción:
Analiza una funcionalidad solicitada y valida su compatibilidad con la arquitectura del proyecto.

Input:

feature_description (string)

Output:

affected_layers (frontend / backend / db)

potential_risks (array)

architecture_fit (true | false)

🔍 analyze_security_impact

Descripción:
Evalúa impacto de seguridad antes de cualquier cambio backend.

Input:

change_description (string)

Output:

risks (XSS, CSRF, auth, data leakage)

required_mitigations (array)

📌 Obligatoria para cambios en backend o BD.

🛠️ Skills de Backend (Laravel)
🧩 create_laravel_component

Descripción:
Crea o modifica componentes Blade reutilizables sin alterar diseño existente.

Input:

component_name

purpose

affected_views

Output:

files_created_or_modified

📌 No define estilos nuevos, solo estructura.

🧩 modify_controller_logic

Descripción:
Ajusta lógica de controladores respetando:

validación estricta

policies y middleware

consistencia transaccional

Input:

controller

change_description

Output:

files_modified

validation_rules

🧩 update_model_logic

Descripción:
Agrega o modifica lógica de dominio en modelos Eloquent.

Casos comunes:

imagen principal del producto

stock por talle/color

estados de producto

Input:

model

business_rule

Output:

methods_added

side_effects

📌 Blade nunca implementa esta lógica.

🗄️ Skills de Base de Datos
🧱 propose_schema_change

Descripción:
Propone cambios de base de datos sin ejecutarlos.

Input:

change_reason

Output:

migration_plan

affected_tables

sql_server_compatibility (true | false)

📌 Nunca ejecuta migraciones automáticamente.

🧱 optimize_query

Descripción:
Optimiza consultas evitando duplicación y queries innecesarias.

Input:

query_context

Output:

before_after_explanation

indexes_suggested

🎨 Skills de Frontend (UX controlada)
✨ enhance_ui_progressively

Descripción:
Agrega mejoras visuales que:

no rompen sin JS

respetan SEO

solo afectan frontend público

Input:

section (home / catalog / product)

enhancement_type (fade / translate / stagger)

Output:

js_files_touched

css_classes_added

📌 Usa IntersectionObserver
📌 Nunca toca /admin

✨ implement_partial_update

Descripción:
Implementa fetch parcial para catálogo sin convertir el sitio en SPA.

Input:

target_section (catalog)

pagination_context

Output:

files_modified

replaced_dom_node (.spa-content)

📌 Nunca mueve <main>, <nav>, <footer>

🛒 Skills de Carrito & Checkout
🛍️ update_cart_logic

Descripción:
Modifica lógica del carrito en sesión.

Input:

change_description

Output:

session_keys_used

validation_applied

💬 generate_whatsapp_message

Descripción:
Construye el mensaje final de checkout para WhatsApp.

Input:

cart_content

customer_notes

Output:

formatted_message (string)

📌 Lenguaje humano
📌 Nada técnico
📌 Fácil de copiar y enviar

🔒 Skills de Validación y Seguridad
🛡️ validate_inputs

Descripción:
Define reglas de validación estrictas para formularios.

Input:

form_name

fields

Output:

validation_rules

🛡️ apply_access_control

Descripción:
Configura policies, middleware o guards cuando corresponde.

Input:

resource

access_rule

Output:

files_modified
