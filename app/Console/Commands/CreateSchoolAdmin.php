<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateSchoolAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-school-admin 
                            {--school= : The school ID}
                            {--name= : The admin name}
                            {--email= : The admin email}
                            {--staff_code= : The staff code (optional)}
                            {--password= : The password (default: password)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a school admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get or prompt for school
        $schoolId = $this->option('school') ?: $this->ask('Enter school ID');
        $school = School::find($schoolId);

        if (! $school) {
            $this->error("School with ID {$schoolId} not found!");

            return self::FAILURE;
        }

        // Get or prompt for user details
        $name = $this->option('name') ?: $this->ask('Enter admin name');
        $email = $this->option('email') ?: $this->ask('Enter admin email');
        $staffCode = $this->option('staff_code') ?: $this->ask('Enter staff code (optional, press enter to skip)');
        $password = $this->option('password') ?: $this->secret('Enter password (default: password)') ?: 'password';

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'staff_code' => $staffCode,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'staff_code' => 'nullable|string|unique:users,staff_code',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Create school admin role if it doesn't exist
        $schoolAdminRole = Role::findOrCreate('school_admin');

        // Create user
        $user = User::create([
            'school_id' => $school->id,
            'name' => $name,
            'email' => $email,
            'staff_code' => filled($staffCode) ? strtoupper($staffCode) : null,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign role
        $user->assignRole($schoolAdminRole);

        $this->info('School Admin created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['School', $school->name],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Staff Code', $user->staff_code ?: 'Not set'],
                ['Password', $password],
            ]
        );

        $this->warn('IMPORTANT: Change this password immediately in production!');

        return self::SUCCESS;
    }
}
