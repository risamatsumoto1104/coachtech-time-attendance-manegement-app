<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_is_before_clock_out()
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

    // 修正申請処理が実行される
    public function test_correction_request_is_executed()
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

        // 勤怠詳細を修正し保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:30:00',
            'clock_out' => $today . ' 12:30:00',
            'remarks' => '勤怠修正',
            'break_start' => [
                1 => $today . ' 11:15:00',
            ],
            'break_end' => [
                1 => $today . ' 11:45:00',
            ],
        ]);

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'clock_in' => $today . ' 09:30:00',
            'clock_out' => $today . ' 12:30:00',
        ]);
        $this->assertDatabaseHas('break_times', [
            'break_start' => $today . ' 11:15:00',
            'break_end' => $today . ' 11:45:00',
        ]);
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'status' => 'pending',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));

        // ログアウト
        $response = $this->post(route('user.logout'));

        // 管理者を登録する
        $admin = User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く（管理者）
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 申請一覧画面を開く（管理者）
        $response = $this->get(route('admin.stamp_correction_request.list.index'));
        $response->assertStatus(200);

        // 修正申請したユーザーが表示されるか確認する
        $response->assertSee('Test User');

        // 「詳細」ボタンを押下する　修正申請承認画面へ（管理者）
        $response = $this->get(route('admin.stamp_correction_request.approve.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 修正申請したユーザーが表示されるか確認する
        $response->assertSee('Test User');
        $response->assertSee($today);
        $response->assertSee('09:30');
        $response->assertSee('12:30');
        $response->assertSee('11:15');
        $response->assertSee('勤怠修正');
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_all_applications_submitted_by_the_user_are_displayed_on_the_waiting_for_approval_screen()
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

        // 勤怠詳細を修正し保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => '勤怠修正',
        ]);

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'status' => 'pending',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));

        // 申請一覧画面を開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        //「承認待ち」タグを押下する
        $response = $this->get(route('stamp_correction_request.list.index', ['tab' => 'pending']));
        $response->assertStatus(200);

        // 申請一覧に自分の申請が表示されているか確認
        $response->assertSee('Test User');
        $response->assertSee('勤怠修正');
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_all_requests_approved_by_the_administrator_are_displayed_on_the_approved_screen()
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

        // 勤怠詳細を修正し保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => '勤怠修正',
        ]);

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'status' => 'pending',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));

        // ログアウト
        $response = $this->post(route('user.logout'));

        // 管理者を登録する
        $admin = User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く（管理者）
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 申請一覧画面を開く（管理者）
        $response = $this->get(route('admin.stamp_correction_request.list.index'));
        $response->assertStatus(200);

        //「詳細」ボタンを押下する　修正申請承認画面へ（管理者）
        $response = $this->get(route('admin.stamp_correction_request.approve.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        //「承認」ボタンを押下する
        $response = $this->patch(route('admin.stamp_correction_request.approve.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'status' => 'approved',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('admin.stamp_correction_request.list.index', ['tab' => 'approved']));

        // ログアウト（管理者）
        $response = $this->post(route('admin.logout'));

        // ユーザーに再ログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 申請一覧画面を開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        //「承認済み」タグを押下する
        $response = $this->get(route('stamp_correction_request.list.index', ['tab' => 'approved']));
        $response->assertStatus(200);

        // 申請一覧に自分の申請が表示されているか確認
        $response->assertSee('Test User');
        $response->assertSee('勤怠修正');
    }

    // 各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function test_clicking_details_will_take_you_to_the_correction_request_details_screen()
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

        // 勤怠情報が登録されたユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 勤怠一覧を開く
        $response = $this->get(route('attendance.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 勤怠詳細を修正し保存処理をする
        $response = $this->patch(route('attendance.update', ['user_id' => $user->user_id, 'date' => $today]), [
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'current_date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
            'remarks' => '勤怠修正',
        ]);

        // データベースに出勤が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 12:00:00',
        ]);
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'status' => 'pending',
        ]);

        // リダイレクトを確認
        $response->assertRedirect(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));

        // 申請一覧画面を開く
        $response = $this->get(route('stamp_correction_request.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押下する
        $response = $this->get(route('attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 申請詳細画面に遷移したか確認
        $response->assertViewIs('attendance.edit');
        $response->assertSee('勤怠詳細');
    }
}
