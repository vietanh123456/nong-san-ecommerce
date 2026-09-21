<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user || $user->role !== 'seller') {
            abort(
                403,
                'Bạn không có quyền truy cập khu vực người bán.'
            );
        }

        return $next($request);
    }
}