<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListGetTest extends TestCase
{
    use RefreshDatabase;

    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_accurately_check_the_attendance_information_of_all_users_on_that_day()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        $admin = User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // ユーザーを登録する
        $user2 = User::create([
            'name' => 'Test2 User',
            'email' => 'test2@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password456'),
            'role' => 'user',
        ]);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user2->user_id,
            'clock_in' => $today . ' 15:00:00',
            'clock_out' => $today . ' 18:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // その日の全ユーザーの勤怠情報が正確な値になっているか確認
        $response->assertSee($today);
        $response->assertSee('Test User');
        $response->assertSee('09:00');
        $response->assertSee('12:00');
        $response->assertSee('Test2 User');
        $response->assertSee('15:00');
        $response->assertSee('18:00');
    }

    // 遷移した際に現在の日付が表示される
    public function test_the_current_date_is_displayed_when_transitioning()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        $admin = User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 勤怠一覧画面にその日の日付が表示されている
        $response->assertSee($today);
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_when_you_click_on_the_previous_day_the_attendance_information_for_the_previous_day_will_be_displayed()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        $admin = User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 前日の日付を取得
        $previousDay = date('Y-m-d', strtotime($today . ' -1 day'));

        // ユーザーを登録する
        $user2 = User::create([
            'name' => 'Test2 User',
            'email' => 'test2@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password456'),
            'role' => 'user',
        ]);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user2->user_id,
            'clock_in' => $previousDay . ' 15:00:00',
            'clock_out' => $previousDay . ' 18:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「前日」ボタンを押す
        $response = $this->get(route('admin.attendance.list.index', ['date' => $previousDay]));
        $response->assertStatus(200);

        // 前日の日付の勤怠情報が表示されるか確認（当日のデータは表示されない）
        $response->assertSee($previousDay);
        $response->assertSee('Test2 User');
        $response->assertSee('15:00');
        $response->assertSee('18:00');
        $response->assertDontSee('Test User');
        $response->assertDontSee('09:00');
        $response->assertDontSee('12:00');
    }

    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function test_when_you_click_on_the_next_day_the_attendance_information_for_the_next_day_will_be_displayed()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        $admin = User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 翌月の日付を取得
        $nextDay = date('Y-m-d', strtotime($today . ' -1 day'));

        // ユーザーを登録する
        $user2 = User::create([
            'name' => 'Test2 User',
            'email' => 'test2@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password456'),
            'role' => 'user',
        ]);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user2->user_id,
            'clock_in' => $nextDay . ' 15:00:00',
            'clock_out' => $nextDay . ' 18:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「翌月」ボタンを押す
        $response = $this->get(route('admin.attendance.list.index', ['date' => $nextDay]));
        $response->assertStatus(200);

        // 翌月の日付の勤怠情報が表示されるか確認（当日のデータは表示されない）
        $response->assertSee($nextDay);
        $response->assertSee('Test2 User');
        $response->assertSee('15:00');
        $response->assertSee('18:00');
        $response->assertDontSee('Test User');
        $response->assertDontSee('09:00');
        $response->assertDontSee('12:00');
    }
}
