<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // 出勤登録画面の表示（一般ユーザー）
    public function create(Request $request)
    {
        // ログインしているユーザーを取得
        $user = Auth::user();

        $today = date('Y-m-d'); // 今日の日付のみ
        $date = date('Y-m-d H:i:s');
        $timestamp = strtotime($date);
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[date('w', $timestamp)];

        // セッションの日付を取得（初回は空なので null）
        $lastAccessDate = session('lastAccessDate');

        // 前回アクセス日と今日が異なればセッションをリセット
        if ($lastAccessDate !== $today) {
            session(['attendanceStatus' => 'before', 'lastAccessDate' => $today]);
        }

        // セッションからattendanceStatusを取得
        $attendanceStatus = session('attendanceStatus', 'before');

        // すでに今日の勤怠が登録されていたら、checked_out を表示
        $checkedOutAttendance = Attendance::where('user_id', $user->user_id)
            ->whereDate('clock_in', $today)
            ->whereDate('clock_out', $today)
            ->first();
        if ($checkedOutAttendance) {
            session(['attendanceStatus' => 'checked_out']);
            $attendanceStatus = 'checked_out';
        }

        // 出勤中にログアウトしてしまったら、working を表示
        $workingAttendance = Attendance::where('user_id', $user->user_id)
            ->whereDate('clock_in', $today)
            ->whereNull('clock_out')
            ->first();
        if ($workingAttendance) {
            session(['attendanceStatus' => 'working']);
            $attendanceStatus = 'working';
        }

        // 休憩中にログアウトしてしまったら、break を表示
        $workingBreakAttendance = Attendance::where('user_id', $user->user_id)
            ->whereDate('clock_in', $today)
            ->whereNull('clock_out')
            ->first();
        if ($workingBreakAttendance) {
            $breakTime = $workingBreakAttendance->breakTimes()
                ->where('attendance_id', $workingBreakAttendance->attendance_id)
                ->whereNull('break_end')
                ->first();
            if ($breakTime) {
                session(['attendanceStatus' => 'break']);
                $attendanceStatus = 'break';
            }
        }

        return view('attendance.create', compact('user', 'date', 'weekday', 'attendanceStatus'));
    }

    // 出勤登録画面の保存（一般ユーザー）
    public function store(Request $request)
    {
        // ログインしているユーザーを取得
        $user = Auth::user();

        // 現在の `attendanceStatus` を取得（なければ `before`）
        $attendanceStatus = session('attendanceStatus', 'before');

        // 出勤ボタンを押したとき、「勤務中」画面へ
        if ($request->input('clock_in')) {
            $clockIn = $request->input('clock_in');
            $clockInDateTime = new \DateTime($clockIn);

            // 秒を取得し、切り上げる
            $second = (int) $clockInDateTime->format('s');
            if ($second > 0) {
                $clockInDateTime->modify('+1 minute')->setTime(
                    (int) $clockInDateTime->format('H'),
                    (int) $clockInDateTime->format('i'),
                    0
                );
            }

            $clockInFormatted = $clockInDateTime->format('Y-m-d H:i:s');

            // Attendancesテーブルに新規保存
            $attendance = Attendance::create(
                [
                    'user_id' => $user->user_id,
                    'clock_in' => $clockInFormatted,
                    'clock_out' => null,
                    'remarks' => null,
                ]
            );

            // 更新後のデータを再取得
            $attendance->refresh();

            if ($attendance->clock_in) {
                session(['attendanceStatus' => 'working']);
                return redirect()->route('attendance.create');
            } else {
                return redirect()->back()->withErrors(['error' => '保存に失敗しました。']);
            }

            // 休憩入ボタンを押したとき、「休憩中」画面へ
        } elseif ($request->input('break_start')) {
            $breakStart = $request->input('break_start');
            $breakStartDateTime = new \DateTime($breakStart);

            $second = (int) $breakStartDateTime->format('s');
            if ($second > 0) {
                $breakStartDateTime->modify('+1 minute')->setTime(
                    (int) $breakStartDateTime->format('H'),
                    (int) $breakStartDateTime->format('i'),
                    0
                );
            }

            $breakStartFormatted = $breakStartDateTime->format('Y-m-d H:i:s');

            $attendance = Attendance::where('user_id', $user->user_id)
                ->whereNull('clock_out')
                ->first();

            if ($attendance) {
                // breakTimesテーブルへ新規保存
                $breakTime = $attendance->breakTimes()->create([
                    'user_id' => $user->user_id,
                    'attendance_id' => $attendance->attendance_id,
                    'break_start' => $breakStartFormatted,
                    'break_end' => null,
                ]);

                $breakTime->refresh();

                if ($breakTime->break_start) {
                    session(['attendanceStatus' => 'break']);
                    return redirect()->route('attendance.create');
                } else {
                    return redirect()->back()->withErrors(['error' => '保存に失敗しました。']);
                }
            }

            // 休憩戻ボタンを押したとき、「勤務中」画面へ
        } elseif ($request->input('break_end')) {
            $breakEnd = $request->input('break_end');
            $breakEndDateTime = new \DateTime($breakEnd);

            $second = (int) $breakEndDateTime->format('s');
            if ($second > 0) {
                $breakEndDateTime->modify('+1 minute')->setTime(
                    (int) $breakEndDateTime->format('H'),
                    (int) $breakEndDateTime->format('i'),
                    0
                );
            }

            $breakEndFormatted = $breakEndDateTime->format('Y-m-d H:i:s');

            $attendance = Attendance::where('user_id', $user->user_id)
                ->whereNull('clock_out')
                ->first();

            if ($attendance) {
                $breakTime = $attendance->breakTimes()
                    ->where('attendance_id', $attendance->attendance_id)
                    ->whereNull('break_end')
                    ->first();

                if ($breakTime) {
                    // breakTimesテーブルへ更新
                    $breakTime->update([
                        'break_end' => $breakEndFormatted,
                    ]);

                    $breakTime->refresh();

                    if ($breakTime->break_end) {
                        session(['attendanceStatus' => 'working']);
                        return redirect()->route('attendance.create');
                    } else {
                        return redirect()->back()->withErrors(['error' => '保存に失敗しました。']);
                    }
                }
            }

            // 退勤ボタンを押したとき、「退勤済」画面へ
        } elseif ($request->input('clock_out')) {
            $clockOut = $request->input('clock_out');
            $clockOutDateTime = new \DateTime($clockOut);

            $second = (int) $clockOutDateTime->format('s');
            if ($second > 0) {
                $clockOutDateTime->modify('+1 minute')->setTime(
                    (int) $clockOutDateTime->format('H'),
                    (int) $clockOutDateTime->format('i'),
                    0
                );
            }

            $clockOutFormatted = $clockOutDateTime->format('Y-m-d H:i:s');

            $attendance = Attendance::where('user_id', $user->user_id)
                ->whereNull('clock_out')
                ->first();

            if ($attendance) {
                // Attendancesテーブルへ更新
                $attendance->update([
                    'clock_out' => $clockOutFormatted,
                ]);

                $attendance->refresh();

                if ($attendance->clock_out) {
                    session(['attendanceStatus' => 'checked_out']);
                    return redirect()->route('attendance.create');
                } else {
                    return redirect()->back()->withErrors(['error' => '保存に失敗しました。']);
                }
            }
        }

        // 状態をセッションに保存
        session(['attendanceStatus' => $attendanceStatus]);
        return redirect()->route('attendance.create');
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

        return view('attendance.index', compact('user', 'currentDateYearMonth', 'attendances'));
    }

    // 勤怠詳細画面を表示・更新（一般ユーザー）
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

        // フラグを事前に計算
        $hasPendingRequest = false;
        $hasApprovedRequest = false;

        foreach ($attendances as $attendance) {
            $stampRequest = $attendance->stampCorrectionRequest;
            if ($stampRequest) {
                if ($stampRequest->status === 'pending') {
                    $hasPendingRequest = true;
                } elseif ($stampRequest->status === 'approved') {
                    $hasApprovedRequest = true;
                }
            }
        }

        return view('attendance.edit', compact('userId', 'currentDateFormatted', 'attendances', 'hasPendingRequest', 'hasApprovedRequest'));
    }

    // 勤怠詳細画面の保存（一般ユーザー）
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
                    $validatedRemarks = $validated['remarks'];
                    foreach ($attendance->breakTimes as $index => $breakTime) {
                        $validatedBreakStart = $validated['break_start'][$index + 1];
                        $validatedBreakEnd = $validated['break_end'][$index + 1];
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
                        'remarks' => $validatedRemarks ?? null,
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

                    // 再取得
                    $attendance->refresh();

                    // 承認待ち状態にする
                    if ($validatedRemarks) {
                        $attendance->stampCorrectionRequest()->create([
                            'user_id' => $attendance->user_id,
                            'attendance_id' => $attendance->attendance_id,
                            'status' => 'pending',
                        ]);
                    }
                }

                DB::commit();

                return redirect()->route('attendance.edit', ['user_id' => $userId, 'date' => $date])->with('success', '勤怠データが更新されました。');
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

                    $validatedRemarks = $validated['remarks'];

                    // 勤怠データを更新
                    $attendance->update([
                        'clock_in' => $validated['clock_in'] ?? null,
                        'clock_out' => $validated['clock_out'] ?? null,
                        'remarks' => $validatedRemarks ?? null,
                    ]);

                    // 休憩データを更新
                    foreach ($attendance->breakTimes as $index => $breakTime) {
                        $breakTime->update([
                            'break_start' => $validated["break_start"][$index + 1] ?? $breakTime->break_start,
                            'break_end' => $validated["break_end"][$index + 1] ?? $breakTime->break_end,
                        ]);
                    }

                    // 再取得
                    $attendance->refresh();

                    // 承認待ち状態にする
                    if ($validatedRemarks) {
                        $attendance->stampCorrectionRequest()->create([
                            'user_id' => $attendance->user_id,
                            'attendance_id' => $attendance->attendance_id,
                            'status' => 'pending',
                        ]);
                    }
                }

                DB::commit();

                return redirect()->route('attendance.edit', ['user_id' => $userId, 'date' => $date])->with('success', '勤怠データが更新されました。');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', '更新に失敗しました。');
            }
        }
    }
}
