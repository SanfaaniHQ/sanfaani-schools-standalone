<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'school_admin',
            'result_officer',
            'teacher',
            'student',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }
    }
}
