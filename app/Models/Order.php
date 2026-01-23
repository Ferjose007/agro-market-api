<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 1. Lista blanca de campos que se pueden guardar
    protected $fillable = [
        'farm_profile_id',  // El vendedor (Tu granja)
        'user_id',          // El comprador (El cliente)
        'total_amount',     // Cuánto costó
        'status',           // 'pendiente', 'completado', 'cancelado'
    ];

    // 2. Relaciones

    // Un pedido pertenece a un CLIENTE (Comprador)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un pedido pertenece a una GRANJA (Vendedor)
    public function farmProfile()
    {
        return $this->belongsTo(FarmProfile::class);
    }
}