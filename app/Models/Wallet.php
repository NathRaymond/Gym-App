<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance', 'total_funded', 'total_spent', 'currency', 'is_active'];
    protected $casts = ['balance' => 'decimal:2', 'total_funded' => 'decimal:2', 'total_spent' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(WalletTransaction::class); }

    public function credit(float $amount, string $description, $source = null, $transactionable = null, ?int $createdBy = null): WalletTransaction
    {
        return \DB::transaction(function () use ($amount, $description, $source, $transactionable, $createdBy) {
            $before = $this->balance;
            $this->increment('balance', $amount);
            $this->increment('total_funded', $amount);
            $this->refresh();

            return $this->transactions()->create([
                'reference' => 'WTX-' . strtoupper(\Str::random(10)),
                'user_id' => $this->user_id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $this->balance,
                'description' => $description,
                'source' => $source,
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id' => $transactionable?->id,
                'status' => 'completed',
                'created_by' => $createdBy,
            ]);
        });
    }

    public function debit(float $amount, string $description, $source = null, $transactionable = null, ?int $createdBy = null): WalletTransaction
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient wallet balance.');
        }

        return \DB::transaction(function () use ($amount, $description, $source, $transactionable, $createdBy) {
            $before = $this->balance;
            $this->decrement('balance', $amount);
            $this->increment('total_spent', $amount);
            $this->refresh();

            return $this->transactions()->create([
                'reference' => 'WTX-' . strtoupper(\Str::random(10)),
                'user_id' => $this->user_id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $this->balance,
                'description' => $description,
                'source' => $source,
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id' => $transactionable?->id,
                'status' => 'completed',
                'created_by' => $createdBy,
            ]);
        });
    }
}