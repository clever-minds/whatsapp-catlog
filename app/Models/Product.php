<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        // Extract the ID from a slug like '156-product-name'
        $id = explode('-', $value)[0];
        return $this->where($field ?? 'id', $id)->firstOrFail();
    }
}
