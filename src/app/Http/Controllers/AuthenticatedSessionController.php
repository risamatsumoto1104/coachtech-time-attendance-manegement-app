<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // ログイン処理
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 管理者ログイン
        if ($request->route()->getName() === 'admin.login') {
            if (Auth::attempt($credentials)) {
                // ログイン後、勤怠一覧画面へ
                return redirect()->route('admin.attendance.list.index');
            }
            // 認証失敗
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません。',
                'password' => 'ログイン情報が登録されていません。',
            ]);
        }

        // 一般ユーザーログイン
        if (Auth::attempt($credentials)) {
            // ログイン後、勤怠登録画面へ
            return redirect()->route('attendance.create');
        }
        // 認証失敗
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
            'password' => 'ログイン情報が登録されていません。',
        ]);
    }

    // ログアウト処理
    public function destroy(Request $request)
    {
        // ログインしているユーザーを取得
        $user = Auth::user();

        // 管理者か一般ユーザーかを判断
        if ($user && $user->role === 'admin') {
            // 管理者をログアウト
            Auth::logout();
            // セッションを無効化
            $request->session()->invalidate();
            // CSRFトークンを再生成
            $request->session()->regenerateToken();

            // ログアウト後
            return redirect()->route('admin.login');
        }

        // 一般ユーザーをログアウト
        Auth::logout();
        // セッションを無効化
        $request->session()->invalidate();
        // CSRFトークンを再生成
        $request->session()->regenerateToken();

        // ログアウト後
        return redirect()->route('user.login');
    }
}
