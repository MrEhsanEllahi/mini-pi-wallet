<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Lee',
                'email' => 'lee@test.com',
                'password' => Hash::make('123456'),
                'balance' => 1000.00,
            ],
            [
                'name' => 'Zendi',
                'email' => 'zendi@test.com',
                'password' => Hash::make('123456'),
                'balance' => 500.00,
            ],
            [
                'name' => 'Mark',
                'email' => 'mark@test.com',
                'password' => Hash::make('123456'),
                'balance' => 750.00,
            ],
            [
                'name' => 'Daisy',
                'email' => 'daisy@test.com',
                'password' => Hash::make('123456'),
                'balance' => 300.00,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
