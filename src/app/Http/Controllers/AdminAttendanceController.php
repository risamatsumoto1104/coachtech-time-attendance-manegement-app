<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
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

        // clock_inの日付と一致するものを取得
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('clock_in', $currentDateFormatted)
            ->get();

        // 休憩時間と勤務時間を計算
        $attendances->each(function ($attendance) {
            // break_end が NULL でないかをチェック
            if (!is_null($attendance->clock_out)) {
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
            } else {
                // 退勤時間または休憩終了時間が無い場合
                $attendance->totalWorkTime = '00:00';
                $attendance->totalBreakTime = '00:00';
            }
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
            // clock_inの日付 が一致するものを取得
            ->whereDate('clock_in', $currentDateFormatted)
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

        // 日付を'Y-m-d'形式で文字列にフォーマット
        $currentDate = new \DateTime($date);
        $currentDateFormatted = $currentDate->format('Y-m-d');

        // 送信された日付を取得
        $sentDate = $request->input('current_date');

        $attendances = Attendance::with('user', 'breakTimes')
            // attendance_id が一致するものを取得
            ->where('attendance_id', $attendanceId)
            // user_id が一致するものを取得
            ->where('user_id', $userId)
            // clock_in が一致するものを取得
            ->whereDate('clock_in', $currentDateFormatted)
            ->get();

        // 日付が異なる場合
        if ($sentDate !== $currentDateFormatted) {
            // 送信された日付を使用
            $sentDateTime = (new \DateTime($sentDate))->format('Y-m-d');

            // 複数のテーブルをまとめて更新知るため、トランザクションを使用
            DB::beginTransaction();

            try {
                foreach ($attendances as $attendance) {

                    $validatedClockIn = $validated['clock_in'];
                    $validatedClockOut = $validated['clock_out'];
                    foreach ($attendance->breakTimes as $index => $breakTime) {
                        $validatedBreakStart = $validated['break_start'][$index];
                        $validatedBreakEnd = $validated['break_end'][$index];
                    }

                    $clockOutFormatted = (new \DateTime($validatedClockOut))->format('Y-m-d');
                    $breakStartFormatted = (new \DateTime($validatedBreakStart))->format('Y-m-d');
                    $breakEndFormatted = (new \DateTime($validatedBreakEnd))->format('Y-m-d');

                    // clock_inの日付再設定
                    $newClockIn = $sentDateTime . substr($validatedClockIn, 10);

                    // clock_outの日付再設定
                    $clockOutNewDate = $sentDateTime;
                    $clockOutNewDateTime = new \DateTime($clockOutNewDate);
                    if ($clockOutFormatted > $currentDateFormatted) {
                        $clockOutNewDateTime->modify('+1 day');
                    }
                    $clockOutNewDateFormatted = $clockOutNewDateTime->format('Y-m-d');
                    $newClockOut = $clockOutNewDateFormatted . substr($validatedClockOut, 10);

                    // 勤怠データを更新
                    $attendance->update([
                        'clock_in' => $newClockIn ?? $attendance->clock_in,
                        'clock_out' => $newClockOut ?? $attendance->clock_out,
                        'remarks' => $validated['remarks'] ?? null,
                    ]);

                    // break_startの日付再設定
                    $breakStartNewDate = $sentDateTime;
                    $breakStartNewDateTime = new \DateTime($breakStartNewDate);
                    if ($breakStartFormatted > $currentDateFormatted) {
                        $breakStartNewDateTime->modify('+1 day');
                    }
                    $breakStartNewDateFormatted = $breakStartNewDateTime->format('Y-m-d');
                    $newBreakStart = $breakStartNewDateFormatted . substr($validatedBreakStart, 10);

                    // break_endの日付再設定
                    $breakEndNewDate = $sentDateTime;
                    $breakEndNewDateTime = new \DateTime($breakEndNewDate);
                    if ($breakEndFormatted > $currentDateFormatted) {
                        $breakEndNewDateTime->modify('+1 day');
                    }
                    $breakStartNewDateFormatted = $breakEndNewDateTime->format('Y-m-d');
                    $newBreakEnd = $breakStartNewDateFormatted . substr($validatedBreakEnd, 10);

                    // 休憩データを更新
                    foreach ($attendance->breakTimes as $index => $breakTime) {
                        $breakTime->update([
                            'break_start' => $newBreakStart ?? $breakTime->break_start,
                            'break_end' => $newBreakEnd ?? $breakTime->break_end,
                        ]);
                    }
                }

                DB::commit();

                return redirect()->route('admin.attendance.list.index')->with('success', '勤怠データが更新されました。');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', '更新に失敗しました。');
            }
        } else {
            // 通常の日付を使用
            // 複数のテーブルをまとめて更新知るため、トランザクションを使用
            DB::beginTransaction();

            try {
                foreach ($attendances as $attendance) {

                    // 勤怠データを更新
                    $attendance->update([
                        'clock_in' => $validated['clock_in'] ?? null,
                        'clock_out' => $validated['clock_out'] ?? null,
                        'remarks' => $validated['remarks'] ?? null,
                    ]);

                    // 休憩データを更新
                    foreach ($attendance->breakTimes as $index => $breakTime) {
                        $breakTime->update([
                            'break_start' => $validated["break_start"][$index] ?? $breakTime->break_start,
                            'break_end' => $validated["break_end"][$index] ?? $breakTime->break_end,
                        ]);
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
}
