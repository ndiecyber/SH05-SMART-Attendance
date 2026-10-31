<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-admin-user {email} {name}')]
#[Description('Bootstrap the first LEXA administrator when no admin account exists.')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (User::query()->where('role', 'admin')->exists()) {
            $this->components->error('An Admin account already exists. Use an Admin invitation for additional accounts.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        $name = trim((string) $this->argument('name'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || User::query()->where('email', $email)->exists()) {
            $this->components->error('Provide a valid, unused email address and a non-empty name.');

            return self::FAILURE;
        }

        $password = $this->secret('Admin password (minimum 12 characters)');
        if (! is_string($password) || ! Password::min(12)->passes('password', $password)) {
            $this->components->error('The password must contain at least 12 characters.');

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();

        $this->components->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
