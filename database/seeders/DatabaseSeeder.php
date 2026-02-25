<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin
        User::firstOrCreate(
            ['email' => 'admin@livechat.com'],
            [
                'name'     => 'Super Admin',
                'password' => 'password',
                'role'     => UserRole::ADMIN->value,
                'status'   => 'offline',
            ]
        );

        // Create a demo agent
        User::firstOrCreate(
            ['email' => 'agent@livechat.com'],
            [
                'name'     => 'Demo Agent',
                'password' => 'password',
                'role'     => UserRole::AGENT->value,
                'status'   => 'offline',
            ]
        );
    }
}
