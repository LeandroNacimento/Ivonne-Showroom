# Production Web Cutover

This runbook covers the production cutover that removes the `web` service
dependency on the host checkout and moves public uploads into the
`public_storage` Docker volume.

## Goal

- `web` serves `/var/www/public` from its own image
- `app` and `web` share only `public/storage`
- uploads persist outside the image

## Manual seed steps

Run these commands on the production host from the repository root before the
first deploy with `public_storage` enabled:

```bash
docker volume create ivonne-showroom_public_storage

docker run --rm \
  -v ivonne-showroom_public_storage:/volume \
  -v "$PWD/storage/app/public:/seed:ro" \
  alpine:3.20 \
  sh -lc 'cd /seed && cp -a . /volume/'
```

If you need to inspect the seeded files before deploying:

```bash
docker run --rm \
  -v ivonne-showroom_public_storage:/volume \
  alpine:3.20 \
  sh -lc 'find /volume -maxdepth 2 -type f | sort | head -n 50'
```

## Deploy

Use the existing production deploy flow after the volume has been seeded.

## Post-deploy smoke tests

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps
curl -I --max-time 20 http://127.0.0.1/up
curl -I --max-time 20 http://127.0.0.1/
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T web sh -lc 'test -f /var/www/public/index.php'
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T web sh -lc 'test -f /var/www/public/build/manifest.json'
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T web sh -lc 'test -d /var/www/public/storage'
```

Verify at least one existing `/storage/...` image and one newly uploaded image
after restarting `app` and `web`.

## Rollback notes

- Never remove the `public_storage` volume during rollback.
- Do not use `docker compose down -v`.
- If rollback is required, keep `public_storage` as the source of truth for
  uploads.
