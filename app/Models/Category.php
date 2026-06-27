<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['cat_name', 'parent_id'];

    // Relationship to Products
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    // Relationship to Parent Category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relationship to Subcategories (children)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
