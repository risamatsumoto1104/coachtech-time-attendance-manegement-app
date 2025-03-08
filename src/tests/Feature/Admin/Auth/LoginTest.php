<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // メールアドレス以外のユーザー情報を入力する
        // ログインの処理を行う
        $response = $this->post('/admin/login', [
            'password' => 'admin1234',
        ]);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // パスワード以外のユーザー情報を入力する
        // ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
        ]);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_date_does_not_match()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 管理者を登録する
        User::create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
        ]);

        // ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // 誤ったメールアドレスのユーザー情報を入力する
        // ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => 'different@example.com',
            'password' => 'admin1234',
        ]);

        // 「ログイン情報が登録されていません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません。']);
    }
}
