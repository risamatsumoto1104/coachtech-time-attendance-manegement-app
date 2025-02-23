<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    // 申請一覧画面を表示（管理者）
    public function index(Request $request)
    {
        // タブの状態
        $tab = $request->get('pending', 'approved');

        // status と一致するものを取得
        $stampCorrectionRequests = StampCorrectionRequest::with('user', 'attendance')
            ->where('status', $tab)
            ->get();

        return view('admin.request.index', compact('tab', 'stampCorrectionRequests'));
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
