<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_date',
        'transaction_type'
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation to TransactionDetails
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, "transaction_id");
    }
}
