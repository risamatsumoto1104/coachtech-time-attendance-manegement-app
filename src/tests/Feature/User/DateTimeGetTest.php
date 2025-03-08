<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class DateTimeGetTest extends TestCase
{
    use RefreshDatabase;

    // 現在の日時情報がUIと同じ形式で出力されている
    public function test_matches_the_current_date_and_time()
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

        // ユーザーにログインをする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 現在の日時
        $formattedNow = now()->format('Y-m-d H:i');

        // 画面上に表示されている日時が現在の日時と一致するか確認
        $response->assertSee($formattedNow);
    }
}
