<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle user after authentication.
     */
    protected function authenticated($request, $user)
    {
        Log::info('Login redirect', [
            'user_id' => $user->id,
            'role' => optional($user->role)->name,
            'isAdmin' => method_exists($user, 'isAdmin') ? $user->isAdmin() : null,
            'isDosen' => method_exists($user, 'isDosen') ? $user->isDosen() : null,
            'isMahasiswa' => method_exists($user, 'isMahasiswa') ? $user->isMahasiswa() : null,
        ]);
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect('/admin');
        } elseif (method_exists($user, 'isDosen') && $user->isDosen()) {
            return redirect('/dosen/dashboard');
        } elseif (method_exists($user, 'isMahasiswa') && $user->isMahasiswa()) {
            return redirect('/mahasiswa/dashboard');
        }
        // Default fallback
        return redirect('/home');
    }
}
