<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class codes extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id', 'code', 'user_id'
    ];


    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
