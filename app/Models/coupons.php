<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class coupons extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon',
        'discount',
        'admin_id'
    ];

    /**
     * Get the admin that owns the coupon.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Set the discount attribute, ensuring it does not exceed 100%.
     */
    public function setDiscountAttribute($value)
    {
        // Ensure discount can store numeric values with one decimal or words
        if (is_numeric($value)) {
            // Limit the discount to a maximum of 100%
            $discountValue = min(100.0, (float)$value);
            $this->attributes['discount'] = number_format($discountValue, 1, '.', '');
        } else {
            $this->attributes['discount'] = $value;
        }
    }
}
