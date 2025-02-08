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
        $clockIn = $this->faker->dateTimeBetween('-1 month', 'now'); // 過去1ヶ月以内のランダムな出勤時間
        $clockOut = (clone $clockIn)->modify('+9 hours'); // 9時間後に退勤

        return [
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
