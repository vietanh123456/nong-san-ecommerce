<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isSeller(), 403, 'Tài khoản seller của bạn chưa được admin duyệt.');

        return $next($request);
    }
}
