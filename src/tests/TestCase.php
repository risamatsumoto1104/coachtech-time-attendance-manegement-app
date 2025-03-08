<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    // テスト用のデータベースを初期化
    protected function resetDatabase()
    {
        $this->artisan('config:clear');
        $this->artisan('cache:clear');
        $this->artisan('route:clear');
        $this->artisan('view:clear');

        // 外部キー制約を無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // テーブルのデータをリセット（truncate）
        DB::table('users')->truncate();
        DB::table('attendances')->truncate();
        DB::table('break_times')->truncate();
        DB::table('stamp_correction_requests')->truncate();

        // 外部キー制約を再度有効化
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // データベースのauto_incrementをリセット
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE attendances AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE break_times AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE stamp_correction_requests AUTO_INCREMENT = 1;');
    }
}
