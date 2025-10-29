<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('mahasiswa.profile.index', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('mahasiswa.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->name = $validated['name'];

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada dan bukan default (hapus di public dan storage)
            if (!empty($user->avatar) && !str_contains($user->avatar, 'users/default.png')) {
                $oldRel = ltrim($user->avatar, '/');
                if (str_starts_with($oldRel, 'storage/')) {
                    $oldRel = substr($oldRel, strlen('storage/'));
                }
                // Hapus di disk public (storage/app/public)
                if (Storage::disk('public')->exists($oldRel)) {
                    Storage::disk('public')->delete($oldRel);
                }
                // Hapus di public path (public/storage)
                $oldPublic = public_path('storage/'.$oldRel);
                if (File::exists($oldPublic)) {
                    File::delete($oldPublic);
                }
            }

            $file = $request->file('avatar');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeName = Str::slug($originalName);
            $filename = uniqid().'_'.$safeName.'.'.$extension;

            // Pastikan direktori public/storage/users ada
            $publicDir = public_path('storage/users');
            if (!File::exists($publicDir)) {
                File::makeDirectory($publicDir, 0755, true);
            }

            // Pindahkan langsung ke public/storage/users agar bisa diakses tanpa symlink
            $file->move($publicDir, $filename);

            // Opsional: juga simpan ke storage disk public untuk konsistensi (abaikan error)
            try {
                $storagePath = storage_path('app/public/users/'.$filename);
                $storageDir = dirname($storagePath);
                if (!File::exists($storageDir)) {
                    File::makeDirectory($storageDir, 0755, true);
                }
                if (!File::exists($storagePath)) {
                    File::copy($publicDir.DIRECTORY_SEPARATOR.$filename, $storagePath);
                }
            } catch (\Throwable $e) {
                // Abaikan bila gagal; file di public sudah cukup untuk dilayani
            }

            // Simpan path relatif yang dipakai oleh asset('storage/...')
            $user->avatar = 'users/'.$filename; // contoh: users/abc.jpg
        }

        $user->save();

        return redirect()->route('mahasiswa.profile.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword()
    {
        return view('mahasiswa.profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('mahasiswa.profile.index')->with('success', 'Password berhasil diperbarui.');
    }
}


