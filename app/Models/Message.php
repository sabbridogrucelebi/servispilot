<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'company_id',
        'sender_id',
        'receiver_id',
        'chat_group_id',
        'body',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'receiver_id');
    }

    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class);
    }
}
