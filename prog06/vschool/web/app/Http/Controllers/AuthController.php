<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function home()
    {
        return view('index');
    }

    public function showLogin()
    {
        return view('login', ['error' => '']);
    }

    public function login(Request $request)
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');

        if ($username === '' || $password === '') {
            return view('login', ['error' => 'Username va mat khau khong hop le.']);
        }

        $user = DB::table('users')
            ->select(['id', 'username', 'password', 'full_name', 'role'])
            ->where('username', $username)
            ->first();

        if (!$user) {
            return view('login', ['error' => 'Khong tim thay tai khoan.']);
        }

        $passwordOk = $password === $user->password || Hash::check($password, $user->password);

        if (!$passwordOk) {
            return view('login', ['error' => 'Sai mat khau.']);
        }

        $request->session()->put([
            'user_id' => (int) $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'role' => $user->role,
        ]);

        return redirect('/dashboard.php');
    }

    public function showRegister()
    {
        return view('register', ['message' => '', 'error' => '']);
    }

    public function register(Request $request)
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');
        $fullName = trim((string) $request->input('full_name', ''));
        $email = trim((string) $request->input('email', ''));
        $phone = trim((string) $request->input('phone_number', ''));

        if ($username === '' || $password === '' || $fullName === '' || $email === '') {
            return view('register', ['message' => '', 'error' => 'Vui long nhap day du cac truong bat buoc.']);
        }

        $exists = DB::table('users')
            ->where('username', $username)
            ->orWhere('email', $email)
            ->exists();

        if ($exists) {
            return view('register', ['message' => '', 'error' => 'Username hoac email da ton tai.']);
        }

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

        return view('register', ['message' => 'Dang ky thanh cong. Ban co the dang nhap ngay bay gio.', 'error' => '']);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/index.php');
    }
}
