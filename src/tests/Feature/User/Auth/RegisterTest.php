<?php

namespace Tests\Feature\User\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // 名前が未入力の場合、バリデーションメッセージが表示される
    public function test_name_is_required()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 名前以外のユーザー情報を入力する
        // 会員登録の処理を行う
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 「お名前を入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください。']);
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // メールアドレス以外のユーザー情報を入力する
        // 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function test_password_is_min_8()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // パスワードを8文字未満にし、ユーザー情報を入力する
        // 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        // 「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください。']);
    }

    // パスワードが一致しない場合、バリデーションメッセージが表示される
    public function test_password_is_confirmed()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 確認用のパスワードとパスワードを一致させず、ユーザー情報を入力する
        // 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        // 「パスワードと一致しません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません。']);
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // パスワード以外のユーザー情報を入力する
        // 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    // フォームに内容が入力されていた場合、データが正常に保存される
    public function test_the_input_date_is_saved_successfully()
    {
        // データベースをリセット
        $this->resetDatabase();

        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // ユーザー情報を入力する
        // 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // データベースに登録されていることを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
