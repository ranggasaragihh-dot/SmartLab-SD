<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Link dibuat https hanya jika website dibuka lewat domain APP_URL yang https
 * (atau tunnel trycloudflare). Akses lokal seperti http://127.0.0.1:8000 tetap http.
 */
class ForceHttpsForAppUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = parse_url((string) config('app.url'));
        $host = $request->getHost();
        $servedFromHttpsAppUrl = ($appUrl['scheme'] ?? null) === 'https' && ($appUrl['host'] ?? null) === $host;

        if ($servedFromHttpsAppUrl || str_contains($host, 'trycloudflare.com')) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
