<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ];
    }

    public function messages()
    {
        return [
            // 入力必須
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
            // メール形式
            'email.email' => 'メールアドレスはメール形式で入力してください',
            // 8文字以上
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }
}
