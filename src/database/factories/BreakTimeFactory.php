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

        return [
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}
