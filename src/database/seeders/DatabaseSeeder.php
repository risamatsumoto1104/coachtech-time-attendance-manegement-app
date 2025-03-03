<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;

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
            // ユーザーごとにセッションリセット
            session()->forget('selected_dates');

            Attendance::factory(2)->create(['user_id' => $user->user_id]);
        });
    }
}
