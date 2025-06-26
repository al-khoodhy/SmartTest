<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Models\User;
// use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('users')->delete();

        if (User::count() == 0) {
            $role = Role::where('name', 'admin')->firstOrFail();

            User::firstOrCreate(
                ['email' => 'admin@admin.com'],
                [
                    'name'           => 'Admin',
                    'password'       => bcrypt('password'),
                    'remember_token' => Str::random(60),
                    'role_id'        => $role->id,
                ]
            );
        }
    }
}
