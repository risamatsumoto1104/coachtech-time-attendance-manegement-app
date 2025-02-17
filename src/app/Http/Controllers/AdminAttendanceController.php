<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面を表示（管理者）
    public function index(Request $request)
    {
        // クエリパラメータから日付を取得（デフォルトは今日の日付）
        $date = $request->query('date', now()->toDateString());
        $currentDate = new \DateTime($date);

        // 日付を'Y-m-d'形式で文字列にフォーマット
        $currentDateFormatted = $currentDate->format('Y-m-d');

        // created_atの日付と一致するものを取得
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('created_at', $currentDateFormatted)
            ->get();

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

        return view('admin.attendance.index', compact('currentDateFormatted', 'attendances'));
    }

    // 勤怠詳細画面を表示・更新（管理者）
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
            // created_atの日付 が一致するものを取得
            ->whereDate('created_at', $date)
            ->get();

        return view('admin.attendance.edit', compact('userId', 'currentDateFormatted', 'attendances'));
    }

    // 勤怠詳細画面を保存（管理者）
    public function update(AttendanceRequest $request)
    {
        $attendanceId = $request->input('attendance_id');
        $userId = $request->input('user_id');
        $date = $request->route('date');

        // バリデーション済みデータ取得
        $validated = $request->validated();

        $attendances = Attendance::with('user', 'breakTimes')
            // attendance_id が一致するものを取得
            ->where('attendance_id', $attendanceId)
            // user_id が一致するものを取得
            ->where('user_id', $userId)
            // created_at が一致するものを取得
            ->whereDate('created_at', $date)
            ->get();

        // 複数のテーブルをまとめて更新知るため、トランザクションを使用
        DB::beginTransaction();

        try {
            foreach ($attendances as $attendance) {

                // 勤怠データを更新
                $attendance->update([
                    'clock_in' => $clockInDatetime,
                    'clock_out' => $clockOutDatetime,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                // 休憩時間の更新処理
                $breakStarts = $validated['break_start'] ?? [];
                $breakEnds = $validated['break_end'] ?? [];


                foreach ($breakStarts as $index => $start) {
                    if (!empty($start) && !empty($breakEnds[$index])) {
                        // 既存の休憩時間があれば更新、なければ作成
                        $breakTime = BreakTime::firstOrNew([
                            'attendance_id' => $attendance->id,
                            'break_start' => $startDatetime,
                        ]);
                        $breakTime->break_end = $endDatetime;
                        $breakTime->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.attendance.list.index')->with('success', '勤怠データが更新されました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '更新に失敗しました。');
        }
    }
}
