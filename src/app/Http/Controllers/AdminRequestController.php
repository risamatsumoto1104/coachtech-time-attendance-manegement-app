<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    // 申請一覧画面を表示（管理者）
    public function index(Request $request)
    {
        return view('admin.request.index');
    }

    // 修正申請承認画面の表示・更新（管理者）
    public function edit(Request $request)
    {
        return view('admin.request.edit');
    }

    //  修正申請承認画面の保存（管理者）
    public function update(Request $request)
    {
        return view('admin.request.edit');
    }
}
