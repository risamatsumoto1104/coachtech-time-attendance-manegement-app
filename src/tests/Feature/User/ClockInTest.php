<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    // 出勤ボタンが正しく機能する
    public function test_the_clock_in_button_is_works_correctly()
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

        // ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 画面に「出勤」ボタンが表示されていることを確認する
        $response->assertSee('出勤');

        // 出勤の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // リダイレクト後の画面を再取得し、「出勤中」が表示されることを確認
        $response = $this->get(route('attendance.create'));
        $response->assertSee('出勤中');
    }

    // 出勤は一日一回のみできる
    public function test_clock_in_is_only_go_to_once_a_day()
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
        $attendance = Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 休憩を登録
        BreakTime::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => $today . ' 11:15:00',
        ]);

        // ステータスが退勤済のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 画面上に「出勤」ボタンが表示されないことを確認
        $response->assertDontSee('出勤');
    }

    // 出勤時刻が管理画面で確認できる
    public function test_accurately_display_your_work_time()
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

        // ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 現在の日付を取得
        $today = now()->toDateString();

        // 出勤の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'clock_in' => $today . ' 09:00:00',
        ]);
    }
}
