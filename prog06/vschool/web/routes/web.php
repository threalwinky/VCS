<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailboxController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'home']);

Route::get('/index.php', [AuthController::class, 'home']);

Route::get('/login.php', [AuthController::class, 'showLogin']);
Route::post('/login.php', [AuthController::class, 'login']);

Route::get('/register.php', [AuthController::class, 'showRegister']);
Route::post('/register.php', [AuthController::class, 'register']);

Route::get('/dashboard.php', [DashboardController::class, 'dashboard']);

Route::match(['get', 'post'], '/profile.php', [ProfileController::class, 'profile']);

Route::get('/users.php', [UserController::class, 'users']);
Route::match(['get', 'post'], '/user_detail.php', [UserController::class, 'userDetail']);

Route::match(['get', 'post'], '/mailbox.php', [MailboxController::class, 'mailbox']);
Route::match(['get', 'post'], '/assignments.php', [AssignmentController::class, 'assignments']);
Route::match(['get', 'post'], '/challenge.php', [ChallengeController::class, 'challenge']);
Route::match(['get', 'post'], '/teacher.php', [TeacherController::class, 'teacher']);

Route::get('/logout.php', [AuthController::class, 'logout']);
