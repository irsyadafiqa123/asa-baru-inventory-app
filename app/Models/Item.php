<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'item_name',
        'selling_price',
        'capital_price',
        'stock'
    ];

    // Relation to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relation to TransactionDetails
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'item_id');
    }
}
