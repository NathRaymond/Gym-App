<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'reference','wallet_id','user_id','type','amount','balance_before',
        'balance_after','description','source','transactionable_type',
        'transactionable_id','status','meta','created_by',
    ];

    protected $casts = ['meta' => 'array', 'amount' => 'decimal:2', 'balance_before' => 'decimal:2', 'balance_after' => 'decimal:2'];

    public function wallet() { return $this->belongsTo(Wallet::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function transactionable() { return $this->morphTo(); }
}