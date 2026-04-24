<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\ChatGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function fetchConversations()
    {
        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        // Get all users except current
        $users = User::where('company_id', $companyId)
            ->where('id', '!=', $userId)
            ->select('id', 'name', 'avatar', 'is_active')
            ->get();

        // Get all groups the user is part of
        $groups = ChatGroup::where('company_id', $companyId)
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['users:id,name'])
            ->get();

        // Calculate unread counts and last message for each user
        $conversations = [];

        foreach ($users as $user) {
            $lastMessage = Message::where('company_id', $companyId)
                ->whereNull('chat_group_id')
                ->where(function($q) use ($userId, $user) {
                    $q->where(function($q) use ($userId, $user) {
                        $q->where('sender_id', $userId)->where('receiver_id', $user->id);
                    })->orWhere(function($q) use ($userId, $user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $userId);
                    });
                })
                ->latest()
                ->first();

            $unreadCount = Message::where('company_id', $companyId)
                ->whereNull('chat_group_id')
                ->where('sender_id', $user->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'type' => 'direct',
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? null,
                'is_active' => $user->is_active,
                'last_message' => $lastMessage ? $lastMessage->body : null,
                'last_message_time' => $lastMessage ? $lastMessage->created_at->format('H:i') : null,
                'last_message_timestamp' => $lastMessage ? $lastMessage->created_at->timestamp : 0,
                'unread_count' => $unreadCount,
            ];
        }

        foreach ($groups as $group) {
            $lastMessage = Message::where('chat_group_id', $group->id)->latest()->first();
            
            // For groups, we could store last read per user. For simplicity, just check messages sent since user joined or use a simple heuristic.
            // A quick fix for group unread: messages in group not read by this user.
            // We need a pivot table or simpler approach. We will just say 0 for now or fetch new since last check.
            $unreadCount = 0; 

            $conversations[] = [
                'type' => 'group',
                'id' => $group->id,
                'name' => $group->name,
                'avatar' => null,
                'is_active' => true,
                'last_message' => $lastMessage ? ($lastMessage->sender_id === $userId ? 'Sen: ' : $lastMessage->sender->name . ': ') . $lastMessage->body : null,
                'last_message_time' => $lastMessage ? $lastMessage->created_at->format('H:i') : null,
                'last_message_timestamp' => $lastMessage ? $lastMessage->created_at->timestamp : 0,
                'unread_count' => $unreadCount,
                'participants' => $group->users->pluck('name')->implode(', '),
            ];
        }

        usort($conversations, function ($a, $b) {
            return $b['last_message_timestamp'] <=> $a['last_message_timestamp'];
        });

        return response()->json($conversations);
    }

    public function fetchMessages(Request $request)
    {
        $type = $request->get('type'); // 'direct' or 'group'
        $id = $request->get('id');
        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        if ($type === 'direct') {
            $messages = Message::with('sender:id,name,avatar')
                ->where('company_id', $companyId)
                ->whereNull('chat_group_id')
                ->where(function($q) use ($userId, $id) {
                    $q->where(function($q) use ($userId, $id) {
                        $q->where('sender_id', $userId)->where('receiver_id', $id);
                    })->orWhere(function($q) use ($userId, $id) {
                        $q->where('sender_id', $id)->where('receiver_id', $userId);
                    });
                })
                ->oldest()
                ->get();

            // Mark as read
            Message::where('company_id', $companyId)
                ->whereNull('chat_group_id')
                ->where('sender_id', $id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

        } else {
            $messages = Message::with('sender:id,name,avatar')
                ->where('company_id', $companyId)
                ->where('chat_group_id', $id)
                ->oldest()
                ->get();
        }

        $formatted = $messages->map(function ($msg) use ($userId) {
            return [
                'id' => $msg->id,
                'is_mine' => $msg->sender_id === $userId,
                'body' => $msg->body,
                'time' => $msg->created_at->format('H:i'),
                'sender_name' => $msg->sender->name ?? 'Bilinmeyen',
                'is_read' => $msg->is_read,
            ];
        });

        return response()->json($formatted);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'type' => 'required|in:direct,group',
            'id' => 'required|integer',
            'body' => 'required|string|max:2000',
        ]);

        $msg = Message::create([
            'company_id' => auth()->user()->company_id,
            'sender_id' => auth()->id(),
            'receiver_id' => $request->type === 'direct' ? $request->id : null,
            'chat_group_id' => $request->type === 'group' ? $request->id : null,
            'body' => $request->body,
        ]);

        return response()->json(['success' => true]);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'users' => 'required|array',
            'users.*' => 'integer|exists:users,id',
        ]);

        $group = ChatGroup::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'creator_id' => auth()->id(),
        ]);

        $users = collect($request->users)->push(auth()->id())->unique();
        $group->users()->attach($users);

        return response()->json(['success' => true]);
    }

    public function fetchAllUsers()
    {
        $users = User::where('company_id', auth()->user()->company_id)
            ->where('id', '!=', auth()->id())
            ->select('id', 'name', 'email')
            ->get();
            
        return response()->json($users);
    }

    public function fetchUnreadCount()
    {
        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        // Check for new direct messages
        $unreadCount = Message::where('company_id', $companyId)
            ->whereNull('chat_group_id')
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        // Find latest unread message for the toast
        $latest = Message::with('sender:id,name')
            ->where('company_id', $companyId)
            ->whereNull('chat_group_id')
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->latest()
            ->first();

        return response()->json([
            'count' => $unreadCount,
            'latest' => $latest ? [
                'id' => $latest->id,
                'sender' => $latest->sender->name ?? 'Bilinmeyen',
                'body' => \Illuminate\Support\Str::limit($latest->body, 30),
            ] : null
        ]);
    }
}
