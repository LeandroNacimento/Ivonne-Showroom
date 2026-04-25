# Operations Policy

## Purpose

Use this file to make runtime, Docker, cache, infrastructure, and deploy decisions.

## Use When

Use this file when changing:

- Docker-based workflows
- Artisan command guidance
- runtime or cache behavior
- production assumptions
- Azure VM deployment behavior
- GitHub Actions deploy workflows
- config or env-sensitive infrastructure behavior

## Do Not Use For

Do not use this file for:

- Git branching or commit conventions
- business rules such as stock or checkout
- frontend interaction choices unless runtime constraints are involved
- authorization policy decisions unless infra changes affect exposure

## Hard Rules

Apply these rules:

- Use Docker as the mandatory development environment.
- Use `docker compose exec app php artisan <command>` for Artisan commands.
- Never run `php artisan` directly on the host.
- Treat production as Azure VM + Docker + GitHub Actions over SSH.
- Keep every infra-sensitive change compatible with containerized runtime.
- Treat the repository as the source of truth.
- Treat the VM as runtime, not as the permanent configuration authority.
- Reject VM-only hotfixes as the normal solution.
- Treat `storage/*`, `bootstrap/cache/*`, logs, sessions, compiled views, and temporary caches as runtime artifacts.
- Do not use runtime artifacts as persistent configuration.
- Keep config and route changes compatible with `config:cache` and `route:cache`.
- For code changes, preserve repository formatting conventions so CI style checks keep passing.
- When PHP files change, expect Laravel Pint or equivalent style validation to be part of the acceptance path.
- Reject route-file `env()` usage.

## Defaults

Default to:

- Docker command examples
- repository-driven fixes
- cache-safe Laravel configuration patterns
- explicit warnings when infra or deploy behavior may change
- rebuild-awareness for container-sensitive changes

## Escalate When

Escalate when the task may change:

- Dockerfile
- compose files
- `docker-entrypoint.sh`
- Nginx configuration
- Laravel config files with runtime impact
- environment variable handling
- permissions for `storage/` or `bootstrap/cache`
- GitHub Actions workflows
- production cache or deploy assumptions

## Task Checklists

For deploy-related changes:

- identify rebuild need
- identify cache impact
- identify runtime effect
- identify CI/CD implications

For route/config-sensitive changes:

- keep `config:cache` compatibility
- keep `route:cache` compatibility
- avoid `env()` in route files
- warn if deploy assumptions change

For code-quality-sensitive changes:

- identify whether formatter or linter checks are affected
- keep imports, spacing, and file structure aligned with project conventions
- do not leave style-only CI failures for later cleanup
