<?php

use App\Http\Controllers\Legacy\LegacyAssignmentController;
use App\Http\Controllers\Legacy\LegacyAuthController;
use App\Http\Controllers\Legacy\LegacyChallengeController;
use App\Http\Controllers\Legacy\LegacyDashboardController;
use App\Http\Controllers\Legacy\LegacyMailboxController;
use App\Http\Controllers\Legacy\LegacyProfileController;
use App\Http\Controllers\Legacy\LegacyTeacherController;
use App\Http\Controllers\Legacy\LegacyUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LegacyAuthController::class, 'home']);

Route::get('/index.php', [LegacyAuthController::class, 'home']);

Route::get('/login.php', [LegacyAuthController::class, 'showLogin']);
Route::post('/login.php', [LegacyAuthController::class, 'login']);

Route::get('/register.php', [LegacyAuthController::class, 'showRegister']);
Route::post('/register.php', [LegacyAuthController::class, 'register']);

Route::get('/dashboard.php', [LegacyDashboardController::class, 'dashboard']);

Route::match(['get', 'post'], '/profile.php', [LegacyProfileController::class, 'profile']);

Route::get('/users.php', [LegacyUserController::class, 'users']);
Route::match(['get', 'post'], '/user_detail.php', [LegacyUserController::class, 'userDetail']);

Route::match(['get', 'post'], '/mailbox.php', [LegacyMailboxController::class, 'mailbox']);
Route::match(['get', 'post'], '/assignments.php', [LegacyAssignmentController::class, 'assignments']);
Route::match(['get', 'post'], '/challenge.php', [LegacyChallengeController::class, 'challenge']);
Route::match(['get', 'post'], '/teacher.php', [LegacyTeacherController::class, 'teacher']);

Route::get('/logout.php', [LegacyAuthController::class, 'logout']);
