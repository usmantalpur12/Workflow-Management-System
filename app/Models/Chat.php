<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'sender_id', 'recipient_id', 'message', 'read', 'message_type', 'file_path',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function isRead()
    {
        return $this->read;
    }

    public function markAsRead()
    {
        $this->update(['read' => true]);
        return $this;
    }
}