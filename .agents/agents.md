# AGENTS.md - Ivonne Showroom

## Purpose

Use this file as the root decision system for the project.
Use it to decide:

- what constraints always apply
- what file to consult next
- what must be stated before changing code
- what source prevails when instructions conflict

Do not use this file as a full handbook. Consult the smallest relevant thematic file after reading this one.

## Project Identity

- Project: Ivonne Showroom
- Type: e-commerce / catalog for women's fashion
- Business model: conversational sales via WhatsApp
- Location: Formosa, Argentina

Treat the project as a real brand, not a demo.
Prioritize:

- aesthetics
- trust
- clarity
- fluency

Keep the brand principle, but execute it operationally:

> Ivonne Showroom sells feeling, not technology.
> If it is noticeable, discard it.
> If it flows, keep it.

Translate that into implementation behavior:

- Prefer invisible UX over flashy behavior.
- Reject decorative complexity that does not improve trust or clarity.
- Keep the public experience light, elegant, and calm.

## Hard Rules

Apply these rules globally:

- Use Docker as the mandatory development environment.
- Never run `php artisan` directly on the host.
- Treat the app as Laravel 12 + Blade + Tailwind CSS v4 + Vite + Livewire, with Alpine only for local UX.
- Treat the app as a traditional MPA with server-rendered pages.
- Reject solutions that introduce global SPA behavior.
- Use MySQL 8+ with InnoDB as the only supported database engine.
- Treat JavaScript as progressive enhancement only.
- Keep stock and availability calculations in the backend.
- Keep checkout ending in WhatsApp.
- Keep a strong separation between storefront and admin.
- Keep route and config changes compatible with `config:cache` and `route:cache`.
- Use `config('admin.path')` for admin routes. Do not hardcode `/admin`.
- Do not perform irreversible Git actions without explicit user instruction.
- Treat the repository as the source of truth. Treat the VM only as runtime.

## Decision Hierarchy

If instructions conflict, use this precedence:

1. `agents.md`
2. thematic documents in `.agents/`
3. tactical skills in `.agents/skills/`
4. general model suggestions

Apply this hierarchy strictly:

- Treat this file as the root authority.
- Treat thematic files as normative expansions.
- Treat skills as tactical execution help.
- Ignore lower-priority guidance when it conflicts with a higher-priority file.

## Navigation

Consult the smallest relevant file instead of rereading the whole system:

- Read `architecture.md` for stack, routes, frontend rendering, Livewire, Alpine, MPA behavior, and admin/storefront boundaries.
- Read `domain.md` for stock, cart, checkout, WhatsApp flow, product states, and image source of truth.
- Read `operations.md` for Docker, runtime, cache, deploy, Azure VM, GitHub Actions, and infra-sensitive changes.
- Read `git.md` for branches, Gitflow, commit conventions, and Git safety boundaries.
- Read `security.md` for validation, authorization, middleware, policies, guards, and data exposure rules.

Use skills only after the task is already scoped:

- `.agents/skills/checkout_security/`
- `.agents/skills/database_integrity/`
- `.agents/skills/frontend_ux/`
- `.agents/skills/laravel_backend/`
- `.agents/skills/livewire_interactivity/`

## Protocol Before Changing Code

Before touching code, state:

1. what will change
2. which files will be touched
3. what will not be touched
4. whether visual design changes
5. whether backend, security, infrastructure, or production behavior changes

Escalate the explanation when the task touches a sensitive area.

## Sensitive Areas

Be more explicit when the task affects:

- routes
- auth
- stock
- cart
- checkout
- images
- Docker or deploy
- security
- runtime or cache compatibility

## Global Prohibitions

Do not:

- run `php artisan` on the host
- hardcode `/admin` when a named route or `config('admin.path')` should be used
- place critical business logic in JavaScript
- calculate stock or availability in the frontend
- mix storefront marketing behavior into admin workflows
- use the VM as the permanent source of truth
- treat runtime/cache files as persistent configuration
- perform destructive or irreversible Git actions without explicit instruction

## Documentation Maintenance

Keep the documentation system healthy:

- Do not add a rule without stating whether it replaces, extends, or exemplifies an existing one.
- Move rules that apply to less than 20% of tasks out of the root file.
- Extract root sections that become too long.
- Keep one normative source per important rule.
- Avoid duplicating long prohibition lists across files.
- Keep long explanations and examples out of the root file.
- Keep all files in `.agents/` in clean UTF-8 text.

## Final Rule

If a proposal conflicts with this file, this file prevails.

Treat this documentation system as an implementation constraint, not as optional guidance.
