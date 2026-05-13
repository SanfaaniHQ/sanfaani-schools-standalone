<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin role if it doesn't exist
        $superAdminRole = Role::findOrCreate('super_admin');

        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@sanfaani.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@sanfaani.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'school_id' => null, // Super Admin has no school
            ]
        );

        // Assign Super Admin role
        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: admin@sanfaani.com');
        $this->command->info('Password: password');
        $this->command->warn('IMPORTANT: Change this password immediately in production!');
    }
}
