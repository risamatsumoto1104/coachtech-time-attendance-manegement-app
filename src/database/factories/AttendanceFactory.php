<?php

namespace Database\Factories;

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
        // 今日の開始時刻を取得
        $todayStart = now()->startOfDay();

        // 今日の終了時刻を取得
        $todayEnd = now()->endOfDay();

        // clock_in を今日の開始時刻から終了時刻の間でランダムに設定
        $clockIn = $this->faker->dateTimeBetween($todayStart, $todayEnd);

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
