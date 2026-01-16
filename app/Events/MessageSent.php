<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat.' . $this->chat->recipient_id),
            new PrivateChannel('chat.' . $this->chat->sender_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->chat->id,
            'sender_id' => $this->chat->sender_id,
            'recipient_id' => $this->chat->recipient_id,
            'message' => $this->chat->message,
            'message_type' => $this->chat->message_type,
            'file_path' => $this->chat->file_path,
            'read' => $this->chat->read,
            'created_at' => $this->chat->created_at,
            'sender' => [
                'id' => $this->chat->sender->id,
                'name' => $this->chat->sender->name,
                'profile_picture_url' => $this->chat->sender->profile_picture_url,
            ]
        ];
    }
}