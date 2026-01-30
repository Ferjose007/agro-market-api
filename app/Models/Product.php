<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_profile_id',
        'category_id',
        'name',
        'description',
        'price_per_unit',
        'stock_quantity',
        'unit',
        'farming_type',
        'harvest_date',
        'is_active',
        'image_url',
        'farmer_earning',
        'platform_fee',
        'logistics_cost',
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function farmProfile()
    {
        return $this->belongsTo(FarmProfile::class);
    }

    public function priceBreakdown()
    {
        return $this->hasOne(PriceBreakdown::class);
    }
}
