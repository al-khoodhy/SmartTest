<?php

namespace App\Policies;

use App\Models\Tugas;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TugasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isDosen() || $user->isAdmin();
    }

    /**
     * Voyager BREAD alias for browse permission.
     */
    public function browse(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tugas $tugas): bool
    {
        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }
        
        // Dosen can view tugas if he created it
        if ($user->isDosen()) {
            return $tugas->dosen_id === $user->id;
        }
        
        // Mahasiswa can view tugas from their enrolled mata kuliah
        if ($user->isMahasiswa()) {
            return $user->enrollments()
                ->where('kelas_id', $tugas->kelas_id)
                ->where('status', 'active')
                ->exists();
        }
        
        return false;
    }

    /**
     * Voyager BREAD alias for read permission.
     */
    public function read(User $user, Tugas $tugas): bool
    {
        return $this->view($user, $tugas);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isDosen();
    }

    /**
     * Voyager BREAD alias for add permission.
     */
    public function add(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tugas $tugas): bool
    {
        // Admin can update all
        if ($user->isAdmin()) {
            return true;
        }
        
        // Dosen can update tugas if he created it
        if ($user->isDosen()) {
            return $tugas->dosen_id === $user->id;
        }
        
        return false;
    }

    /**
     * Voyager BREAD alias for edit permission.
     */
    public function edit(User $user, Tugas $tugas): bool
    {
        return $this->update($user, $tugas);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tugas $tugas): bool
    {
        // Admin can delete all
        if ($user->isAdmin()) {
            return true;
        }
        
        // Dosen can delete tugas if he created it
        if ($user->isDosen()) {
            return $tugas->dosen_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tugas $tugas): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tugas $tugas): bool
    {
        return $user->isAdmin();
    }
}
