<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use SessionGuards;

    public function users(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $users = DB::table('users')
            ->select(['id', 'username', 'full_name', 'email', 'phone_number', 'role'])
            ->whereIn('role', ['teacher', 'student'])
            ->orderBy('role')
            ->orderBy('full_name')
            ->get();

        return view('users', ['users' => $users]);
    }

    public function userDetail(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $targetUserId = (int) $request->query('id', 0);
        if ($targetUserId <= 0) {
            abort(400, 'User khong hop le.');
        }

        $currentUserId = $this->currentUserId($request);
        $message = '';
        $error = '';

        if ($request->isMethod('post') && $request->input('action') === 'send_message') {
            $content = trim((string) $request->input('content', ''));
            if ($content === '') {
                $error = 'Noi dung tin nhan khong duoc rong.';
            } else {
                DB::table('messages')->insert([
                    'sender_id' => $currentUserId,
                    'receiver_id' => $targetUserId,
                    'content' => $content,
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $message = 'Gui tin nhan thanh cong.';
            }
        }

        $user = DB::table('users')
            ->select(['id', 'username', 'full_name', 'email', 'phone_number', 'avatar', 'role'])
            ->where('id', $targetUserId)
            ->whereIn('role', ['teacher', 'student'])
            ->first();

        if (!$user) {
            abort(404, 'Khong tim thay nguoi dung.');
        }

        $canReadMessages = $currentUserId === $targetUserId;
        $messages = collect();

        if ($canReadMessages) {
            $messages = DB::table('messages as m')
                ->join('users as u', 'u.id', '=', 'm.sender_id')
                ->select(['m.id', 'm.sender_id', 'm.content', 'm.created_at', 'u.username as sender_username'])
                ->where('m.receiver_id', $targetUserId)
                ->orderByDesc('m.created_at')
                ->get();
        }

        return view('user_detail', compact('user', 'messages', 'message', 'error', 'canReadMessages', 'currentUserId'));
    }
}
