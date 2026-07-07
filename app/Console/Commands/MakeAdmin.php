<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'make:admin {email?}';

    protected $description = 'Set a user as admin or create a new admin user';

    public function handle(): void
    {
        $email = $this->argument('email');

        if ($email) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->error("User with email '{$email}' not found.");

                return;
            }

            $user->update(['is_admin' => true]);
            $this->info("User '{$user->name}' is now an admin.");

            return;
        }

        $name = $this->ask('Name', 'Admin');
        $email = $this->ask('Email', 'admin@jakkaspace.com');
        $password = $this->secret('Password');

        if (User::where('email', $email)->exists()) {
            $this->error("Email '{$email}' already taken.");

            return;
        }

        User::create([
            'name' => $name,
            'username' => 'admin',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("Admin user '{$name}' created successfully.");
    }
}
