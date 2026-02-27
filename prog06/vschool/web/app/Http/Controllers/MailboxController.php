<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailboxController extends Controller
{
    use SessionGuards;

    public function mailbox(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $userId = $this->currentUserId($request);
        $message = '';
        $error = '';

        if ($deleteId = (int) $request->query('delete_message', 0)) {
            DB::table('messages')->where('id', $deleteId)->where('sender_id', $userId)->delete();
            return redirect('/mailbox.php');
        }

        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');

            if ($action === 'send_message') {
                $receiverId = (int) $request->input('receiver_id', 0);
                $content = trim((string) $request->input('content', ''));

                if ($receiverId <= 0 || $content === '') {
                    $error = 'Thong tin gui tin nhan khong hop le.';
                } else {
                    $exists = DB::table('users')
                        ->where('id', $receiverId)
                        ->whereIn('role', ['teacher', 'student'])
                        ->exists();

                    if (!$exists) {
                        $error = 'Nguoi nhan khong hop le.';
                    } else {
                        DB::table('messages')->insert([
                            'sender_id' => $userId,
                            'receiver_id' => $receiverId,
                            'content' => $content,
                            'is_read' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $message = 'Gui tin nhan thanh cong.';
                    }
                }
            }

            if ($action === 'toggle_read') {
                $messageId = (int) $request->input('message_id', 0);
                if ($messageId > 0) {
                    $isRead = $request->has('is_read') ? 1 : 0;
                    DB::table('messages')
                        ->where('id', $messageId)
                        ->where('receiver_id', $userId)
                        ->update([
                            'is_read' => $isRead,
                            'read_at' => $isRead ? now() : null,
                            'updated_at' => now(),
                        ]);
                    $message = 'Cap nhat trang thai doc thanh cong.';
                }
            }

            if ($action === 'edit_message') {
                $messageId = (int) $request->input('message_id', 0);
                $content = trim((string) $request->input('content', ''));
                if ($messageId > 0 && $content !== '') {
                    DB::table('messages')
                        ->where('id', $messageId)
                        ->where('sender_id', $userId)
                        ->update([
                            'content' => $content,
                            'is_read' => 0,
                            'read_at' => null,
                            'updated_at' => now(),
                        ]);
                    $message = 'Sua tin nhan thanh cong.';
                } else {
                    $error = 'Khong the sua tin nhan.';
                }
            }
        }

        $userList = DB::table('users')
            ->select(['id', 'username', 'full_name', 'role'])
            ->where('id', '<>', $userId)
            ->whereIn('role', ['teacher', 'student'])
            ->orderBy('role')
            ->orderBy('full_name')
            ->get();

        $inbox = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->select(['m.id', 'm.content', 'm.is_read', 'm.read_at', 'm.created_at', 'u.full_name as sender_name', 'u.username as sender_username'])
            ->where('m.receiver_id', $userId)
            ->orderByDesc('m.created_at')
            ->get();

        $outbox = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.receiver_id')
            ->select(['m.id', 'm.content', 'm.is_read', 'm.created_at', 'u.full_name as receiver_name', 'u.username as receiver_username'])
            ->where('m.sender_id', $userId)
            ->orderByDesc('m.created_at')
            ->get();

        return view('mailbox', compact('userList', 'inbox', 'outbox', 'message', 'error'));
    }
}
