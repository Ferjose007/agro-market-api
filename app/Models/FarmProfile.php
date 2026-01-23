<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FarmProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'location_lat',
        'location_lng',
        'bio',
        'soil_type',
        'whatsapp_number',
        'address',
        'contact_email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function weatherLogs()
    {
        return $this->hasMany(WeatherLog::class);
    }
}
