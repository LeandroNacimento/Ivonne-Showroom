<?php

/**
 * Admin panel configuration.
 *
 * This file intentionally wraps ADMIN_PATH so that config('admin.path')
 * works correctly after `php artisan config:cache` and `route:cache`.
 *
 * ❌ NEVER use env('ADMIN_PATH') directly in route files — env() returns
 *    null once the application is running from config cache, which silently
 *    breaks every admin route in production.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Path Prefix
    |--------------------------------------------------------------------------
    |
    | This value is used as the URL prefix for all admin panel routes.
    | Set ADMIN_PATH in your .env file to a secret, hard-to-guess slug
    | (e.g. "login-secreto-2026") to obscure the admin entry point.
    |
    */
    'path' => env('ADMIN_PATH', 'admin'),

];
