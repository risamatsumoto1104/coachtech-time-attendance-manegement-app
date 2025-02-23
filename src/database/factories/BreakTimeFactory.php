<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Attendanceテーブルからランダムに1件を取得
        $attendance = Attendance::inRandomOrder()->first();
        $clockIn = $attendance->clock_in;
        $clockOut = $attendance->clock_out;

        // break_start と break_end の作成
        // break_start を clock_in から clock_out の間でランダムに設定
        $breakStart = $this->faker->dateTimeBetween($clockIn, $clockOut);

        // break_end は break_start から1時間後
        $breakEnd = (clone $breakStart)->modify('+1 hours');

        // 秒を切り上げる処理
        $roundUpBreakStart = $this->roundUpToNextMinute($breakStart);
        $roundUpBreakEnd = $this->roundUpToNextMinute($breakEnd);

        return [
            'break_start' => $roundUpBreakStart,
            'break_end' => $roundUpBreakEnd,
        ];
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
