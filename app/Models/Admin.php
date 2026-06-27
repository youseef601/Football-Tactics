<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import the BelongsTo class
use Illuminate\Database\Eloquent\Relations\HasMany; // Import the HasMany class

class Admin extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'admin_name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id'); // Specify the foreign key
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'admin_id');
    }

}
