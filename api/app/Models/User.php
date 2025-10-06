<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Append formatted ID to JSON responses.
     *
     * @return array<int, string>
     */
    protected $appends = ['formatted_id'];

    /**
     * Get the user's formatted ID (6 digits with leading zeros).
     */
    public function getFormattedIdAttribute(): string
    {
        return str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the transactions sent by the user.
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }

    /**
     * Get the transactions received by the user.
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

    /**
     * Get all transactions (sent and received) for the user.
     */
    public function transactions()
    {
        return Transaction::where('sender_id', $this->id)
            ->orWhere('receiver_id', $this->id)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Calculate balance from transaction history (for verification/audit purposes only).
     * NOTE: This should NOT be used for normal balance retrieval due to performance
     * concerns with millions of rows. Use the 'balance' column instead.
     */
    public function calculateBalanceFromTransactions(): float
    {
        $sent = $this->sentTransactions()
            ->sum(DB::raw('amount + commission_fee'));

        $received = $this->receivedTransactions()
            ->sum('amount');

        return round($received - $sent, 2);
    }

    /**
     * Verify that the stored balance matches the calculated balance from transactions.
     * Useful for auditing and detecting discrepancies.
     */
    public function verifyBalance(): array
    {
        $storedBalance = $this->balance;
        $calculatedBalance = $this->calculateBalanceFromTransactions();
        $difference = round($storedBalance - $calculatedBalance, 2);

        return [
            'stored_balance' => $storedBalance,
            'calculated_balance' => $calculatedBalance,
            'difference' => $difference,
            'is_accurate' => abs($difference) < 0.01, // Allow for minor rounding differences
        ];
    }
}
