<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramSpecialMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'chat_id',
        'chat_type',
        'type',
        'resource_id',
        'resource_type',
        'created_at',
        'updated_at'
    ];
}
