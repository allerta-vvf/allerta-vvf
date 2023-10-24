<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBotLogins extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_id',
        'tmp_login_code'
    ];

    /**
     * Get the user that owns the Telegram chat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
