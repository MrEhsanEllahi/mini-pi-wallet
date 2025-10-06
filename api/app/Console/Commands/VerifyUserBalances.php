<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:verify {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify user balances against transaction history for audit purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            // Verify single user
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            $this->verifyUser($user);
        } else {
            // Verify all users
            $this->info('Verifying all user balances...');
            $users = User::all();

            $discrepancies = 0;
            foreach ($users as $user) {
                if (!$this->verifyUser($user, false)) {
                    $discrepancies++;
                }
            }

            if ($discrepancies === 0) {
                $this->info('✓ All user balances are accurate!');
            } else {
                $this->warn("Found {$discrepancies} discrepancies.");
            }
        }

        return 0;
    }

    /**
     * Verify a single user's balance
     */
    private function verifyUser(User $user, bool $verbose = true): bool
    {
        $verification = $user->verifyBalance();

        if ($verbose || !$verification['is_accurate']) {
            $this->table(
                ['Field', 'Value'],
                [
                    ['User ID', $user->id],
                    ['User Name', $user->name],
                    ['Stored Balance', '$' . number_format($verification['stored_balance'], 2)],
                    ['Calculated Balance', '$' . number_format($verification['calculated_balance'], 2)],
                    ['Difference', '$' . number_format($verification['difference'], 2)],
                    ['Status', $verification['is_accurate'] ? '✓ Accurate' : '✗ Discrepancy Found'],
                ]
            );
        }

        return $verification['is_accurate'];
    }
}
