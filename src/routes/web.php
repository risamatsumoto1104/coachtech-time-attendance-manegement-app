<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

// 誰でもアクセスできるルート
// Route::middleware('guest')->group(function () {
// ログイン（管理者）
Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');

// ログイン（一般ユーザー）
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('user.login');
// });

// ログイン済みのみがアクセスできるルート（管理者）
// Route::middleware('auth')->group(function () {
// 勤怠一覧画面
Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list.index');

// 勤怠詳細画面
Route::get('/admin/attendance/1', [AdminAttendanceController::class, 'edit'])->name('admin.attendance.edit');
Route::post('/admin/attendance/{user_id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

// スタッフ一覧画面
Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list.index');

// スタッフ別勤怠一覧画面
Route::get('/admin/attendance/staff/1', [AdminStaffController::class, 'show'])->name('admin.attendance.staff.show');

// 申請一覧画面
Route::get('/admin/stamp_correction_request/list', [AdminRequestController::class, 'index'])->name('admin.stamp_correction_request.list.index');

// 修正申請承認画面
Route::get('/admin/stamp_correction_request/approve/1', [AdminRequestController::class, 'edit'])->name('admin.stamp_correction_request.approve.edit');
Route::post('/admin/stamp_correction_request/approve/{request_id}', [AdminRequestController::class, 'update'])->name('admin.stamp_correction_request.approve.update');

// ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
// });


// 一般ユーザー
//  メール認証通知の表示と送信
Route::get('/email/verify', function () {
    return view('auth.verify_email');
})->name('verification.notice');

// メール内のリンクをクリックしたときにアクセスされるもの
Route::get('/email/verify/{user_id}/{hash}', [AuthenticatedSessionController::class, 'verify'])
    // 署名付きURL
    ->middleware(['signed'])
    ->name('verification.verify');

// メール認証再送信
Route::post('/email/resend', function () {
    $user = Auth::user();

    if (!$user->hasVerifiedEmail()) {
        // メール認証の再送信
        $user->sendEmailVerificationNotification();
    }

    return back()->with('resent', true); // 再送信したことを通知
})->name('verification.send');

// ログイン済みかつメール認証済みのユーザーのみがアクセスできるルート（一般ユーザー）
// Route::middleware(['auth', 'verified'])->group(function () {
// 出勤登録画面
Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

// 勤怠一覧画面
Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list.index');

// 勤怠詳細画面
Route::get('/attendance/1', [AttendanceController::class, 'edit'])->name('attendance.edit');
Route::post('/attendance/{user_id}', [AttendanceController::class, 'update'])->name('attendance.update');

// 勤怠詳細画面（承認待ち）
Route::get('/attendance/pending/1', [AttendanceController::class, 'show'])->name('attendance.show');

// 申請一覧画面
Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('stamp_correction_request.list.index');

// ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('user.logout');
// });
