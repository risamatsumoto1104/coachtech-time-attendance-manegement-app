<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTimeTest extends TestCase
{
    use RefreshDatabase;

    // 休憩ボタンが正しく機能する
    public function test_the_break_start_button_is_works_correctly()
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

        // ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 画面に「休憩入」ボタンが表示されていることを確認する
        $response->assertSee('休憩入');

        // 休憩の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 画面に「休憩中」が表示されていることを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertSee('休憩中');
    }

    // 休憩は一日に何回でもできる
    public function test_break_start_can_be_executed_multiple_times_a_day()
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

        // ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 休憩入の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 画面に「休憩戻」ボタンが表示されていることを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertSee('休憩戻');

        // 休憩戻の処理を行う
        $response = $this->post(route('attendance.store'), [
            'break_end' => $today . ' 11:15:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 画面に「休憩入」ボタンが表示されていることを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertSee('休憩入');
    }

    // 休憩戻ボタンが正しく機能する
    public function test_the_break_end_button_is_works_correctly()
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

        // ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 休憩入の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 休憩戻の処理を行う
        $response = $this->post(route('attendance.store'), [
            'break_end' => $today . ' 11:15:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 画面に「出勤中」が表示されていることを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertSee('出勤中');
    }

    // 休憩戻は一日に何回でもできる
    public function test_break_end_can_be_executed_multiple_times_a_day()
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

        // ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 休憩入の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 休憩戻の処理を行う
        $response = $this->post(route('attendance.store'), [
            'break_end' => $today . ' 11:15:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 再度、休憩入の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 15:00:00',
            'break_end' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 画面に「休憩戻」ボタンが表示されていることを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertSee('休憩戻');
    }

    // 休憩時刻が管理画面で確認できる
    public function test_accurately_display_your_break_time()
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

        // ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 休憩入の処理を行う
        $response = $this->post(route('attendance.store'), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 休憩戻の処理を行う
        $response = $this->post(route('attendance.store'), [
            'break_end' => $today . ' 11:15:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('break_times', [
            'break_start' => $today . ' 11:00:00',
            'break_end' => $today . ' 11:15:00',
        ]);
    }
}
