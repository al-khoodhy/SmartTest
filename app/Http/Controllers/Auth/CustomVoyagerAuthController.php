<?php

namespace App\Http\Controllers\Auth;

use TCG\Voyager\Http\Controllers\VoyagerAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomVoyagerAuthController extends VoyagerAuthController
{
    public function postLogin(Request $request)
    {
        $response = parent::postLogin($request);

        if (Auth::guest()) {
            return $response;
        }

        $user = Auth::user();

        // Redirect sesuai role
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->route('voyager.dashboard');
        } elseif (method_exists($user, 'isDosen') && $user->isDosen()) {
            return redirect()->route('dosen.dashboard');
        } elseif (method_exists($user, 'isMahasiswa') && $user->isMahasiswa()) {
            return redirect()->route('mahasiswa.dashboard');
        }

        // Default fallback
        return redirect('/home');
    }
} 