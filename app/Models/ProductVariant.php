<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'discount_price',
        'stock',
        'thumbnails',
    ];

    protected $attributes = [
        'stock' => 0,
    ];

    public function attributes()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes', 'product_variant_id', 'attribute_value_id');
    }
}
