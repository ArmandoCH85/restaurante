<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8Encoding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Forzar encoding UTF-8 en la respuesta
        if ($response instanceof \Illuminate\Http\Response) {
            $response->header('Content-Type', $response->headers->get('Content-Type', 'text/html') . '; charset=UTF-8');
        }

        return $response;
    }
}

