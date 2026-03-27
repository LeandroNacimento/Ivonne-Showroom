# Domain Policy

## Purpose

Use this file to make business-rule decisions for products, stock, cart, checkout, and images.

## Use When

Use this file when changing:

- stock logic
- product availability
- cart behavior
- checkout behavior
- WhatsApp message flow
- product image selection or rendering source of truth

## Do Not Use For

Do not use this file for:

- deploy or infrastructure decisions
- Git branching decisions
- route architecture unless it directly changes domain behavior
- general frontend framework choices

## Hard Rules

Apply these rules:

- Keep stock by size and color as a critical backend concern.
- Calculate availability in the backend.
- Reject frontend stock or availability calculations.
- Keep stock states deterministic and clear.
- Keep the cart in session unless an explicit decision changes that model.
- Keep checkout ending in WhatsApp.
- Keep the final WhatsApp message clear, human, ordered, and easy to read.
- Keep image-selection logic in the model or domain layer.
- Use Blade only to render the chosen image source of truth.
- Reject implicit main-image selection by list order.
- Use a controlled placeholder if no valid main image can be determined.
- Keep MySQL 8+ with InnoDB as the supported engine for domain behavior.

## Defaults

Default to:

- backend domain logic over frontend heuristics
- deterministic product states over inferred UI behavior
- session-based cart behavior
- WhatsApp as the checkout completion channel
- one canonical source of truth for image selection
- models or services for business-rule decisions

## Escalate When

Escalate when the task may change:

- stock calculation rules
- size/color stock handling
- checkout flow or WhatsApp output
- pricing-sensitive or order-sensitive behavior
- image source of truth
- placeholder behavior that affects product trust

## Task Checklists

For stock changes:

- validate backend source of truth
- avoid JS calculations
- keep states deterministic
- verify size/color handling

For checkout changes:

- preserve session cart behavior unless intentionally changed
- preserve WhatsApp completion flow
- keep the message human and readable
- verify no hidden auth dependency is introduced

For image changes:

- keep one source of truth
- avoid view-level decision logic
- avoid implicit ordering assumptions
- keep placeholder behavior controlled
