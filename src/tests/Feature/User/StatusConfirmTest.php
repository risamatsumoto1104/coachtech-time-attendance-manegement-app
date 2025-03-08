<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusConfirmTest extends TestCase
{
    use RefreshDatabase;

    // 勤務外の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_is_before()
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

        // 画面に表示されているステータスを確認する
        $response->assertSee('勤務外');
    }

    // 出勤中の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_is_working()
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
            'clock_out' => null,
        ]);

        // ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 画面に表示されているステータスを確認する
        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_is_break()
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
            'clock_out' => null,
        ]);

        // 休憩を登録
        BreakTime::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => null,
        ]);

        // ステータスが休憩中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 画面に表示されているステータスを確認する
        $response->assertSee('休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_is_checked_out()
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

        // 画面に表示されているステータスを確認する
        $response->assertSee('退勤済');
    }
}
