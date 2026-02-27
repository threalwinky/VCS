<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use SessionGuards;

    public function profile(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $userId = $this->currentUserId($request);
        $message = '';
        $error = '';

        if ($request->isMethod('post')) {
            $email = trim((string) $request->input('email', ''));
            $phone = trim((string) $request->input('phone_number', ''));
            $avatarUrl = trim((string) $request->input('avatar_url', ''));
            $newPassword = (string) $request->input('password', '');
            $avatarPath = '';

            if ($request->hasFile('avatar_file')) {
                $file = $request->file('avatar_file');
                if ($file && $file->isValid()) {
                    $ext = strtolower((string) $file->getClientOriginalExtension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                        $error = 'Avatar file khong hop le. Chi chap nhan jpg/jpeg/png/gif/webp.';
                    } else {
                        $targetDir = public_path('static/images');
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0777, true);
                        }
                        $newName = 'avatar_'.$userId.'_'.time().'.'.$ext;
                        $file->move($targetDir, $newName);
                        $avatarPath = '/static/images/'.$newName;
                    }
                } else {
                    $error = 'Khong upload duoc avatar.';
                }
            }

            if ($error === '' && $avatarPath === '' && $avatarUrl !== '') {
                $avatarPath = $avatarUrl;
            }

            if ($error === '') {
                $updates = [
                    'email' => $email,
                    'phone_number' => $phone,
                    'updated_at' => now(),
                ];

                if ($avatarPath !== '') {
                    $updates['avatar'] = $avatarPath;
                }

                if ($newPassword !== '') {
                    $updates['password'] = $newPassword;
                }

                DB::table('users')->where('id', $userId)->update($updates);
                $message = 'Cap nhat thong tin thanh cong.';
            }
        }

        $user = DB::table('users')
            ->select(['username', 'full_name', 'email', 'phone_number', 'avatar', 'role'])
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        $classes = DB::table('class_user as cu')
            ->join('classes as c', 'c.id', '=', 'cu.class_id')
            ->select(['c.class_name', 'c.class_code'])
            ->where('cu.user_id', $userId)
            ->orderBy('c.class_name')
            ->get();

        return view('profile', compact('user', 'classes', 'message', 'error'));
    }
}
