<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
     use HasFactory;

    protected $fillable = [
        'name', 'slug', 'quantity', 'regular_price', 'sale_price',
        'category', 'thumbnail', 'viewer', 'author', 'description'
    ];

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_product');
    }

    public function categoryData()
    {
        return $this->belongsTo(Category::class, 'category');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'author');
    }
}
