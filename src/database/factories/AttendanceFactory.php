<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;


class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 過去一週間の開始時刻を取得（2日前から今日まで）
        $weekStart = now()->subDays(2)->startOfDay();
        $today = now()->endOfDay();

        // 過去一週間の日付を配列に取得
        $dates = [];
        for ($date = $weekStart; $date <= $today; $date->modify('+1 day')) {
            $dates[] = $date->format('Y-m-d');
        }

        // セッションに格納された選ばれた日付を取得
        $selectedDates = session('selected_dates', []); // セッションから取得。まだ選ばれていない場合は空配列

        // セッションが空の場合、初期値を設定（ダミーの日付を追加）
        if (empty($selectedDates)) {
            $selectedDates = ['2025-01-01'];  // ダミーの日付を設定
            session(['selected_dates' => $selectedDates]);  // セッションに保存
        }

        // 重複しない日付をランダムに選び、選ばれた日付を配列から削除
        $availableDates = array_diff($dates, $selectedDates); // 既に選ばれた日付を除外

        if (!empty($availableDates)) {
            // ランダムな日付を選択
            $randomDate = $availableDates[array_rand($availableDates)]; // ランダムに選ぶ

            // 選ばれた日付を保存
            $selectedDates[] = $randomDate;

            // セッションに保存
            session(['selected_dates' => $selectedDates]);
        } else {
            // すべての日付が選ばれている場合、今日の日付にリセットする
            $randomDate = date('Y-m-d'); // 今日の日付を設定

            // 選ばれた日付を保存
            $selectedDates[] = $randomDate;

            // セッションに保存
            session(['selected_dates' => $selectedDates]);
        }

        // clock_in を今日の開始時刻から終了時刻の間でランダムに設定
        $clockIn = $this->faker->dateTimeBetween($randomDate . '00:00:00', $randomDate . ' 14:59:59');

        // clock_out は clock_in から9時間後
        $clockOut = (clone $clockIn)->modify('+9 hours');

        // 秒を切り上げる処理
        $roundUpClockIn = $this->roundUpToNextMinute($clockIn);
        $roundUpClockOut = $this->roundUpToNextMinute($clockOut);

        return [
            'clock_in' => $roundUpClockIn,
            'clock_out' => $roundUpClockOut,
            'remarks' => '電車遅延の為'
        ];
    }

    // BreakTimeに関連付け
    public function configure()
    {
        return $this->afterCreating(function (Attendance $attendance) {
            // clock_in と clock_out を取得
            $clockIn = $attendance->clock_in;
            $clockOut = $attendance->clock_out;

            // break_start を clock_in から clock_out の間でランダムに設定
            $breakStart = $this->faker->dateTimeBetween($clockIn, $clockOut);

            // break_end は break_start から1時間後
            $breakEnd = (clone $breakStart)->modify('+1 hour');

            // break_end が clock_out を超えないように修正
            if ($breakEnd > $clockOut) {
                $breakEnd = $clockOut; // 退勤時間と同じに設定
            }

            // 秒を切り上げる処理
            $roundUpBreakStart = $this->roundUpToNextMinute($breakStart);
            $roundUpBreakEnd = $this->roundUpToNextMinute($breakEnd);

            // BreakTimeを作成
            BreakTime::create([
                'attendance_id' => $attendance->attendance_id,
                'user_id' => $attendance->user_id,
                'break_start' => $roundUpBreakStart,
                'break_end' => $roundUpBreakEnd,
            ]);
        });
    }

    // 秒を切り上げる処理
    private function roundUpToNextMinute($time)
    {
        // $timeがDateTimeオブジェクトの場合、文字列に変換
        if ($time instanceof \DateTime) {
            $time = $time->format('Y-m-d H:i:s');
        }

        // DateTime オブジェクトを生成
        $dateTime = new \DateTime($time);

        // 秒を切り上げて次の分にする
        if ($dateTime->format('s') > 0) {
            $dateTime->modify('+1 minute');
        }

        // 秒をゼロに設定
        $dateTime->setTime(
            $dateTime->format('H'),
            $dateTime->format('i'),
            0
        );

        return $dateTime;
    }
}
