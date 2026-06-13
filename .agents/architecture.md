# Architecture Policy

## Purpose

Use this file to make architecture and frontend structure decisions.

## Use When

Use this file when changing:

- routes or route organization
- frontend rendering behavior
- Blade or Livewire responsibilities
- Alpine or JavaScript usage patterns
- admin/storefront boundaries
- cache-sensitive route conventions

## Do Not Use For

Do not use this file for:

- deploy workflow decisions unless architecture is affected
- Git branching or commit decisions
- security policy decisions unless architecture directly changes access or exposure
- domain rules such as stock, checkout, or image source of truth

## Hard Rules

Apply these rules:

- Treat the app as Laravel 12 + Blade + Tailwind CSS v4 + Vite.
- Prefer Livewire when interactivity has real value.
- Use Alpine only for local UX behavior.
- Treat the application as a traditional MPA.
- Reject solutions that introduce global SPA behavior.
- Reject Vue, React, Inertia, or equivalent frontend frameworks.
- Keep rendering server-first.
- Keep admin and storefront concerns separated.
- Keep admin routes separate from public routes.
- Keep admin free from storefront-style decorative behavior.
- Keep all route work compatible with `config:cache` and `route:cache`.
- Resolve route configuration through `config(...)`, not `env()` in route files.
- Use `config('admin.path')` for admin routes.
- Prefer named routes and proper URL helpers over manual URL construction.

## Defaults

Default to:

- Blade for page composition
- Livewire for meaningful interactivity
- Alpine for local toggles and microinteractions
- backend forms and routes before JS-heavy patterns
- named routes over hardcoded URLs
- subtle public UX instead of tech-forward UI behavior

Treat the public frontend as:

- clean
- elegant
- trustworthy
- lightweight

## Escalate When

Escalate when the task may:

- blur the boundary between storefront and admin
- introduce navigation or state patterns that behave like a SPA
- require route shape changes with cache implications
- change the admin prefix behavior
- push meaningful business behavior into frontend JS

## Task Checklists

For route changes:

- keep `config:cache` compatibility
- keep `route:cache` compatibility
- use `config('admin.path')` for admin routing
- prefer named routes and helpers

For interactive UI changes:

- keep JS local and removable
- avoid critical state in the frontend
- preserve admin/storefront separation
