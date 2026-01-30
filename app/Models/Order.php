<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 1. Lista blanca de campos que se pueden guardar
    protected $fillable = [
        'farm_profile_id',
        'user_id',
        'total_amount',
        'status',
    ];

    // 2. Relaciones

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function farmProfile()
    {
        return $this->belongsTo(FarmProfile::class);
    }
}