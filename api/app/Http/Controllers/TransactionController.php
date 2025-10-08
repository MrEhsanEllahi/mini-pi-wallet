<?php

namespace App\Http\Controllers;

use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Get transaction history and balance for authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Optimized query for large datasets
        // Using whereIn with subquery allows better use of composite indexes
        $transactions = Transaction::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'balance' => $user->balance ?? 0,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Execute a new money transfer.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $sender = $request->user();
        $receiverId = $request->receiver_id;
        $amount = $request->amount;

        // Additional validations
        if ($sender->id === $receiverId) {
            return response()->json([
                'message' => 'You cannot send money to yourself.',
            ], 422);
        }

        // Calculate commission (1.5% of the amount)
        $commissionFee = round($amount * 0.015, 2);
        $totalDebit = $amount + $commissionFee;

        // Check if sender has sufficient balance
        if ($sender->balance < $totalDebit) {
            return response()->json([
                'message' => 'Insufficient balance. You need ' . number_format($totalDebit, 2) . ' (including ' . number_format($commissionFee, 2) . ' commission fee).',
            ], 422);
        }

        try {
            // Use database transaction to ensure atomicity
            $transaction = DB::transaction(function () use ($sender, $receiverId, $amount, $commissionFee, $totalDebit) {
                // Lock the sender's row to prevent race conditions
                $sender = User::where('id', $sender->id)->lockForUpdate()->first();

                // Re-check balance after locking (in case of concurrent requests)
                if ($sender->balance < $totalDebit) {
                    throw new \Exception('Insufficient balance after lock.');
                }

                // Lock the receiver's row
                $receiver = User::where('id', $receiverId)->lockForUpdate()->first();

                if (!$receiver) {
                    throw new \Exception('Receiver not found.');
                }

                // Log balances before transaction
                \Log::info('Transaction processing', [
                    'sender_id' => $sender->id,
                    'sender_balance_before' => $sender->balance,
                    'receiver_id' => $receiver->id,
                    'receiver_balance_before' => $receiver->balance,
                    'amount' => $amount,
                    'commission_fee' => $commissionFee,
                    'total_debit' => $totalDebit,
                ]);

                // Deduct from sender (amount + commission)
                $sender->balance -= $totalDebit;
                $sender->save();

                // Credit receiver (only the amount, not commission)
                $receiver->balance += $amount;
                $receiver->save();

                // Log balances after transaction
                \Log::info('Transaction completed', [
                    'sender_id' => $sender->id,
                    'sender_balance_after' => $sender->balance,
                    'receiver_id' => $receiver->id,
                    'receiver_balance_after' => $receiver->balance,
                ]);

                // Create transaction record
                $transaction = Transaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'commission_fee' => $commissionFee,
                ]);

                // Load relationships for the response
                $transaction->load(['sender:id,name,email', 'receiver:id,name,email']);

                // Broadcast event to both sender and receiver
                \Log::info('Broadcasting transaction', [
                    'transaction_id' => $transaction->id,
                    'sender_id' => $transaction->sender_id,
                    'receiver_id' => $transaction->receiver_id,
                    'channels' => ['user.' . $transaction->sender_id, 'user.' . $transaction->receiver_id]
                ]);

                broadcast(new TransactionCreated($transaction));

                return $transaction;
            });

            return response()->json([
                'message' => 'Transaction successful.',
                'transaction' => $transaction,
                'new_balance' => $sender->fresh()->balance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
