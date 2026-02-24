<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@livechat.com'],
            [
                'name'     => 'Super Admin',
                'password' => 'password',
                'is_super' => true,
            ]
        );
    }
}
