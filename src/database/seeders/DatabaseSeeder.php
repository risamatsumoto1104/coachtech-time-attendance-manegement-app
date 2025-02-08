<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 管理者作成
        $this->call(UsersTableSeeder::class);

        // ユーザーを作成
        $users = User::factory(10)->create();

        // 各ユーザーに対して勤怠データを作成
        $users->each(function ($user) {
            // 各ユーザーに1件ずつ勤怠を作成
            $attendances = Attendance::factory(1)->create(['user_id' => $user->user_id]);

            // 各勤怠データに2件ずつ休憩を作成
            $attendances->each(function ($attendance) {
                BreakTime::factory(2)->create([
                    'user_id' => $attendance->user_id,
                    'attendance_id' => $attendance->attendance_id
                ]);
            });
        });
    }
}
