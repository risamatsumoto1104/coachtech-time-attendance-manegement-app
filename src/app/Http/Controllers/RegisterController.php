<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function registered(Request $request)
    {
        // ユーザー作成
        $user = app(CreateNewUser::class)->create($request->all());

        // ユーザーをログイン状態にする
        Auth::login($user);

        // イベントを発火
        event(new Registered($user));

        // リダイレクト
        return redirect()->route('verification.notice');
    }
}
