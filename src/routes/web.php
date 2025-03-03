<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    // ログイン（管理者）
    Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');

    // ログイン（一般ユーザー）
    Route::post('/register', [RegisterController::class, 'registered'])->name('user.register');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('user.login');
});


// 管理者
Route::middleware('verified_admin')->group(function () {
    // 勤怠一覧画面
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list.index');

    // スタッフ一覧画面
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list.index');

    // スタッフ別勤怠一覧画面
    Route::get('/admin/attendance/staff/{user_id}', [AdminStaffController::class, 'show'])->name('admin.attendance.staff.show');

    // エクスポート
    Route::get('/admin/attendance/staff/export-csv/{user_id}', [AdminStaffController::class, 'exportCsv'])->name('admin.attendance.staff.export.csv');

    // 勤怠詳細画面
    Route::get('/admin/attendance/{user_id}/{date}', [AdminAttendanceController::class, 'edit'])->name('admin.attendance.edit');
    Route::patch('/admin/attendance/{user_id}/{date}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    // 申請一覧画面
    Route::get('/admin/stamp_correction_request/list', [AdminRequestController::class, 'index'])->name('admin.stamp_correction_request.list.index');

    // 修正申請承認画面
    Route::get('/admin/stamp_correction_request/approve/{user_id}/{date}', [AdminRequestController::class, 'edit'])->name('admin.stamp_correction_request.approve.edit');
    Route::patch('/admin/stamp_correction_request/approve/{user_id}/{date}', [AdminRequestController::class, 'update'])->name('admin.stamp_correction_request.approve.update');

    // ログアウト
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});


// 一般ユーザー(ログインのみ)
Route::middleware(['user'])->group(function () {
    // ログインユーザーのみがアクセスできるルート
    Route::get('/email/verify', function () {
        return view('auth.verify_email');
    })->name('verification.notice');

    // メール内のリンクをクリックしたときにアクセスされるもの
    Route::get('/email/verify/{user_id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware('signed') // 署名付き URL を確認
        ->name('email.verify');

    // メール認証再送信
    Route::post('/email/resend', [VerifyEmailController::class, 'resendVerificationEmail']);
});


// 一般ユーザー
Route::middleware(['verified_user'])->group(function () {
    // 出勤登録画面
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list.index');

    // 勤怠詳細画面
    Route::get('/attendance/{user_id}/{date}', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::patch('/attendance/{user_id}/{date}', [AttendanceController::class, 'update'])->name('attendance.update');

    // 申請一覧画面
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('stamp_correction_request.list.index');

    // ログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('user.logout');
});
