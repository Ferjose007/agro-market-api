<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_profile_id', // O 'farm_id' dependiendo de cómo lo llamaste al final
        'category_id',     // <--- ¡Importante! No olvides agregar este nuevo
        'name',
        'description',
        'price_per_unit',  // <--- CAMBIADO (Antes era 'price')
        'stock_quantity',
        'unit',            // <--- CAMBIADO (Antes era 'unit_type')
        'harvest_date',
        'is_active',
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function farmProfile()
    {
        return $this->belongsTo(FarmProfile::class);
    }

    public function priceBreakdown()
    {
        return $this->hasOne(PriceBreakdown::class);
    }
}
