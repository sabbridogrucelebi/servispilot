<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'creator_id',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'creator_id');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'chat_group_user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
