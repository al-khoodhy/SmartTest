<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $user = auth()->user();
        
        // Debug log untuk role
        Log::info('ROLE DEBUG', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'roles' => $roles,
            'roles_int' => array_map('intval', $roles),
        ]);
        
        // Check if user has required role
        if (!empty($roles) && !in_array((int)$user->role_id, array_map('intval', $roles))) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }
        
        return $next($request);
    }
}
