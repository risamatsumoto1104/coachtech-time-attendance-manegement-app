<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailGetTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_the_names_on_the_attendance_details_screen_match()
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

        // 名前がログインユーザーの名前になっているか確認
        $response->assertSee('Test User');
    }

    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function test_the_dates_on_the_attendance_details_screen_match()
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

        // 日付が選択した日付になっている
        $response->assertSee($today);
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_the_clock_in_and_clock_out_on_the_attendance_details_screen_match()
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

        // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致しているか確認
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_the_break_times_on_the_attendance_details_screen_match()
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

        // 出勤を登録
        BreakTime::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'break_start' => $today . ' 11:00:00',
            'break_end' => $today . ' 11:30:00',
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

        // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
        $response->assertSee('11:00');
        $response->assertSee('11:30');
    }
}
