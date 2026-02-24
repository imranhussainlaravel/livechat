<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make($password),
                'is_super' => true,
            ]
        );
    }
}
