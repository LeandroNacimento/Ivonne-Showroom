# Skills — Ivonne Showroom

Este documento define **qué puede hacer el agente y cómo**, dentro del proyecto _Ivonne Showroom_.
Todas las skills **deben cumplir estrictamente** las reglas establecidas en `agents.md`.

---

## Principios Generales

- Las skills **NO toman decisiones de producto**
- Las skills **NO modifican diseño visual** sin autorización explícita
- Las skills **NO ejecutan acciones de Git** (commit, push, merge, etc.)
- Las skills **NO introducen dependencias nuevas** sin validación previa
- Las skills **respetan mobile-first, progressive enhancement y SSR**

---

## 🧠 Skills de Análisis (no tocan código)

### 🔍 analyze_project_structure

**Descripción**
Analiza la estructura actual del proyecto y detecta inconsistencias arquitectónicas.

**Input**

- project_tree

**Output**

- layers_detected (frontend / backend / db)
- coupling_issues
- livewire_misuse
- mobile_risks
- refactor_recommendations

📌 Skill base obligatoria antes de refactors grandes.

---

### 🔍 analyze_feature

**Descripción**
Analiza una funcionalidad solicitada y valida su compatibilidad con la arquitectura actual.

**Input**

- feature_description (string)

**Output**

- affected_layers (frontend / backend / db)
- potential_risks (array)
- architecture_fit (true | false)

---

### 🔍 analyze_security_impact

**Descripción**
Evalúa impacto de seguridad antes de cualquier cambio en backend o base de datos.

**Input**

- change_description (string)

**Output**

- risks (XSS, CSRF, auth, data leakage)
- required_mitigations (array)

📌 **Obligatoria** para cambios en backend o BD.

---

## 🛠️ Skills de Backend (Laravel)

### 🧩 create_laravel_component

**Descripción**
Crea o modifica componentes Blade reutilizables sin alterar el diseño existente.

**Input**

- component_name
- purpose
- affected_views

**Output**

- files_created_or_modified

📌 No define estilos nuevos, solo estructura.

---

### 🧩 modify_controller_logic

**Descripción**
Ajusta lógica de controladores respetando:

- validación estricta
- policies y middleware
- consistencia transaccional

**Input**

- controller
- change_description

**Output**

- files_modified
- validation_rules

---

### 🧩 update_model_logic

**Descripción**
Agrega o modifica lógica de dominio en modelos Eloquent.

**Casos comunes**

- imagen principal del producto
- stock por talle / color
- estados de producto

**Input**

- model
- business_rule

**Output**

- methods_added
- side_effects

📌 Blade **nunca** implementa esta lógica.

---

## ⚡ Skills Livewire & Interactividad

### ⚡ refactor_livewire_component

**Descripción**
Refactoriza componentes Livewire para cumplir buenas prácticas.

**Criterios**

- estado mínimo
- responsabilidades claras
- compatible con mobile

**Input**

- component_name
- refactor_goal

**Output**

- files_modified
- state_changes

📌 No introduce lógica JS innecesaria.

---

### ⚡ analyze_livewire_mobile_issues

**Descripción**
Detecta problemas comunes de Livewire en mobile (eventos, rehidratación, layout).

**Input**

- component_name

**Output**

- detected_issues
- root_causes
- recommended_fixes

---

## 🗄️ Skills de Base de Datos

### 🧱 propose_schema_change

**Descripción**
Propone cambios de base de datos **sin ejecutarlos**.

**Input**

- change_reason

**Output**

- migration_plan
- affected_tables
- sql_server_compatibility (true | false)

📌 Nunca ejecuta migraciones automáticamente.

---

### 🧱 optimize_query

**Descripción**
Optimiza consultas evitando duplicación y queries innecesarias.

**Input**

- query_context

**Output**

- before_after_explanation
- indexes_suggested

---

## 🎨 Skills de Frontend (UX controlada)

### ✨ enhance_ui_progressively

**Descripción**
Agrega mejoras visuales progresivas que:

- no rompen sin JS
- respetan SEO
- solo afectan frontend público

**Input**

- section (home / catalog / product)
- enhancement_type (fade / translate / stagger)

**Output**

- js_files_touched
- css_classes_added

📌 Usa `IntersectionObserver`
📌 Nunca toca `/admin`

---

### ✨ implement_partial_update

**Descripción**
Implementa actualización parcial de contenido sin convertir el sitio en SPA.

**Input**

- target_section (catalog)
- pagination_context

**Output**

- files_modified
- replaced_dom_node (.spa-content)

📌 Nunca mueve `<main>`, `<nav>`, `<footer>`

---

## 🛒 Skills de Carrito & Checkout

### 🛍️ update_cart_logic

**Descripción**
Modifica lógica del carrito basada en sesión.

**Input**

- change_description

**Output**

- session_keys_used
- validation_applied

---

### 💬 generate_whatsapp_message

**Descripción**
Construye el mensaje final de checkout para WhatsApp.

**Input**

- cart_content
- customer_notes

**Output**

- formatted_message (string)

📌 Lenguaje humano
📌 Nada técnico
📌 Fácil de copiar y enviar

---

## 🔒 Skills de Validación y Seguridad

### 🛡️ validate_inputs

**Descripción**
Define reglas de validación estrictas para formularios.

**Input**

- form_name
- fields

**Output**

- validation_rules

---

### 🛡️ apply_access_control

**Descripción**
Configura policies, middleware o guards cuando corresponde.

**Input**

- resource
- access_rule

**Output**

- files_modified
