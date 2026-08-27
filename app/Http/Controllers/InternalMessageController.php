<?php

namespace App\Http\Controllers;

use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternalMessageController extends Controller
{
    /**
     * Ambil daftar kontak (karyawan / Layanan Admin Support) beserta pesan terakhir & jumlah unread.
     */
    public function contacts(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;
        $search = trim((string) $request->query('search', ''));

        $isAdmin = in_array(strtolower($currentUser->role ?? ''), ['admin', 'superadmin', 'management']) 
            || str_contains(strtolower($currentUser->jabatan ?? ''), 'admin');

        // IF NON-ADMIN: Show General Admin Support Helpdesk Channel
        if (!$isAdmin) {
            $primaryAdmin = User::whereIn('role', ['admin', 'superadmin', 'management'])
                ->orWhere('jabatan', 'like', '%admin%')
                ->first() ?? User::find(1);

            $adminId = $primaryAdmin ? $primaryAdmin->id : 1;

            $unreadCount = InternalMessage::where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->count();

            $lastMsg = InternalMessage::where('sender_id', $currentUserId)
                ->orWhere('receiver_id', $currentUserId)
                ->orderBy('created_at', 'desc')
                ->first();

            $supportContact = [
                'id' => $adminId,
                'name' => 'Admin Support OneClick',
                'nrp' => 'SUPPORT',
                'jabatan' => 'Pusat Layanan & Helpdesk Admin',
                'unit' => 'ONECLICK SUPPORT',
                'online' => true,
                'unread' => $unreadCount,
                'last_message' => $lastMsg ? [
                    'body' => $lastMsg->body,
                    'created_at' => $lastMsg->created_at->format('H:i'),
                ] : null,
            ];

            return response()->json([
                'status' => 'success',
                'is_admin' => false,
                'contacts' => [$supportContact],
            ]);
        }

        // IF ADMIN: Fetch list of users who have messaged or available users
        $usersQuery = User::with('employee')
            ->where('id', '!=', $currentUserId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                });
            });

        $users = $usersQuery->take(50)->get()->map(function ($u) use ($currentUserId) {
            $unreadCount = InternalMessage::where('sender_id', $u->id)
                ->where('is_read', false)
                ->count();

            $lastMsg = InternalMessage::where('sender_id', $u->id)
                ->orWhere('receiver_id', $u->id)
                ->orderBy('created_at', 'desc')
                ->first();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'nrp' => $u->username ?? '-',
                'jabatan' => $u->employee?->jabatan_ss6 ?? $u->jabatan ?? 'KARYAWAN',
                'unit' => $u->employee?->unit ?? $u->unit ?? 'OPERATIONAL',
                'online' => true,
                'unread' => $unreadCount,
                'last_message' => $lastMsg ? [
                    'body' => $lastMsg->body,
                    'created_at' => $lastMsg->created_at->format('H:i'),
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'is_admin' => true,
            'contacts' => $users,
        ]);
    }

    /**
     * Ambil riwayat percakapan antara user login dan target kontak.
     */
    public function getMessages(int $contactId): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;

        $isAdmin = in_array(strtolower($currentUser->role ?? ''), ['admin', 'superadmin', 'management']) 
            || str_contains(strtolower($currentUser->jabatan ?? ''), 'admin');

        if ($isAdmin) {
            // Admin viewing user conversation
            $targetUserId = $contactId;

            // Mark incoming messages from target user as read
            InternalMessage::where('sender_id', $targetUserId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messagesQuery = InternalMessage::where('sender_id', $targetUserId)
                ->orWhere('receiver_id', $targetUserId);
        } else {
            // Non-admin user viewing Admin Support conversation
            InternalMessage::where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messagesQuery = InternalMessage::where('sender_id', $currentUserId)
                ->orWhere('receiver_id', $currentUserId);
        }

        $messages = $messagesQuery->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) use ($currentUserId, $isAdmin) {
                $isSelf = $m->sender_id === $currentUserId;
                
                $senderName = $isSelf ? 'Anda' : ($m->sender?->name ? $m->sender->name . ' (Admin)' : 'Admin Support');

                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => $senderName,
                    'body' => $m->body,
                    'is_read' => $m->is_read,
                    'created_at' => $m->created_at->format('Y-m-d, H:i'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'messages' => $messages,
        ]);
    }

    /**
     * Kirim pesan baru ke target kontak / Admin Support.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string|max:2000',
        ]);

        $msg = InternalMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'body' => trim($validated['body']),
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => 'Anda',
                'body' => $msg->body,
                'is_read' => false,
                'created_at' => $msg->created_at->format('Y-m-d, H:i'),
            ],
        ]);
    }

    /**
     * Hitung total pesan belum dibaca untuk topbar badge.
     */
    public function unreadCount(): JsonResponse
    {
        $count = InternalMessage::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
