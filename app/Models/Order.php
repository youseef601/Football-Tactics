<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Import the BelongsTo class

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_price',
        'currency',
    ];

    // Define the relationship with the User model
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the OrderItem model
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
