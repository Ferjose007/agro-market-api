<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceBreakdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'farmer_earning',
        'plataform_fee',
        'logistics_cost',
        'taxes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
