<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    // 退勤ボタンが正しく機能する
    public function test_the_clock_out_button_is_works_correctly()
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

        // 画面に「退勤」ボタンが表示されていることを確認する
        $response->assertSee('退勤');

        // 退勤の処理を行う
        $response = $this->post(route('attendance.store'), [
            'clock_out' => $today . ' 12:00:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 画面に「退勤済」が表示されていることを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertSee('退勤済');
    }

    // 退勤時刻が管理画面で確認できる
    public function test_accurately_display_your_checked_out_time()
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
            'clock_out' => null,
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // 退勤の処理を行う
        $response = $this->post(route('attendance.store'), [
            'clock_out' => $today . ' 12:00:00',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.create'));

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'clock_out' => $today . ' 12:00:00',
        ]);
    }
}
