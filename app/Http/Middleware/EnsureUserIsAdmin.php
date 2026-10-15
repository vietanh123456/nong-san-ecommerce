<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            in_array($request->user()?->role, ['admin', 'super_admin'], true),
            403,
            'Bạn không có quyền truy cập khu vực quản trị.'
        );

        return $next($request);
    }
}
