<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'item_id',
        'item_name',
        'amount',
        'capital_price',
        'selling_price',
        'subtotal',
    ];

    // Relation to Transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relation to Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
