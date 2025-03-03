<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    // スタッフ一覧画面を表示（管理者）
    public function index(Request $request)
    {
        $users = User::where('role', 'user')->get();

        return view('admin.staff.index', compact('users'));
    }

    // スタッフ別勤怠一覧画面を表示
    public function show(Request $request)
    {
        // ルートから取得
        $userId = $request->route('user_id');

        // ユーザー情報を取得
        $user = User::where('user_id', $userId)->first();

        // クエリパラメータから日付を取得（デフォルトは今日の日付）
        $date = $request->query('date', date('Y-m'));

        // 日付オブジェクトを作成
        $currentDate = new \DateTime($date);
        $currentDateYearMonth = $currentDate->format('Y-m');

        $attendances = Attendance::with('user', 'breakTimes')
            ->where('user_id', $userId)
            ->whereYear('clock_in', date('Y', strtotime($currentDateYearMonth)))
            ->whereMonth('clock_in', date('m', strtotime($currentDateYearMonth)))
            ->orderBy('clock_in', 'asc') // 日付で昇順に並び変え
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

        return view('admin.staff.show', compact('user', 'currentDateYearMonth', 'attendances'));
    }

    public function exportCsv(Request $request, $user_id)
    {
        // ユーザー情報を取得
        $user = User::where('user_id', $user_id)->first();

        // クエリパラメータから日付を取得（デフォルトは今日の日付）
        $date = $request->query('date', date('Y-m'));

        // 日付オブジェクトを作成
        $currentDate = new \DateTime($date);
        $currentDateYearMonth = $currentDate->format('Y-m');
        $dateYearMonth = $currentDate->format('Ym');


        // 勤怠データを取得
        $attendances = Attendance::with('user', 'breakTimes')
            ->where('user_id', $user->user_id)
            ->whereYear('clock_in', date('Y', strtotime($currentDateYearMonth)))
            ->whereMonth('clock_in', date('m', strtotime($currentDateYearMonth)))
            ->orderBy('clock_in', 'asc')
            ->get()
            ->map(function ($attendance) {
                $timestamp = strtotime($attendance->clock_in);
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                $weekday = $weekdays[date('w', $timestamp)];
                $attendance->formatted_clock_in = date('n/j', $timestamp) . "（{$weekday}）";
                return $attendance;
            });

        // 休憩時間と勤務時間を計算
        $attendances->each(function ($attendance) {
            $clockIn = strtotime($attendance->clock_in);
            $clockOut = strtotime($attendance->clock_out);
            $secondsBreakTime = 0;

            foreach ($attendance->breakTimes as $breakTime) {
                $breakStart = strtotime($breakTime->break_start);
                $breakEnd = strtotime($breakTime->break_end);
                $secondsBreakTime += ($breakEnd - $breakStart);
            }

            $secondsWorkTime = ($clockOut - $clockIn - $secondsBreakTime);
            $hoursBreakTime = $secondsBreakTime / 3600;
            $hoursWorkTime = $secondsWorkTime / 3600;

            $breakHours = floor($hoursBreakTime);
            $breakMinutes = round(($hoursBreakTime - $breakHours) * 60);

            $workHours = floor($hoursWorkTime);
            $workMinutes = round(($hoursWorkTime - $workHours) * 60);

            $attendance->totalBreakTime = sprintf("%02d:%02d", $breakHours, $breakMinutes);
            $attendance->totalWorkTime = sprintf("%02d:%02d", $workHours, $workMinutes);
        });

        // ファイル名を設定
        $fileName = "{$dateYearMonth}_attendance_{$user->name}.csv";

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOMの追加（Excelでの文字化け防止）
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            // CSVにデータを書き込む
            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->formatted_clock_in,
                    substr($attendance->clock_in ?? '', 11, 5),
                    substr($attendance->clock_out ?? '', 11, 5),
                    $attendance->totalBreakTime ?? '',
                    $attendance->totalWorkTime ?? ''
                ]);
            }

            fclose($handle);
        });

        // CSVのヘッダー設定
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
