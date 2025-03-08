<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffGetTest extends TestCase
{
    use RefreshDatabase;

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_all_staff_names_and_emails_are_displayed_correctly()
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

        // スタッフ一覧画面を開く
        $response = $this->get(route('admin.staff.list.index'));
        $response->assertStatus(200);

        // 全ての一般ユーザーの氏名とメールアドレスが正しく表示されている
        $response->assertSee('Test User');
        $response->assertSee('test@example.com');
        $response->assertSee('Test2 User');
        $response->assertSee('test2@example.com');
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_user_attendance_information_is_displayed_accurately()
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

        // スタッフ一覧画面を開く
        $response = $this->get(route('admin.staff.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す スタッフ別勤怠一覧画面へ
        $response = $this->get(route('admin.attendance.staff.show', ['user_id' => $user->user_id]));
        $response->assertStatus(200);

        // 勤怠情報が正確に表示されるか確認
        $response->assertSee('Test User');
        $response->assertSee($today);
        $response->assertSee('09:00');
        $response->assertSee('12:00');
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_display_previous_month_information_when_previous_month_button_is_pressed()
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

        // 前月の日付を取得
        $previousMonth = date('Y-m-d', strtotime($today . ' -1 month'));

        // 年月を抽出
        $yearMonth = substr($previousMonth, 0, 7);

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
            'clock_in' => $previousMonth . ' 15:00:00',
            'clock_out' => $previousMonth . ' 18:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // スタッフ一覧画面を開く
        $response = $this->get(route('admin.staff.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す スタッフ別勤怠一覧画面へ
        $response = $this->get(route('admin.attendance.staff.show', ['user_id' => $user2->user_id]));
        $response->assertStatus(200);

        // 「前月」ボタンを押す
        $response = $this->get(route('admin.attendance.staff.show', ['user_id' => $user2->user_id, 'date' => $previousMonth]));
        $response->assertStatus(200);

        // 前月の情報が表示されているか確認
        $response->assertSee($yearMonth);
        $response->assertSee('Test2 User');
        $response->assertSee('15:00');
        $response->assertSee('18:00');
        $response->assertDontSee('Test User');
        $response->assertDontSee('09:00');
        $response->assertDontSee('12:00');
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_display_next_month_information_when_next_month_button_is_pressed()
    { // データベースをリセット
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
        $nextMonth = date('Y-m-d', strtotime($today . ' -1 month'));

        // 年月を抽出
        $yearMonth = substr($nextMonth, 0, 7);

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
            'clock_in' => $nextMonth . ' 15:00:00',
            'clock_out' => $nextMonth . ' 18:00:00',
        ]);

        // 管理者にログインする
        $this->actingAs($admin);

        // 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list.index'));
        $response->assertStatus(200);

        // スタッフ一覧画面を開く
        $response = $this->get(route('admin.staff.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す スタッフ別勤怠一覧画面へ
        $response = $this->get(route('admin.attendance.staff.show', ['user_id' => $user2->user_id]));
        $response->assertStatus(200);

        // 「翌月」ボタンを押す
        $response = $this->get(route('admin.attendance.staff.show', ['user_id' => $user2->user_id, 'date' => $nextMonth]));
        $response->assertStatus(200);

        // 翌月の情報が表示されているか確認
        $response->assertSee($yearMonth);
        $response->assertSee('Test2 User');
        $response->assertSee('15:00');
        $response->assertSee('18:00');
        $response->assertDontSee('Test User');
        $response->assertDontSee('09:00');
        $response->assertDontSee('12:00');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_clicking_details_will_take_you_to_the_attendance_details_screen_for_that_day()
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

        // スタッフ一覧画面を開く
        $response = $this->get(route('admin.staff.list.index'));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す スタッフ別勤怠一覧画面へ
        $response = $this->get(route('admin.attendance.staff.show', ['user_id' => $user->user_id]));
        $response->assertStatus(200);

        // 「詳細」ボタンを押す 勤怠詳細画面へ
        $response = $this->get(route('admin.attendance.edit', ['user_id' => $user->user_id, 'date' => $today]));
        $response->assertStatus(200);

        // その日の勤怠詳細画面に遷移するか確認
        $response->assertSee('勤怠詳細');
        $response->assertSee($today);
    }
}
