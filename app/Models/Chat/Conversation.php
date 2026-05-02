<?php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use App\Models\User;

class Conversation extends Model {
    use BelongsToCompany;

    protected $fillable = ['company_id', 'type', 'name'];

    public function users() {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('last_read_message_id', 'deleted_at')
            ->withTimestamps();
    }

    public function messages() {
        return $this->hasMany(Message::class);
    }

    public function lastMessage() {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
