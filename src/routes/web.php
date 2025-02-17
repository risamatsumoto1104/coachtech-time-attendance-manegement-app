<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    // ログイン（管理者）
    Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');

    // ログイン（一般ユーザー）
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('user.login');
});

// メール認証通知の表示と送信
Route::get('/email/verify', [AuthenticatedSessionController::class, 'showVerificationNotice'])->name('verification.notice');
// メール内のリンクをクリックしたときにアクセスされるもの
Route::middleware('signed')->group(function () {
    Route::get('/email/verify/{user_id}/{hash}', [AuthenticatedSessionController::class, 'verify'])->name('verification.verify');
});
// メール認証再送信
Route::post('/email/resend', [AuthenticatedSessionController::class, 'resendVerificationEmail'])->name('verification.send');


// 管理者
Route::middleware('verified_admin')->group(function () {
    // 勤怠一覧画面
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list.index');

    // 勤怠詳細画面
    Route::get('/admin/attendance/{user_id}/{date}', [AdminAttendanceController::class, 'edit'])->name('admin.attendance.edit');
    Route::patch('/admin/attendance/{user_id}/{date}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    // スタッフ一覧画面
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list.index');

    // スタッフ別勤怠一覧画面
    Route::get('/admin/attendance/staff/1', [AdminStaffController::class, 'show'])->name('admin.attendance.staff.show');

    // 申請一覧画面
    Route::get('/admin/stamp_correction_request/list', [AdminRequestController::class, 'index'])->name('admin.stamp_correction_request.list.index');

    // 修正申請承認画面
    Route::get('/admin/stamp_correction_request/approve/1', [AdminRequestController::class, 'edit'])->name('admin.stamp_correction_request.approve.edit');
    Route::patch('/admin/stamp_correction_request/approve/{request_id}', [AdminRequestController::class, 'update'])->name('admin.stamp_correction_request.approve.update');

    // ログアウト
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});

// 一般ユーザー
Route::middleware(['guest'])->group(function () {
    // 出勤登録画面
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list.index');

    // 勤怠詳細画面
    Route::get('/attendance/1', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::patch('/attendance/{user_id}', [AttendanceController::class, 'update'])->name('attendance.update');

    // 勤怠詳細画面（承認待ち）
    Route::get('/attendance/pending/1', [AttendanceController::class, 'show'])->name('attendance.show');

    // 申請一覧画面
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('stamp_correction_request.list.index');

    // ログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('user.logout');
});
