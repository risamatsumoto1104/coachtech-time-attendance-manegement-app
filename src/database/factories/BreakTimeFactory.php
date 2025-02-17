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
        // Attendance の出勤・退勤時間を取得
        $attendance = Attendance::inRandomOrder()->first();
        $clockIn = $attendance->clock_in;
        $clockOut = $attendance->clock_out;

        // 休憩開始と終了時間を出勤・退勤時間内に設定
        $breakStart = $this->faker->dateTimeBetween($clockIn, $clockOut);
        $breakEnd = $this->faker->dateTimeBetween($breakStart, $clockOut);

        // 秒を切り上げる処理
        $breakStart = $this->roundUpToNextMinute($breakStart);
        $breakEnd = $this->roundUpToNextMinute($breakEnd);

        return [
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
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
