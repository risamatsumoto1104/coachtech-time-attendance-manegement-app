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

        // 出勤時間が退勤時間より前でない場合は、退勤時間を翌日に設定
        if ($clockIn > $clockOut) {
            $clockOut->modify('+1 day');
        }

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

        /// 不正な場合、1回だけ再生成を試みる
        if ($breakStart >= $breakEnd || $breakStart < $clockIn || $breakEnd > $clockOut) {
            // 1回だけ再生成
            $breakStart = $this->faker->dateTimeBetween($clockIn, $clockOut);
            $breakEnd = $this->faker->dateTimeBetween($breakStart, $clockOut);

            // 再生成後に依然として不正なら、そのままにしておく
            if ($breakStart >= $breakEnd || $breakStart < $clockIn || $breakEnd > $clockOut) {
                // ここで不正データを返すか、エラーハンドリングを行う
                return []; // null を返す代わりに空の配列を返す
            }
        }
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
