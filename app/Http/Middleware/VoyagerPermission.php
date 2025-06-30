<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VoyagerPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $user = auth()->user();
        
        // Debug log untuk permission
        Log::info('PERMISSION DEBUG', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'role_name' => $user->role ? $user->role->name : 'No role',
            'permissions' => $permissions,
        ]);
        
        // Check if user has required permissions
        if (!empty($permissions)) {
            $hasPermission = false;
            
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            if (!$hasPermission) {
                abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
            }
        }
        
        return $next($request);
    }
} 