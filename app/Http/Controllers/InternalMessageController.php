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
    public function contacts(?Request $request = null): JsonResponse
    {
        $request = $request ?? request();
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;
        $search = trim((string) $request->query('search', ''));

        $isAdmin = $currentUser->isAdmin();
        $adminIds = User::admins()->pluck('id')->all();

        // IF NON-ADMIN: Show General Admin Support Helpdesk Channel
        if (!$isAdmin) {
            $primaryAdmin = User::admins()->first() ?? User::find(1);
            $adminId = $primaryAdmin ? $primaryAdmin->id : 1;

            $unreadCount = InternalMessage::where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->count();

            $lastMsg = InternalMessage::where(function ($q) use ($currentUserId, $adminIds) {
                $q->where('sender_id', $currentUserId)->whereIn('receiver_id', $adminIds);
            })->orWhere(function ($q) use ($currentUserId, $adminIds) {
                $q->whereIn('sender_id', $adminIds)->where('receiver_id', $currentUserId);
            })
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

        // IF ADMIN: Fetch list of users who have messaged Admin Support or available users
        $recentUserIds = InternalMessage::where(function ($q) use ($adminIds) {
                $q->whereIn('receiver_id', $adminIds)
                  ->whereNotIn('sender_id', $adminIds);
            })
            ->orWhere(function ($q) use ($adminIds) {
                $q->whereIn('sender_id', $adminIds)
                  ->whereNotIn('receiver_id', $adminIds);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($m) use ($adminIds) {
                return in_array($m->sender_id, $adminIds) ? $m->receiver_id : $m->sender_id;
            })
            ->unique()
            ->values()
            ->all();

        if ($search === '') {
            $recentUsers = !empty($recentUserIds)
                ? User::with('employee')->whereIn('id', $recentUserIds)->get()->sortBy(function ($u) use ($recentUserIds) {
                    return array_search($u->id, $recentUserIds);
                })
                : collect();

            $otherUsers = User::with('employee')
                ->whereNotIn('id', array_merge($adminIds, $recentUserIds))
                ->take(30)
                ->get();

            $allUsers = $recentUsers->concat($otherUsers);
        } else {
            $allUsers = User::with('employee')
                ->whereNotIn('id', $adminIds)
                ->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                })
                ->take(50)
                ->get();
        }

        $contacts = $allUsers->map(function ($u) use ($adminIds) {
            $unreadCount = InternalMessage::where('sender_id', $u->id)
                ->whereIn('receiver_id', $adminIds)
                ->where('is_read', false)
                ->count();

            $lastMsg = InternalMessage::where(function ($q) use ($u, $adminIds) {
                $q->where('sender_id', $u->id)->whereIn('receiver_id', $adminIds);
            })->orWhere(function ($q) use ($u, $adminIds) {
                $q->whereIn('sender_id', $adminIds)->where('receiver_id', $u->id);
            })
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
        })->values();

        return response()->json([
            'status' => 'success',
            'is_admin' => true,
            'contacts' => $contacts,
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
        $isAdmin = $currentUser->isAdmin();
        $adminIds = User::admins()->pluck('id')->all();

        if ($isAdmin) {
            $targetUserId = $contactId;

            // Mark incoming messages from target user to admin team as read
            InternalMessage::where('sender_id', $targetUserId)
                ->whereIn('receiver_id', $adminIds)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messagesQuery = InternalMessage::where(function ($q) use ($targetUserId, $adminIds) {
                $q->where('sender_id', $targetUserId)->whereIn('receiver_id', $adminIds);
            })->orWhere(function ($q) use ($targetUserId, $adminIds) {
                $q->whereIn('sender_id', $adminIds)->where('receiver_id', $targetUserId);
            });
        } else {
            // Non-admin user viewing Admin Support conversation
            InternalMessage::where('receiver_id', $currentUserId)
                ->whereIn('sender_id', $adminIds)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messagesQuery = InternalMessage::where(function ($q) use ($currentUserId, $adminIds) {
                $q->where('sender_id', $currentUserId)->whereIn('receiver_id', $adminIds);
            })->orWhere(function ($q) use ($currentUserId, $adminIds) {
                $q->whereIn('sender_id', $adminIds)->where('receiver_id', $currentUserId);
            });
        }

        $messages = $messagesQuery->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) use ($currentUserId, $adminIds) {
                $isSelf = $m->sender_id === $currentUserId;
                $isSenderAdmin = in_array($m->sender_id, $adminIds);

                $senderName = $isSelf
                    ? 'Anda'
                    : ($isSenderAdmin
                        ? ($m->sender?->name ? $m->sender->name . ' (Admin)' : 'Admin Support')
                        : ($m->sender?->name ?? 'Karyawan'));

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

        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->isAdmin();

        $receiverId = (int) $validated['receiver_id'];

        // If non-admin sending message to Admin Support, ensure receiver_id is a valid admin
        if (!$isAdmin) {
            $adminIds = User::admins()->pluck('id')->all();
            if (!in_array($receiverId, $adminIds)) {
                $primaryAdmin = User::admins()->first();
                $receiverId = $primaryAdmin ? $primaryAdmin->id : $receiverId;
            }
        }

        $msg = InternalMessage::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $receiverId,
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
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['unread_count' => 0]);
        }

        if ($user->isAdmin()) {
            $adminIds = User::admins()->pluck('id')->all();
            $count = InternalMessage::whereIn('receiver_id', $adminIds)
                ->whereNotIn('sender_id', $adminIds)
                ->where('is_read', false)
                ->count();
        } else {
            $count = InternalMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json(['unread_count' => $count]);
    }
}
