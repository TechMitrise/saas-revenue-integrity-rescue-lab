<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoMutationsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('rescue_lab.demo_mutations_enabled'), 404);

        return $next($request);
    }
}
