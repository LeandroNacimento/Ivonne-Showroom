# Security Policy

## Purpose

Use this file to make validation, authorization, middleware, and data-exposure decisions.

## Use When

Use this file when changing:

- backend request handling
- validation rules
- auth flows
- middleware
- policies or guards
- admin access boundaries
- sensitive data exposure

## Do Not Use For

Do not use this file for:

- Gitflow or branch decisions
- deploy workflow decisions unless they change exposure or secrets handling
- frontend architecture choices unless they directly affect security boundaries
- business rules unrelated to access, validation, or exposure

## Hard Rules

Apply these rules:

- Use strict validation for backend changes.
- Use explicit authorization where access matters.
- Use policies, middleware, and guards where appropriate.
- Keep admin flows protected and clearly separated.
- Minimize exposed data in every view and action.
- Prevent XSS, CSRF, unauthorized access, and accidental leakage of sensitive data.
- Reject insecure routes, unsafe mutations, or broad implicit access.
- Reject implementations that cannot be justified in security terms.

## Defaults

Default to:

- backend validation for all user input
- explicit authorization checks
- minimal data exposure
- protected admin behavior
- cautious treatment of writes, uploads, and settings changes

## Escalate When

Escalate when the task may change:

- auth boundaries
- admin access rules
- middleware or policy behavior
- file uploads or image handling
- settings or environment-sensitive access
- sensitive data exposure

## Task Checklists

For backend mutations:

- validate inputs explicitly
- verify authorization path
- minimize returned or rendered data
- review route/action exposure

For auth-sensitive changes:

- verify access boundaries
- verify admin protection
- verify middleware and policy coverage
- verify no sensitive data leaks through views or responses
