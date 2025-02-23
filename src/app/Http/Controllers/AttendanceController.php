<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // ログインしているユーザーを取得
        $user = Auth::user();

        // クエリパラメータから日付を取得（デフォルトは今日の日付）
        $date = $request->query('date', now()->toDateString());

        // 日付オブジェクトを作成
        $currentDate = new \DateTime($date);
        $currentDateYearMonth = $currentDate->format('Y-m');

        $attendances = Attendance::with('user', 'breakTimes')
            ->where('user_id', $user->user_id)
            ->whereYear('clock_in', date('Y', strtotime($currentDateYearMonth)))
            ->whereMonth('clock_in', date('m', strtotime($currentDateYearMonth)))
            ->get()
            ->map(function ($attendance) {
                $timestamp = strtotime($attendance->clock_in);
                $weekdays = ['日', '月', '火', '水', '木', '金', '土']; // 日本語の曜日
                $weekday = $weekdays[date('w', $timestamp)]; // 0=日曜日, 1=月曜日, ..., 6=土曜日
                $attendance->formatted_clock_in = date('n/j', $timestamp) . "（{$weekday}）";
                return $attendance;
            });

        // 休憩時間と勤務時間を計算
        $attendances->each(function ($attendance) {
            // 出勤時間と退勤時間を秒に変換
            $clockIn = strtotime($attendance->clock_in);
            $clockOut = strtotime($attendance->clock_out);

            // 休憩時間を初期化
            $secondsBreakTime = 0;

            // 休憩時間を計算
            foreach ($attendance->breakTimes as $breakTime) {
                // 休憩時間を秒に変換
                $breakStart = strtotime($breakTime->break_start);
                $breakEnd = strtotime($breakTime->break_end);

                $secondsBreakTime += ($breakEnd - $breakStart);
            }

            // 勤務時間を計算
            $secondsWorkTime = ($clockOut - $clockIn - $secondsBreakTime);

            // 休憩時間と勤務時間を秒から時間に戻す
            $hoursBreakTime = $secondsBreakTime / 3600;
            $hoursWorkTime = $secondsWorkTime / 3600;

            // 休憩時間を時間と分に分ける
            $breakHours = floor($hoursBreakTime);
            $breakMinutes = round(($hoursBreakTime - $breakHours) * 60);

            // 勤務時間を時間と分に分ける
            $workHours = floor($hoursWorkTime);
            $workMinutes = round(($hoursWorkTime - $workHours) * 60);

            // h:i形式に変換して保存
            $attendance->totalBreakTime = sprintf("%02d:%02d", $breakHours, $breakMinutes);
            $attendance->totalWorkTime = sprintf("%02d:%02d", $workHours, $workMinutes);
        });

        return view('attendance.index', compact('user', 'currentDateYearMonth', 'attendances'));
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
