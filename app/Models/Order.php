<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_profile_id',
        'user_id',
        'total_amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function farmProfile()
    {
        return $this->belongsTo(FarmProfile::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}