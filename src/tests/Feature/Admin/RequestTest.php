<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    // 承認待ちの修正申請が全て表示されている
    public function test_displays_pending_correction_requests_for_all_users()
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
            'remarks' => '電車遅延の為',
        ]);

        // 申請情報を登録
        StampCorrectionRequest::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'status' => 'pending',
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
        $attendance2 = Attendance::create([
            'user_id' => $user2->user_id,
            'clock_in' => $today . ' 15:00:00',
            'clock_out' => $today . ' 18:00:00',
            'remarks' => '早退の為',
        ]);

        // 申請情報を登録
        StampCorrectionRequest::create([
            'user_id' => $user2->user_id,
            'attendance_id' => $attendance2->attendance_id,
            'status' => 'pending',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 申請一覧画面を開く
        $response = $this->get(route('admin.stamp_correction_request.list.index'));
        $response->assertStatus(200);

        //「承認待ち」タグを押下する
        $response = $this->get(route('admin.stamp_correction_request.list.index', ['tab' => 'pending']));
        $response->assertStatus(200);

        // 全ユーザーの未承認の修正申請が表示されるか確認
        $response->assertSee('Test User');
        $response->assertSee('電車遅延の為');
        $response->assertSee('Test2 User');
        $response->assertSee('早退の為');
    }

    // 承認済みの修正申請が全て表示されている
    public function test_displays_approved_correction_requests_for_all_users()
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
            'remarks' => '電車遅延の為',
        ]);

        // 申請情報を登録
        StampCorrectionRequest::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'status' => 'approved',
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
        $attendance2 = Attendance::create([
            'user_id' => $user2->user_id,
            'clock_in' => $today . ' 15:00:00',
            'clock_out' => $today . ' 18:00:00',
            'remarks' => '早退の為',
        ]);

        // 申請情報を登録
        StampCorrectionRequest::create([
            'user_id' => $user2->user_id,
            'attendance_id' => $attendance2->attendance_id,
            'status' => 'approved',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 申請一覧画面を開く
        $response = $this->get(route('admin.stamp_correction_request.list.index'));
        $response->assertStatus(200);

        //「承認済み」タグを押下する
        $response = $this->get(route('admin.stamp_correction_request.list.index', ['tab' => 'approved']));
        $response->assertStatus(200);

        // 全ユーザーの承認済みの修正申請が表示されるか確認
        $response->assertSee('Test User');
        $response->assertSee('電車遅延の為');
        $response->assertSee('Test2 User');
        $response->assertSee('早退の為');
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_the_requests_details_are_displayed_correctly()
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

        // 申請情報を登録
        StampCorrectionRequest::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'status' => 'pending',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 申請一覧画面を開く
        $response = $this->get(route('admin.stamp_correction_request.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す 修正申請承認画面へ
        $response = $this->get(route('admin.stamp_correction_request.approve.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // 申請内容が正しく表示されているか確認
        $response->assertSee('Test User');
        $response->assertSee($today);
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 修正申請の承認処理が正しく行われる
    public function test_correction_requests_are_approved_correctly()
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

        // 申請情報を登録
        StampCorrectionRequest::create([
            'user_id' => $user->user_id,
            'attendance_id' => $attendance->attendance_id,
            'status' => 'pending',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // 申請一覧画面を開く
        $response = $this->get(route('admin.stamp_correction_request.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す 修正申請承認画面へ
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

        // データベースが更新されたか確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->attendance_id,
            'user_id' => $user->user_id,
            'status' => 'approved',
        ]);
    }
}
