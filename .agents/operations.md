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

## Production Environment Variables (VM Only)

These variables must exist in the `.env` file on the Azure VM.
They are not committed to the repository.
The VM `.env` is mounted read-only via `docker-compose.prod.yml`.

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com
TRUSTED_PROXIES=127.0.0.1

LOG_LEVEL=error

SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=tudominio.com
```

Key constraints:

- `TRUSTED_PROXIES=127.0.0.1` declara confianza explícita sobre un proxy conocido
  en una topología cerrada. El valor no se elige por resolución de red Docker ni
  por la IP real del gateway bridge. Se elige porque el único punto de entrada
  externo es el Nginx del host, controlado a nivel de SO, y el puerto del contenedor
  `web` está enlazado a `127.0.0.1:80` exclusivamente.
- El Nginx del contenedor (`web`) no manipula ni interpreta los X-Forwarded-*
  headers. Su única responsabilidad es pasar la request a PHP-FPM. Los HTTP
  headers del request llegan a PHP-FPM como variables `HTTP_*` de forma nativa
  por el protocolo FastCGI. Laravel los lee exclusivamente a través de TrustProxies.
- `SESSION_SECURE_COOKIE=true` requires HTTPS to be active end-to-end before enabling.
- HSTS (`Strict-Transport-Security`) es responsabilidad del Nginx del host, no PHP.
  El host termina TLS; PHP no tiene visibilidad sobre si la conexión al cliente
  fue TLS o no. Do not add HSTS back to `SecurityHeadersMiddleware`.
- `proxy_set_header X-Forwarded-Proto https;` — valor fijo en el host Nginx.
  TLS termina allí, el valor es siempre `https`. No usar `$scheme`.
- OPcache `validate_timestamps=0` is correct for immutable Docker images.
  Do not revert it unless PHP files are mounted via volume in production.
