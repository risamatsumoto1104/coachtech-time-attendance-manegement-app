<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    // スタッフ一覧画面を表示（管理者）
    public function index(Request $request)
    {
        return view('admin.staff.index');
    }

    // スタッフ別勤怠一覧画面を表示
    public function show(Request $request)
    {
        return view('admin.staff.show');
    }
}
