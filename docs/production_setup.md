# Production Setup Guide

This document breaks down the necessary steps to prepare the Ivonne Showroom application for a production environment. 

## Laravel Production Caching

Laravel provides several caching commands that should always be used in production to optimize performance. 

Run the following commands during your deployment process:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### What these commands do:
- `config:cache` — Combines all of your configuration files into a single, cached file, drastically reducing the number of files the framework needs to load.
- `route:cache` — Compiles all of your route definitions into a single, cached file, which significantly speeds up route registration.
- `view:cache` — Precompiles all Blade templates so they don't have to be compiled on the fly when requested by the user.
- `event:cache` — Caches the application's events and listeners for faster event dispatching.

If you ever need to clear these optimization caches (e.g., when debugging or modifying configuration directly), you can use:

```bash
php artisan optimize:clear
```

---

## Server Performance Configuration

### PHP OPcache

OPcache prevents PHP from loading and parsing scripts on every request by storing precompiled script bytecode in shared memory. Enabling OPcache is one of the most effective ways to dramatically improve request performance in production.

Example `php.ini` configuration:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
```

*Note: With `validate_timestamps=0`, OPcache will never check for updated code automatically. When deploying new code, you must clear the OPcache (e.g., by restarting PHP-FPM).*

### Gzip Compression (Nginx)

Gzip compression reduces the payload size of responses sent to the browser, significantly speeding up page loading times for end users.

Example Nginx configuration snippet (typically placed inside the `http`, `server`, or `location` block):

```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
gzip_min_length 1024;
```
