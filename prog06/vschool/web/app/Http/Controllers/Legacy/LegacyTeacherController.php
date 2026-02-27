<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LegacyTeacherController extends Controller
{
    use LegacySessionGuards;

    public function teacher(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $this->requireTeacherOrAdmin($request);

        $message = '';
        $error = '';

        if ($deleteId = (int) $request->query('delete_id', 0)) {
            DB::table('users')->where('id', $deleteId)->where('role', 'student')->delete();
            return redirect('/teacher.php');
        }

        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');

            if ($action === 'add_student') {
                $username = trim((string) $request->input('username', ''));
                $password = (string) $request->input('password', '');
                $fullName = trim((string) $request->input('full_name', ''));
                $email = trim((string) $request->input('email', ''));
                $phone = trim((string) $request->input('phone_number', ''));

                if ($username === '' || $password === '' || $fullName === '' || $email === '') {
                    $error = 'Vui long nhap day du thong tin sinh vien.';
                } else {
                    try {
                        DB::table('users')->insert([
                            'username' => $username,
                            'password' => Hash::make($password),
                            'full_name' => $fullName,
                            'email' => $email,
                            'phone_number' => $phone,
                            'role' => 'student',
                            'avatar' => '/static/images/default.jpg',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $message = 'Them sinh vien thanh cong.';
                    } catch (\Throwable) {
                        $error = 'Khong the them sinh vien. Co the trung username/email.';
                    }
                }
            }

            if ($action === 'edit_student') {
                $id = (int) $request->input('id', 0);
                $username = trim((string) $request->input('username', ''));
                $password = (string) $request->input('password', '');
                $fullName = trim((string) $request->input('full_name', ''));
                $email = trim((string) $request->input('email', ''));
                $phone = trim((string) $request->input('phone_number', ''));

                if ($id <= 0 || $username === '' || $fullName === '' || $email === '') {
                    $error = 'Du lieu cap nhat khong hop le.';
                } else {
                    $update = [
                        'username' => $username,
                        'full_name' => $fullName,
                        'email' => $email,
                        'phone_number' => $phone,
                        'updated_at' => now(),
                    ];
                    if ($password !== '') {
                        $update['password'] = Hash::make($password);
                    }

                    DB::table('users')->where('id', $id)->where('role', 'student')->update($update);
                    $message = 'Cap nhat sinh vien thanh cong.';
                }
            }
        }

        $editStudent = null;
        if ($editId = (int) $request->query('edit_id', 0)) {
            $editStudent = DB::table('users')
                ->select(['id', 'username', 'full_name', 'email', 'phone_number'])
                ->where('id', $editId)
                ->where('role', 'student')
                ->first();
        }

        $students = DB::table('users')
            ->select(['id', 'username', 'full_name', 'email', 'phone_number', 'created_at'])
            ->where('role', 'student')
            ->orderByDesc('id')
            ->get();

        return view('legacy.teacher', compact('students', 'editStudent', 'message', 'error'));
    }
}
