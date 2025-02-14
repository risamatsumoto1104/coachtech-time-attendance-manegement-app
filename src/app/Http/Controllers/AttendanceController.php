<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // 出勤登録画面の新規作成（一般ユーザー）
    public function create(Request $request)
    {
        return view('attendance.create');
    }

    // 出勤登録画面の保存（一般ユーザー）
    public function store(Request $request)
    {
        return view('attendance.store');
    }

    // 勤怠一覧画面を表示（一般ユーザー）
    public function index(Request $request)
    {
        return view('attendance.index');
    }

    // 勤怠詳細画面を表示・更新（一般ユーザー）
    public function edit(Request $request)
    {
        return view('attendance.edit');
    }

    // 勤怠詳細画面の保存（一般ユーザー）
    public function update(AttendanceRequest $request)
    {
        return view('attendance.update');
    }

    // 勤怠詳細画面(承認待ち）を表示（一般ユーザー）
    public function show(Request $request)
    {
        return view('attendance.show');
    }
}
