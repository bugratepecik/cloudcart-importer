<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    use HasFactory;
    protected $fillable = [
        'name', 'sku', 'price', 'quantity', 'brand', 'category', 'tags',
        'description', 'image_url', 'variant_1_name', 'variant_1_value',
        'variant_2_name', 'variant_2_value'
    ];
}
