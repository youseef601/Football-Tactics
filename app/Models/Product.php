<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Import the BelongsTo class

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id', 'name', 'price', 'size', 'stock', 'status', 'category_id','leagues_id', 'imgs' , 'discount', 'final_price'
    ];

    // Relationship with the Admin model
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    // Relationship with the Category model
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function leagues(): BelongsTo
    {
        return $this->belongsTo(Leagues::class, 'leagues_id');
    }
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


}

