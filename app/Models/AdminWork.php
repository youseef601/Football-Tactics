<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminWork extends Model
{
    use HasFactory;

    // Explicitly define the table name
    protected $table = 'admin_work';

    // Add the new columns to the fillable array
    protected $fillable = [
        'admin_id',
        'slide1',
        'slide2',
        'slide3',
        'text1',
        'text2',
        'text3',
        'best1',
        'best2',
        'best3',
        'best4',
        'new1',
        'new2',
        'new3',
        'new4'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    // Define relationships for the best and new products
    public function best1()
    {
        return $this->belongsTo(Product::class, 'best1');
    }

    public function best2()
    {
        return $this->belongsTo(Product::class, 'best2');
    }

    public function best3()
    {
        return $this->belongsTo(Product::class, 'best3');
    }
    public function best4()
    {
        return $this->belongsTo(Product::class, 'best3');
    }


    public function new1()
    {
        return $this->belongsTo(Product::class, 'new1');
    }

    public function new2()
    {
        return $this->belongsTo(Product::class, 'new2');
    }

    public function new3()
    {
        return $this->belongsTo(Product::class, 'new3');
    }
    public function new4()
    {
        return $this->belongsTo(Product::class, 'new4');
    }

}
