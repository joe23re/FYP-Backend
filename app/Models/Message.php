<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['user_id','garage_id','message','sender_type','is_read'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }
}
