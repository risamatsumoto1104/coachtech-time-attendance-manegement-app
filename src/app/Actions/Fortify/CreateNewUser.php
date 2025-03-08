<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // バリデーション処理
        Validator::make($input, [
            'name' => ['required'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                // 自動的に 'password_confirmation' を確認
                'confirmed',
            ],
        ], [
            // 入力必須
            'name.required' => 'お名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            // メール形式
            'email.email' => 'メールアドレスはメール形式で入力してください。',
            // 8文字以上
            'password.min' => 'パスワードは8文字以上で入力してください。',
            // 一致するか
            'password.confirmed' => 'パスワードと一致しません。',
        ])->validate();

        // ユーザーを作成し、そのインスタンスを返す
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
