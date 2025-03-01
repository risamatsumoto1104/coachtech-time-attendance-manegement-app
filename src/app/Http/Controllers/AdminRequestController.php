<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    // 申請一覧画面を表示（管理者）
    public function index(Request $request)
    {
        // 'user' を全て取得
        $users = User::where('role', 'user')->get();

        // ユーザーIDの配列を取得
        $userIds = $users->pluck('user_id')->toArray();

        $attendances = Attendance::with('user', 'stampCorrectionRequest')
            ->whereIn('user_id', $userIds)
            ->get();

        $attendances->each(function ($attendance) {
            $clockIn = $attendance->clock_in;
            $clockInDateTime = new \DateTime($clockIn);
            $attendance->formatted_clock_in = $clockInDateTime->format('Y/m/d');
            $attendance->date = $clockInDateTime->format('Y-m-d');

            if ($attendance->stampCorrectionRequest) {
                $requestCreatedAt = $attendance->stampCorrectionRequest->created_at;
                $requestCreatedAtDateTime = new \DateTime($requestCreatedAt);
                $attendance->formatted_created_at = $requestCreatedAtDateTime->format('Y/m/d');
            }
        });

        // 名前順、申請日時順に並び替え
        $attendances = $attendances->sortBy(function ($attendance) {
            return [$attendance->user->name, $attendance->formatted_created_at];
        });

        $tab = $request->get('tab', 'pending'); //デフォルト

        return view('admin.request.index', compact('attendances', 'tab'));
    }

    // 修正申請承認画面の表示・更新（管理者）
    public function edit(Request $request)
    {
        // ルートから取得
        $date = $request->route('date');
        $userId = $request->route('user_id');

        // 日付を'Y-m-d'形式で文字列にフォーマット
        $currentDate = new \DateTime($date);
        $currentDateFormatted = $currentDate->format('Y-m-d');

        $attendances = Attendance::with('user', 'breakTimes')
            // user_id が一致するものを取得
            ->where('user_id', $userId)
            // clock_inの日付 が一致するものを取得
            ->whereDate('clock_in', $currentDateFormatted)
            ->get();

        foreach ($attendances as $attendance) {
            $stampRequest = $attendance->stampCorrectionRequest;
        }

        return view('admin.request.edit', compact('userId', 'currentDateFormatted', 'attendances', 'stampRequest'));
    }

    //  修正申請承認画面の保存（管理者）
    public function update(Request $request)
    {
        $userId = $request->input('user_id');
        $attendanceId = $request->input('attendance_id');
        $date = $request->route('date');

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('clock_in', $date)
            ->first();

        if ($attendance) {
            $stampRequest = $attendance->stampCorrectionRequest()
                ->where('attendance_id', $attendanceId)
                ->first();

            if ($stampRequest) {
                if ($stampRequest->status === 'pending') {
                    if ($request->input('status')) {
                        $stampRequest->update([
                            'status' => 'approved',
                        ]);

                        $stampRequest->refresh();

                        return redirect()->route('admin.stamp_correction_request.list.index', ['tab' => 'approved']);
                    }
                } else {
                    return redirect()->back()->withErrors(['error' => '保存に失敗しました。']);
                }
            }
        }
    }
}
