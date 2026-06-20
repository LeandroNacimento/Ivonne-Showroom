<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // HSTS es responsabilidad del Nginx del host (reverse proxy con TLS).
        // No se emite desde PHP para evitar conflictos semánticos: la conexión
        // interna host→contenedor es siempre HTTP, y HSTS solo tiene sentido
        // sobre una conexión TLS terminada en el punto que lo emite.

        // Fuentes de desarrollo (Vite HMR) solo en entorno local.
        if (app()->environment('local')) {
            $viteScriptSrc = ' http://localhost:5173 ws://localhost:5173';
            $viteStyleSrc = ' http://localhost:5173';
            $viteImgSrc = ' http://localhost:5173';
            $viteFontSrc = ' http://localhost:5173';
            $viteConnectSrc = ' http://localhost:5173 ws://localhost:5173';
        } else {
            $viteScriptSrc = '';
            $viteStyleSrc = '';
            $viteImgSrc = '';
            $viteFontSrc = '';
            $viteConnectSrc = '';
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$viteScriptSrc} https://*.googleapis.com",
            "style-src 'self' 'unsafe-inline'{$viteStyleSrc} https://fonts.googleapis.com https://*.googleapis.com",
            "img-src 'self' data: blob:{$viteImgSrc} https://*.googleapis.com https://*.google.com https://*.gstatic.com https://picsum.photos https://*.picsum.photos https://via.placeholder.com",
            "font-src 'self' data:{$viteFontSrc} https://fonts.gstatic.com",
            "connect-src 'self'{$viteConnectSrc} https://*.googleapis.com",
            "frame-src 'self' https://*.google.com",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
