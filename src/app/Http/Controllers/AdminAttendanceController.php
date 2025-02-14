<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面を表示（管理者）
    public function index(Request $request)
    {
        return view('admin.attendance.index');
    }

    // 勤怠詳細画面を表示・更新（管理者）
    public function edit(Request $request)
    {
        return view('admin.attendance.edit');
    }

    // 勤怠詳細画面を保存（管理者）
    public function update(AttendanceRequest $request)
    {
        return view('admin.attendance.update');
    }
}
