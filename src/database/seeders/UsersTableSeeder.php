<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = [
            'name' => 'test admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin1234'),
            // 'email_verified_at' => now(),
            'role' => 'admin',
        ];

        DB::table('users')->insert($adminUser);
    }
}
