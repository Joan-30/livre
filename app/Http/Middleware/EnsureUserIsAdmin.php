<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== User::ROLE_ADMIN) {
            // Si pas admin, on redirige vers le tableau de bord lecteur
            return redirect()->route('tableau-de-bord')->with('error', 'Vous n\'avez pas les droits pour accéder à cette zone.');
        }

        return $next($request);
    }
}
