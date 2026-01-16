<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get all users except current user
        $users = User::where('id', '!=', Auth::id())->get();
        return view('chats.index', compact('users'));
    }

    public function advanced()
    {
        // Get all users except current user with last message info
        $users = User::where('id', '!=', Auth::id())
            ->with(['sentChats' => function($query) {
                $query->where('recipient_id', Auth::id())
                      ->orderBy('created_at', 'desc')
                      ->limit(1);
            }, 'receivedChats' => function($query) {
                $query->where('sender_id', Auth::id())
                      ->orderBy('created_at', 'desc')
                      ->limit(1);
            }])
            ->get()
            ->map(function($user) {
                // Get last message between current user and this user
                $lastMessage = Chat::where(function($query) use ($user) {
                    $query->where('sender_id', Auth::id())
                          ->where('recipient_id', $user->id);
                })->orWhere(function($query) use ($user) {
                    $query->where('sender_id', $user->id)
                          ->where('recipient_id', Auth::id());
                })->orderBy('created_at', 'desc')->first();

                $user->last_message = $lastMessage;
                $user->unread_count = Chat::where('sender_id', $user->id)
                    ->where('recipient_id', Auth::id())
                    ->where('read', false)
                    ->count();

                return $user;
            })
            ->sortByDesc(function($user) {
                return $user->last_message ? $user->last_message->created_at : null;
            });

        return view('chats.advanced', compact('users'));
    }

    public function getMessages($userId)
    {
        // Get messages between current user and selected user
        $messages = Chat::where(function($query) use ($userId) {
            $query->where('sender_id', Auth::id())
                  ->where('recipient_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('recipient_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'message_type' => 'nullable|in:text,image,file',
            'file_path' => 'nullable|string',
        ]);

        $chat = Chat::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'message' => $request->message,
            'message_type' => $request->message_type ?? 'text',
            'file_path' => $request->file_path,
            'read' => false,
        ]);

        // Load sender relationship for response
        $chat->load('sender');

        // Broadcast real-time event
        broadcast(new \App\Events\MessageSent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $chat
        ]);
    }

    public function markAsRead($userId)
    {
        Chat::where('sender_id', $userId)
            ->where('recipient_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = Chat::where('recipient_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function typing(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'is_typing' => 'required|boolean'
        ]);

        // Store typing status in cache for 10 seconds
        $key = "typing_{$request->recipient_id}_{Auth::id()}";
        
        if ($request->is_typing) {
            cache()->put($key, true, 10);
        } else {
            cache()->forget($key);
        }

        // Broadcast typing event
        broadcast(new \App\Events\UserTyping(Auth::user(), $request->recipient_id, $request->is_typing))->toOthers();

        return response()->json(['success' => true]);
    }

    public function getTypingStatus($userId)
    {
        $key = "typing_{$userId}_{Auth::id()}";
        $isTyping = cache()->has($key);

        return response()->json(['is_typing' => $isTyping]);
    }
}