<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckResturant
{

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->user_type === 'restaurant')
            return $next($request);
        return response()->json(['message' => 'Unauthanteceted'], 403);
    }
}
