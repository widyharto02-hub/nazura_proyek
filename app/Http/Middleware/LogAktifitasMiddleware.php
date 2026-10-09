<?php

namespace App\Http\Middleware;

use App\Services\AktifitasLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAktifitasMiddleware
{
    public function __construct(private AktifitasLogger $aktifitasLogger)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $this->aktifitasLogger->logFromRequest($request);
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (! $request->user() || ! $request->route()) {
            return false;
        }

        if (! $response->isSuccessful() && ! $response->isRedirection()) {
            return false;
        }

        $actionMethod = $request->route()->getActionMethod();

        if (! $actionMethod) {
            return false;
        }

        return str_starts_with($actionMethod, 'store')
            || str_starts_with($actionMethod, 'update')
            || str_starts_with($actionMethod, 'destroy')
            || str_starts_with($actionMethod, 'delete');
    }
}
