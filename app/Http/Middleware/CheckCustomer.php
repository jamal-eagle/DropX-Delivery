<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomer
{

    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::user()->role === 'customer')
        return $next($request);
        return response()->json(['message'=> 'Unauthanteceted'], 403);
    }
}
