<?php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Message extends Model {
    use SoftDeletes;

    protected $fillable = ['conversation_id', 'sender_id', 'body', 'type', 'deleted_for_everyone'];

    public function conversation() {
        return $this->belongsTo(Conversation::class);
    }

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments() {
        return $this->hasMany(Attachment::class);
    }
}
