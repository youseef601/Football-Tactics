<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingData extends Model
{
    use HasFactory;

    // Specify the table name if it differs from the model name
    protected $table = 'billing_data';

    // Define the fillable fields
    protected $fillable = [
        'apartment',
        'first_name',
        'last_name',
        'street',
        'building',
        'phone_number',
        'city',
        'country',
        'email',
        'floor',
        'state',
        'total_price',
        'currency',
        'payment_methods',
        'order_id', // Add order_id to fillable
        'user_id'   // Add user_id to fillable
    ];

    // Cast payment_methods to array since it's stored as JSON in the database
    protected $casts = [
        'payment_methods' => 'array',
        'total_price' => 'decimal:2', // Ensure decimal with 2 places is used
    ];
}
