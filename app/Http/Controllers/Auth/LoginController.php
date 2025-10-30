<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // Penting untuk logout

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
    protected $redirectTo = '/home'; // Ubah ini jika perlu

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware 'guest' mengecualikan 'logout'.
        // Ini PENTING agar user yang sedang login bisa mengakses fungsi logout.
        $this->middleware('guest')->except('logout');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect('/'); // Arahkan ke halaman utama setelah logout
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        // Tidak ada logika khusus, biarkan redirect di atas bekerja
    }
}