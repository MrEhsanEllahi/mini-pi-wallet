<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Give all existing users an initial balance for testing purposes.
     */
    public function run(): void
    {
        $users = User::where('balance', 0)->get();

        foreach ($users as $user) {
            $user->balance = 1000.00; // Give each user $1000 initial balance
            $user->save();
            $this->command->info("Added $1000 initial balance to user: {$user->name} (ID: {$user->id})");
        }

        $this->command->info('Initial balance seeding completed!');
    }
}
