<?php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {
    protected $fillable = ['message_id', 'path', 'filename', 'mime_type'];

    public function message() {
        return $this->belongsTo(Message::class);
    }
}
