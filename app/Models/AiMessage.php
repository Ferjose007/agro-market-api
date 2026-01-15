<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $fillable = [
        'ai_chat_id',
        'role',
        'content'
    ];

    public function Chat()
    {
        return $this->belongsTo(AiChat::class);
    }
}
