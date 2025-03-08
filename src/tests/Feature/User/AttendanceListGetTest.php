<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListGetTest extends TestCase
{
    use RefreshDatabase;

    // 自分が行った勤怠情報が全て表示されている
    public function test_all_attendance_information_is_displayed()
    {
        // データベースをリセット
        $this->resetDatabase();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 勤怠情報が登録されたユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        // 自分の勤怠情報がすべて表示されていることを確認する
        $response->assertSee($today);
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_current_month_is_displayed_when_transitioning()
    {
        // データベースをリセット
        $this->resetDatabase();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 年月を抽出
        $yearMonth = substr($today, 0, 7);

        // ユーザーにログインをする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        // 現在の月が表示されている
        $response->assertSee($yearMonth);
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_display_previous_month_information_when_previous_month_button_is_pressed()
    {
        // データベースをリセット
        $this->resetDatabase();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 前月の日付を取得
        $previousMonth = date('Y-m-d', strtotime($today . ' -1 month'));

        // 年月を抽出
        $yearMonth = substr($previousMonth, 0, 7);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $previousMonth . ' 09:00:00',
            'clock_out' => $previousMonth . ' 12:00:00',
        ]);

        // 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        // 「前月」ボタンを押す
        $response = $this->get(route('attendance.list.index', ['date' => $yearMonth]));
        $response->assertStatus(200);

        // 前月の情報が表示されていることを確認
        $response->assertSee($previousMonth);
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_display_next_month_information_when_next_month_button_is_pressed()
    {
        // データベースをリセット
        $this->resetDatabase();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 翌月の日付を取得
        $nextMonth = date('Y-m-d', strtotime($today . ' +1 month'));

        // 年月を抽出
        $yearMonth = substr($nextMonth, 0, 7);

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $nextMonth . ' 09:00:00',
            'clock_out' => $nextMonth . ' 12:00:00',
        ]);

        // 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        // 「前月」ボタンを押す
        $response = $this->get(route('attendance.list.index', ['date' => $yearMonth]));
        $response->assertStatus(200);

        // 前月の情報が表示されていることを確認
        $response->assertSee($nextMonth);
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_clicking_details_will_take_you_to_the_attendance_details_screen_for_that_day()
    {
        // データベースをリセット
        $this->resetDatabase();

        // ユーザーを登録する
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 出勤を登録
        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 勤怠情報が登録されたユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // その日の勤怠詳細画面に遷移するか確認
        $response->assertSee('勤怠詳細');
        $response->assertSee($today);
    }
}
