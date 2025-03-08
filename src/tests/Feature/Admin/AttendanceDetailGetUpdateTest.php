<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailGetUpdateTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_the_contents_of_the_attendance_details_screen_match_the_selected_information()
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

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('admin.attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 詳細画面の内容が選択した情報と一致するか確認
        $response->assertSee($today);
        $response->assertSee('Test User');
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_is_before_clock_out()
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
        $attendance = Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('admin.attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 出勤時間を退勤時間より後に設定する
        // 保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 13:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => '勤怠修正',
        ]);

        //「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。']);
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_is_before_clock_out()
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
        $attendance = Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('admin.attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 休憩開始時間を退勤時間より後に設定する
        // 保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => '勤怠修正',
            'break_start' => [
                1 => $today . ' 13:00:00',
            ],
            'break_end' => [
                1 => $today . ' 11:30:00',
            ],
        ]);

        //「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['break_start.1' => '休憩時間が勤務時間外です。']);
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_is_before_clock_out()
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
        $attendance = Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('admin.attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 休憩終了時間を退勤時間より後に設定する
        // 保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => '勤怠修正',
            'break_start' => [
                1 => $today . ' 11:00:00',
            ],
            'break_end' => [
                1 => $today . ' 13:00:00',
            ],
        ]);

        //「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['break_end.1' => '休憩時間が勤務時間外です。']);
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_remarks_is_required()
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
        $attendance = Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('admin.attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 備考欄を未入力のまま保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => null,
        ]);

        //「備考を記入してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['remarks' => '備考を記入してください。']);
    }
}
