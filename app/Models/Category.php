<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'category_name',
        'information'
    ];

    // Relation to Items
    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
